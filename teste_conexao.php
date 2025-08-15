<?php
require_once 'includes/config.php';
require_once 'includes/database.php';

echo "<h2>Teste de Conexão com Banco de Dados</h2>";

try {
    $db = Database::getInstance();
    echo "<p style='color: green;'>✅ Conexão com banco de dados estabelecida com sucesso!</p>";
    
    $result = $db->fetch("SELECT COUNT(*) as total FROM salas");
    echo "<p>Total de salas no banco: " . $result['total'] . "</p>";
    
    $result = $db->fetch("SELECT COUNT(*) as total FROM usuarios");
    echo "<p>Total de usuários no banco: " . $result['total'] . "</p>";
    
    $result = $db->fetch("SELECT COUNT(*) as total FROM reservas");
    echo "<p>Total de reservas no banco: " . $result['total'] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro na conexão: " . $e->getMessage() . "</p>";
}

echo "<h3>Configurações atuais:</h3>";
echo "<p>Host: " . DB_HOST . "</p>";
echo "<p>Database: " . DB_NAME . "</p>";
echo "<p>User: " . DB_USER . "</p>";
echo "<p>Password: " . (DB_PASS ? 'Definida' : 'Não definida') . "</p>";

echo "<h3>Teste de funções:</h3>";

require_once 'includes/functions.php';

$testeEmail = enviarEmail(
    'teste@exemplo.com',
    'Teste de E-mail',
    '<h1>Teste</h1><p>Este é um teste de envio de e-mail.</p>'
);

if ($testeEmail) {
    echo "<p style='color: green;'>✅ Função de e-mail funcionando</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Função de e-mail pode ter problemas (normal em ambiente local)</p>";
}

echo "<h3>Logs de erro recentes:</h3>";
$logFile = ini_get('error_log');
if ($logFile && file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $linhas = explode("\n", $logs);
    $ultimas = array_slice($linhas, -10);
    echo "<pre style='background: #f5f5f5; padding: 10px;'>";
    foreach ($ultimas as $linha) {
        if (trim($linha)) {
            echo htmlspecialchars($linha) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p>Arquivo de log não encontrado ou não configurado.</p>";
}
?>
