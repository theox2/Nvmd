<?php
/**
 * api/admin/pedidos/listar.php - Listar Pedidos para Admin
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../../../config.php';

try {
    $stmt = $pdo->query("
        SELECT 
            p.id,
            p.numero_pedido,
            p.data_pedido,
            p.status,
            p.total,
            u.nome as cliente_nome,
            u.email as cliente_email,
            COUNT(pi.id) as total_itens
        FROM pedidos p
        JOIN usuarios u ON p.usuario_id = u.id
        LEFT JOIN pedido_itens pi ON p.id = pi.pedido_id
        GROUP BY p.id
        ORDER BY p.data_pedido DESC
    ");
    
    $pedidos = $stmt->fetchAll();
    
    foreach ($pedidos as &$pedido) {
        $pedido['total'] = (float)$pedido['total'];
        $pedido['total_itens'] = (int)$pedido['total_itens'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $pedidos
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>