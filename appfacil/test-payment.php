<?php
// Script de teste para registrar pagamento
require_once __DIR__ . '/config/pdo.php';

function testPaymentRegistration() {
    try {
        // Conexão com o banco
        $conn = getDbConnection();
        
        // Dados de teste
        $usuario_id = 1;
        $order_nsu = uniqid('order_test_', true);
        $amount = 39.90;
        $customer_email = 'teste@example.com';
        $plano_tipo = 'basic';
        $periodo = 'mensal';
        
        // Inserir registro na tabela pagamentos_infinitepay
        $stmt = $conn->prepare('
            INSERT INTO pagamentos_infinitepay 
            (usuario_id, order_nsu, status, amount, customer_email, plano_tipo, periodo, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ');
        
        $ok = $stmt->execute([
            $usuario_id,
            $order_nsu,
            'pending',
            $amount,
            $customer_email,
            $plano_tipo,
            $periodo
        ]);
        
        if ($ok) {
            echo "Pagamento registrado com sucesso!\n";
            echo "ID: " . $conn->lastInsertId() . "\n";
            echo "Order NSU: " . $order_nsu . "\n";
            return true;
        } else {
            echo "Erro ao registrar pagamento\n";
            return false;
        }
    } catch (Exception $e) {
        echo "Erro: " . $e->getMessage() . "\n";
        return false;
    }
}

// Executar o teste
testPaymentRegistration();
?>