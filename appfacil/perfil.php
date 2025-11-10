<?php
// Headers CORS
header('Access-Control-Allow-Origin: https://gestaodeobrafacil.com');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');

// Responder às requisições OPTIONS (preflight)
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

// Wrapper para compatibilidade com o restante do código
function sendResponse($success, $message, $data = null) {
    response($success, $message, $data);
}

function getUserProfile($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                id,
                tipo_conta,
                nome,
                documento,
                telefone,
                email,
                data_cadastro,
                ultimo_acesso,
                ativo
            FROM usuarios 
            WHERE id = ? AND ativo = 1
        ");
        
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Formatar dados para o frontend
            $user['accountType'] = $user['tipo_conta'] === 'fisica' ? 'individual' : 'business';
            $user['name'] = $user['nome'];
            $user['phone'] = $user['telefone'];
            $user['document'] = $user['documento'];
            $user['createdAt'] = $user['data_cadastro'];
            $user['lastAccess'] = $user['ultimo_acesso'];
            $user['active'] = (bool)$user['ativo'];
            
            // Manter os campos originais para compatibilidade com InfinitePay
            // Não remover os campos originais para garantir que todas as informações estejam disponíveis
            
            sendResponse(true, 'Perfil carregado com sucesso', $user);
        } else {
            sendResponse(false, 'Usuário não encontrado');
        }
    } catch (Exception $e) {
        sendResponse(false, 'Erro ao buscar perfil: ' . $e->getMessage());
    }
}

function updateUserProfile($userId, $data) {
    global $pdo;
    
    try {
        // Validações
        if (empty($data['name'])) {
            sendResponse(false, 'Nome é obrigatório');
        }
        
        if (empty($data['email'])) {
            sendResponse(false, 'E-mail é obrigatório');
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            sendResponse(false, 'E-mail inválido');
        }
        
        if (empty($data['phone'])) {
            sendResponse(false, 'Telefone é obrigatório');
        }
        
        if (empty($data['document'])) {
            sendResponse(false, 'Documento é obrigatório');
        }
        
        // Verificar se email já existe para outro usuário
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->execute([$data['email'], $userId]);
        if ($stmt->fetch()) {
            sendResponse(false, 'Este e-mail já está sendo usado por outro usuário');
        }
        
        // Verificar se documento já existe para outro usuário
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE documento = ? AND id != ?");
        $stmt->execute([$data['document'], $userId]);
        if ($stmt->fetch()) {
            sendResponse(false, 'Este documento já está cadastrado para outro usuário');
        }
        
        // Atualizar perfil
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET 
                nome = ?,
                email = ?,
                telefone = ?,
                documento = ?
            WHERE id = ? AND ativo = 1
        ");
        
        $result = $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['document'],
            $userId
        ]);
        
        if ($result) {
            sendResponse(true, 'Perfil atualizado com sucesso');
        } else {
            sendResponse(false, 'Erro ao atualizar perfil');
        }
        
    } catch (Exception $e) {
        sendResponse(false, 'Erro ao atualizar perfil: ' . $e->getMessage());
    }
}

function changePassword($userId, $data) {
    global $pdo;
    
    try {
        if (empty($data['currentPassword'])) {
            sendResponse(false, 'Senha atual é obrigatória');
        }
        
        if (empty($data['newPassword'])) {
            sendResponse(false, 'Nova senha é obrigatória');
        }
        
        if (strlen($data['newPassword']) < 6) {
            sendResponse(false, 'Nova senha deve ter pelo menos 6 caracteres');
        }
        
        // Verificar senha atual
        $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ? AND ativo = 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($data['currentPassword'], $user['senha'])) {
            sendResponse(false, 'Senha atual incorreta');
        }
        
        // Atualizar senha
        $newPasswordHash = password_hash($data['newPassword'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        $result = $stmt->execute([$newPasswordHash, $userId]);
        
        if ($result) {
            sendResponse(true, 'Senha alterada com sucesso');
        } else {
            sendResponse(false, 'Erro ao alterar senha');
        }
        
    } catch (Exception $e) {
        sendResponse(false, 'Erro ao alterar senha: ' . $e->getMessage());
    }
}

function updateUserPhoto($userId, $photoData) {
    global $pdo;
    
    try {
        // Aqui você pode implementar o upload da foto
        // Por exemplo, salvar em um diretório específico e armazenar o caminho no banco
        
        $uploadDir = 'uploads/profile/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Decodificar base64 se necessário
        if (isset($photoData['base64'])) {
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $photoData['base64']));
            $fileName = $userId . '_' . time() . '.jpg';
            $filePath = $uploadDir . $fileName;
            
            if (file_put_contents($filePath, $imageData)) {
                // Salvar caminho da foto no banco (você pode adicionar uma coluna 'foto' na tabela usuarios)
                // $stmt = $pdo->prepare("UPDATE usuarios SET foto = ? WHERE id = ?");
                // $stmt->execute([$filePath, $userId]);
                
                sendResponse(true, 'Foto atualizada com sucesso', ['photoPath' => $filePath]);
            } else {
                sendResponse(false, 'Erro ao salvar foto');
            }
        }
        
    } catch (Exception $e) {
        sendResponse(false, 'Erro ao processar foto: ' . $e->getMessage());
    }
}

// Main router
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    $conn = getDbConnection();
    $pdo = $conn; // compatibilidade com as funções existentes

    // Para debug - remover em produção
    // error_log("Method: $method");
    // error_log("Input: " . print_r($input, true));

    switch ($method) {
        case 'GET':
            $userId = $_GET['user_id'] ?? null;
            if (!$userId) {
                sendResponse(false, 'ID do usuário é obrigatório');
            }
            getUserProfile($userId);
            break;
            
        case 'PUT':
            $userId = $input['user_id'] ?? null;
            if (!$userId) {
                sendResponse(false, 'ID do usuário é obrigatório');
            }
            updateUserProfile($userId, $input);
            break;
            
        case 'POST':
            $action = $input['action'] ?? '';
            $userId = $input['user_id'] ?? null;
            
            if (!$userId) {
                sendResponse(false, 'ID do usuário é obrigatório');
            }
            
            switch ($action) {
                case 'change_password':
                    changePassword($userId, $input);
                    break;
                    
                case 'update_photo':
                    updateUserPhoto($userId, $input);
                    break;
                    
                default:
                    sendResponse(false, 'Ação não reconhecida');
            }
            break;
            
        default:
            sendResponse(false, 'Método não suportado');
    }
} catch (PDOException $e) {
    error_log('Erro de conexão com o banco de dados: ' . $e->getMessage());
    response(false, 'Erro de conexão com o banco de dados: ' . $e->getMessage());
} catch (Exception $e) {
    error_log('Erro inesperado: ' . $e->getMessage());
    response(false, 'Ocorreu um erro inesperado');
}
?>