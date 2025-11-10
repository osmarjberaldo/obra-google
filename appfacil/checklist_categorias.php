<?php
// CORS headers
header('Access-Control-Allow-Origin: https://gestaodeobrafacil.com');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');
require_once __DIR__ . '/config/pdo.php';

function response($success, $message, $data = null) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data], JSON_PRETTY_PRINT);
    exit;
}

function createTableIfNotExists($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS checklist_categorias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        nome VARCHAR(255) NOT NULL,
        descricao TEXT NULL,
        status ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
        data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    try {
        $conn->exec($sql);
    } catch (PDOException $e) {
        error_log('Erro ao criar tabela checklist_categorias: ' . $e->getMessage());
        response(false, 'Erro ao configurar o banco de dados');
    }
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $conn = getDbConnection();
    createTableIfNotExists($conn);

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
    $categoria_id = $_GET['categoria_id'] ?? null;
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';

    if (!$usuario_id) {
        response(false, 'Usuário não informado');
    }

    if ($categoria_id) {
        $stmt = $conn->prepare('SELECT id, usuario_id, nome, descricao, status, data_criacao, data_atualizacao FROM checklist_categorias WHERE id = ? AND usuario_id = ?');
        $stmt->execute([$categoria_id, $usuario_id]);
        $categoria = $stmt->fetch();
        if ($categoria) {
            response(true, 'Categoria encontrada', $categoria);
        }
        response(false, 'Categoria não encontrada');
    }

    $sql = 'SELECT id, usuario_id, nome, descricao, status, data_criacao, data_atualizacao FROM checklist_categorias WHERE usuario_id = ?';
    $params = [$usuario_id];

    if ($search) {
        $sql .= ' AND (nome LIKE ? OR descricao LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    if ($status) {
        $sql .= ' AND status = ?';
        $params[] = $status;
    }

    $sql .= ' ORDER BY nome ASC';
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $categorias = $stmt->fetchAll();
    response(true, 'Categorias encontradas', $categorias);
}

function handlePost($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['usuario_id']) || empty($data['nome'])) {
        response(false, 'Campos obrigatórios não preenchidos');
    }

    try {
        $stmt = $conn->prepare('INSERT INTO checklist_categorias (usuario_id, nome, descricao, status) VALUES (?, ?, ?, ?)');
        $success = $stmt->execute([
            $data['usuario_id'],
            $data['nome'],
            $data['descricao'] ?? null,
            $data['status'] ?? 'ativo'
        ]);
        if ($success) {
            $id = $conn->lastInsertId();
            response(true, 'Categoria criada com sucesso', ['id' => $id]);
        }
        response(false, 'Erro ao criar categoria');
    } catch (PDOException $e) {
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}

function handlePut($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id']) || empty($data['usuario_id'])) {
        response(false, 'ID da categoria e usuário são obrigatórios');
    }

    try {
        $fields = [];
        $params = [];
        $updatable = ['nome' => 'nome', 'descricao' => 'descricao', 'status' => 'status'];
        foreach ($updatable as $field => $dbField) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$dbField = ?";
                $params[] = $data[$field];
            }
        }
        if (empty($fields)) {
            response(false, 'Nenhum campo para atualizar');
        }
        $fields[] = 'data_atualizacao = NOW()';
        $sql = 'UPDATE checklist_categorias SET ' . implode(', ', $fields) . ' WHERE id = ? AND usuario_id = ?';
        $params[] = $data['id'];
        $params[] = $data['usuario_id'];
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute($params);
        if ($success) {
            response(true, 'Categoria atualizada com sucesso');
        }
        response(false, 'Erro ao atualizar categoria');
    } catch (PDOException $e) {
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}

function handleDelete($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id']) || empty($data['usuario_id'])) {
        response(false, 'ID da categoria e usuário são obrigatórios');
    }

    try {
        $stmt = $conn->prepare('DELETE FROM checklist_categorias WHERE id = ? AND usuario_id = ?');
        $success = $stmt->execute([$data['id'], $data['usuario_id']]);
        if ($success) {
            response(true, 'Categoria excluída com sucesso');
        }
        response(false, 'Erro ao excluir categoria');
    } catch (PDOException $e) {
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}