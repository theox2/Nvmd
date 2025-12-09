<?php
/**
 * api/carrinho/remover.php - Remover item do carrinho
 * Método: POST/DELETE
 * Body: { usuario_id?, sessao_id?, item_id?, produto_id?, limpar_tudo? }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'DELETE'])) {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Apenas POST ou DELETE permitido']));
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    // ==========================================
    // IDENTIFICAR CARRINHO
    // ==========================================
    
    $usuario_id = $input['usuario_id'] ?? null;
    $sessao_id = $input['sessao_id'] ?? null;
    $item_id = $input['item_id'] ?? null;
    $produto_id = $input['produto_id'] ?? null;
    $limpar_tudo = $input['limpar_tudo'] ?? false;
    
    if (!$usuario_id && !$sessao_id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'usuario_id ou sessao_id é obrigatório'
        ]);
        exit;
    }
    
    // ==========================================
    // BUSCAR CARRINHO
    // ==========================================
    
    if ($usuario_id) {
        $stmt = $pdo->prepare("SELECT id FROM carrinhos WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM carrinhos WHERE sessao_id = ?");
        $stmt->execute([$sessao_id]);
    }
    
    $carrinho = $stmt->fetch();
    
    if (!$carrinho) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Carrinho não encontrado'
        ]);
        exit;
    }
    
    $carrinho_id = $carrinho['id'];
    
    // ==========================================
    // LIMPAR TODO O CARRINHO
    // ==========================================
    
    if ($limpar_tudo) {
        $stmt = $pdo->prepare("DELETE FROM carrinho_itens WHERE carrinho_id = ?");
        $stmt->execute([$carrinho_id]);
        
        $itens_removidos = $stmt->rowCount();
        
        echo json_encode([
            'success' => true,
            'message' => 'Carrinho limpo com sucesso',
            'itens_removidos' => $itens_removidos,
            'totais' => [
                'total_itens' => 0,
                'total_quantidade' => 0,
                'subtotal' => 0.00
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // ==========================================
    // REMOVER ITEM ESPECÍFICO
    // ==========================================
    
    if (!$item_id && !$produto_id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'item_id ou produto_id é obrigatório'
        ]);
        exit;
    }
    
    // Remover por item_id (mais preciso)
    if ($item_id) {
        $stmt = $pdo->prepare("
            DELETE FROM carrinho_itens 
            WHERE id = ? AND carrinho_id = ?
        ");
        $stmt->execute([$item_id, $carrinho_id]);
        
    } 
    // Remover por produto_id (remove todos os itens desse produto)
    else {
        $stmt = $pdo->prepare("
            DELETE FROM carrinho_itens 
            WHERE produto_id = ? AND carrinho_id = ?
        ");
        $stmt->execute([$produto_id, $carrinho_id]);
    }
    
    $itens_removidos = $stmt->rowCount();
    
    if ($itens_removidos === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Item não encontrado no carrinho'
        ]);
        exit;
    }
    
    // ==========================================
    // CALCULAR NOVOS TOTAIS
    // ==========================================
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_itens,
            SUM(quantidade) as total_quantidade,
            SUM(quantidade * preco_unitario) as subtotal
        FROM carrinho_itens
        WHERE carrinho_id = ?
    ");
    $stmt->execute([$carrinho_id]);
    $totais = $stmt->fetch();
    
    // ==========================================
    // RESPOSTA
    // ==========================================
    
    echo json_encode([
        'success' => true,
        'message' => 'Item(ns) removido(s) com sucesso',
        'itens_removidos' => $itens_removidos,
        'totais' => [
            'total_itens' => (int)$totais['total_itens'],
            'total_quantidade' => (int)($totais['total_quantidade'] ?? 0),
            'subtotal' => (float)($totais['subtotal'] ?? 0.00)
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao remover do carrinho',
        'error' => $e->getMessage()
    ]);
}
?>