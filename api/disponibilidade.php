<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respostaJson(['success' => false, 'message' => 'Método não permitido']);
}

$data = limparInput($_POST['data'] ?? '');

if (!validarData($data)) {
    respostaJson(['success' => false, 'message' => 'Data inválida']);
}

$sql = "SELECT s.*, 
        CASE WHEN r.id IS NULL THEN 1 ELSE 0 END as disponivel
        FROM salas s 
        LEFT JOIN reservas r ON s.id = r.sala_id 
        AND r.data_reserva = ? 
        AND r.status = 'confirmada'
        WHERE s.ativo = 1
        GROUP BY s.id";

$salas = $db->fetchAll($sql, [$data]);

respostaJson($salas);
?>
