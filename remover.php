<?php
/**
 * api/favoritos/remover.php - Remover produto dos favoritos
 * Método: POST/DELETE
 * Body: { usuario_id, produto_id }
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
    
    // Remover dos favoritos
    $stmt = $pdo->prepare("
        DELETE FROM favoritos 
        WHERE usuario_id = ? AND produto_id = ?
    ");
    $stmt->execute([$usuario_id, $produto_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Produto removido dos favoritos'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Produto não estava nos favoritos'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao remover favorito',
        'error' => $e->getMessage()
    ]);
}
?>