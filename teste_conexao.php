<?php
require_once 'config.php';

echo "<h1>Teste de Conexão</h1>";

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM produtos");
    $result = $stmt->fetch();
    
    echo "✅ Conexão bem-sucedida!<br>";
    echo "Total de produtos no banco: " . $result['total'];
    
} catch(PDOException $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>