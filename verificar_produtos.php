<?php
/**
 * verificar_produtos.php - Verificar produtos no banco
 * Salve na RAIZ: /Novamoda/verificar_produtos.php
 * Acesse: http://localhost/Novamoda/verificar_produtos.php
 */

require_once 'config.php';

echo "<style>
body { font-family: Arial; background: #0a0a0a; color: #fff; padding: 20px; }
h1 { color: #14d0d6; }
.success { color: #4ade80; }
.error { color: #ff3b30; }
.warning { color: #fbbf24; }
table { width: 100%; border-collapse: collapse; margin: 20px 0; background: #111; }
th, td { padding: 12px; text-align: left; border: 1px solid #333; }
th { background: #14d0d6; color: #000; font-weight: bold; }
.btn { background: #14d0d6; color: #000; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; text-decoration: none; display: inline-block; margin: 10px 5px; }
.btn:hover { background: #0ea5e9; }
</style>";

echo "<h1>🔍 VERIFICAÇÃO DE PRODUTOS - NOVAMODA</h1>";

// ==========================================
// VERIFICAR SE PRODUTOS EXISTEM
// ==========================================
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM produtos");
    $total = $stmt->fetchColumn();
    
    echo "<h2>📊 Status do Banco</h2>";
    
    if ($total > 0) {
        echo "<p class='success'>✅ Banco já tem <strong>$total produtos</strong></p>";
        
        // Listar produtos
        $stmt = $pdo->query("
            SELECT 
                p.id,
                p.nome,
                p.preco,
                p.preco_antigo,
                p.estoque,
                p.ativo,
                c.nome as categoria_nome
            FROM produtos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            ORDER BY p.id
        ");
        $produtos = $stmt->fetchAll();
        
        echo "<h3>Produtos Cadastrados:</h3>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nome</th><th>Categoria</th><th>Preço</th><th>Preço Antigo</th><th>Estoque</th><th>Status</th></tr>";
        
        foreach ($produtos as $p) {
            $ativo = $p['ativo'] ? '<span class="success">✓ Ativo</span>' : '<span class="error">✗ Inativo</span>';
            $preco_antigo = $p['preco_antigo'] ? 'R$ ' . number_format($p['preco_antigo'], 2, ',', '.') : '-';
            $estoque_cor = $p['estoque'] > 10 ? 'success' : ($p['estoque'] > 0 ? 'warning' : 'error');
            
            echo "<tr>";
            echo "<td>{$p['id']}</td>";
            echo "<td>{$p['nome']}</td>";
            echo "<td>{$p['categoria_nome']}</td>";
            echo "<td>R$ " . number_format($p['preco'], 2, ',', '.') . "</td>";
            echo "<td>$preco_antigo</td>";
            echo "<td class='$estoque_cor'>{$p['estoque']}</td>";
            echo "<td>$ativo</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>🧪 Testar API:</h3>";
        echo "<a href='api/produtos/listar.php' target='_blank' class='btn'>Testar API: Listar Produtos</a>";
        echo "<a href='categorias.html' target='_blank' class='btn'>Abrir Página Categorias</a>";
        
    } else {
        echo "<p class='error'>❌ Banco está VAZIO - nenhum produto encontrado</p>";
        echo "<p class='warning'>⚠️ Você precisa INSERIR os produtos</p>";
        
        echo "<h3>📝 Escolha uma opção:</h3>";
        echo "<a href='inserir_produtos.php' class='btn'>INSERIR PRODUTOS AGORA</a>";
        echo "<p>ou</p>";
        echo "<p>Rode o script SQL completo no phpMyAdmin</p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
}

// ==========================================
// VERIFICAR CATEGORIAS
// ==========================================
echo "<h2>🏷️ Categorias</h2>";

try {
    $stmt = $pdo->query("SELECT id, nome, slug FROM categorias ORDER BY id");
    $categorias = $stmt->fetchAll();
    
    if (count($categorias) > 0) {
        echo "<p class='success'>✅ " . count($categorias) . " categorias encontradas</p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nome</th><th>Slug</th></tr>";
        foreach ($categorias as $c) {
            echo "<tr><td>{$c['id']}</td><td>{$c['nome']}</td><td>{$c['slug']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>❌ Nenhuma categoria encontrada</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr style='border: 1px solid #333; margin: 40px 0;'>";
echo "<p style='text-align: center;'><a href='admin-produtos.html' class='btn'>Ir para Painel Admin</a></p>";
?>