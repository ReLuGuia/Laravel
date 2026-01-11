<?php
// LINHA 2 CORRIGIDA:
require_once __DIR__ . '/includes/config.php';
include __DIR__ . '/includes/header.php';

$stmt = $pdo->query("SELECT * FROM produtos ORDER BY data_cadastro DESC LIMIT 6");
$produtos = $stmt->fetchAll();
?>

<div class="container mt-5">
    <h1 class="mb-4">Bem-vindo ao E-commerce Básico</h1>
    <p class="lead mb-5">Confira nossos produtos em destaque</p>
    
    <div class="row">
        <?php foreach ($produtos as $produto): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <img src="/ecommerce/assets/images/<?php echo $produto['imagem'] ?: 'placeholder.jpg'; ?>" 
                         class="card-img-top" alt="<?php echo htmlspecialchars($produto['nome']); ?>"
                         style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                        <p class="card-text text-success fw-bold"><?php echo formatarPreco($produto['preco']); ?></p>
                    </div>
                    <div class="card-footer">
                        <a href="/ecommerce/produtos/ver.php?id=<?php echo $produto['id']; ?>" 
                           class="btn btn-primary w-100">Ver Detalhes</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center mt-5">
        <a href="/ecommerce/produtos/" class="btn btn-outline-primary btn-lg">
            Ver Todos os Produtos
        </a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>