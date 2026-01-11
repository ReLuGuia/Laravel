<?php
require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';

// Buscar produtos com filtros
$categoria = $_GET['categoria'] ?? '';
$busca = $_GET['busca'] ?? '';
$ordenar = $_GET['ordenar'] ?? 'nome';

// Construir query base
$sql = "SELECT * FROM produtos WHERE 1=1";
$params = [];

if (!empty($busca)) {
    $sql .= " AND (nome LIKE ? OR descricao LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

// Ordenação
$ordenacoes_validas = ['nome', 'preco', 'data_cadastro'];
if (in_array($ordenar, $ordenacoes_validas)) {
    $sql .= " ORDER BY $ordenar";
} else {
    $sql .= " ORDER BY nome";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll();
?>

<div class="container mt-5">
    <h1 class="mb-4">📦 Nossos Produtos</h1>
    
    <!-- Filtros e Busca -->
    <div class="row mb-4">
        <div class="col-md-8">
            <form method="GET" class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="busca" class="form-control" placeholder="Buscar produtos..." 
                           value="<?php echo htmlspecialchars($busca); ?>">
                </div>
                <div class="col-md-3">
                    <select name="ordenar" class="form-select" onchange="this.form.submit()">
                        <option value="nome" <?php echo $ordenar === 'nome' ? 'selected' : ''; ?>>Ordenar por Nome</option>
                        <option value="preco" <?php echo $ordenar === 'preco' ? 'selected' : ''; ?>>Ordenar por Preço</option>
                        <option value="data_cadastro" <?php echo $ordenar === 'data_cadastro' ? 'selected' : ''; ?>>Mais Recentes</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">🔍 Buscar</button>
                </div>
            </form>
        </div>
        <div class="col-md-4 text-end">
            <a href="/ecommerce/" class="btn btn-outline-secondary">← Voltar</a>
        </div>
    </div>

    <!-- Resultados -->
    <?php if (empty($produtos)): ?>
        <div class="alert alert-info text-center">
            <h4>Nenhum produto encontrado</h4>
            <p>Tente alterar os termos da busca ou verifique nossa loja completa.</p>
            <a href="/ecommerce/produtos/" class="btn btn-primary">Ver Todos os Produtos</a>
        </div>
    <?php else: ?>
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
                            <p class="card-text small text-muted">
                                <?php 
                                $descricao = strip_tags($produto['descricao']);
                                echo strlen($descricao) > 100 ? substr($descricao, 0, 100) . '...' : $descricao;
                                ?>
                            </p>
                            <?php if ($produto['estoque'] > 0): ?>
                                <span class="badge bg-success">Em estoque: <?php echo $produto['estoque']; ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger">Esgotado</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <a href="/ecommerce/produtos/ver.php?id=<?php echo $produto['id']; ?>" 
                               class="btn btn-primary w-100">Ver Detalhes</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Contador de resultados -->
        <div class="mt-4 text-center">
            <p class="text-muted">
                Mostrando <strong><?php echo count($produtos); ?></strong> 
                produto<?php echo count($produtos) !== 1 ? 's' : ''; ?>
                <?php echo !empty($busca) ? 'para "' . htmlspecialchars($busca) . '"' : ''; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>