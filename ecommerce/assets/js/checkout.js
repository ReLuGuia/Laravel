// assets/js/checkout.js

let totalAmount = 0;
let mp = null;

// Inicializar quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    console.log('Checkout carregado');
    
    // Inicializar Mercado Pago
    if (typeof MercadoPago !== 'undefined') {
        mp = new MercadoPago(publicKey, {
            locale: 'pt-BR'
        });
    }
    
    // Configurar botões
    configurarBotoes();
});

function configurarBotoes() {
    // Botão PIX
    const btnPix = document.getElementById('btn-pix');
    if (btnPix) {
        btnPix.addEventListener('click', pagamentoPix);
    }
    
    // Botão Boleto
    const btnBoleto = document.getElementById('btn-boleto');
    if (btnBoleto) {
        btnBoleto.addEventListener('click', pagamentoBoleto);
    }
    
    // Botão Cartão
    const btnCartao = document.getElementById('btn-cartao');
    if (btnCartao) {
        btnCartao.addEventListener('click', pagamentoCartao);
    }
}

// Função PIX
async function pagamentoPix() {
    try {
        console.log('Iniciando PIX...');
        mostrarLoading('Criando pedido...');
        
        const response = await fetch('/ecommerce/pagamento/salvar_pedido.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                metodo_pagamento: 'pix'
            })
        });
        
        const result = await response.json();
        esconderLoading();
        
        if (result.success) {
            criarPagamentoPix(result.pedido_id, result.total);
        } else {
            throw new Error(result.message);
        }
        
    } catch (error) {
        esconderLoading();
        mostrarErro('Erro: ' + error.message);
    }
}

// Criar pagamento PIX
async function criarPagamentoPix(pedidoId, total) {
    try {
        mostrarLoading('Gerando PIX...');
        
        const response = await fetch('/ecommerce/pagamento/criar_pagamento.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                metodo: 'pix',
                pedido_id: pedidoId,
                total: total
            })
        });
        
        const result = await response.json();
        esconderLoading();
        
        if (result.success) {
            mostrarPix(result);
        } else {
            throw new Error(result.message);
        }
        
    } catch (error) {
        esconderLoading();
        mostrarErro('Erro no PIX: ' + error.message);
    }
}

// Mostrar modal PIX
function mostrarPix(dados) {
    const modalHtml = `
        <div class="modal fade" id="pixModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">📱 Pagamento PIX</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p>Escaneie o QR Code com seu app bancário</p>
                        <img src="${dados.qr_code}" alt="QR Code" class="img-fluid mb-3" style="max-width: 250px;">
                        
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" value="${dados.codigo_pix}" readonly id="pixCode">
                            <button class="btn btn-outline-secondary" type="button" onclick="copiarPix()">
                                Copiar
                            </button>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>Pedido:</strong> #${dados.pedido_id}<br>
                            <strong>Valor:</strong> R$ ${parseFloat(dados.total).toFixed(2).replace('.', ',')}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" onclick="verificarPagamento('${dados.payment_id}', ${dados.pedido_id})">
                            ✅ Já paguei
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modal anterior se existir
    const modalAntigo = document.getElementById('pixModal');
    if (modalAntigo) {
        modalAntigo.remove();
    }
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('pixModal'));
    modal.show();
}

// Copiar código PIX
function copiarPix() {
    const input = document.getElementById('pixCode');
    input.select();
    document.execCommand('copy');
    alert('Código PIX copiado!');
}

// Verificar pagamento
async function verificarPagamento(paymentId, pedidoId) {
    try {
        mostrarLoading('Verificando pagamento...');
        
        const response = await fetch('/ecommerce/pagamento/verificar_pagamento.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                payment_id: paymentId,
                pedido_id: pedidoId
            })
        });
        
        const result = await response.json();
        esconderLoading();
        
        if (result.status === 'approved') {
            window.location.href = '/ecommerce/pagamento/sucesso.php?pedido_id=' + pedidoId;
        } else {
            alert('Pagamento ainda não identificado. Tente novamente em alguns segundos.');
        }
        
    } catch (error) {
        esconderLoading();
        mostrarErro('Erro ao verificar: ' + error.message);
    }
}

// Funções auxiliares
function mostrarLoading(mensagem) {
    // Criar ou mostrar loading
    let loading = document.getElementById('loading');
    if (!loading) {
        loading = document.createElement('div');
        loading.id = 'loading';
        loading.className = 'loading-overlay';
        loading.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner-border text-primary"></div>
                <p>${mensagem}</p>
            </div>
        `;
        document.body.appendChild(loading);
    }
    loading.style.display = 'flex';
}

function esconderLoading() {
    const loading = document.getElementById('loading');
    if (loading) {
        loading.style.display = 'none';
    }
}

function mostrarErro(mensagem) {
    alert(mensagem);
}

// Funções para outros métodos de pagamento
async function pagamentoBoleto() {
    alert('Boleto selecionado - em desenvolvimento');
}

async function pagamentoCartao() {
    alert('Cartão selecionado - em desenvolvimento');
}