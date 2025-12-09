<?php
/**
 * api/carrinho/listar.php - Listar itens do carrinho
 * Método: GET
 * Params: ?usuario_id=1 OU ?sessao_id=abc123
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../../config.php';

try {
    // ==========================================
    // IDENTIFICAR CARRINHO
    // ==========================================
    
    $usuario_id = $_GET['usuario_id'] ?? null;
    $sessao_id = $_GET['sessao_id'] ?? null;
    
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
        $stmt = $pdo->prepare("SELECT id, data_criacao FROM carrinhos WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
    } else {
        $stmt = $pdo->prepare("SELECT id, data_criacao FROM carrinhos WHERE sessao_id = ?");
        $stmt->execute([$sessao_id]);
    }
    
    $carrinho = $stmt->fetch();
    
    // Se não existe carrinho, retornar vazio
    if (!$carrinho) {
        echo json_encode([
            'success' => true,
            'data' => [
                'itens' => [],
                'totais' => [
                    'total_itens' => 0,
                    'total_quantidade' => 0,
                    'subtotal' => 0.00
                ]
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $carrinho_id = $carrinho['id'];
    
    // ==========================================
    // BUSCAR ITENS DO CARRINHO
    // ==========================================
    
    $stmt = $pdo->prepare("
        SELECT 
            ci.id as item_id,
            ci.produto_id,
            ci.quantidade,
            ci.tamanho,
            ci.cor,
            ci.preco_unitario,
            ci.data_adicao,
            p.nome as produto_nome,
            p.slug as produto_slug,
            p.imagem_principal,
            p.estoque,
            p.ativo,
            (ci.quantidade * ci.preco_unitario) as subtotal_item
        FROM carrinho_itens ci
        JOIN produtos p ON ci.produto_id = p.id
        WHERE ci.carrinho_id = ?
        ORDER BY ci.data_adicao DESC
    ");
    
    $stmt->execute([$carrinho_id]);
    $itens = $stmt->fetchAll();
    
    // ==========================================
    // PROCESSAR ITENS
    // ==========================================
    
    $itens_processados = [];
    $total_quantidade = 0;
    $subtotal_carrinho = 0;
    
    foreach ($itens as $item) {
        // Verificar se produto ainda está disponível
        $disponivel = $item['ativo'] && $item['estoque'] > 0;
        $estoque_suficiente = $item['estoque'] >= $item['quantidade'];
        
        $item_data = [
            'item_id' => (int)$item['item_id'],
            'produto_id' => (int)$item['produto_id'],
            'produto_nome' => $item['produto_nome'],
            'produto_slug' => $item['produto_slug'],
            'imagem' => $item['imagem_principal'],
            'preco_unitario' => (float)$item['preco_unitario'],
            'quantidade' => (int)$item['quantidade'],
            'tamanho' => $item['tamanho'],
            'cor' => $item['cor'],
            'subtotal' => (float)$item['subtotal_item'],
            'estoque_disponivel' => (int)$item['estoque'],
            'disponivel' => $disponivel,
            'estoque_suficiente' => $estoque_suficiente,
            'data_adicao' => $item['data_adicao']
        ];
        
        // Adicionar avisos se necessário
        if (!$disponivel) {
            $item_data['aviso'] = 'Produto não disponível';
        } elseif (!$estoque_suficiente) {
            $item_data['aviso'] = "Apenas {$item['estoque']} unidade(s) disponível(eis)";
        }
        
        $itens_processados[] = $item_data;
        
        // Somar totais (apenas produtos disponíveis)
        if ($disponivel && $estoque_suficiente) {
            $total_quantidade += (int)$item['quantidade'];
            $subtotal_carrinho += (float)$item['subtotal_item'];
        }
    }
    
    // ==========================================
    // CALCULAR FRETE (SIMULADO)
    // ==========================================
    
    $frete = 0;
    $frete_gratis_a_partir = 199.00;
    
    if ($subtotal_carrinho > 0 && $subtotal_carrinho < $frete_gratis_a_partir) {
        // Frete fixo de R$ 15
        $frete = 15.00;
    }
    
    $total_com_frete = $subtotal_carrinho + $frete;
    
    // ==========================================
    // RESPOSTA
    // ==========================================
    
    echo json_encode([
        'success' => true,
        'data' => [
            'carrinho_id' => (int)$carrinho_id,
            'data_criacao' => $carrinho['data_criacao'],
            'itens' => $itens_processados,
            'totais' => [
                'total_itens' => count($itens_processados),
                'total_quantidade' => $total_quantidade,
                'subtotal' => $subtotal_carrinho,
                'frete' => $frete,
                'frete_gratis_a_partir' => $frete_gratis_a_partir,
                'frete_gratis' => $frete === 0,
                'total' => $total_com_frete
            ]
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar carrinho',
        'error' => $e->getMessage()
    ]);
}
?>