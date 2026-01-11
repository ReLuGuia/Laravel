<?php
require_once __DIR__ . '/../includes/config.php';

// Verificar se o usuário está logado
if (!estaLogado()) {
    header('Location: /ecommerce/conta/login.php');
    exit();
}

include __DIR__ . '/../includes/header.php';

// Buscar dados do usuário
$usuario_id = $_SESSION['usuario_id'];
try {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();
} catch (PDOException $e) {
    die("Erro ao buscar dados do usuário: " . $e->getMessage());
}

// Buscar pedidos do usuário
try {
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY data_pedido DESC LIMIT 5");
    $stmt->execute([$usuario_id]);
    $pedidos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar pedidos: " . $e->getMessage());
}
?>

<div class="container mt-5">
    <h1 class="mb-4">👤 Minha Conta</h1>
    
    <?php if (isset($_SESSION['sucesso'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['sucesso']; unset($_SESSION['sucesso']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['erro'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['erro']; unset($_SESSION['erro']); ?></div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Informações do Usuário -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Meus Dados</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Nome:</strong> <?php echo htmlspecialchars($usuario['nome']); ?>
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?>
                    </div>
                    <div class="mb-3">
                        <strong>Telefone:</strong> <?php echo htmlspecialchars($usuario['telefone'] ?: 'Não informado'); ?>
                    </div>
                    <div class="mb-3">
                        <strong>Endereço:</strong> 
                        <?php if ($usuario['endereco']): ?>
                            <p class="mt-1"><?php echo nl2br(htmlspecialchars($usuario['endereco'])); ?></p>
                        <?php else: ?>
                            <span class="text-muted">Não informado</span>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <strong>Data de Cadastro:</strong> 
                        <?php echo date('d/m/Y', strtotime($usuario['data_cadastro'])); ?>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="/ecommerce/conta/editar.php" class="btn btn-outline-primary">
                            ✏️ Editar Dados
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pedidos Recentes -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Meus Pedidos Recentes</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($pedidos)): ?>
                        <p class="text-muted">Você ainda não fez nenhum pedido.</p>
                        <a href="/ecommerce/" class="btn btn-primary">Fazer Primeira Compra</a>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($pedidos as $pedido): ?>
                                <a href="/ecommerce/conta/pedidos/ver.php?id=<?php echo $pedido['id']; ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Pedido #<?php echo str_pad($pedido['id'], 5, '0', STR_PAD_LEFT); ?></h6>
                                        <small>
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
                                        </small>
                                    </div>
                                    <p class="mb-1">Total: <?php echo formatarPreco($pedido['total']); ?></p>
                                    <small class="text-muted">
                                        Data: <?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?>
                                    </small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mt-3 text-center">
                            <a href="/ecommerce/conta/pedidos/" class="btn btn-outline-success">
                                📋 Ver Todos os Pedidos
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Ações Rápidas -->
            <div class="card mt-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Ações</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/ecommerce/" class="btn btn-outline-primary">
                            🛍️ Continuar Comprando
                        </a>
                        <a href="/ecommerce/carrinho/" class="btn btn-outline-warning">
                            🛒 Ver Carrinho
                        </a>
                        <a href="/ecommerce/conta/logout.php" class="btn btn-outline-danger">
                            🚪 Sair
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>