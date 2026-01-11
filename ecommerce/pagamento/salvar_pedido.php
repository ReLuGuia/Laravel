<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log para debug
error_log("=== SALVAR PEDIDO INICIADO ===");

if (!estaLogado()) {
    error_log("Usuário não logado");
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit();
}

if (empty($_SESSION['carrinho'])) {
    error_log("Carrinho vazio");
    echo json_encode(['success' => false, 'message' => 'Carrinho vazio']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$metodo_pagamento = $input['metodo_pagamento'] ?? 'pix';

error_log("Método pagamento: " . $metodo_pagamento);
error_log("Usuário ID: " . $_SESSION['usuario_id']);
error_log("Itens carrinho: " . count($_SESSION['carrinho']));

try {
    $pdo->beginTransaction();
    
    // Calcular total
    $total = 0;
    $produtos_ids = array_keys($_SESSION['carrinho']);
    
    if (empty($produtos_ids)) {
        throw new Exception("Nenhum produto no carrinho");
    }
    
    $placeholders = implode(',', array_fill(0, count($produtos_ids), '?'));
    $stmt = $pdo->prepare("SELECT id, nome, preco, estoque FROM produtos WHERE id IN ($placeholders)");
    $stmt->execute($produtos_ids);
    $produtos = $stmt->fetchAll();
    
    foreach ($produtos as $produto) {
        $quantidade = $_SESSION['carrinho'][$produto['id']];
        $total += $produto['preco'] * $quantidade;
        error_log("Produto: {$produto['nome']} x {$quantidade} = R$ " . ($produto['preco'] * $quantidade));
    }
    
    error_log("Total calculado: R$ " . $total);
    
    // Buscar dados do usuário
    $usuario_id = $_SESSION['usuario_id'];
    $stmt = $pdo->prepare("SELECT nome, email, endereco FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        throw new Exception("Usuário não encontrado no banco de dados");
    }
    
    error_log("Usuário encontrado: " . $usuario['nome']);
    
    // Verificar estrutura da tabela pedidos
    $stmt = $pdo->query("DESCRIBE pedidos");
    $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    error_log("Colunas da tabela pedidos: " . implode(', ', $colunas));
    
    // Criar pedido - versão segura
    $sql = "INSERT INTO pedidos (usuario_id, total, endereco_entrega, status, metodo_pagamento) VALUES (?, ?, ?, 'pendente', ?)";
    error_log("SQL: " . $sql);
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $usuario_id,
        $total,
        $usuario['endereco'] ?? 'Endereço não informado',
        $metodo_pagamento
    ]);
    
    $pedido_id = $pdo->lastInsertId();
    error_log("Pedido criado com ID: " . $pedido_id);
    
    // Adicionar itens do pedido
    foreach ($produtos as $produto) {
        $quantidade = $_SESSION['carrinho'][$produto['id']];
        
        $stmt = $pdo->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        $stmt->execute([$pedido_id, $produto['id'], $quantidade, $produto['preco']]);
        error_log("Item adicionado: {$produto['nome']} x {$quantidade}");
    }
    
    $pdo->commit();
    
    error_log("=== PEDIDO SALVO COM SUCESSO ===");
    
    echo json_encode([
        'success' => true,
        'pedido_id' => $pedido_id,
        'total' => $total,
        'message' => 'Pedido criado com sucesso'
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("ERRO: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao criar pedido: ' . $e->getMessage()
    ]);
}
?>