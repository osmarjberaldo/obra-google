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

function response($success, $message, $data = null, $stats = null) {
    $result = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $result['data'] = $data;
    }
    if ($stats !== null) {
        $result['stats'] = $stats;
    }
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $conn = getDbConnection();
    $usuario_id = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : 0;
    
    if (!$usuario_id) {
        response(false, 'ID do usuário é obrigatório');
    }
    
    switch ($method) {
        case 'GET':
            handleGet($conn, $usuario_id);
            break;
        case 'POST':
            handlePost($conn, $usuario_id);
            break;
        case 'PUT':
            handlePut($conn, $usuario_id);
            break;
        case 'DELETE':
            handleDelete($conn, $usuario_id);
            break;
        default:
            response(false, 'Método não permitido');
            break;
    }
    
} catch (Exception $e) {
    response(false, 'Erro interno do servidor: ' . $e->getMessage());
}

function handleGet($conn, $usuario_id) {
    try {
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $cargo = isset($_GET['cargo']) ? $_GET['cargo'] : '';
        
        // Garantir que o usuario_id é válido
        if (!$usuario_id || $usuario_id <= 0) {
            response(false, 'ID do usuário inválido');
        }
        
        $sql = "SELECT id, nome, cargo, telefone, email, status FROM equipe WHERE usuario_id = ?";
        $params = [$usuario_id];
        
        if (!empty($search)) {
            $sql .= " AND (nome LIKE ? OR cargo LIKE ? OR email LIKE ?)";
            $searchParam = '%' . $search . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if (!empty($status)) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        if (!empty($cargo)) {
            $sql .= " AND cargo LIKE ?";
            $params[] = '%' . $cargo . '%';
        }
        
        $sql .= " ORDER BY nome ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $equipe = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Converter IDs para string para compatibilidade com frontend
        foreach ($equipe as &$membro) {
            $membro['id'] = (string)$membro['id'];
        }
        
        // Buscar estatísticas
        $statsQuery = "SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'ativo' THEN 1 END) as ativos,
            COUNT(CASE WHEN status = 'inativo' THEN 1 END) as inativos,
            COUNT(DISTINCT cargo) as cargos_diferentes
            FROM equipe WHERE usuario_id = ?";
        
        $statsStmt = $conn->prepare($statsQuery);
        $statsStmt->execute([$usuario_id]);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        
        // Converter stats para inteiros
        $stats = [
            'total' => (int)$stats['total'],
            'ativos' => (int)$stats['ativos'],
            'inativos' => (int)$stats['inativos'],
            'cargos_diferentes' => (int)$stats['cargos_diferentes']
        ];
        
        response(true, 'Membros carregados com sucesso', $equipe, $stats);
        
    } catch (Exception $e) {
        error_log('Erro ao buscar membros da equipe: ' . $e->getMessage());
        response(false, 'Erro ao buscar membros da equipe: ' . $e->getMessage());
    }
}

function handlePost($conn, $usuario_id) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            response(false, 'Dados inválidos');
        }
        
        $nome = trim($input['nome'] ?? '');
        $cargo = trim($input['cargo'] ?? '');
        $telefone = trim($input['telefone'] ?? '');
        $email = trim($input['email'] ?? '');
        $status = $input['status'] ?? 'ativo';
        
        if (empty($nome) || empty($cargo)) {
            response(false, 'Nome e cargo são obrigatórios');
        }
        
        // Validar email se fornecido
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            response(false, 'Email inválido');
        }
        
        // Verificar se já existe membro com mesmo nome
        $checkStmt = $conn->prepare("SELECT id FROM equipe WHERE usuario_id = ? AND nome = ?");
        $checkStmt->execute([$usuario_id, $nome]);
        
        if ($checkStmt->rowCount() > 0) {
            response(false, 'Já existe um membro com este nome');
        }
        
        $sql = "INSERT INTO equipe (usuario_id, nome, cargo, telefone, email, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $usuario_id,
            $nome,
            $cargo,
            $telefone ?: null,
            $email ?: null,
            $status
        ]);
        
        if ($result) {
            $newId = $conn->lastInsertId();
            response(true, 'Membro da equipe adicionado com sucesso', ['id' => $newId]);
        } else {
            response(false, 'Falha ao inserir no banco de dados');
        }
        
    } catch (Exception $e) {
        response(false, 'Erro ao adicionar membro: ' . $e->getMessage());
    }
}

function handlePut($conn, $usuario_id) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id'])) {
            response(false, 'ID é obrigatório');
        }
        
        $id = (int)$input['id'];
        $nome = trim($input['nome'] ?? '');
        $cargo = trim($input['cargo'] ?? '');
        $telefone = trim($input['telefone'] ?? '');
        $email = trim($input['email'] ?? '');
        $status = $input['status'] ?? 'ativo';
        
        if (empty($nome) || empty($cargo)) {
            response(false, 'Nome e cargo são obrigatórios');
        }
        
        // Validar email se fornecido
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            response(false, 'Email inválido');
        }
        
        // Verificar se o membro pertence ao usuário
        $checkStmt = $conn->prepare("SELECT id FROM equipe WHERE id = ? AND usuario_id = ?");
        $checkStmt->execute([$id, $usuario_id]);
        
        if ($checkStmt->rowCount() === 0) {
            response(false, 'Membro não encontrado');
        }
        
        // Verificar se já existe outro membro com mesmo nome
        $checkNameStmt = $conn->prepare("SELECT id FROM equipe WHERE usuario_id = ? AND nome = ? AND id != ?");
        $checkNameStmt->execute([$usuario_id, $nome, $id]);
        
        if ($checkNameStmt->rowCount() > 0) {
            response(false, 'Já existe outro membro com este nome');
        }
        
        $sql = "UPDATE equipe SET nome = ?, cargo = ?, telefone = ?, 
                email = ?, status = ? WHERE id = ? AND usuario_id = ?";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $nome,
            $cargo,
            $telefone ?: null,
            $email ?: null,
            $status,
            $id,
            $usuario_id
        ]);
        
        if ($result) {
            response(true, 'Membro atualizado com sucesso');
        } else {
            response(false, 'Falha ao atualizar no banco de dados');
        }
        
    } catch (Exception $e) {
        response(false, 'Erro ao atualizar membro: ' . $e->getMessage());
    }
}

function handleDelete($conn, $usuario_id) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id'])) {
            response(false, 'ID é obrigatório');
        }
        
        $id = (int)$input['id'];
        
        // Verificar se o membro pertence ao usuário
        $checkStmt = $conn->prepare("SELECT id FROM equipe WHERE id = ? AND usuario_id = ?");
        $checkStmt->execute([$id, $usuario_id]);
        
        if ($checkStmt->rowCount() === 0) {
            response(false, 'Membro não encontrado');
        }
        
        $sql = "DELETE FROM equipe WHERE id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$id, $usuario_id]);
        
        if ($result) {
            response(true, 'Membro removido com sucesso');
        } else {
            response(false, 'Falha ao deletar do banco de dados');
        }
        
    } catch (Exception $e) {
        response(false, 'Erro ao remover membro: ' . $e->getMessage());
    }
}
?>