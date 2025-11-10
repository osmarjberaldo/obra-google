<?php
// Headers CORS
header('Access-Control-Allow-Origin: https://gestaodeobrafacil.com');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');

// Responder às requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');

require_once __DIR__ . '/config/pdo.php';

function response($success, $message, $data = null) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data], JSON_PRETTY_PRINT);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $conn = getDbConnection();
    
    switch ($method) {
        case 'GET':
            handleGet($conn);
            break;
        case 'POST':
            handlePost($conn);
            break;
        case 'PUT':
            handlePut($conn);
            break;
        case 'DELETE':
            handleDelete($conn);
            break;
        default:
            response(false, 'Método não permitido');
    }
} catch (PDOException $e) {
    error_log('Erro de conexão com o banco de dados: ' . $e->getMessage());
    response(false, 'Erro de conexão com o banco de dados: ' . $e->getMessage());
} catch (Exception $e) {
    error_log('Erro inesperado: ' . $e->getMessage());
    response(false, 'Ocorreu um erro inesperado');
}

function handleGet($conn) {
    $usuario_id = $_GET['usuario_id'] ?? null;
    
    if (!$usuario_id) {
        response(false, 'ID do usuário é obrigatório');
    }

    try {
        $sql = 'SELECT id, nome FROM fornecedores WHERE usuario_id = ? AND status = "ativo" ORDER BY nome ASC';
        $stmt = $conn->prepare($sql);
        $stmt->execute([$usuario_id]);
        $fornecedores = $stmt->fetchAll();
        
        response(true, 'Fornecedores listados', $fornecedores);
    } catch (Exception $e) {
        response(false, 'Erro ao buscar fornecedores: ' . $e->getMessage());
    }
}

function handlePost($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['usuario_id']) || !isset($data['nome'])) {
        response(false, 'ID do usuário e nome são obrigatórios');
    }
    
    try {
        $sql = 'INSERT INTO fornecedores (
                    usuario_id, nome, tipo_pessoa, cpf_cnpj, telefone, 
                    email, endereco, cidade, estado, cep, categoria, observacoes, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $data['usuario_id'],
            $data['nome'],
            $data['tipo_pessoa'] ?? 'juridica',
            $data['cpf_cnpj'] ?? null,
            $data['telefone'] ?? null,
            $data['email'] ?? null,
            $data['endereco'] ?? null,
            $data['cidade'] ?? null,
            $data['estado'] ?? null,
            $data['cep'] ?? null,
            $data['categoria'] ?? null,
            $data['observacoes'] ?? null,
            'ativo'
        ]);
        
        response(true, 'Fornecedor criado com sucesso', ['id' => $conn->lastInsertId()]);
    } catch (Exception $e) {
        response(false, 'Erro ao criar fornecedor: ' . $e->getMessage());
    }
}

function handlePut($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id']) || !isset($data['usuario_id'])) {
        response(false, 'ID do fornecedor e ID do usuário são obrigatórios');
    }
    
    // Verificar se o fornecedor pertence ao usuário
    $stmt = $conn->prepare('SELECT id FROM fornecedores WHERE id = ? AND usuario_id = ?');
    $stmt->execute([$data['id'], $data['usuario_id']]);
    if (!$stmt->fetch()) {
        response(false, 'Fornecedor não encontrado ou não autorizado');
    }
    
    $fields = [];
    $values = [];
    
    $allowed_fields = [
        'nome', 'tipo_pessoa', 'cpf_cnpj', 'telefone', 'email',
        'endereco', 'cidade', 'estado', 'cep', 'categoria', 'observacoes', 'status'
    ];
    
    foreach ($allowed_fields as $field) {
        if (isset($data[$field])) {
            $fields[] = "$field = ?";
            $values[] = $data[$field];
        }
    }
    
    if (empty($fields)) {
        response(false, 'Nenhum campo para atualizar');
    }
    
    $fields[] = 'data_atualizacao = NOW()';
    $values[] = $data['id'];
    
    try {
        $sql = 'UPDATE fornecedores SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute($values);
        
        if ($success) {
            response(true, 'Fornecedor atualizado com sucesso');
        } else {
            response(false, 'Erro ao atualizar fornecedor');
        }
    } catch (Exception $e) {
        response(false, 'Erro no banco de dados: ' . $e->getMessage());
    }
}

function handleDelete($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id']) || !isset($data['usuario_id'])) {
        response(false, 'ID do fornecedor e ID do usuário são obrigatórios');
    }
    
    // Verificar se o fornecedor pertence ao usuário
    $stmt = $conn->prepare('SELECT id FROM fornecedores WHERE id = ? AND usuario_id = ?');
    $stmt->execute([$data['id'], $data['usuario_id']]);
    if (!$stmt->fetch()) {
        response(false, 'Fornecedor não encontrado ou não autorizado');
    }
    
    try {
        // Excluir o fornecedor (soft delete - mudar status para inativo)
        $stmt = $conn->prepare('UPDATE fornecedores SET status = "inativo", data_atualizacao = NOW() WHERE id = ?');
        $success = $stmt->execute([$data['id']]);
        
        if ($success) {
            response(true, 'Fornecedor excluído com sucesso');
        } else {
            response(false, 'Erro ao excluir fornecedor');
        }
    } catch (Exception $e) {
        response(false, 'Erro no banco de dados: ' . $e->getMessage());
    }
}
