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
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

// Recebe os dados brutos
$json = file_get_contents('php://input');
$input = json_decode($json, true);

// Verifica se houve erro no decode do JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    if (!empty($_POST)) {
        $input = $_POST;
    } else {
        response(false, 'Erro ao processar os dados recebidos. JSON inválido: ' . json_last_error_msg());
    }
}

// Validação dos campos obrigatórios
$required = ['usuario_id', 'valor', 'plano', 'periodo'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        response(false, "Campo obrigatório: $field");
    }
}

$usuario_id = $input['usuario_id'];
$valor = $input['valor'];
$plano = $input['plano'];
$periodo = $input['periodo'];
$cliente_nome = $input['cliente_nome'] ?? '';
$cliente_email = $input['cliente_email'] ?? '';
$cliente_documento = $input['cliente_documento'] ?? '';

// Conexão com o banco
$conn = getDbConnection();

try {
    // Gerar um order_nsu único
    $order_nsu = uniqid('order_', true);
    
    // Determinar o tipo de plano
    $plano_tipo = 'basic';
    if (stripos($plano, 'premium') !== false) {
        $plano_tipo = 'premium';
    } else if (stripos($plano, 'profissional') !== false) {
        $plano_tipo = 'profissional';
    }
    
    // Inserir registro na tabela pagamentos_infinitepay
    $stmt = $conn->prepare('
        INSERT INTO pagamentos_infinitepay 
        (usuario_id, order_nsu, status, amount, customer_email, plano_tipo, periodo, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ');
    
    // Converter valor para formato decimal (remover "R$ " e converter vírgula para ponto)
    $valor_numerico = str_replace(['R$', ' ', '.'], '', $valor);
    $valor_numerico = str_replace(',', '.', $valor_numerico);
    $amount = floatval($valor_numerico);
    
    $ok = $stmt->execute([
        $usuario_id,
        $order_nsu,
        'pending',  // Status inicial
        $amount,
        $cliente_email,
        $plano_tipo,
        $periodo
    ]);
    
    if (!$ok) {
        response(false, 'Erro ao registrar pagamento');
    }
    
    // Retornar os dados necessários para criar o link de pagamento
    response(true, 'Pagamento registrado com sucesso', [
        'order_nsu' => $order_nsu,
        'payment_id' => $conn->lastInsertId(),
        'amount' => $amount,
        'customer_email' => $cliente_email,
        'plano_tipo' => $plano_tipo,
        'periodo' => $periodo
    ]);
    
} catch (Exception $e) {
    response(false, 'Erro ao registrar pagamento: ' . $e->getMessage());
}

?>