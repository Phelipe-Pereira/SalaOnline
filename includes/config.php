<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'sala_online');
define('DB_USER', 'root');
define('DB_PASS', '');

define('SITE_NAME', 'Sala Online');
define('SITE_URL', 'http://localhost/SalaOnline');
define('EMAIL_FROM', 'sistema@salaonline.com');

define('HORARIO_INICIO', '08:00');
define('HORARIO_FIM', '18:00');
define('DURACAO_MINIMA', 30);
define('DURACAO_MAXIMA', 240);

error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('America/Sao_Paulo');
?>
