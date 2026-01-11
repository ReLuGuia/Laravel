<?php
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /ecommerce/carrinho/');
    exit();
}

if (!isset($_POST['produto_id'])) {
    $_SESSION['erro'] = 'Produto não especificado.';
    header('Location: /ecommerce/carrinho/');
    exit();
}

$produto_id = (int)$_POST['produto_id'];

if (isset($_SESSION['carrinho'][$produto_id])) {
    unset($_SESSION['carrinho'][$produto_id]);
    $_SESSION['sucesso'] = 'Produto removido do carrinho!';
} else {
    $_SESSION['erro'] = 'Produto não encontrado no carrinho.';
}

header('Location: /ecommerce/carrinho/');
exit();
?>