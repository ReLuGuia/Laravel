<?php
// includes/config.php
session_start();

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecommerce_basico');
define('DB_USER', 'root');
define('DB_PASS', '');

// Conexão com PDO
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Funções úteis
function formatarPreco($preco) {
    return 'R$ ' . number_format($preco, 2, ',', '.');
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function estaLogado() {
    return isset($_SESSION['usuario_id']);
}

function isAdmin() {
    return isset($_SESSION['usuario_admin']) && $_SESSION['usuario_admin'] == 1;
}

function requerLogin() {
    if (!estaLogado()) {
        $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
        header('Location: /ecommerce/conta/login.php');
        exit();
    }
}

function requerAdmin() {
    requerLogin();
    if (!isAdmin()) {
        header('Location: /ecommerce/');
        exit();
    }
}
function getUsuarioId() {
    return isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
}


// Mercado Pago Configuration
define('MP_ACCESS_TOKEN', 'SEU_ACCESS_TOKEN_AQUI');
define('MP_PUBLIC_KEY', 'SEU_PUBLIC_KEY_AQUI');
define('MP_SANDBOX', true); // true para testes, false para produção

?>