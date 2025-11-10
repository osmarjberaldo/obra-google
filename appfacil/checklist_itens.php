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
    // Ensure categories table exists (dependency)
    $sqlCategoria = "CREATE TABLE IF NOT EXISTS checklist_categorias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        nome VARCHAR(255) NOT NULL,
        descricao TEXT NULL,
        status ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
        data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $sqlItens = "CREATE TABLE IF NOT EXISTS checklist_itens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        categoria_id INT NOT NULL,
        nome VARCHAR(255) NOT NULL,
        tipo ENUM('texto','check') NOT NULL DEFAULT 'texto',
        valor_texto TEXT DEFAULT NULL,
        feito TINYINT(1) NOT NULL DEFAULT 0,
        ordem INT DEFAULT NULL,
        status ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
        data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (categoria_id) REFERENCES checklist_categorias(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    try {
        $conn->exec($sqlCategoria);
        $conn->exec($sqlItens);
    } catch (PDOException $e) {
        error_log('Erro ao criar tabelas de checklist: ' . $e->getMessage());
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
    $item_id = $_GET['item_id'] ?? null;
    $categoria_id = $_GET['categoria_id'] ?? null;
    $search = $_GET['search'] ?? '';
    $tipo = $_GET['tipo'] ?? '';
    $status = $_GET['status'] ?? '';

    if (!$usuario_id) {
        response(false, 'Usuário não informado');
    }

    if ($item_id) {
        $stmt = $conn->prepare('SELECT id, usuario_id, categoria_id, nome, tipo, valor_texto, feito, ordem, status, data_criacao, data_atualizacao FROM checklist_itens WHERE id = ? AND usuario_id = ?');
        $stmt->execute([$item_id, $usuario_id]);
        $item = $stmt->fetch();
        if ($item) {
            response(true, 'Item encontrado', $item);
        }
        response(false, 'Item não encontrado');
    }

    $sql = 'SELECT id, usuario_id, categoria_id, nome, tipo, valor_texto, feito, ordem, status, data_criacao, data_atualizacao FROM checklist_itens WHERE usuario_id = ?';
    $params = [$usuario_id];

    if ($categoria_id) {
        $sql .= ' AND categoria_id = ?';
        $params[] = $categoria_id;
    }
    if ($search) {
        $sql .= ' AND (nome LIKE ? OR valor_texto LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    if ($tipo) {
        $sql .= ' AND tipo = ?';
        $params[] = $tipo;
    }
    if ($status) {
        $sql .= ' AND status = ?';
        $params[] = $status;
    }

    $sql .= ' ORDER BY COALESCE(ordem, 999999) ASC, id ASC';
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $itens = $stmt->fetchAll();
    response(true, 'Itens encontrados', $itens);
}

function handlePost($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['usuario_id']) || empty($data['categoria_id']) || empty($data['nome']) || empty($data['tipo'])) {
        response(false, 'Campos obrigatórios não preenchidos');
    }

    // tipo must be texto or check
    $tipo = $data['tipo'];
    if (!in_array($tipo, ['texto', 'check'])) {
        response(false, 'Tipo inválido. Use "texto" ou "check"');
    }

    try {
        $stmt = $conn->prepare('INSERT INTO checklist_itens (usuario_id, categoria_id, nome, tipo, valor_texto, feito, ordem, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $success = $stmt->execute([
            $data['usuario_id'],
            $data['categoria_id'],
            $data['nome'],
            $tipo,
            $data['valor_texto'] ?? null,
            !empty($data['feito']) ? 1 : 0,
            $data['ordem'] ?? null,
            $data['status'] ?? 'ativo'
        ]);
        if ($success) {
            $id = $conn->lastInsertId();
            response(true, 'Item criado com sucesso', ['id' => $id]);
        }
        response(false, 'Erro ao criar item');
    } catch (PDOException $e) {
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}

function handlePut($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id']) || empty($data['usuario_id'])) {
        response(false, 'ID do item e usuário são obrigatórios');
    }

    try {
        $fields = [];
        $params = [];
        $updatable = [
            'nome' => 'nome',
            'tipo' => 'tipo',
            'valor_texto' => 'valor_texto',
            'feito' => 'feito',
            'ordem' => 'ordem',
            'status' => 'status',
            'categoria_id' => 'categoria_id'
        ];

        if (isset($data['tipo']) && !in_array($data['tipo'], ['texto', 'check'])) {
            response(false, 'Tipo inválido. Use "texto" ou "check"');
        }

        foreach ($updatable as $field => $dbField) {
            if (array_key_exists($field, $data)) {
                // Normalize boolean for feito
                if ($field === 'feito') {
                    $fields[] = "$dbField = ?";
                    $params[] = !empty($data[$field]) ? 1 : 0;
                } else {
                    $fields[] = "$dbField = ?";
                    $params[] = $data[$field];
                }
            }
        }
        if (empty($fields)) {
            response(false, 'Nenhum campo para atualizar');
        }
        $fields[] = 'data_atualizacao = NOW()';
        $sql = 'UPDATE checklist_itens SET ' . implode(', ', $fields) . ' WHERE id = ? AND usuario_id = ?';
        $params[] = $data['id'];
        $params[] = $data['usuario_id'];
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute($params);
        if ($success) {
            response(true, 'Item atualizado com sucesso');
        }
        response(false, 'Erro ao atualizar item');
    } catch (PDOException $e) {
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}

function handleDelete($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id']) || empty($data['usuario_id'])) {
        response(false, 'ID do item e usuário são obrigatórios');
    }

    try {
        $stmt = $conn->prepare('DELETE FROM checklist_itens WHERE id = ? AND usuario_id = ?');
        $success = $stmt->execute([$data['id'], $data['usuario_id']]);
        if ($success) {
            response(true, 'Item excluído com sucesso');
        }
        response(false, 'Erro ao excluir item');
    } catch (PDOException $e) {
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}