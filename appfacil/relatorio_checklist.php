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
    $sql = "CREATE TABLE IF NOT EXISTS relatorio_checklist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        relatorio_id INT NOT NULL,
        checklist_item_id INT NOT NULL,
        usuario_id INT NOT NULL,
        status ENUM('pendente','concluido','em_andamento') NOT NULL DEFAULT 'pendente',
        observacoes TEXT DEFAULT NULL,
        data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (relatorio_id) REFERENCES relatorios_diarios(id) ON DELETE CASCADE,
        FOREIGN KEY (checklist_item_id) REFERENCES checklist_itens(id) ON DELETE CASCADE,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    try {
        $conn->exec($sql);
    } catch (PDOException $e) {
        error_log('Erro ao criar tabela relatorio_checklist: ' . $e->getMessage());
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
    $relatorio_id = $_GET['relatorio_id'] ?? null;
    $id = $_GET['id'] ?? null;

    if (!$usuario_id) {
        response(false, 'Usuário não informado');
    }

    if ($id) {
        $stmt = $conn->prepare('SELECT rc.id, rc.relatorio_id, rc.checklist_item_id, rc.usuario_id, rc.status, rc.observacoes, rc.data_criacao, rc.data_atualizacao,
                                       ci.nome AS item_nome, ci.tipo AS item_tipo
                                FROM relatorio_checklist rc
                                JOIN checklist_itens ci ON ci.id = rc.checklist_item_id
                                WHERE rc.id = ? AND rc.usuario_id = ?');
        $stmt->execute([$id, $usuario_id]);
        $row = $stmt->fetch();
        if ($row) {
            response(true, 'Vínculo encontrado', $row);
        }
        response(false, 'Vínculo não encontrado');
    }

    if (!$relatorio_id) {
        response(false, 'Relatório não informado');
    }

    $stmt = $conn->prepare('SELECT rc.id, rc.relatorio_id, rc.checklist_item_id, rc.usuario_id, rc.status, rc.observacoes, rc.data_criacao, rc.data_atualizacao,
                                   ci.nome AS item_nome, ci.tipo AS item_tipo
                            FROM relatorio_checklist rc
                            JOIN checklist_itens ci ON ci.id = rc.checklist_item_id
                            WHERE rc.usuario_id = ? AND rc.relatorio_id = ?
                            ORDER BY rc.id ASC');
    $stmt->execute([$usuario_id, $relatorio_id]);
    $rows = $stmt->fetchAll();
    response(true, 'Itens de checklist do relatório encontrados', $rows);
}

function handlePost($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['usuario_id']) || empty($data['relatorio_id']) || empty($data['checklist_item_id'])) {
        response(false, 'Campos obrigatórios não preenchidos');
    }

    // Evitar duplicados
    $check = $conn->prepare('SELECT id FROM relatorio_checklist WHERE usuario_id = ? AND relatorio_id = ? AND checklist_item_id = ?');
    $check->execute([$data['usuario_id'], $data['relatorio_id'], $data['checklist_item_id']]);
    if ($check->fetch()) {
        response(false, 'Item já vinculado ao relatório');
    }

    try {
        $stmt = $conn->prepare('INSERT INTO relatorio_checklist (usuario_id, relatorio_id, checklist_item_id, status, observacoes) VALUES (?, ?, ?, ?, ?)');
        $success = $stmt->execute([
            $data['usuario_id'],
            $data['relatorio_id'],
            $data['checklist_item_id'],
            $data['status'] ?? 'pendente',
            $data['observacoes'] ?? null
        ]);
        if ($success) {
            $id = $conn->lastInsertId();
            response(true, 'Checklist vinculado com sucesso', ['id' => $id]);
        }
        response(false, 'Erro ao vincular item');
    } catch (PDOException $e) {
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}

function handlePut($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id']) || empty($data['usuario_id'])) {
        response(false, 'ID do vínculo e usuário são obrigatórios');
    }

    $fields = [];
    $params = [];
    $updatable = ['status' => 'status', 'observacoes' => 'observacoes'];

    if (isset($data['status']) && !in_array($data['status'], ['pendente', 'concluido', 'em_andamento'])) {
        response(false, 'Status inválido. Use pendente, concluido ou em_andamento');
    }

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
    $sql = 'UPDATE relatorio_checklist SET ' . implode(', ', $fields) . ' WHERE id = ? AND usuario_id = ?';
    $params[] = $data['id'];
    $params[] = $data['usuario_id'];
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute($params);
    if ($success) {
        response(true, 'Vínculo atualizado com sucesso');
    }
    response(false, 'Erro ao atualizar vínculo');
}

function handleDelete($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id']) || empty($data['usuario_id'])) {
        response(false, 'ID do vínculo e usuário são obrigatórios');
    }

    try {
        $stmt = $conn->prepare('DELETE FROM relatorio_checklist WHERE id = ? AND usuario_id = ?');
        $success = $stmt->execute([$data['id'], $data['usuario_id']]);
        if ($success) {
            response(true, 'Vínculo excluído com sucesso');
        }
        response(false, 'Erro ao excluir vínculo');
    } catch (PDOException $e) {
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}

?>