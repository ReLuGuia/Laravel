<?php
require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';

// Mensagens de feedback
if (isset($_SESSION['sucesso'])) {
    echo '<div class="alert alert-success">' . $_SESSION['sucesso'] . '</div>';
    unset($_SESSION['sucesso']);
}

if (isset($_SESSION['erro'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['erro'] . '</div>';
    unset($_SESSION['erro']);
}
?>

<div class="container mt-5">
    <h1 class="mb-4">🛒 Seu Carrinho</h1>
    
    <?php if (empty($_SESSION['carrinho'])): ?>
        <div class="alert alert-info">
            <h4>Seu carrinho está vazio</h4>
            <p>Que tal dar uma olhada nos nossos produtos?</p>
            <a href="/ecommerce/" class="btn btn-primary">Continuar Comprando</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Produto</th>
                        <th>Preço Unitário</th>
                        <th>Quantidade</th>
                        <th>Subtotal</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_geral = 0;
                    $produtos_ids = array_keys($_SESSION['carrinho']);
                    
                    if (!empty($produtos_ids)) {
                        $placeholders = implode(',', array_fill(0, count($produtos_ids), '?'));
                        $stmt = $pdo->prepare("SELECT id, nome, preco, estoque FROM produtos WHERE id IN ($placeholders)");
                        $stmt->execute($produtos_ids);
                        $produtos = $stmt->fetchAll();
                        
                        foreach ($produtos as $produto):
                            $quantidade = $_SESSION['carrinho'][$produto['id']];
                            $subtotal = $produto['preco'] * $quantidade;
                            $total_geral += $subtotal;
                    ?>
                            <tr>
                                <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                <td><?php echo formatarPreco($produto['preco']); ?></td>
                                <td>
                                    <form method="post" action="/ecommerce/carrinho/atualizar.php" class="d-inline">
                                        <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">
                                        <div class="input-group" style="width: 120px;">
                                            <input type="number" name="quantidade" value="<?php echo $quantidade; ?>" 
                                                   min="1" max="<?php echo $produto['estoque']; ?>" class="form-control">
                                            <button type="submit" class="btn btn-outline-primary">✓</button>
                                        </div>
                                    </form>
                                </td>
                                <td><?php echo formatarPreco($subtotal); ?></td>
                                <td>
                                    <form method="post" action="/ecommerce/carrinho/remover.php" class="d-inline">
                                        <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">🗑️ Remover</button>
                                    </form>
                                </td>
                            </tr>
                    <?php 
                        endforeach;
                    }
                    ?>
                </tbody>
                <tfoot class="table-dark">
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td colspan="2"><strong><?php echo formatarPreco($total_geral); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
            <a href="/ecommerce/" class="btn btn-outline-primary">
                ← Continuar Comprando
            </a>
            
            <?php if (estaLogado()): ?>
                <a href="/ecommerce/checkout/" class="btn btn-success">
                    Finalizar Compra →
                </a>
            <?php else: ?>
                <a href="/ecommerce/conta/login.php" class="btn btn-warning">
                    💳 Fazer Login para Finalizar
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>