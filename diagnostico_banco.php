<?php
/**
 * diagnostico_banco.php - Diagnóstico Completo do Banco
 * Coloque na raiz: /Novamoda/diagnostico_banco.php
 */

require_once 'config.php';

echo "<style>
body { font-family: Arial; background: #0a0a0a; color: #fff; padding: 20px; }
h1 { color: #14d0d6; }
table { width: 100%; border-collapse: collapse; margin: 20px 0; background: #111; }
th, td { padding: 12px; text-align: left; border: 1px solid #333; }
th { background: #14d0d6; color: #000; font-weight: bold; }
.success { color: #4ade80; }
.error { color: #ff3b30; }
.warning { color: #fbbf24; }
.info { color: #14d0d6; }
</style>";

echo "<h1>🔍 DIAGNÓSTICO COMPLETO - NOVAMODA</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";

// ==========================================
// TESTE 1: CONEXÃO
// ==========================================
echo "<h2>✅ TESTE 1: Conexão com Banco</h2>";
try {
    $pdo->query("SELECT 1");
    echo "<p class='success'>✓ Conexão estabelecida com sucesso!</p>";
    echo "<p><strong>Host:</strong> " . DB_HOST . "</p>";
    echo "<p><strong>Banco:</strong> " . DB_NAME . "</p>";
    echo "<p><strong>Usuário:</strong> " . DB_USER . "</p>";
} catch (PDOException $e) {
    echo "<p class='error'>✗ Erro na conexão: " . $e->getMessage() . "</p>";
    die();
}

// ==========================================
// TESTE 2: TABELAS
// ==========================================
echo "<h2>📋 TESTE 2: Tabelas do Banco</h2>";
$tables = [
    'usuarios', 'produtos', 'categorias', 'pedidos', 'pedido_itens',
    'carrinhos', 'carrinho_itens', 'enderecos', 'avaliacoes',
    'cupons', 'favoritos', 'newsletter', 'logs_sistema'
];

echo "<table>";
echo "<tr><th>Tabela</th><th>Registros</th><th>Status</th></tr>";

foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM $table");
        $count = $stmt->fetchColumn();
        $status = $count > 0 ? "<span class='success'>✓ OK</span>" : "<span class='warning'>⚠ Vazia</span>";
        echo "<tr><td>$table</td><td>$count</td><td>$status</td></tr>";
    } catch (PDOException $e) {
        echo "<tr><td>$table</td><td>-</td><td><span class='error'>✗ ERRO: {$e->getMessage()}</span></td></tr>";
    }
}
echo "</table>";

// ==========================================
// TESTE 3: USUÁRIOS
// ==========================================
echo "<h2>👥 TESTE 3: Usuários Cadastrados</h2>";
try {
    $stmt = $pdo->query("SELECT id, nome, email, tipo, ativo, data_cadastro FROM usuarios ORDER BY id");
    $usuarios = $stmt->fetchAll();
    
    if (count($usuarios) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Tipo</th><th>Ativo</th><th>Cadastro</th></tr>";
        foreach ($usuarios as $u) {
            $ativo = $u['ativo'] ? "<span class='success'>✓ Sim</span>" : "<span class='error'>✗ Não</span>";
            $tipo = $u['tipo'] == 'admin' ? "<span class='warning'>ADMIN</span>" : "Cliente";
            $data = date('d/m/Y', strtotime($u['data_cadastro']));
            echo "<tr><td>{$u['id']}</td><td>{$u['nome']}</td><td>{$u['email']}</td><td>$tipo</td><td>$ativo</td><td>$data</td></tr>";
        }
        echo "</table>";
        
        echo "<p class='info'>💡 <strong>Login Admin:</strong> admin@novamoda.com | Senha: password</p>";
    } else {
        echo "<p class='error'>✗ Nenhum usuário encontrado</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>✗ Erro: " . $e->getMessage() . "</p>";
}

// ==========================================
// TESTE 4: PRODUTOS
// ==========================================
echo "<h2>📦 TESTE 4: Produtos Cadastrados</h2>";
try {
    $stmt = $pdo->query("
        SELECT p.id, p.nome, p.preco, p.estoque, p.ativo, c.nome as categoria
        FROM produtos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        ORDER BY p.id
    ");
    $produtos = $stmt->fetchAll();
    
    if (count($produtos) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nome</th><th>Preço</th><th>Estoque</th><th>Categoria</th><th>Ativo</th></tr>";
        foreach ($produtos as $p) {
            $ativo = $p['ativo'] ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>";
            $estoque_cor = $p['estoque'] > 10 ? 'success' : ($p['estoque'] > 0 ? 'warning' : 'error');
            echo "<tr>
                <td>{$p['id']}</td>
                <td>{$p['nome']}</td>
                <td>R$ " . number_format($p['preco'], 2, ',', '.') . "</td>
                <td class='$estoque_cor'>{$p['estoque']}</td>
                <td>{$p['categoria']}</td>
                <td>$ativo</td>
            </tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>✗ Nenhum produto encontrado</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>✗ Erro: " . $e->getMessage() . "</p>";
}

// ==========================================
// TESTE 5: CATEGORIAS
// ==========================================
echo "<h2>🏷️ TESTE 5: Categorias</h2>";
try {
    $stmt = $pdo->query("SELECT id, nome, slug, ativo FROM categorias ORDER BY ordem");
    $categorias = $stmt->fetchAll();
    
    if (count($categorias) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nome</th><th>Slug</th><th>Ativo</th></tr>";
        foreach ($categorias as $c) {
            $ativo = $c['ativo'] ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>";
            echo "<tr><td>{$c['id']}</td><td>{$c['nome']}</td><td>{$c['slug']}</td><td>$ativo</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>✗ Nenhuma categoria encontrada</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>✗ Erro: " . $e->getMessage() . "</p>";
}

// ==========================================
// TESTE 6: PEDIDOS
// ==========================================
echo "<h2>🛒 TESTE 6: Pedidos</h2>";
try {
    $stmt = $pdo->query("
        SELECT p.id, p.numero_pedido, p.total, p.status, u.nome as cliente, p.data_pedido
        FROM pedidos p
        JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.data_pedido DESC
        LIMIT 10
    ");
    $pedidos = $stmt->fetchAll();
    
    if (count($pedidos) > 0) {
        echo "<table>";
        echo "<tr><th>Número</th><th>Cliente</th><th>Total</th><th>Status</th><th>Data</th></tr>";
        foreach ($pedidos as $ped) {
            $data = date('d/m/Y H:i', strtotime($ped['data_pedido']));
            $status_class = $ped['status'] == 'entregue' ? 'success' : ($ped['status'] == 'cancelado' ? 'error' : 'warning');
            echo "<tr>
                <td>{$ped['numero_pedido']}</td>
                <td>{$ped['cliente']}</td>
                <td>R$ " . number_format($ped['total'], 2, ',', '.') . "</td>
                <td class='$status_class'>{$ped['status']}</td>
                <td>$data</td>
            </tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠ Nenhum pedido registrado ainda</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>✗ Erro: " . $e->getMessage() . "</p>";
}

// ==========================================
// TESTE 7: TESTES DE API
// ==========================================
echo "<h2>🔌 TESTE 7: Endpoints da API</h2>";

$endpoints = [
    '/api/produtos/listar.php' => 'Listar Produtos',
    '/api/admin/dashboard.php' => 'Dashboard Admin',
    '/api/admin/clientes.php' => 'Listar Clientes',
    '/api/categorias/listar.php' => 'Listar Categorias',
];

echo "<table>";
echo "<tr><th>Endpoint</th><th>Descrição</th><th>Status</th></tr>";

foreach ($endpoints as $endpoint => $desc) {
    $url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . $endpoint;
    
    // Tentar fazer requisição
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $json = json_decode($response, true);
        $status = isset($json['success']) && $json['success'] ? 
            "<span class='success'>✓ OK</span>" : 
            "<span class='error'>✗ Erro no JSON</span>";
    } else {
        $status = "<span class='error'>✗ HTTP $http_code</span>";
    }
    
    echo "<tr><td><a href='$url' target='_blank' style='color:#14d0d6'>$endpoint</a></td><td>$desc</td><td>$status</td></tr>";
}
echo "</table>";

// ==========================================
// TESTE 8: CONFIGURAÇÕES PHP
// ==========================================
echo "<h2>⚙️ TESTE 8: Configurações PHP</h2>";
echo "<table>";
echo "<tr><th>Configuração</th><th>Valor</th></tr>";
echo "<tr><td>Versão PHP</td><td>" . phpversion() . "</td></tr>";
echo "<tr><td>PDO MySQL</td><td>" . (extension_loaded('pdo_mysql') ? "<span class='success'>✓ Habilitado</span>" : "<span class='error'>✗ Desabilitado</span>") . "</td></tr>";
echo "<tr><td>CURL</td><td>" . (extension_loaded('curl') ? "<span class='success'>✓ Habilitado</span>" : "<span class='error'>✗ Desabilitado</span>") . "</td></tr>";
echo "<tr><td>JSON</td><td>" . (extension_loaded('json') ? "<span class='success'>✓ Habilitado</span>" : "<span class='error'>✗ Desabilitado</span>") . "</td></tr>";
echo "</table>";

// ==========================================
// RESUMO FINAL
// ==========================================
echo "<h2>📊 RESUMO FINAL</h2>";

$total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$total_produtos = $pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
$total_categorias = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
$total_pedidos = $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
$total_clientes = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE tipo='cliente'")->fetchColumn();

echo "<ul style='font-size: 18px; line-height: 2;'>";
echo "<li>👥 <strong>Usuários:</strong> $total_usuarios (Admin: " . ($total_usuarios - $total_clientes) . ", Clientes: $total_clientes)</li>";
echo "<li>📦 <strong>Produtos:</strong> $total_produtos</li>";
echo "<li>🏷️ <strong>Categorias:</strong> $total_categorias</li>";
echo "<li>🛒 <strong>Pedidos:</strong> $total_pedidos</li>";
echo "</ul>";

echo "<hr style='border: 1px solid #333; margin: 30px 0;'>";
echo "<p style='text-align: center; color: #14d0d6; font-size: 20px;'><strong>Diagnóstico concluído!</strong></p>";
?>