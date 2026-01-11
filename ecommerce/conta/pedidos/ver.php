<?php
require_once __DIR__ . '/../../includes/config.php';

// Verificar se o usuário está logado
if (!estaLogado()) {
    header('Location: /ecommerce/conta/login.php');
    exit();
}

// Verificar se o ID do pedido foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: /ecommerce/conta/pedidos/');
    exit();
}

$pedido_id = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

// Buscar pedido (verificando se pertence ao usuário)
try {
    $stmt = $pdo->prepare("SELECT p.*, u.nome as usuario_nome, u.email, u.telefone 
                          FROM pedidos p 
                          INNER JOIN usuarios u ON p.usuario_id = u.id 
                          WHERE p.id = ? AND p.usuario_id = ?");
    $stmt->execute([$pedido_id, $usuario_id]);
    $pedido = $stmt->fetch();
    
    if (!$pedido) {
        $_SESSION['erro'] = "Pedido não encontrado.";
        header('Location: /ecommerce/conta/pedidos/');
        exit();
    }
    
    // Buscar itens do pedido
    $stmt = $pdo->prepare("SELECT pi.*, pr.nome as produto_nome, pr.imagem 
                          FROM pedido_itens pi 
                          INNER JOIN produtos pr ON pi.produto_id = pr.id 
                          WHERE pi.pedido_id = ?");
    $stmt->execute([$pedido_id]);
    $itens = $stmt->fetchAll();
    
} catch (PDOException $e) {
    die("Erro ao buscar pedido: " . $e->getMessage());
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-5">
    <!-- Cabeçalho do Pedido -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>📋 Detalhes do Pedido</h1>
        <a href="/ecommerce/conta/pedidos/" class="btn btn-outline-secondary">
            ← Voltar para Pedidos
        </a>
    </div>
    
    <!-- Alertas -->
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
    
    <div class="row">
        <!-- Coluna Esquerda - Itens do Pedido -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">🛒 Itens do Pedido</h5>
                    <span class="badge bg-light text-dark">#<?php echo str_pad($pedido['id'], 5, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="60"></th>
                                    <th>Produto</th>
                                    <th class="text-center">Quantidade</th>
                                    <th class="text-end">Preço Unitário</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($itens as $item): ?>
                                    <tr>
                                        <td>
                                            <img src="/ecommerce/assets/images/<?php echo htmlspecialchars($item['imagem'] ?: 'placeholder.jpg'); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['produto_nome']); ?>"
                                                 class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['produto_nome']); ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary fs-6"><?php echo $item['quantidade']; ?></span>
                                        </td>
                                        <td class="text-end"><?php echo formatarPreco($item['preco_unitario']); ?></td>
                                        <td class="text-end fw-bold"><?php echo formatarPreco($item['preco_unitario'] * $item['quantidade']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Coluna Direita - Informações -->
        <div class="col-lg-4">
            <!-- Resumo do Pedido -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">📊 Resumo do Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span><?php echo formatarPreco($pedido['total']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Frete:</span>
                        <span class="text-success">Grátis</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong class="text-success fs-5"><?php echo formatarPreco($pedido['total']); ?></strong>
                    </div>
                </div>
            </div>
            
            <!-- Status do Pedido -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">📦 Status do Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <span class="badge fs-6 
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
                    </div>
                    
                    <!-- Timeline do Status -->
                    <div class="status-timeline">
                        <div class="timeline-item <?php echo $pedido['status'] == 'pendente' ? 'active' : ''; ?>">
                            <div class="timeline-dot"></div>
                            <span>Pedido Recebido</span>
                        </div>
                        <div class="timeline-item <?php echo in_array($pedido['status'], ['processando', 'enviado', 'entregue']) ? 'active' : ''; ?>">
                            <div class="timeline-dot"></div>
                            <span>Em Processamento</span>
                        </div>
                        <div class="timeline-item <?php echo in_array($pedido['status'], ['enviado', 'entregue']) ? 'active' : ''; ?>">
                            <div class="timeline-dot"></div>
                            <span>Enviado</span>
                        </div>
                        <div class="timeline-item <?php echo $pedido['status'] == 'entregue' ? 'active' : ''; ?>">
                            <div class="timeline-dot"></div>
                            <span>Entregue</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Informações de Contato -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">👤 Informações</h5>
                </div>
                <div class="card-body">
                    <p><strong>Cliente:</strong><br><?php echo htmlspecialchars($pedido['usuario_nome']); ?></p>
                    <p><strong>Email:</strong><br><?php echo htmlspecialchars($pedido['email']); ?></p>
                    <?php if ($pedido['telefone']): ?>
                        <p><strong>Telefone:</strong><br><?php echo htmlspecialchars($pedido['telefone']); ?></p>
                    <?php endif; ?>
                    <p><strong>Data do Pedido:</strong><br><?php echo date('d/m/Y \à\s H:i', strtotime($pedido['data_pedido'])); ?></p>
                </div>
            </div>
            
            <!-- Endereço de Entrega -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">🏠 Endereço de Entrega</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($pedido['endereco_entrega'])); ?></p>
                </div>
            </div>
            
            <!-- Ações -->
            <div class="mt-3">
                <a href="/ecommerce/" class="btn btn-outline-primary w-100 mb-2">
                    🛍️ Continuar Comprando
                </a>
                <a href="/ecommerce/conta/pedidos/" class="btn btn-outline-secondary w-100">
                    📋 Todos os Pedidos
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.status-timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
    padding-left: 10px;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -20px;
    top: 5px;
    width: 2px;
    height: 100%;
    background: #dee2e6;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #dee2e6;
    position: absolute;
    left: -25px;
    top: 5px;
}

.timeline-item.active .timeline-dot {
    background: #0d6efd;
}

.timeline-item.active:before {
    background: #0d6efd;
}

.timeline-item span {
    font-size: 0.9rem;
    color: #6c757d;
}

.timeline-item.active span {
    color: #0d6efd;
    font-weight: 500;
}
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>