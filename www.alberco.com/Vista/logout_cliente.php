<?php
require_once(__DIR__ . "/../Services/auth_cliente.php");

$auth = getAuthCliente();
$resultado = $auth->logout();

session_destroy();

header("Location: ../index.php");
exit;
