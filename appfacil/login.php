<?php
// Headers CORS (permitindo origens específicas e credenciais)
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
$allowed_origins = [
    'https://gestaodeobrafacil.com',
    'http://localhost:8080',
    'http://localhost',
];
if ($origin && in_array($origin, $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
} else {
    // Default seguro (sem credenciais) quando origem não está na lista
    header('Access-Control-Allow-Origin: https://gestaodeobrafacil.com');
}

header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');

// Responder às requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');

// Adicionar log para depuração
error_log("Requisição recebida em login.php");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'não definido'));
error_log("Origin: " . ($origin ?? 'não definido'));

require_once __DIR__ . '/config/pdo.php';

function response($success, $message, $data = null) {
    // Adicionar log para depuração
    error_log("Resposta: success=$success, message=$message");
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data], JSON_PRETTY_PRINT);
    exit;
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

// Recebe os dados brutos
$json = file_get_contents('php://input');
error_log("Dados recebidos: $json");

$input = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Erro no JSON: " . json_last_error_msg());
    response(false, 'Dados JSON inválidos.');
}

$email = $input['email'] ?? null;
$password = $input['password'] ?? null;

error_log("Email: $email");
error_log("Password: " . (empty($password) ? 'vazio' : 'preenchido'));

if (!$email || !$password) {
    response(false, 'E-mail e senha são obrigatórios.');
}

// Conexão com o banco
$conn = getDbConnection();

// 1) Tentativa de login para tabela usuarios (padrão)
$stmt = $conn->prepare('SELECT id, nome, tipo_conta, senha FROM usuarios WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['senha'])) {
    // Gerar novo token e atualizar data de último acesso
    $token = generateToken();
    $ultimo_acesso = date('Y-m-d H:i:s');
    
    // Atualizar token e data de último acesso no banco
    $updateStmt = $conn->prepare('UPDATE usuarios SET token = ?, token_expiracao = DATE_ADD(NOW(), INTERVAL 30 DAY), ultimo_acesso = ? WHERE id = ?');
    $updateStmt->execute([$token, $ultimo_acesso, $user['id']]);
    
    $data = [
        'id' => $user['id'],
        'name' => $user['nome'],
        'accountType' => $user['tipo_conta'],
        'email' => $email,
        'token' => $token
    ];

    response(true, 'Login realizado com sucesso.', $data);
}

// 2) Fallback: tentar login como funcionário ativo
// Se não encontrou usuário ou senha inválida, tentar na tabela funcionarios
$stmt = $conn->prepare('SELECT f.id AS id_funcionario, f.usuario_id, f.nome, f.email, f.senha, f.status, u.tipo_conta 
                        FROM funcionarios f 
                        JOIN usuarios u ON u.id = f.usuario_id 
                        WHERE f.email = ? AND f.status = "ativo"');
$stmt->execute([$email]);
$func = $stmt->fetch();

if ($func && password_verify($password, $func['senha'])) {
    // Gerar novo token e atualizar data de último acesso para o usuário principal
    $token = generateToken();
    $ultimo_acesso = date('Y-m-d H:i:s');
    
    // Atualizar token e data de último acesso no banco para o usuário principal
    $updateStmt = $conn->prepare('UPDATE usuarios SET token = ?, token_expiracao = DATE_ADD(NOW(), INTERVAL 30 DAY), ultimo_acesso = ? WHERE id = ?');
    $updateStmt->execute([$token, $ultimo_acesso, $func['usuario_id']]);
    
    $data = [
        // Importante: para o app, o id deve ser o usuario_id do dono da conta
        'id' => $func['usuario_id'],
        'name' => $func['nome'],
        'accountType' => $func['tipo_conta'],
        'email' => $func['email'],
        'token' => $token,
        // Enviar também o id_funcionario para o app saber que é login de funcionário
        'id_funcionario' => $func['id_funcionario']
    ];
    response(true, 'Login realizado com sucesso (funcionário).', $data);
}

response(false, 'E-mail ou senha incorretos.');

