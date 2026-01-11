<?php
// fix_tables.php
require_once 'includes/config.php';

echo "<h2>Verificando e corrigindo tabelas...</h2>";

try {
    // Verificar se a coluna metodo_pagamento existe
    $stmt = $pdo->query("DESCRIBE pedidos");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $coluna_existe = false;
    foreach ($colunas as $coluna) {
        if ($coluna['Field'] == 'metodo_pagamento') {
            $coluna_existe = true;
            break;
        }
    }
    
    if (!$coluna_existe) {
        echo "<p>Adicionando coluna 'metodo_pagamento'...</p>";
        $pdo->exec("ALTER TABLE pedidos ADD COLUMN metodo_pagamento VARCHAR(20) DEFAULT 'cartao'");
        echo "<p style='color: green;'>✅ Coluna 'metodo_pagamento' adicionada!</p>";
    } else {
        echo "<p style='color: green;'>✅ Coluna 'metodo_pagamento' já existe!</p>";
    }
    
    // Verificar coluna payment_id
    $coluna_existe = false;
    foreach ($colunas as $coluna) {
        if ($coluna['Field'] == 'payment_id') {
            $coluna_existe = true;
            break;
        }
    }
    
    if (!$coluna_existe) {
        echo "<p>Adicionando coluna 'payment_id'...</p>";
        $pdo->exec("ALTER TABLE pedidos ADD COLUMN payment_id VARCHAR(100) NULL");
        echo "<p style='color: green;'>✅ Coluna 'payment_id' adicionada!</p>";
    } else {
        echo "<p style='color: green;'>✅ Coluna 'payment_id' já existe!</p>";
    }
    
    echo "<h3 style='color: green;'>✅ Tabelas verificadas e corrigidas com sucesso!</h3>";
    echo "<p><a href='/ecommerce/checkout/'>Voltar para o Checkout</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>