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
    $equipamento_id = $_GET['equipamento_id'] ?? null;
    $obra_id = $_GET['obra_id'] ?? null;

    if (!$usuario_id) {
        response(false, 'Usuário não informado');
    }

    if ($equipamento_id) {
        $stmt = $conn->prepare('SELECT * FROM equipamentos WHERE id = ? AND usuario_id = ?');
        $stmt->execute([$equipamento_id, $usuario_id]);
        $equipamento = $stmt->fetch();
        if ($equipamento) {
            response(true, 'Equipamento encontrado', $equipamento);
        } else {
            response(false, 'Equipamento não encontrado');
        }
    } else {
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $tipo = $_GET['tipo'] ?? '';
        $marca = $_GET['marca'] ?? '';

        $sql = 'SELECT * FROM equipamentos WHERE usuario_id = ?';
        $params = [$usuario_id];

        if ($search) {
            $sql .= ' AND (nome LIKE ? OR tipo LIKE ? OR marca LIKE ? OR modelo LIKE ? OR numero_serie LIKE ?)';
            $term = '%' . $search . '%';
            array_push($params, $term, $term, $term, $term, $term);
        }
        if ($status) {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }
        if ($tipo) {
            $sql .= ' AND tipo = ?';
            $params[] = $tipo;
        }
        if ($marca) {
            $sql .= ' AND marca = ?';
            $params[] = $marca;
        }
        if ($obra_id) {
            $sql .= ' AND obra_id = ?';
            $params[] = $obra_id;
        }

        $sql .= ' ORDER BY nome ASC';
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $equipamentos = $stmt->fetchAll();

        // Estatísticas
        $statsSql = 'SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = "disponivel" THEN 1 ELSE 0 END) as disponiveis,
                SUM(CASE WHEN status = "em_uso" THEN 1 ELSE 0 END) as em_uso,
                SUM(CASE WHEN status = "manutencao" THEN 1 ELSE 0 END) as manutencao,
                SUM(CASE WHEN status = "inativo" THEN 1 ELSE 0 END) as inativos,
                COUNT(DISTINCT tipo) as tipos_diferentes,
                COUNT(DISTINCT marca) as marcas_diferentes
            FROM equipamentos
            WHERE usuario_id = ?';
        $statsParams = [$usuario_id];
        if ($obra_id) {
            $statsSql .= ' AND obra_id = ?';
            $statsParams[] = $obra_id;
        }
        $statsStmt = $conn->prepare($statsSql);
        $statsStmt->execute($statsParams);
        $stats = $statsStmt->fetch();

        response(true, 'Equipamentos encontrados', ['data' => $equipamentos, 'stats' => $stats]);
    }
}

function handlePost($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['usuario_id']) || empty($data['nome']) || empty($data['tipo'])) {
        response(false, 'Campos obrigatórios não preenchidos: nome e tipo são obrigatórios');
    }

    $usuario_id = $data['usuario_id'];
    $nome = trim($data['nome']);
    $obra_id = isset($data['obra_id']) && $data['obra_id'] !== '' ? (int)$data['obra_id'] : null;
    $tipo = trim($data['tipo']);
    $marca = trim($data['marca'] ?? '');
    $modelo = trim($data['modelo'] ?? '');
    $numero_serie = trim($data['numero_serie'] ?? '');
    $ano_fabricacao = isset($data['ano_fabricacao']) && $data['ano_fabricacao'] !== '' ? (int)$data['ano_fabricacao'] : null;
    $status = $data['status'] ?? 'disponivel';
    $observacoes = trim($data['observacoes'] ?? '');

    try {
        $stmt = $conn->prepare('INSERT INTO equipamentos (usuario_id, obra_id, nome, tipo, marca, modelo, numero_serie, ano_fabricacao, status, observacoes, data_cadastro) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $success = $stmt->execute([$usuario_id, $obra_id, $nome, $tipo, $marca, $modelo, $numero_serie, $ano_fabricacao, $status, $observacoes]);
        if ($success) {
            $id = $conn->lastInsertId();
            response(true, 'Equipamento criado com sucesso', ['id' => $id]);
        } else {
            response(false, 'Erro ao criar equipamento');
        }
    } catch (PDOException $e) {
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}

function handlePut($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id']) || empty($data['usuario_id'])) {
        response(false, 'ID do equipamento e usuário são obrigatórios');
    }

    try {
        $fields = [];
        $params = [];
        $updatable = ['nome','tipo','marca','modelo','numero_serie','ano_fabricacao','status','observacoes','obra_id'];

        foreach ($updatable as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            response(false, 'Nenhum campo para atualizar');
        }

        $fields[] = 'data_atualizacao = NOW()';
        $sql = 'UPDATE equipamentos SET ' . implode(', ', $fields) . ' WHERE id = ? AND usuario_id = ?';
        $params[] = $data['id'];
        $params[] = $data['usuario_id'];
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute($params);
        if ($success) {
            response(true, 'Equipamento atualizado com sucesso');
        } else {
            response(false, 'Erro ao atualizar equipamento');
        }
    } catch (PDOException $e) {
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}

function handleDelete($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id']) || empty($data['usuario_id'])) {
        response(false, 'ID do equipamento e usuário são obrigatórios');
    }

    try {
        $stmt = $conn->prepare('DELETE FROM equipamentos WHERE id = ? AND usuario_id = ?');
        $success = $stmt->execute([$data['id'], $data['usuario_id']]);
        if ($success) {
            response(true, 'Equipamento excluído com sucesso');
        } else {
            response(false, 'Erro ao excluir equipamento');
        }
    } catch (PDOException $e) {
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}

?>
