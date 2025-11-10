<?php
// Headers CORS
header('Access-Control-Allow-Origin: https://gestaodeobrafacil.com');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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

function generateToken() {
    return bin2hex(random_bytes(32));
}

// Recebe os dados brutos
$json = file_get_contents('php://input');
$input = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    response(false, 'Dados JSON inválidos.');
}

$email = $input['email'] ?? null;
$password = $input['password'] ?? null;

if (!$email || !$password) {
    response(false, 'E-mail e senha são obrigatórios.');
}

// Conexão com o banco
$conn = getDbConnection();

// Tentativa de login para tabela clientes
$stmt = $conn->prepare('SELECT id, usuario_id, nome, email, senha FROM clientes WHERE email = ? AND ativo = 1');
$stmt->execute([$email]);
$cliente = $stmt->fetch();

if ($cliente && password_verify($password, $cliente['senha'])) {
    // Gerar novo token e atualizar data de último acesso
    $token = generateToken();
    $ultimo_acesso = date('Y-m-d H:i:s');
    
    // Atualizar token e data de último acesso no banco
    $updateStmt = $conn->prepare('UPDATE clientes SET token = ?, token_expiracao = DATE_ADD(NOW(), INTERVAL 30 DAY), ultimo_acesso = ? WHERE id = ?');
    $updateStmt->execute([$token, $ultimo_acesso, $cliente['id']]);
    
    $data = [
        'id' => $cliente['id'],
        'usuario_id' => $cliente['usuario_id'],
        'name' => $cliente['nome'],
        'email' => $cliente['email'],
        'token' => $token,
        'accountType' => 'cliente'
    ];

    response(true, 'Login realizado com sucesso.', $data);
}

response(false, 'E-mail ou senha incorretos.');