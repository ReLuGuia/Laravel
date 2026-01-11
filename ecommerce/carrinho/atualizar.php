<?php
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /ecommerce/carrinho/');
    exit();
}

if (!isset($_POST['produto_id']) || !isset($_POST['quantidade'])) {
    $_SESSION['erro'] = 'Dados inválidos.';
    header('Location: /ecommerce/carrinho/');
    exit();
}

$produto_id = (int)$_POST['produto_id'];
$quantidade = (int)$_POST['quantidade'];

if ($quantidade < 1) {
    // Remove o produto se quantidade for 0
    if (isset($_SESSION['carrinho'][$produto_id])) {
        unset($_SESSION['carrinho'][$produto_id]);
    }
} else {
    $_SESSION['carrinho'][$produto_id] = $quantidade;
}

$_SESSION['sucesso'] = 'Carrinho atualizado!';
header('Location: /ecommerce/carrinho/');
exit();
?>