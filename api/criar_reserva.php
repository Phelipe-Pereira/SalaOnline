<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

ob_start();

try {
    require_once '../includes/config.php';
    require_once '../includes/auth.php';
    require_once '../includes/functions.php';
} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro de configuração: ' . $e->getMessage()]);
    exit;
}

$auth = new Auth();
$db = Database::getInstance();

if (!$auth->isLoggedIn()) {
    respostaJson(['success' => false, 'message' => 'Usuário não autenticado']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respostaJson(['success' => false, 'message' => 'Método não permitido']);
}

$salaId = (int)($_POST['sala_id'] ?? 0);
$dataReserva = limparInput($_POST['data_reserva'] ?? '');
$horaInicio = limparInput($_POST['hora_inicio'] ?? '');
$horaFim = limparInput($_POST['hora_fim'] ?? '');
$titulo = limparInput($_POST['titulo'] ?? '');
$descricao = limparInput($_POST['descricao'] ?? '');

if (!$salaId || !$dataReserva || !$horaInicio || !$horaFim || !$titulo) {
    respostaJson(['success' => false, 'message' => 'Todos os campos obrigatórios devem ser preenchidos']);
}

if (!validarData($dataReserva)) {
    respostaJson(['success' => false, 'message' => 'Data inválida']);
}

if (!validarHora($horaInicio) || !validarHora($horaFim)) {
    respostaJson(['success' => false, 'message' => 'Horário inválido']);
}

if ($dataReserva < date('Y-m-d')) {
    respostaJson(['success' => false, 'message' => 'Não é possível fazer reservas para datas passadas']);
}

if ($horaInicio >= $horaFim) {
    respostaJson(['success' => false, 'message' => 'Hora de início deve ser menor que hora de fim']);
}

$duracao = calcularDuracao($horaInicio, $horaFim);
if ($duracao < DURACAO_MINIMA || $duracao > DURACAO_MAXIMA) {
    respostaJson(['success' => false, 'message' => "Duração deve ser entre " . DURACAO_MINIMA . " e " . DURACAO_MAXIMA . " minutos"]);
}

$sql = "SELECT * FROM salas WHERE id = ? AND ativo = 1";
$sala = $db->fetch($sql, [$salaId]);

if (!$sala) {
    respostaJson(['success' => false, 'message' => 'Sala não encontrada']);
}

if (!verificarDisponibilidade($salaId, $dataReserva, $horaInicio, $horaFim)) {
    respostaJson(['success' => false, 'message' => 'Sala não está disponível no horário selecionado']);
}

try {
    error_log("Tentando criar reserva: Usuario=" . $auth->getCurrentUser()['id'] . 
              ", Sala=" . $salaId . ", Data=" . $dataReserva . 
              ", Hora=" . $horaInicio . "-" . $horaFim);
    
    $sql = "INSERT INTO reservas (usuario_id, sala_id, data_reserva, hora_inicio, hora_fim, titulo, descricao) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $db->query($sql, [
        $auth->getCurrentUser()['id'],
        $salaId,
        $dataReserva,
        $horaInicio,
        $horaFim,
        $titulo,
        $descricao
    ]);
    
    $reservaId = $db->lastInsertId();
    error_log("Reserva criada com sucesso. ID: " . $reservaId);
    
    $mensagemEmail = "
    <h2>Confirmação de Reserva</h2>
    <p><strong>Sala:</strong> {$sala['nome']}</p>
    <p><strong>Data:</strong> " . formatarData($dataReserva) . "</p>
    <p><strong>Horário:</strong> " . formatarHora($horaInicio) . " - " . formatarHora($horaFim) . "</p>
    <p><strong>Título:</strong> {$titulo}</p>
    " . ($descricao ? "<p><strong>Descrição:</strong> {$descricao}</p>" : "") . "
    <p>Sua reserva foi confirmada com sucesso!</p>
    ";
    
    $emailEnviado = enviarEmail(
        $auth->getCurrentUser()['email'],
        'Confirmação de Reserva - ' . SITE_NAME,
        $mensagemEmail
    );
    
    $mensagem = 'Reserva criada com sucesso!';
    if ($emailEnviado) {
        $mensagem .= ' Um e-mail de confirmação foi enviado.';
    } else {
        $mensagem .= ' (E-mail de confirmação não pôde ser enviado)';
    }
    
    respostaJson([
        'success' => true, 
        'message' => $mensagem,
        'redirect' => 'reservas.php'
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao criar reserva: " . $e->getMessage());
    respostaJson(['success' => false, 'message' => 'Erro ao criar reserva: ' . $e->getMessage()]);
} catch (Error $e) {
    error_log("Erro fatal ao criar reserva: " . $e->getMessage());
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
    exit;
}

ob_end_flush();
?>
