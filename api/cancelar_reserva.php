<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth = new Auth();
$db = Database::getInstance();

if (!$auth->isLoggedIn()) {
    respostaJson(['success' => false, 'message' => 'Usuário não autenticado']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respostaJson(['success' => false, 'message' => 'Método não permitido']);
}

$reservaId = (int)($_POST['reserva_id'] ?? 0);

if (!$reservaId) {
    respostaJson(['success' => false, 'message' => 'ID da reserva é obrigatório']);
}

$usuario = $auth->getCurrentUser();

$sql = "SELECT r.*, s.nome as sala_nome, u.email 
        FROM reservas r 
        JOIN salas s ON r.sala_id = s.id 
        JOIN usuarios u ON r.usuario_id = u.id 
        WHERE r.id = ? AND r.usuario_id = ?";

$reserva = $db->fetch($sql, [$reservaId, $usuario['id']]);

if (!$reserva) {
    respostaJson(['success' => false, 'message' => 'Reserva não encontrada']);
}

if ($reserva['status'] !== 'confirmada') {
    respostaJson(['success' => false, 'message' => 'Apenas reservas confirmadas podem ser canceladas']);
}

if ($reserva['data_reserva'] < date('Y-m-d')) {
    respostaJson(['success' => false, 'message' => 'Não é possível cancelar reservas de datas passadas']);
}

try {
    $sql = "UPDATE reservas SET status = 'cancelada' WHERE id = ?";
    $db->query($sql, [$reservaId]);
    
    $mensagemEmail = "
    <h2>Cancelamento de Reserva</h2>
    <p><strong>Sala:</strong> {$reserva['sala_nome']}</p>
    <p><strong>Data:</strong> " . formatarData($reserva['data_reserva']) . "</p>
    <p><strong>Horário:</strong> " . formatarHora($reserva['hora_inicio']) . " - " . formatarHora($reserva['hora_fim']) . "</p>
    <p><strong>Título:</strong> {$reserva['titulo']}</p>
    <p>Sua reserva foi cancelada com sucesso.</p>
    ";
    
    enviarEmail(
        $reserva['email'],
        'Cancelamento de Reserva - ' . SITE_NAME,
        $mensagemEmail
    );
    
    respostaJson([
        'success' => true, 
        'message' => 'Reserva cancelada com sucesso! Um e-mail de confirmação foi enviado.'
    ]);
    
} catch (Exception $e) {
    respostaJson(['success' => false, 'message' => 'Erro ao cancelar reserva']);
}
?>
