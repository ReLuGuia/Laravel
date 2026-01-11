<?php
require_once __DIR__ . '/../includes/config.php';

// Ativar display de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar se está logado e se é POST
if (!estaLogado()) {
    $_SESSION['erro'] = "Você precisa estar logado para finalizar a compra.";
    header('Location: /ecommerce/conta/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['erro'] = "Método inválido.";
    header('Location: /ecommerce/checkout/');
    exit();
}

// Verificar se carrinho está vazio
if (empty($_SESSION['carrinho'])) {
    $_SESSION['erro'] = "Seu carrinho está vazio.";
    header('Location: /ecommerce/carrinho/');
    exit();
}

// Validar dados do formulário
$required_fields = ['nome', 'email', 'telefone', 'endereco'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['erro'] = "Preencha todos os campos obrigatórios.";
        header('Location: /ecommerce/checkout/');
        exit();
    }
}

try {
    // Iniciar transação
    $pdo->beginTransaction();
    
    // Calcular total
    $total = 0;
    $produtos_ids = array_keys($_SESSION['carrinho']);
    
    if (empty($produtos_ids)) {
        throw new Exception("Carrinho vazio.");
    }
    
    $placeholders = implode(',', array_fill(0, count($produtos_ids), '?'));
    $stmt = $pdo->prepare("SELECT id, nome, preco, estoque FROM produtos WHERE id IN ($placeholders)");
    $stmt->execute($produtos_ids);
    $produtos = $stmt->fetchAll();
    
    // Verificar estoque e calcular total
    foreach ($produtos as $produto) {
        $quantidade = $_SESSION['carrinho'][$produto['id']];
        $total += $produto['preco'] * $quantidade;
        
        // Verificar estoque
        if ($quantidade > $produto['estoque']) {
            throw new Exception("O produto '{$produto['nome']}' não tem estoque suficiente. Disponível: {$produto['estoque']} unidades.");
        }
    }
    
    // Criar pedido
    $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, total, endereco_entrega) VALUES (?, ?, ?)");
    $stmt->execute([
        $_SESSION['usuario_id'], 
        $total, 
        $_POST['endereco']
    ]);
    $pedido_id = $pdo->lastInsertId();
    
    // Adicionar itens do pedido e atualizar estoque
    foreach ($produtos as $produto) {
        $quantidade = $_SESSION['carrinho'][$produto['id']];
        
        // Inserir item do pedido
        $stmt = $pdo->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $pedido_id, 
            $produto['id'], 
            $quantidade, 
            $produto['preco']
        ]);
        
        // Atualizar estoque
        $stmt = $pdo->prepare("UPDATE produtos SET estoque = estoque - ? WHERE id = ?");
        $stmt->execute([$quantidade, $produto['id']]);
    }
    
    // Commit da transação
    $pdo->commit();
    
    // Limpar carrinho
    unset($_SESSION['carrinho']);
    
    // Mensagem de sucesso
    $_SESSION['sucesso'] = "🎉 Pedido #$pedido_id realizado com sucesso! 
                           <br><small>Você pode acompanhar o status do seu pedido nesta página.</small>";
    
    // Redirecionar para página de pedidos
    header('Location: /ecommerce/conta/pedidos/');
    exit();
    
} catch (Exception $e) {
    // Rollback em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $_SESSION['erro'] = "❌ Erro ao processar pedido: " . $e->getMessage();
    header('Location: /ecommerce/checkout/');
    exit();
}
?>