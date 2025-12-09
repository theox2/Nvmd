<?php
/**
 * api/favoritos/adicionar.php - Adicionar produto aos favoritos
 * Método: POST
 * Body: { usuario_id, produto_id }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Apenas POST permitido']));
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    $usuario_id = $input['usuario_id'] ?? null;
    $produto_id = $input['produto_id'] ?? null;
    
    if (!$usuario_id || !$produto_id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'usuario_id e produto_id são obrigatórios'
        ]);
        exit;
    }
    
    // Verificar se produto existe
    $stmt = $pdo->prepare("SELECT id, nome FROM produtos WHERE id = ?");
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch();
    
    if (!$produto) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Produto não encontrado'
        ]);
        exit;
    }
    
    // Verificar se já está nos favoritos
    $stmt = $pdo->prepare("
        SELECT id FROM favoritos 
        WHERE usuario_id = ? AND produto_id = ?
    ");
    $stmt->execute([$usuario_id, $produto_id]);
    
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Produto já está nos favoritos'
        ]);
        exit;
    }
    
    // Adicionar aos favoritos
    $stmt = $pdo->prepare("
        INSERT INTO favoritos (usuario_id, produto_id)
        VALUES (?, ?)
    ");
    $stmt->execute([$usuario_id, $produto_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Produto adicionado aos favoritos',
        'produto' => [
            'id' => (int)$produto['id'],
            'nome' => $produto['nome']
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao adicionar favorito',
        'error' => $e->getMessage()
    ]);
}
?>