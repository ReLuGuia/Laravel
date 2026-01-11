<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$metodo = $input['metodo'] ?? 'pix';
$pedido_id = $input['pedido_id'] ?? null;
$total = $input['total'] ?? 0;

if (!$pedido_id) {
    echo json_encode(['success' => false, 'message' => 'Pedido não informado']);
    exit();
}

// SIMULAÇÃO - Em produção, integrar com Mercado Pago real
if ($metodo === 'pix') {
    echo json_encode([
        'success' => true,
        'payment_id' => 'TEST_' . uniqid(),
        'pedido_id' => $pedido_id,
        'total' => $total,
        'qr_code' => 'https://via.placeholder.com/250x250/008000/FFFFFF?text=QR+CODE+PIX',
        'codigo_pix' => '00020126580014br.gov.bcb.pix0136teste-pix-' . $pedido_id
    ]);
} elseif ($metodo === 'boleto') {
    echo json_encode([
        'success' => true,
        'payment_id' => 'TEST_' . uniqid(),
        'pedido_id' => $pedido_id,
        'boleto_url' => 'https://via.placeholder.com/600x800/FFFFFF/000000?text=BOLETO'
    ]);
} else {
    echo json_encode([
        'success' => true,
        'payment_id' => 'TEST_' . uniqid(),
        'pedido_id' => $pedido_id
    ]);
}
?>