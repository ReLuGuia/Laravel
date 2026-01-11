<?php
header('Content-Type: application/json');

// SIMULAÇÃO - Sempre retorna pending para teste
echo json_encode(['status' => 'pending']);
?>