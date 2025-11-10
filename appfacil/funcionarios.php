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

// Função para enviar e-mail de boas-vindas
function enviarEmailBoasVindas($nome, $email, $senha) {
    if (empty($email)) {
        return false; // Retorna falso se não houver e-mail
    }
    
    $assunto = 'Bem-vindo ao Gestor de Obra Fácil - Acesso ao Painel';
    
    $mensagem = '<html><body>';
    $mensagem .= '<h2>Olá, ' . htmlspecialchars($nome) . '!</h2>';
    $mensagem .= '<p>Bem-vindo ao <strong>Gestor de Obra Fácil</strong>!</p>';
    $mensagem .= '<p>Seu cadastro foi realizado com sucesso no sistema.</p>';
    $mensagem .= '<p><strong>Dados de acesso:</strong></p>';
    $mensagem .= '<p>E-mail: ' . htmlspecialchars($email) . '</p>';
    $mensagem .= '<p>Senha: ' . htmlspecialchars($senha) . '</p>';
    $mensagem .= '<p>Você pode acessar o painel através do link abaixo:</p>';
    $mensagem .= '<p><a href="https://gestaodeobrafacil.com/login">https://gestaodeobrafacil.com/login</a></p>';
    $mensagem .= '<p>Atenciosamente,<br>Equipe Gestor de Obra Fácil</p>';
    $mensagem .= '</body></html>';
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: Gestor de Obra Fácil <contato@gestaodeobrafacil.com>',
        'Reply-To: contato@gestaodeobrafacil.com',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    return mail($email, $assunto, $mensagem, implode("\r\n", $headers));
}

// Backward-compat wrapper to keep existing calls
function sendResponse($success, $message, $data = null) {
    response($success, $message, $data);
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

// CREATE funcionario
function createFuncionario($data) {
    global $pdo;

    // Required fields
    $usuario_id = $data['usuario_id'] ?? null;
    $nome = $data['nome'] ?? '';
    $email = $data['email'] ?? '';
    $senha = $data['senha'] ?? '';

    if (!$usuario_id) sendResponse(false, 'usuario_id é obrigatório');
    if (!$nome) sendResponse(false, 'nome é obrigatório');
    if (!$email) sendResponse(false, 'email é obrigatório');
    if (!validateEmail($email)) sendResponse(false, 'email inválido');
    if (!$senha) sendResponse(false, 'senha é obrigatória');

    // Optional
    $telefone = $data['telefone'] ?? null;
    $cargo = $data['cargo'] ?? null;

    try {
        // Check duplicated email on same usuario_id
        $stmt = $pdo->prepare('SELECT id FROM funcionarios WHERE email = ? AND usuario_id = ?');
        $stmt->execute([$email, $usuario_id]);
        if ($stmt->fetch()) {
            sendResponse(false, 'E-mail já cadastrado para este usuário');
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $token = generateToken();

        $stmt = $pdo->prepare('INSERT INTO funcionarios (usuario_id, nome, telefone, email, senha, cargo, token, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $ok = $stmt->execute([$usuario_id, $nome, $telefone, $email, $senhaHash, $cargo, $token, 'ativo']);

        if (!$ok) sendResponse(false, 'Erro ao criar funcionário');

        $id = $pdo->lastInsertId();
        
        // Enviar e-mail de boas-vindas (em background para não atrasar a resposta)
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
            enviarEmailBoasVindas($nome, $email, $senha);
        } else {
            // Se não for FastCGI, tenta enviar de forma assíncrona
            if (function_exists('exec') && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                $scriptPath = __FILE__;
                $command = sprintf('php -r "include \'%s\'; \App\enviarEmailBoasVindas(\'%s\', \'%s\', \'%s\');" > /dev/null 2>&1 &', 
                    addslashes($scriptPath),
                    addslashes($nome),
                    addslashes($email),
                    addslashes($senha)
                );
                exec($command);
            } else {
                // Último recurso: envia de forma síncrona
                enviarEmailBoasVindas($nome, $email, $senha);
            }
        }
        
        sendResponse(true, 'Funcionário criado com sucesso', ['id' => (int)$id]);
    } catch (Exception $e) {
        sendResponse(false, 'Erro ao criar funcionário: ' . $e->getMessage());
    }
}

// READ funcionario(s)
function getFuncionarios($params) {
    global $pdo;

    $id = $params['id'] ?? null;
    $usuario_id = $params['usuario_id'] ?? null;

    try {
        if ($id) {
            $stmt = $pdo->prepare('SELECT id, usuario_id, nome, telefone, email, cargo, status, data_cadastro, data_atualizacao FROM funcionarios WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) sendResponse(false, 'Funcionário não encontrado');
            sendResponse(true, 'OK', $row);
        }

        if (!$usuario_id) sendResponse(false, 'usuario_id é obrigatório para listagem');

        $stmt = $pdo->prepare('SELECT id, usuario_id, nome, telefone, email, cargo, status, data_cadastro, data_atualizacao FROM funcionarios WHERE usuario_id = ? ORDER BY id DESC');
        $stmt->execute([$usuario_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse(true, 'OK', ['data' => $rows]);
    } catch (Exception $e) {
        sendResponse(false, 'Erro ao buscar funcionários: ' . $e->getMessage());
    }
}

// UPDATE funcionario
function updateFuncionario($data) {
    global $pdo;

    $id = $data['id'] ?? null;
    if (!$id) sendResponse(false, 'id é obrigatório');

    // Load current
    $stmt = $pdo->prepare('SELECT * FROM funcionarios WHERE id = ?');
    $stmt->execute([$id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$current) sendResponse(false, 'Funcionário não encontrado');

    $nome = $data['nome'] ?? $current['nome'];
    $email = $data['email'] ?? $current['email'];
    $telefone = $data['telefone'] ?? $current['telefone'];
    $cargo = $data['cargo'] ?? $current['cargo'];
    $status = $data['status'] ?? $current['status'];
    $senha = $data['senha'] ?? null; // update only if provided

    if (!$nome) sendResponse(false, 'nome é obrigatório');
    if (!$email) sendResponse(false, 'email é obrigatório');
    if (!validateEmail($email)) sendResponse(false, 'email inválido');

    try {
        // unique email per usuario_id
        $stmt = $pdo->prepare('SELECT id FROM funcionarios WHERE email = ? AND usuario_id = ? AND id <> ?');
        $stmt->execute([$email, $current['usuario_id'], $id]);
        if ($stmt->fetch()) {
            sendResponse(false, 'E-mail já cadastrado para este usuário');
        }

        if ($senha) {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE funcionarios SET nome = ?, email = ?, telefone = ?, cargo = ?, status = ?, senha = ?, data_atualizacao = CURRENT_TIMESTAMP WHERE id = ?');
            $ok = $stmt->execute([$nome, $email, $telefone, $cargo, $status, $senhaHash, $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE funcionarios SET nome = ?, email = ?, telefone = ?, cargo = ?, status = ?, data_atualizacao = CURRENT_TIMESTAMP WHERE id = ?');
            $ok = $stmt->execute([$nome, $email, $telefone, $cargo, $status, $id]);
        }

        if (!$ok) sendResponse(false, 'Erro ao atualizar funcionário');

        sendResponse(true, 'Funcionário atualizado com sucesso');
    } catch (Exception $e) {
        sendResponse(false, 'Erro ao atualizar funcionário: ' . $e->getMessage());
    }
}

// DELETE funcionario
function deleteFuncionario($params) {
    global $pdo;
    $id = $params['id'] ?? null;
    if (!$id) sendResponse(false, 'id é obrigatório');

    try {
        $stmt = $pdo->prepare('DELETE FROM funcionarios WHERE id = ?');
        $ok = $stmt->execute([$id]);
        if (!$ok || $stmt->rowCount() === 0) sendResponse(false, 'Funcionário não encontrado ou já removido');
        sendResponse(true, 'Funcionário removido com sucesso');
    } catch (Exception $e) {
        sendResponse(false, 'Erro ao remover funcionário: ' . $e->getMessage());
    }
}

// Router
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    $conn = getDbConnection();
    // manter compatibilidade com funções que usam $pdo
    $pdo = $conn;

    switch ($method) {
        case 'GET':
            // GET /funcionarios.php?id=... | ?usuario_id=...
            getFuncionarios($_GET);
            break;
        case 'POST':
            if (!$input) sendResponse(false, 'JSON inválido');
            createFuncionario($input);
            break;
        case 'PUT':
            if (!$input) sendResponse(false, 'JSON inválido');
            updateFuncionario($input);
            break;
        case 'DELETE':
            deleteFuncionario($_GET);
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
