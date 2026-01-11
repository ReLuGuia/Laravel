<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if (!estaLogado() || empty($_SESSION['carrinho'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado ou carrinho vazio']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    // Calcular total
    $total = 0;
    $produtos_ids = array_keys($_SESSION['carrinho']);
    $placeholders = implode(',', array_fill(0, count($produtos_ids), '?'));
    
    $stmt = $pdo->prepare("SELECT id, nome, preco FROM produtos WHERE id IN ($placeholders)");
    $stmt->execute($produtos_ids);
    $produtos = $stmt->fetchAll();
    
    foreach ($produtos as $produto) {
        $quantidade = $_SESSION['carrinho'][$produto['id']];
        $total += $produto['preco'] * $quantidade;
    }
    
    // Buscar dados do usuário
    $usuario_id = $_SESSION['usuario_id'];
    $stmt = $pdo->prepare("SELECT endereco FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();
    
    // Criar pedido SEM metodo_pagamento (usando colunas básicas)
    $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, total, endereco_entrega, status) VALUES (?, ?, ?, 'pendente')");
    $stmt->execute([
        $usuario_id,
        $total,
        $usuario['endereco'] ?? 'Endereço não informado'
    ]);
    
    $pedido_id = $pdo->lastInsertId();
    
    // Adicionar itens do pedido
    foreach ($produtos as $produto) {
        $quantidade = $_SESSION['carrinho'][$produto['id']];
        
        $stmt = $pdo->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        $stmt->execute([$pedido_id, $produto['id'], $quantidade, $produto['preco']]);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'pedido_id' => $pedido_id,
        'total' => $total,
        'message' => 'Pedido criado com sucesso'
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>