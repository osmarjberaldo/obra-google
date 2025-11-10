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

// Função para log detalhado
function log_debug($message, $data = null) {
    $log = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    if ($data !== null) {
        $log .= 'Data: ' . json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL;
    }
    $log .= str_repeat('-', 50) . PHP_EOL;
    file_put_contents(__DIR__.'/register_debug.log', $log, FILE_APPEND);
}

function response($success, $message, $data = null) {
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'debug_received' => $GLOBALS['debug_received'] ?? null,
        'debug_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
    ];
    
    log_debug('Response sent', $response);
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

// Inicia o log
log_debug('=== NOVA REQUISIÇÃO ===');

// Recebe os dados brutos
$json = file_get_contents('php://input');

// Log dos headers recebidos
log_debug('Headers recebidos:', [
    'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? 'não definido',
    'HTTP_ACCEPT' => $_SERVER['HTTP_ACCEPT'] ?? 'não definido',
    'CONTENT_LENGTH' => $_SERVER['CONTENT_LENGTH'] ?? 'não definido',
    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'não definido'
]);

// Log dos dados brutos recebidos
log_debug('Dados brutos recebidos:', [
    'raw' => $json,
    'raw_length' => strlen($json),
    'is_json' => json_decode($json) !== null ? 'sim' : 'não',
    'json_last_error' => json_last_error_msg()
]);

// Tenta decodificar o JSON
$input = json_decode($json, true);

// Verifica se houve erro no decode do JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    log_debug('Erro ao decodificar JSON', [
        'error' => json_last_error_msg(),
        'json' => $json,
        'json_length' => strlen($json),
        'json_first_chars' => substr($json, 0, 200) . (strlen($json) > 200 ? '...' : ''),
        'php_version' => phpversion(),
        'all_headers' => getallheaders(),
        'request_method' => $_SERVER['REQUEST_METHOD'],
        'post_data' => $_POST
    ]);
    
    // Tenta outras formas de receber os dados
    if (!empty($_POST)) {
        log_debug('Tentando usar dados POST', $_POST);
        $input = $_POST;
    } else {
        // Tenta decodificar manualmente para diagnóstico
        $cleanedJson = trim($json);
        if (substr($cleanedJson, 0, 1) === '{' && substr($cleanedJson, -1) === '}') {
            log_debug('JSON parece estar bem formado, mas falhou ao decodificar', [
                'first_char' => substr($cleanedJson, 0, 1),
                'last_char' => substr($cleanedJson, -1),
                'length' => strlen($cleanedJson),
                'hex_dump' => bin2hex(substr($cleanedJson, 0, 50))
            ]);
        }
        
        response(false, 'Erro ao processar os dados recebidos. JSON inválido: ' . json_last_error_msg());
    }
}

$GLOBALS['debug_received'] = $input;
log_debug('Input decodificado', $input);

// Se debug estiver ativo, apenas registra no log mas continua o processamento
if (isset($input['debug'])) {
    log_debug('Modo DEBUG ativado', [
        'received' => $input,
        'server_info' => [
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
            'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? 'não definido',
            'CONTENT_LENGTH' => $_SERVER['CONTENT_LENGTH'] ?? 'não definido'
        ]
    ]);
}

// Verifica se é português brasileiro (idioma não enviado, assume português por padrão)
$isPortuguese = !isset($input['idioma']) || $input['idioma'] === 'pt-br';

$required = ['nome', 'email', 'senha', 'tipo_conta', 'telefone'];
// Para português brasileiro, documento continua obrigatório
if ($isPortuguese) {
    $required[] = 'documento';
}

log_debug('Iniciando validação de campos obrigatórios', [
    'required_fields' => $required,
    'is_portuguese' => $isPortuguese
]);

foreach ($required as $field) {
    log_debug("Verificando campo: $field", [
        'existe' => isset($input[$field]) ? 'sim' : 'não',
        'valor' => $input[$field] ?? 'não definido',
        'vazio' => empty($input[$field]) ? 'sim' : 'não'
    ]);
    
    if (empty($input[$field])) {
        log_debug("Campo obrigatório faltando: $field");
        response(false, "Campo obrigatório: $field");
    }
}

// Não permitir URLs nos campos
$urlPattern = '/https?:\/\//i';
$fieldsToCheck = $isPortuguese ? ['nome','email','documento','telefone'] : ['nome','email','telefone'];
foreach ($fieldsToCheck as $field) {
    if (isset($input[$field]) && preg_match($urlPattern, $input[$field])) {
        response(false, "Campo inválido: $field não pode conter URL");
    }
}

// Validar senha: pelo menos 1 maiúscula e 1 caractere especial
if (!preg_match('/[A-Z]/', $input['senha']) || !preg_match('/[^a-zA-Z0-9]/', $input['senha'])) {
    response(false, 'A senha deve conter pelo menos 1 letra maiúscula e 1 caractere especial');
}

$nome = $input['nome'];
$email = $input['email'];
$senha = password_hash($input['senha'], PASSWORD_BCRYPT);
$tipo_conta = $input['tipo_conta'];
$documento = $input['documento'] ?? ''; // Para idiomas diferentes de português, documento é opcional
$telefone = $input['telefone'];
$tipo_plat = 'Android'; // Valor fixo conforme solicitado
$data_cadastro = date('Y-m-d H:i:s');
$ativo = 1;

// Conexão com o banco
$conn = getDbConnection();

// Verifica se email já existe
log_debug('Verificando se email já existe', ['email' => $email]);
$stmt = $conn->prepare('SELECT id FROM usuarios WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    log_debug('E-mail já cadastrado', ['email' => $email]);
    response(false, 'E-mail já cadastrado');
}

// Verifica se documento (CPF/CNPJ) já existe (apenas para português brasileiro e quando documento não está vazio)
if ($isPortuguese && !empty($documento)) {
    $stmt = $conn->prepare('SELECT id FROM usuarios WHERE documento = ?');
    $stmt->execute([$documento]);
    if ($stmt->fetch()) {
        response(false, 'CPF/CNPJ já cadastrado');
    }
}

// Insere usuário
log_debug('Preparando para inserir usuário', [
    'tipo_conta' => $tipo_conta,
    'nome' => $nome,
    'documento' => $documento,
    'email' => $email,
    'telefone' => $telefone,
    'data_cadastro' => $data_cadastro,
    'ativo' => $ativo
]);

$stmt = $conn->prepare('INSERT INTO usuarios (tipo_conta, nome, documento, telefone, email, senha, tipo_plat, data_cadastro, ativo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
$ok = $stmt->execute([$tipo_conta, $nome, $documento, $telefone, $email, $senha, $tipo_plat, $data_cadastro, $ativo]);
if (!$ok) {
    response(false, 'Erro ao cadastrar usuário');
}

$usuario_id = $conn->lastInsertId();

// Cria assinatura trial de 30 dias
$data_inicio = date('Y-m-d');
$data_fim = date('Y-m-d', strtotime('+30 days'));
$stmt = $conn->prepare('INSERT INTO assinaturas (usuario_id, plano_id, data_inicio, data_fim, tipo_assinatura, status) VALUES (?, NULL, ?, ?, ?, ?)');
$ok = $stmt->execute([$usuario_id, $data_inicio, $data_fim, 'trial', 'ativo']);
if (!$ok) {
    response(false, 'Usuário criado, mas erro ao criar assinatura trial');
}

response(true, 'Usuário e assinatura trial criados com sucesso', ['usuario_id' => $usuario_id]);