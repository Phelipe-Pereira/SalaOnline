<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
$resultado = $auth->logout();

mostrarMensagem('success', $resultado['message']);
redirecionar('index.php');
?>
