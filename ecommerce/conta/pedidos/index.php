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
    
    <?php if (isset($_SESSION['sucesso'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['sucesso']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['sucesso']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['erro']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['erro']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($pedidos)): ?>
        <div class="alert alert-info text-center">
            <h4>🎉 Nenhum pedido encontrado</h4>
            <p>Você ainda não fez nenhum pedido em nossa loja.</p>
            <p class="mb-3">Que tal dar uma olhada nos nossos produtos?</p>
            <a href="/ecommerce/" class="btn btn-primary btn-lg">Fazer Primeira Compra</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
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
                            <td>
                                <strong>#<?php echo str_pad($pedido['id'], 5, '0', STR_PAD_LEFT); ?></strong>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></td>
                            <td class="fw-bold text-success"><?php echo formatarPreco($pedido['total']); ?></td>
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
                                   class="btn btn-sm btn-primary">
                                   👁️ Detalhes
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="text-center mt-3">
            <p class="text-muted">
                Mostrando <strong><?php echo count($pedidos); ?></strong> 
                pedido<?php echo count($pedidos) !== 1 ? 's' : ''; ?>
            </p>
        </div>
    <?php endif; ?>
    
    <div class="mt-4">
        <a href="/ecommerce/conta/" class="btn btn-outline-secondary">
            ← Voltar para Minha Conta
        </a>
        <a href="/ecommerce/" class="btn btn-outline-primary ms-2">
            🛍️ Continuar Comprando
        </a>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>