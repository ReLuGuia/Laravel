<?php
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

function getUsuarioId() {
    return estaLogado() ? $_SESSION['usuario_id'] : null;
}

function addCarrinho($produto_id, $quantidade = 1) {
    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }
    
    if (isset($_SESSION['carrinho'][$produto_id])) {
        $_SESSION['carrinho'][$produto_id] += $quantidade;
    } else {
        $_SESSION['carrinho'][$produto_id] = $quantidade;
    }
}

function removerCarrinho($produto_id) {
    if (isset($_SESSION['carrinho'][$produto_id])) {
        unset($_SESSION['carrinho'][$produto_id]);
    }
}

function atualizarCarrinho($produto_id, $quantidade) {
    if ($quantidade <= 0) {
        removerCarrinho($produto_id);
    } else {
        $_SESSION['carrinho'][$produto_id] = $quantidade;
    }
}

function getCarrinhoTotalItens() {
    $total = 0;
    if (isset($_SESSION['carrinho'])) {
        foreach ($_SESSION['carrinho'] as $quantidade) {
            $total += $quantidade;
        }
    }
    return $total;
}

function calcularTotalCarrinho($pdo) {
    $total = 0;
    if (isset($_SESSION['carrinho']) && !empty($_SESSION['carrinho'])) {
        $produtos_ids = array_keys($_SESSION['carrinho']);
        $placeholders = implode(',', array_fill(0, count($produtos_ids), '?'));
        
        $stmt = $pdo->prepare("SELECT id, preco FROM produtos WHERE id IN ($placeholders)");
        $stmt->execute($produtos_ids);
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($produtos as $produto) {
            $quantidade = $_SESSION['carrinho'][$produto['id']];
            $total += $produto['preco'] * $quantidade;
        }
    }
    return $total;
}
?>