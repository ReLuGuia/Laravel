<?php
require_once __DIR__ . '/../includes/config.php';

// Se já estiver logado, redireciona
if (estaLogado()) {
    header('Location: /ecommerce/');
    exit();
}

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Login bem-sucedido
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_admin'] = $usuario['admin'];
            
            // Redirecionar para página inicial (CAMINHO FIXO)
            header('Location: /ecommerce/');
            exit();
        } else {
            $erro = "Email ou senha incorretos!";
        }
    } catch (PDOException $e) {
        $erro = "Erro no sistema: " . $e->getMessage();
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Login</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($erro)): ?>
                        <div class="alert alert-danger"><?php echo $erro; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['sucesso'])): ?>
                        <div class="alert alert-success">Cadastro realizado com sucesso! Faça login.</div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Entrar</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Não tem conta? <a href="/ecommerce/conta/cadastro.php">Cadastre-se aqui</a></p>
                    </div>
                    
                    <div class="text-center">
                        <small class="text-muted">
                            <strong>Usuário de teste:</strong><br>
                            Email: joao@email.com<br>
                            Senha: 123456
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>