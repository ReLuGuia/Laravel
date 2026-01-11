<?php
require_once '../includes/config.php';

// Verificar se o usuário é admin
if (!estaLogado() || !isset($_SESSION['admin']) || !$_SESSION['admin']) {
    redirect('/conta/login.php');
}

include '../includes/header.php';
?>

<div class="container mt-5">
    <h1 class="mb-4">Painel de Administração</h1>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Produtos</h5>
                    <p class="card-text">Gerencie os produtos do seu e-commerce</p>
                    <a href="/admin/produtos/" class="btn btn-light">Acessar</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Pedidos</h5>
                    <p class="card-text">Visualize e gerencie os pedidos</p>
                    <a href="/admin/pedidos/" class="btn btn-light">Acessar</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Relatórios</h5>
                    <p class="card-text">Acompanhe o desempenho das vendas</p>
                    <a href="/admin/relatorios/" class="btn btn-light">Acessar</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>