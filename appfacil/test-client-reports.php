<?php
// Script de teste para verificar se o endpoint relatorios_cliente.php está funcionando corretamente

// Incluir configuração do banco de dados
require_once __DIR__ . '/config/pdo.php';

// Conexão com o banco
$conn = getDbConnection();

// ID do usuário para teste (você pode alterar conforme necessário)
$usuario_id = 15; // Exemplo de usuário

echo "Testando endpoint relatorios_cliente.php para usuario_id = $usuario_id\n\n";

// 1. Verificar clientes do usuário
echo "1. Clientes do usuário $usuario_id:\n";
$stmt = $conn->prepare('SELECT id, nome, email FROM clientes WHERE usuario_id = ?');
$stmt->execute([$usuario_id]);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($clientes)) {
    echo "   Nenhum cliente encontrado para este usuário.\n";
} else {
    foreach ($clientes as $cliente) {
        echo "   ID: {$cliente['id']}, Nome: {$cliente['nome']}, Email: {$cliente['email']}\n";
    }
}

echo "\n";

// 2. Verificar relatórios do cliente
echo "2. Relatórios de clientes para o usuário $usuario_id:\n";

// Obter IDs dos clientes
$clienteIds = array_column($clientes, 'id');

if (empty($clienteIds)) {
    echo "   Nenhum relatório de cliente encontrado (nenhum cliente).\n";
} else {
    // Converter array de clientes para string para usar na query
    $placeholders = str_repeat('?,', count($clienteIds) - 1) . '?';
    
    $sql = "SELECT id, obra_id, nome_relatorio, data_relatorio, status, id_cliente FROM relatorios_diarios WHERE usuario_id = ? AND id_cliente IN ($placeholders) ORDER BY data_relatorio DESC";
    $params = array_merge([$usuario_id], $clienteIds);
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($relatorios)) {
        echo "   Nenhum relatório de cliente encontrado.\n";
    } else {
        foreach ($relatorios as $relatorio) {
            echo "   ID: {$relatorio['id']}, Obra: {$relatorio['obra_id']}, Cliente: {$relatorio['id_cliente']}, Nome: {$relatorio['nome_relatorio']}, Data: {$relatorio['data_relatorio']}, Status: {$relatorio['status']}\n";
            
            // Verificar nome da obra
            $obraStmt = $conn->prepare('SELECT nome_obra FROM obras WHERE id = ?');
            $obraStmt->execute([$relatorio['obra_id']]);
            $obra = $obraStmt->fetch(PDO::FETCH_ASSOC);
            echo "      Obra: " . ($obra ? $obra['nome_obra'] : 'Obra não encontrada') . "\n";
        }
    }
}

echo "\n";

// 3. Verificar relatórios com status 'finalizado'
echo "3. Relatórios FINALIZADOS de clientes para o usuário $usuario_id:\n";

if (empty($clienteIds)) {
    echo "   Nenhum relatório finalizado de cliente encontrado (nenhum cliente).\n";
} else {
    $sql = "SELECT id, obra_id, nome_relatorio, data_relatorio, status, id_cliente FROM relatorios_diarios WHERE usuario_id = ? AND id_cliente IN ($placeholders) AND status = 'finalizado' ORDER BY data_relatorio DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $relatoriosFinalizados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($relatoriosFinalizados)) {
        echo "   Nenhum relatório finalizado de cliente encontrado.\n";
    } else {
        foreach ($relatoriosFinalizados as $relatorio) {
            echo "   ID: {$relatorio['id']}, Obra: {$relatorio['obra_id']}, Cliente: {$relatorio['id_cliente']}, Nome: {$relatorio['nome_relatorio']}, Data: {$relatorio['data_relatorio']}\n";
        }
    }
}

$conn = null;
?>