<?php
require_once __DIR__ . '/../includes/config.php';

// Destruir sessão
session_start();
session_unset();
session_destroy();

// Redirecionar para login (CAMINHO FIXO)
header('Location: /ecommerce/conta/login.php');
exit();
?>