<?php
/**
 * api/produtos/detalhes.php - Detalhes de um Produto
 * Uso: /api/produtos/detalhes.php?id=1
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../../config.php';

try {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ID do produto é obrigatório'
        ]);
        exit;
    }
    
    // Buscar produto
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            c.nome as categoria_nome,
            c.slug as categoria_slug,
            ROUND(COALESCE((SELECT AVG(nota) FROM avaliacoes WHERE produto_id = p.id AND aprovada = 1), 0), 1) as nota_media,
            (SELECT COUNT(*) FROM avaliacoes WHERE produto_id = p.id AND aprovada = 1) as total_avaliacoes
        FROM produtos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        WHERE p.id = ?
    ");
    
    $stmt->execute([$id]);
    $produto = $stmt->fetch();
    
    if (!$produto) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Produto não encontrado'
        ]);
        exit;
    }
    
    // Buscar tamanhos
    $stmt = $pdo->prepare("SELECT tamanho, estoque FROM produto_tamanhos WHERE produto_id = ? ORDER BY id");
    $stmt->execute([$id]);
    $produto['tamanhos'] = $stmt->fetchAll();
    
    // Buscar cores
    $stmt = $pdo->prepare("SELECT cor, codigo_hex FROM produto_cores WHERE produto_id = ? ORDER BY id");
    $stmt->execute([$id]);
    $produto['cores'] = $stmt->fetchAll();
    
    // Buscar imagens
    $stmt = $pdo->prepare("SELECT url FROM produto_imagens WHERE produto_id = ? ORDER BY ordem");
    $stmt->execute([$id]);
    $imagens = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Adicionar imagem principal
    if ($produto['imagem_principal']) {
        array_unshift($imagens, $produto['imagem_principal']);
    }
    $produto['imagens'] = $imagens;
    
    // Buscar avaliações
    $stmt = $pdo->prepare("
        SELECT 
            a.*,
            u.nome as usuario_nome
        FROM avaliacoes a
        JOIN usuarios u ON a.usuario_id = u.id
        WHERE a.produto_id = ? AND a.aprovada = 1
        ORDER BY a.data_avaliacao DESC
        LIMIT 20
    ");
    $stmt->execute([$id]);
    $avaliacoes = $stmt->fetchAll();
    
    // Buscar imagens das avaliações
    foreach ($avaliacoes as &$avaliacao) {
        $stmt = $pdo->prepare("SELECT url FROM avaliacao_imagens WHERE avaliacao_id = ?");
        $stmt->execute([$avaliacao['id']]);
        $avaliacao['imagens'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    $produto['avaliacoes'] = $avaliacoes;
    
    // Produtos relacionados (mesma categoria)
    $stmt = $pdo->prepare("
        SELECT id, nome, preco, preco_antigo, imagem_principal, estoque
        FROM produtos
        WHERE categoria_id = ? AND id != ? AND ativo = 1
        ORDER BY RAND()
        LIMIT 4
    ");
    $stmt->execute([$produto['categoria_id'], $id]);
    $produto['produtos_relacionados'] = $stmt->fetchAll();
    
    // Formatar valores
    $produto['preco'] = (float)$produto['preco'];
    $produto['preco_antigo'] = $produto['preco_antigo'] ? (float)$produto['preco_antigo'] : null;
    $produto['estoque'] = (int)$produto['estoque'];
    $produto['nota_media'] = (float)$produto['nota_media'];
    $produto['total_avaliacoes'] = (int)$produto['total_avaliacoes'];
    
    // Resposta
    echo json_encode([
        'success' => true,
        'data' => $produto
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro no servidor',
        'error' => $e->getMessage()
    ]);
}
?>