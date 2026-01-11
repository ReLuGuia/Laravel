<?php
require_once __DIR__ . '/../includes/config.php';

if (!estaLogado() || empty($_SESSION['carrinho'])) {
    header('Location: /ecommerce/');
    exit();
}

// Calcular total
$total = 0;
$produtos_ids = array_keys($_SESSION['carrinho']);
if (!empty($produtos_ids)) {
    $placeholders = implode(',', array_fill(0, count($produtos_ids), '?'));
    $stmt = $pdo->prepare("SELECT id, nome, preco FROM produtos WHERE id IN ($placeholders)");
    $stmt->execute($produtos_ids);
    $produtos = $stmt->fetchAll();
    
    foreach ($produtos as $produto) {
        $quantidade = $_SESSION['carrinho'][$produto['id']];
        $total += $produto['preco'] * $quantidade;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5">
    <h1 class="mb-4">💳 Finalizar Compra</h1>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Escolha a Forma de Pagamento</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-primary w-100 h-100 py-4" onclick="iniciarPagamento('pix')">
                                <div class="fs-1">📱</div>
                                <div class="fw-bold">PIX</div>
                                <small>Pagamento instantâneo</small>
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-success w-100 h-100 py-4" onclick="iniciarPagamento('boleto')">
                                <div class="fs-1">📄</div>
                                <div class="fw-bold">BOLETO</div>
                                <small>Pague em qualquer banco</small>
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-info w-100 h-100 py-4" onclick="iniciarPagamento('cartao')">
                                <div class="fs-1">💳</div>
                                <div class="fw-bold">CARTÃO</div>
                                <small>Crédito ou Débito</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Área para mostrar o status do pagamento -->
            <div id="payment-status" class="mt-3" style="display: none;">
                <div class="alert alert-info">
                    <div class="spinner-border spinner-border-sm me-2"></div>
                    <span id="status-message">Processando pagamento...</span>
                </div>
            </div>

            <!-- Área para QR Code PIX -->
            <div id="pix-area" class="mt-3" style="display: none;">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">📱 Pagamento PIX</h6>
                    </div>
                    <div class="card-body text-center">
                        <div id="qrcode-container"></div>
                        <div class="mt-3">
                            <button class="btn btn-success" onclick="verificarPagamento()">
                                ✅ Já efetuei o pagamento
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Resumo do Pedido -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">📦 Resumo do Pedido</h6>
                </div>
                <div class="card-body">
                    <?php foreach ($produtos as $produto): ?>
                        <?php $quantidade = $_SESSION['carrinho'][$produto['id']]; ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?php echo htmlspecialchars($produto['nome']); ?> x<?php echo $quantidade; ?></span>
                            <span>R$ <?php echo number_format($produto['preco'] * $quantidade, 2, ',', '.'); ?></span>
                        </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>Total:</strong>
                        <strong class="text-success fs-5">R$ <?php echo number_format($total, 2, ',', '.'); ?></strong>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">👤 Seus Dados</h5>
                </div>
                <div class="card-body">
                    <?php
                    $usuario_id = $_SESSION['usuario_id'];
                    $stmt = $pdo->prepare("SELECT nome, email, telefone, endereco FROM usuarios WHERE id = ?");
                    $stmt->execute([$usuario_id]);
                    $usuario = $stmt->fetch();
                    ?>
                    <p><strong>Nome:</strong><br><?php echo htmlspecialchars($usuario['nome']); ?></p>
                    <p><strong>Email:</strong><br><?php echo htmlspecialchars($usuario['email']); ?></p>
                    <p><strong>Telefone:</strong><br><?php echo htmlspecialchars($usuario['telefone']); ?></p>
                    <p><strong>Endereço:</strong><br><?php echo nl2br(htmlspecialchars($usuario['endereco'])); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript DIRETO no arquivo (evita problemas de carregamento) -->
<script>
// Variáveis globais
let currentPaymentId = null;
let currentPedidoId = null;

// Função principal para iniciar pagamento
async function iniciarPagamento(metodo) {
    console.log('Iniciando pagamento:', metodo);
    
    try {
        // Mostrar status
        mostrarStatus('Criando pedido...');
        
        // 1. Salvar pedido no banco
        const pedidoResponse = await fetch('/ecommerce/pagamento/salvar_pedido.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                metodo_pagamento: metodo
            })
        });
        
        const pedidoResult = await pedidoResponse.json();
        console.log('Resposta do pedido:', pedidoResult);
        
        if (!pedidoResult.success) {
            throw new Error(pedidoResult.message || 'Erro ao criar pedido');
        }
        
        currentPedidoId = pedidoResult.pedido_id;
        
        // 2. Criar pagamento
        mostrarStatus('Processando pagamento...');
        
        const pagamentoResponse = await fetch('/ecommerce/pagamento/criar_pagamento.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                metodo: metodo,
                pedido_id: currentPedidoId,
                total: pedidoResult.total
            })
        });
        
        const pagamentoResult = await pagamentoResponse.json();
        console.log('Resposta do pagamento:', pagamentoResult);
        
        if (pagamentoResult.success) {
            currentPaymentId = pagamentoResult.payment_id;
            
            if (metodo === 'pix') {
                // Mostrar QR Code PIX
                mostrarQRCodePix(pagamentoResult);
            } else if (metodo === 'boleto') {
                // Abrir boleto em nova janela
                window.open(pagamentoResult.boleto_url, '_blank');
                mostrarStatus('Boleto gerado! Verifique sua janela de download.');
                setTimeout(() => {
                    window.location.href = '/ecommerce/conta/pedidos/';
                }, 3000);
            } else {
                // Cartão - redirecionar para sucesso (simulação)
                window.location.href = '/ecommerce/pagamento/sucesso.php?pedido_id=' + currentPedidoId;
            }
            
        } else {
            throw new Error(pagamentoResult.message || 'Erro no pagamento');
        }
        
    } catch (error) {
        console.error('Erro no pagamento:', error);
        mostrarStatus('Erro: ' + error.message, 'danger');
    }
}

// Mostrar QR Code PIX
function mostrarQRCodePix(dados) {
    esconderStatus();
    
    // Mostrar área do PIX
    const pixArea = document.getElementById('pix-area');
    const qrcodeContainer = document.getElementById('qrcode-container');
    
    qrcodeContainer.innerHTML = `
        <p>Escaneie o QR Code com seu app bancário</p>
        <img src="${dados.qr_code}" alt="QR Code PIX" class="img-fluid border rounded" style="max-width: 250px;">
        <div class="mt-3">
            <div class="input-group">
                <input type="text" class="form-control" value="${dados.codigo_pix}" readonly id="pix-code">
                <button class="btn btn-outline-secondary" type="button" onclick="copiarCodigoPix()">
                    📋 Copiar
                </button>
            </div>
        </div>
        <div class="alert alert-info mt-3">
            <strong>💰 Valor:</strong> R$ ${parseFloat(dados.total).toFixed(2).replace('.', ',')}<br>
            <strong>📦 Pedido:</strong> #${dados.pedido_id}
        </div>
    `;
    
    pixArea.style.display = 'block';
}

// Copiar código PIX
function copiarCodigoPix() {
    const codigoPix = document.getElementById('pix-code').value;
    
    navigator.clipboard.writeText(codigoPix).then(() => {
        alert('✅ Código PIX copiado! Cole no seu app bancário.');
    }).catch(() => {
        // Fallback para navegadores antigos
        const input = document.getElementById('pix-code');
        input.select();
        document.execCommand('copy');
        alert('✅ Código PIX copiado!');
    });
}

// Verificar pagamento PIX
async function verificarPagamento() {
    if (!currentPaymentId || !currentPedidoId) {
        alert('Erro: ID do pagamento não encontrado');
        return;
    }
    
    try {
        mostrarStatus('Verificando pagamento...');
        
        const response = await fetch('/ecommerce/pagamento/verificar_pagamento.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                payment_id: currentPaymentId,
                pedido_id: currentPedidoId
            })
        });
        
        const result = await response.json();
        
        if (result.status === 'approved') {
            window.location.href = '/ecommerce/pagamento/sucesso.php?pedido_id=' + currentPedidoId;
        } else if (result.status === 'pending') {
            mostrarStatus('Pagamento ainda não identificado. Tente novamente em alguns segundos.', 'warning');
        } else {
            mostrarStatus('Pagamento não aprovado. Tente novamente.', 'danger');
        }
        
    } catch (error) {
        console.error('Erro ao verificar pagamento:', error);
        mostrarStatus('Erro ao verificar pagamento: ' + error.message, 'danger');
    }
}

// Funções auxiliares para mostrar/esconder status
function mostrarStatus(mensagem, tipo = 'info') {
    const statusDiv = document.getElementById('payment-status');
    const messageSpan = document.getElementById('status-message');
    
    statusDiv.style.display = 'block';
    messageSpan.textContent = mensagem;
    
    // Atualizar classe do alert
    statusDiv.querySelector('.alert').className = `alert alert-${tipo}`;
}

function esconderStatus() {
    document.getElementById('payment-status').style.display = 'none';
}

// Debug: Verificar se o JavaScript está carregando
console.log('JavaScript do checkout carregado!');
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM totalmente carregado');
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>