<?php
require_once '../../includes/config.php';

// Verificar se o usuário é admin
if (!estaLogado() || !isset($_SESSION['admin']) || !$_SESSION['admin']) {
    redirect('/conta/login.php');
}

include '../../includes/header.php';

// Buscar produtos
$stmt = $pdo->query("SELECT * FROM produtos ORDER BY data_cadastro DESC");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gerenciar Produtos</h1>
        <a href="/admin/produtos/adicionar.php" class="btn btn-primary">Adicionar Produto</a>
    </div>
    
    <?php if (isset($_SESSION['sucesso'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['sucesso']; unset($_SESSION['sucesso']); ?></div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Preço</th>
                    <th>Estoque</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td><?php echo $produto['id']; ?></td>
                        <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                        <td><?php echo formatarPreco($produto['preco']); ?></td>
                        <td><?php echo $produto['estoque']; ?></td>
                        <td>
                            <a href="/admin/produtos/editar.php?id=<?php echo $produto['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                            <form method="post" action="/admin/produtos/excluir.php" class="d-inline">
                                <input type="hidden" name="id" value="<?php echo $produto['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este produto?')">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>