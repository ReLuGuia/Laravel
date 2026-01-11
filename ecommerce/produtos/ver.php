<?php
// Ativar display de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir configurações
require_once __DIR__ . '/../includes/config.php';

// Verificar se o ID do produto foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: /ecommerce/');
    exit();
}

// Sanitizar o ID
$produto_id = (int)$_GET['id'];

if ($produto_id <= 0) {
    header('Location: /ecommerce/');
    exit();
}

try {
    // Buscar produto no banco
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch();
    
    // Verificar se produto existe
    if (!$produto) {
        header('Location: /ecommerce/');
        exit();
    }
    
} catch (PDOException $e) {
    die("Erro ao buscar produto: " . $e->getMessage());
}

// Incluir header
include __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <img src="/ecommerce/assets/images/<?php echo htmlspecialchars($produto['imagem'] ?: 'placeholder.jpg'); ?>" 
                 class="img-fluid rounded shadow" 
                 alt="<?php echo htmlspecialchars($produto['nome']); ?>"
                 style="max-height: 400px; object-fit: cover; width: 100%;">
        </div>
        
        <div class="col-md-6">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/ecommerce/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/ecommerce/produtos/">Produtos</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($produto['nome']); ?></li>
                </ol>
            </nav>
            
            <h1 class="fw-bold"><?php echo htmlspecialchars($produto['nome']); ?></h1>
            
            <div class="d-flex align-items-center mb-3">
                <h2 class="text-success fw-bold me-3"><?php echo formatarPreco($produto['preco']); ?></h2>
                <?php if ($produto['estoque'] > 0): ?>
                    <span class="badge bg-success fs-6">✅ Em estoque</span>
                <?php else: ?>
                    <span class="badge bg-danger fs-6">⛔ Esgotado</span>
                <?php endif; ?>
            </div>
            
            <?php if ($produto['estoque'] > 0): ?>
                <p class="text-muted">📦 Disponível: <?php echo $produto['estoque']; ?> unidades</p>
            <?php endif; ?>
            
            <div class="product-description mb-4">
                <h4 class="mb-3">Descrição do Produto</h4>
                <p class="lead" style="line-height: 1.6;">
                    <?php echo nl2br(htmlspecialchars($produto['descricao'])); ?>
                </p>
            </div>
            
            <?php if ($produto['estoque'] > 0): ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Adicionar ao Carrinho</h5>
                        <form method="post" action="/ecommerce/carrinho/adicionar.php">
                            <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">
                            
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <label for="quantidade" class="form-label">Quantidade:</label>
                                    <input type="number" class="form-control form-control-lg" 
                                           id="quantidade" name="quantidade" 
                                           value="1" min="1" max="<?php echo $produto['estoque']; ?>">
                                </div>
                                
                                <div class="col-md-8">
                                    <button type="submit" class="btn btn-primary btn-lg w-100 mt-3 mt-md-0">
                                        <i class="bi bi-cart-plus"></i> 🛒 Adicionar ao Carrinho
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <h5>Produto Indisponível</h5>
                    <p>Este produto está temporariamente esgotado. Volte em breve!</p>
                </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="/ecommerce/produtos/" class="btn btn-outline-secondary">
                    ← Voltar para Produtos
                </a>
                <a href="/ecommerce/" class="btn btn-outline-primary ms-2">
                    🏠 Continuar Comprando
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .product-description {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        border-left: 4px solid #0d6efd;
    }
    
    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 1rem;
    }
    
    .btn-lg {
        padding: 12px 24px;
        font-size: 1.1rem;
    }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>