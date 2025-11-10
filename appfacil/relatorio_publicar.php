<?php
// Headers CORS
header('Access-Control-Allow-Origin: https://gestaodeobrafacil.com');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Responder às requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Função para enviar resposta em JSON
function sendJsonResponse($success, $message = '', $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar se o arquivo de configuração existe
if (!file_exists(__DIR__ . '/config/pdo.php')) {
    sendJsonResponse(false, 'Arquivo de configuração do banco de dados não encontrado', null, 500);
}

require_once __DIR__ . '/config/pdo.php';

try {
    // Initialize database connection
    try {
        $conn = getDbConnection();
    } catch (RuntimeException $e) {
        error_log('Database connection error: ' . $e->getMessage());
        sendJsonResponse(false, 'Erro ao conectar ao banco de dados', null, 500);
    }
    
    // Only handle PUT requests for updating id_cliente
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Get input data
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        
        // Get required fields
        $relatorio_id = $input['id'] ?? null;
        $id_cliente = $input['id_cliente'] ?? null;
        $status = $input['status'] ?? 'finalizado';
        
        // Validate required fields
        if (!$relatorio_id || !$id_cliente) {
            sendJsonResponse(false, 'ID do relatório e ID do cliente são obrigatórios', null, 400);
        }
        
        try {
            // Verificar se o relatório existe
            $verifyQuery = "SELECT id FROM relatorios_diarios WHERE id = ?";
            $verifyStmt = $conn->prepare($verifyQuery);
            $verifyStmt->execute([$relatorio_id]);
            
            if ($verifyStmt->rowCount() === 0) {
                sendJsonResponse(false, 'Relatório não encontrado', null, 404);
            }
            
            // Atualizar apenas o id_cliente e status do relatório
            $updateQuery = "
                UPDATE relatorios_diarios SET 
                    id_cliente = ?,
                    status = ?
                WHERE id = ?
            ";
            
            $stmt = $conn->prepare($updateQuery);
            $stmt->execute([$id_cliente, $status, $relatorio_id]);
            
            if ($stmt->rowCount() > 0) {
                sendJsonResponse(true, 'Relatório publicado com sucesso', [
                    'id' => $relatorio_id,
                    'id_cliente' => $id_cliente,
                    'status' => $status
                ]);
            } else {
                sendJsonResponse(false, 'Nenhuma alteração foi feita', null, 400);
            }
            
        } catch (PDOException $e) {
            error_log('Erro ao atualizar relatório: ' . $e->getMessage());
            sendJsonResponse(false, 'Erro ao atualizar relatório', null, 500);
        }
        
    } else {
        sendJsonResponse(false, 'Método não permitido', null, 405);
    }
    
} catch (Exception $e) {
    error_log('Erro geral: ' . $e->getMessage());
    sendJsonResponse(false, 'Erro interno do servidor', null, 500);
}
?>