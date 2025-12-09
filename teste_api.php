<?php
/**
 * teste_api.php - Diagnóstico Completo da API
 * Acesse: http://localhost/Novamoda/teste_api.php
 */

echo "<style>
body { font-family: Arial; background: #0a0a0a; color: #fff; padding: 20px; }
h1 { color: #14d0d6; }
.success { color: #4ade80; }
.error { color: #ff3b30; }
.warning { color: #fbbf24; }
pre { background: #111; padding: 15px; border-radius: 8px; overflow-x: auto; }
</style>";

echo "<h1>🔍 DIAGNÓSTICO DA API - NOVAMODA</h1>";

// ==========================================
// TESTE 1: CONFIG.PHP
// ==========================================
echo "<h2>1️⃣ Teste: config.php</h2>";

if (file_exists('config.php')) {
    echo "<p class='success'>✅ config.php encontrado na raiz</p>";
    
    require_once 'config.php';
    
    try {
        $pdo->query("SELECT 1");
        echo "<p class='success'>✅ Conexão com banco estabelecida</p>";
        echo "<p>Host: " . DB_HOST . "</p>";
        echo "<p>Banco: " . DB_NAME . "</p>";
    } catch(PDOException $e) {
        echo "<p class='error'>❌ Erro na conexão: " . $e->getMessage() . "</p>";
        die();
    }
} else {
    echo "<p class='error'>❌ config.php NÃO encontrado na raiz</p>";
    die();
}

// ==========================================
// TESTE 2: TABELAS DO BANCO
// ==========================================
echo "<h2>2️⃣ Teste: Tabelas do Banco</h2>";

$tabelas = ['produtos', 'categorias', 'usuarios', 'pedidos'];

foreach ($tabelas as $tabela) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabela");
        $count = $stmt->fetchColumn();
        echo "<p class='success'>✅ $tabela: $count registros</p>";
    } catch(PDOException $e) {
        echo "<p class='error'>❌ Erro na tabela $tabela: " . $e->getMessage() . "</p>";
    }
}

// ==========================================
// TESTE 3: ENDPOINTS DA API
// ==========================================
echo "<h2>3️⃣ Teste: Endpoints da API</h2>";

$endpoints = [
    '/api/produtos/listar.php',
    '/api/admin/dashboard.php',
    '/api/admin/clientes.php'
];

foreach ($endpoints as $endpoint) {
    $fullPath = __DIR__ . $endpoint;
    
    if (file_exists($fullPath)) {
        echo "<p class='success'>✅ $endpoint encontrado</p>";
    } else {
        echo "<p class='error'>❌ $endpoint NÃO encontrado</p>";
        echo "<p class='warning'>📍 Esperado em: $fullPath</p>";
    }
}

// ==========================================
// TESTE 4: TESTAR LISTAR PRODUTOS
// ==========================================
echo "<h2>4️⃣ Teste: Listar Produtos (Simulação)</h2>";

try {
    $stmt = $pdo->query("
        SELECT id, nome, preco, estoque
        FROM produtos
        LIMIT 3
    ");
    $produtos = $stmt->fetchAll();
    
    if (count($produtos) > 0) {
        echo "<p class='success'>✅ Query funcionando. Primeiros 3 produtos:</p>";
        echo "<pre>" . json_encode($produtos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    } else {
        echo "<p class='warning'>⚠️ Nenhum produto encontrado no banco</p>";
    }
} catch(PDOException $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
}

// ==========================================
// TESTE 5: VERIFICAR EXTENSÕES PHP
// ==========================================
echo "<h2>5️⃣ Teste: Extensões PHP</h2>";

$extensoes = ['pdo_mysql', 'json', 'curl'];

foreach ($extensoes as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✅ $ext: Habilitado</p>";
    } else {
        echo "<p class='error'>❌ $ext: DESABILITADO</p>";
    }
}

echo "<p>Versão PHP: " . phpversion() . "</p>";

// ==========================================
// TESTE 6: CORS
// ==========================================
echo "<h2>6️⃣ Teste: Headers CORS</h2>";

if (headers_sent()) {
    echo "<p class='success'>✅ Headers já enviados (config.php funcionando)</p>";
} else {
    echo "<p class='warning'>⚠️ Headers ainda não enviados</p>";
}

// ==========================================
// RESUMO FINAL
// ==========================================
echo "<h2>📊 RESUMO</h2>";

$total_produtos = $pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
$total_categorias = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
$total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();

echo "<ul style='font-size: 18px; line-height: 2;'>";
echo "<li>📦 <strong>Produtos:</strong> $total_produtos</li>";
echo "<li>🏷️ <strong>Categorias:</strong> $total_categorias</li>";
echo "<li>👥 <strong>Usuários:</strong> $total_usuarios</li>";
echo "</ul>";

echo "<hr style='border: 1px solid #333; margin: 30px 0;'>";
echo "<p style='text-align: center; color: #14d0d6; font-size: 20px;'>";
echo "<strong>✅ Diagnóstico concluído!</strong>";
echo "</p>";

echo "<h2>🔗 PRÓXIMO PASSO:</h2>";
echo "<p>Acesse: <a href='http://localhost/Novamoda/api/produtos/listar.php' target='_blank' style='color:#14d0d6'>http://localhost/Novamoda/api/produtos/listar.php</a></p>";
?>