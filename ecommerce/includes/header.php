<?php
// includes/header.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo defined('SITE_NOME') ? SITE_NOME : 'E-commerce'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 56px;
        }
        .card {
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/ecommerce/"><?php echo defined('SITE_NOME') ? SITE_NOME : 'E-commerce'; ?></a>
            
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/ecommerce/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ecommerce/produtos/">Produtos</a>
                    </li>
                </ul>
                
                <div class="d-flex">
                    <a href="/ecommerce/carrinho/" class="btn btn-outline-light me-2">
                        🛒 Carrinho 
                        <?php 
                        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['carrinho'])) {
                            $total_itens = array_sum($_SESSION['carrinho']);
                            echo '<span class="badge bg-danger">'.$total_itens.'</span>';
                        }
                        ?>
                    </a>
                    
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <a href="/ecommerce/conta/" class="btn btn-outline-light me-2">Minha Conta</a>
                        <a href="/ecommerce/conta/logout.php" class="btn btn-outline-light">Sair</a>
                    <?php else: ?>
                        <a href="/ecommerce/conta/login.php" class="btn btn-outline-light me-2">Login</a>
                        <a href="/ecommerce/conta/cadastro.php" class="btn btn-outline-light">Cadastrar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4"></div>