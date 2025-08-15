<?php
header('Content-Type: text/html; charset=UTF-8');

echo "<h2>Teste da API de Criação de Reservas</h2>";

$_POST = [
    'sala_id' => '1',
    'data_reserva' => '2025-08-20',
    'hora_inicio' => '09:00',
    'hora_fim' => '10:00',
    'titulo' => 'Teste API',
    'descricao' => 'Teste de funcionamento da API'
];

echo "<h3>Dados de teste:</h3>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

ob_start();

try {
    include 'api/criar_reserva.php';
    
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "<h3>Resposta da API:</h3>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    $json = json_decode($output, true);
    if ($json) {
        echo "<h3>JSON decodificado:</h3>";
        echo "<pre>" . print_r($json, true) . "</pre>";
        
        if ($json['success']) {
            echo "<p style='color: green;'>✅ API funcionando corretamente!</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ API retornou erro: " . $json['message'] . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Resposta não é JSON válido!</p>";
        echo "<p>Erro JSON: " . json_last_error_msg() . "</p>";
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "<p style='color: red;'>❌ Erro ao executar API: " . $e->getMessage() . "</p>";
} catch (Error $e) {
    ob_end_clean();
    echo "<p style='color: red;'>❌ Erro fatal: " . $e->getMessage() . "</p>";
}

echo "<h3>Verificação de configurações:</h3>";
echo "<p>display_errors: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "</p>";
echo "<p>error_reporting: " . ini_get('error_reporting') . "</p>";
echo "<p>log_errors: " . (ini_get('log_errors') ? 'ON' : 'OFF') . "</p>";
echo "<p>error_log: " . ini_get('error_log') . "</p>";

echo "<h3>Teste de funções:</h3>";

$functions = ['formatarData', 'validarData', 'validarHora', 'calcularDuracao'];
foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "<p style='color: green;'>✅ Função {$func} disponível</p>";
    } else {
        echo "<p style='color: red;'>❌ Função {$func} não encontrada</p>";
    }
}

echo "<h3>Teste de classes:</h3>";

$classes = ['Auth', 'Database'];
foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "<p style='color: green;'>✅ Classe {$class} disponível</p>";
    } else {
        echo "<p style='color: red;'>❌ Classe {$class} não encontrada</p>";
    }
}
?>
