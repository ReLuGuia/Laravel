<?php
require_once __DIR__ . '/../../includes/config.php';

// Verificar se o usuário está logado
if (!estaLogado()) {
    header('Location: /ecommerce/conta/login.php');
    exit();
}

include __DIR__ . '/../../includes/header.php';

// Buscar todos os pedidos do usuário
$usuario_id = $_SESSION['usuario_id'];
try {
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY data_pedido DESC");
    $stmt->execute([$usuario_id]);
    $pedidos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar pedidos: " . $e->getMessage());
}
?>

<div class="container mt-5">
    <h1 class="mb-4">📋 Meus Pedidos</h1>
    
    <?php if (empty($pedidos)): ?>
        <div class="alert alert-info text-center">
            <h4>Nenhum pedido encontrado</h4>
            <p>Você ainda não fez nenhum pedido em nossa loja.</p>
            <a href="/ecommerce/" class="btn btn-primary">Fazer Primeira Compra</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Nº Pedido</th>
                        <th>Data</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td>#<?php echo str_pad($pedido['id'], 5, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></td>
                            <td><?php echo formatarPreco($pedido['total']); ?></td>
                            <td>
                                <span class="badge 
                                    <?php 
                                    switch($pedido['status']) {
                                        case 'pendente': echo 'bg-warning'; break;
                                        case 'processando': echo 'bg-info'; break;
                                        case 'enviado': echo 'bg-primary'; break;
                                        case 'entregue': echo 'bg-success'; break;
                                        default: echo 'bg-secondary';
                                    }
                                    ?>">
                                    <?php echo ucfirst($pedido['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="/ecommerce/conta/pedidos/ver.php?id=<?php echo $pedido['id']; ?>" 
                                   class="btn btn-sm btn-primary">Detalhes</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <div class="mt-3">
        <a href="/ecommerce/conta/" class="btn btn-outline-secondary">← Voltar para Minha Conta</a>
        <a href="/ecommerce/" class="btn btn-outline-primary">Continuar Comprando</a>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>