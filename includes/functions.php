<?php
require_once 'database.php';

function formatarData($data) {
    return date('d/m/Y', strtotime($data));
}

function formatarHora($hora) {
    return date('H:i', strtotime($hora));
}

function formatarDataHora($dataHora) {
    return date('d/m/Y H:i', strtotime($dataHora));
}

function validarData($data) {
    $dataObj = DateTime::createFromFormat('Y-m-d', $data);
    return $dataObj && $dataObj->format('Y-m-d') === $data;
}

function validarHora($hora) {
    $horaObj = DateTime::createFromFormat('H:i', $hora);
    return $horaObj && $horaObj->format('H:i') === $hora;
}

function verificarDisponibilidade($salaId, $data, $horaInicio, $horaFim) {
    $db = Database::getInstance();
    
    $sql = "SELECT COUNT(*) as count FROM reservas 
            WHERE sala_id = ? AND data_reserva = ? 
            AND status = 'confirmada'
            AND ((hora_inicio < ? AND hora_fim > ?) 
                 OR (hora_inicio < ? AND hora_fim > ?)
                 OR (hora_inicio >= ? AND hora_fim <= ?))";
    
    $result = $db->fetch($sql, [$salaId, $data, $horaFim, $horaInicio, $horaFim, $horaInicio, $horaInicio, $horaFim]);
    
    return $result['count'] == 0;
}

function calcularDuracao($horaInicio, $horaFim) {
    $inicio = new DateTime($horaInicio);
    $fim = new DateTime($horaFim);
    $diff = $inicio->diff($fim);
    return $diff->h * 60 + $diff->i;
}

function gerarHorarios() {
    $horarios = [];
    $inicio = new DateTime(HORARIO_INICIO);
    $fim = new DateTime(HORARIO_FIM);
    $intervalo = new DateInterval('PT30M');
    
    $periodo = new DatePeriod($inicio, $intervalo, $fim);
    
    foreach ($periodo as $hora) {
        $horarios[] = $hora->format('H:i');
    }
    
    return $horarios;
}

function enviarEmail($para, $assunto, $mensagem) {
    try {
        $headers = "From: " . EMAIL_FROM . "\r\n";
        $headers .= "Reply-To: " . EMAIL_FROM . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $resultado = mail($para, $assunto, $mensagem, $headers);
        
        if (!$resultado) {
            error_log("Falha no envio de e-mail para: " . $para);
        }
        
        return $resultado;
    } catch (Exception $e) {
        error_log("Erro no envio de e-mail: " . $e->getMessage());
        return false;
    }
}

function limparInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function redirecionar($url) {
    header("Location: $url");
    exit;
}

function mostrarMensagem($tipo, $mensagem) {
    $_SESSION['mensagem'] = [
        'tipo' => $tipo,
        'texto' => $mensagem
    ];
}

function obterMensagem() {
    if (isset($_SESSION['mensagem'])) {
        $mensagem = $_SESSION['mensagem'];
        unset($_SESSION['mensagem']);
        return $mensagem;
    }
    return null;
}

function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function respostaJson($dados) {
    if (ob_get_length()) {
        ob_clean();
    }
    
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    $dados = array_map(function($valor) {
        if (is_string($valor)) {
            return mb_convert_encoding($valor, 'UTF-8', 'UTF-8');
        }
        return $valor;
    }, $dados);
    
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}
?>
