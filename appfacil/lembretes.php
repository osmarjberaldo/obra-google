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

// Função para criar a tabela se não existir
function createTableIfNotExists($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS lembretes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        titulo VARCHAR(255) NOT NULL,
        descricao TEXT,
        data_lembrete DATETIME NOT NULL,
        prioridade ENUM('baixa', 'media', 'alta') DEFAULT 'media',
        tipo ENUM('geral', 'obra', 'financeiro', 'reuniao', 'prazo') DEFAULT 'geral',
        obra_id INT NULL,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (obra_id) REFERENCES obras(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    try {
        $conn->exec($sql);
    } catch (PDOException $e) {
        error_log('Erro ao criar tabela lembretes: ' . $e->getMessage());
        response(false, 'Erro ao configurar o banco de dados');
    }
}

function response($success, $message, $data = null) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data], JSON_PRETTY_PRINT);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $conn = getDbConnection();
    
    // Criar a tabela se não existir
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
    $lembrete_id = $_GET['lembrete_id'] ?? null;
    
    if ($lembrete_id) {
        // Buscar lembrete específico
        $stmt = $conn->prepare('SELECT id, usuario_id, titulo, descricao, data_lembrete, prioridade, tipo, obra_id, data_criacao, data_atualizacao FROM lembretes WHERE id = ? AND usuario_id = ?');
        $stmt->execute([$lembrete_id, $usuario_id]);
        $lembrete = $stmt->fetch();
        
        if ($lembrete) {
            response(true, 'Lembrete encontrado', $lembrete);
        } else {
            response(false, 'Lembrete não encontrado');
        }
    } else {
        // Listar todos os lembretes do usuário
        $search = $_GET['search'] ?? '';
        $tipo = $_GET['tipo'] ?? '';
        $data_inicio = $_GET['data_inicio'] ?? '';
        $data_fim = $_GET['data_fim'] ?? '';
        
        $sql = 'SELECT id, usuario_id, titulo, descricao, data_lembrete, prioridade, tipo, obra_id, data_criacao, data_atualizacao FROM lembretes WHERE usuario_id = ?';
        $params = [$usuario_id];
        
        if ($search) {
            $sql .= ' AND (titulo LIKE ? OR descricao LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        
        if ($tipo) {
            $sql .= ' AND tipo = ?';
            $params[] = $tipo;
        }
        
        if ($data_inicio) {
            $sql .= ' AND data_lembrete >= ?';
            $params[] = $data_inicio;
        }
        
        if ($data_fim) {
            $sql .= ' AND data_lembrete <= ?';
            $params[] = $data_fim . ' 23:59:59';
        }
        
        $sql .= ' ORDER BY data_lembrete ASC';
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $lembretes = $stmt->fetchAll();
        
        response(true, 'Lembretes encontrados', $lembretes);
    }
}

function handlePost($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validação dos campos obrigatórios
    if (empty($data['usuario_id']) || empty($data['titulo']) || empty($data['data_lembrete'])) {
        response(false, 'Campos obrigatórios não preenchidos');
    }
    
    try {
        $stmt = $conn->prepare('INSERT INTO lembretes 
            (usuario_id, titulo, descricao, data_lembrete, prioridade, tipo, obra_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)');
            
        $success = $stmt->execute([
            $data['usuario_id'],
            $data['titulo'],
            $data['descricao'] ?? null,
            $data['data_lembrete'],
            $data['prioridade'] ?? 'media',
            $data['tipo'] ?? 'geral',
            $data['obra_id'] ?? null
        ]);
        
        // Se a tabela tiver as colunas de data, atualize-as
        try {
            $conn->exec("ALTER TABLE lembretes 
                ADD COLUMN IF NOT EXISTS data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
                ADD COLUMN IF NOT EXISTS data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        } catch (PDOException $e) {
            // Ignora erros de colunas já existentes
            if (strpos($e->getMessage(), 'duplicate column') === false) {
                throw $e;
            }
        }
        
        if ($success) {
            $lembreteId = $conn->lastInsertId();
            response(true, 'Lembrete criado com sucesso', ['id' => $lembreteId]);
        } else {
            response(false, 'Erro ao criar lembrete');
        }
    } catch (PDOException $e) {
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}

function handlePut($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['id']) || empty($data['usuario_id'])) {
        response(false, 'ID do lembrete e usuário são obrigatórios');
    }
    
    try {
        $fieldsToUpdate = [];
        $params = [];
        
        // Campos que podem ser atualizados
        $updatableFields = [
            'titulo' => 'titulo', 
            'descricao' => 'descricao', 
            'data_lembrete' => 'data_lembrete', 
            'prioridade' => 'prioridade', 
            'tipo' => 'tipo', 
            'obra_id' => 'obra_id',
            'data_atualizacao' => 'data_atualizacao'
        ];
        
        // Adiciona a data de atualização
        $data['data_atualizacao'] = date('Y-m-d H:i:s');
        
        foreach ($updatableFields as $field => $dbField) {
            if (array_key_exists($field, $data)) {
                $fieldsToUpdate[] = "$dbField = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fieldsToUpdate)) {
            response(false, 'Nenhum campo para atualizar');
        }
        
        $fieldsToUpdate[] = 'data_atualizacao = NOW()';
        
        $sql = 'UPDATE lembretes SET ' . implode(', ', $fieldsToUpdate) . ' WHERE id = ? AND usuario_id = ?';
        $params[] = $data['id'];
        $params[] = $data['usuario_id'];
        
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute($params);
        
        if ($success) {
            response(true, 'Lembrete atualizado com sucesso');
        } else {
            response(false, 'Erro ao atualizar lembrete');
        }
    } catch (PDOException $e) {
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}

function handleDelete($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['id']) || empty($data['usuario_id'])) {
        response(false, 'ID do lembrete e usuário são obrigatórios');
    }
    
    try {
        $stmt = $conn->prepare('DELETE FROM lembretes WHERE id = ? AND usuario_id = ?');
        $success = $stmt->execute([$data['id'], $data['usuario_id']]);
        
        if ($success) {
            response(true, 'Lembrete excluído com sucesso');
        } else {
            response(false, 'Erro ao excluir lembrete');
        }
    } catch (PDOException $e) {
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}
