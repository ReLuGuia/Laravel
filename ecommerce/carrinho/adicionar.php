<?php
require_once __DIR__ . '/../includes/config.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /ecommerce/');
    exit();
}

// Verificar se o produto_id foi enviado
if (!isset($_POST['produto_id']) || !isset($_POST['quantidade'])) {
    $_SESSION['erro'] = 'Produto não especificado.';
    header('Location: /ecommerce/');
    exit();
}

$produto_id = (int)$_POST['produto_id'];
$quantidade = (int)$_POST['quantidade'];

// Validar quantidade
if ($quantidade < 1) {
    $_SESSION['erro'] = 'Quantidade inválida.';
    header('Location: /ecommerce/produtos/ver.php?id=' . $produto_id);
    exit();
}

// Verificar se o produto existe no banco
try {
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ? AND estoque > 0");
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch();
    
    if (!$produto) {
        $_SESSION['erro'] = 'Produto não encontrado ou sem estoque.';
        header('Location: /ecommerce/');
        exit();
    }
    
    // Verificar se a quantidade solicitada está disponível
    if ($quantidade > $produto['estoque']) {
        $_SESSION['erro'] = 'Quantidade solicitada maior que o estoque disponível.';
        header('Location: /ecommerce/produtos/ver.php?id=' . $produto_id);
        exit();
    }
    
    // Inicializar carrinho se não existir
    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }
    
    // Adicionar ou atualizar item no carrinho
    if (isset($_SESSION['carrinho'][$produto_id])) {
        $_SESSION['carrinho'][$produto_id] += $quantidade;
    } else {
        $_SESSION['carrinho'][$produto_id] = $quantidade;
    }
    
    // Mensagem de sucesso
    $_SESSION['sucesso'] = 'Produto adicionado ao carrinho!';
    
    // Redirecionar de volta para o produto ou para o carrinho
    header('Location: /ecommerce/produtos/ver.php?id=' . $produto_id);
    exit();
    
} catch (PDOException $e) {
    $_SESSION['erro'] = 'Erro ao adicionar produto: ' . $e->getMessage();
    header('Location: /ecommerce/');
    exit();
}
?>