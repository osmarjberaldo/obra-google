<?php
// Script para testar a conexão com o banco de dados
require_once 'api/config/pdo.php';

try {
    $conn = getDbConnection();
    echo "Conexão com o banco de dados estabelecida com sucesso!\n";
    
    // Testar uma consulta simples
    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM usuarios');
    $stmt->execute();
    $result = $stmt->fetch();
    echo "Total de usuários no banco: " . $result['count'] . "\n";
    
    // Testar consulta específica para o usuário de teste
    $stmt = $conn->prepare('SELECT id, nome, email FROM usuarios WHERE email = ?');
    $stmt->execute(['test@test.com.br']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "Usuário de teste encontrado:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Nome: " . $user['nome'] . "\n";
        echo "Email: " . $user['email'] . "\n";
    } else {
        echo "Usuário de teste não encontrado no banco.\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}