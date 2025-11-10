<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://gestaodeobrafacil.com');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');

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

try {
    // Verificar se o arquivo de configuração existe
    if (!file_exists(__DIR__ . '/config/pdo.php')) {
        sendJsonResponse(false, 'Arquivo de configuração do banco de dados não encontrado', null, 500);
    }
    
    require_once __DIR__ . '/config/pdo.php';
    
    // Conectar ao banco
    $conn = getDbConnection();
    
    // Handle request based on method
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Buscar arquivos de um relatório específico
            $relatorio_id = $_GET['relatorio_id'] ?? null;
            $usuario_id = $_GET['usuario_id'] ?? null;
            
            if (!$relatorio_id || !$usuario_id) {
                sendJsonResponse(false, 'ID do relatório e ID do usuário são obrigatórios', null, 400);
            }
            
            try {
                // Buscar arquivos do relatório (simplificado - sem verificação de permissão por enquanto)
                $stmt = $conn->prepare("
                    SELECT 
                        id, 
                        nome_arquivo,
                        nome_original,
                        tipo_arquivo,
                        tamanho_arquivo,
                        caminho_arquivo,
                        descricao,
                        categoria,
                        data_criacao
                    FROM relatorio_arquivos 
                    WHERE relatorio_id = :relatorio_id
                    ORDER BY data_criacao ASC
                ");
                $stmt->execute([':relatorio_id' => $relatorio_id]);
                
                $arquivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Mapear os resultados para o formato esperado pelo frontend
                $arquivos = array_map(function($arquivo) {
                    return [
                        'id' => (string)$arquivo['id'],
                        'nome_arquivo' => $arquivo['nome_arquivo'],
                        'nome_original' => $arquivo['nome_original'],
                        'tipo_arquivo' => $arquivo['tipo_arquivo'],
                        'tamanho_arquivo' => (int)$arquivo['tamanho_arquivo'],
                        'caminho_arquivo' => $arquivo['caminho_arquivo'],
                        'descricao' => $arquivo['descricao'] ?? '',
                        'categoria' => $arquivo['categoria'],
                        'data_criacao' => $arquivo['data_criacao']
                    ];
                }, $arquivos);
                
                sendJsonResponse(true, 'Arquivos carregados com sucesso', $arquivos);
                
            } catch (PDOException $e) {
                sendJsonResponse(false, 'Erro ao carregar arquivos: ' . $e->getMessage(), null, 500);
            }
            break;
            
        case 'POST':
            sendJsonResponse(false, 'Upload não implementado ainda na versão simples', null, 501);
            break;
            
        case 'PUT':
            sendJsonResponse(false, 'Edição não implementada ainda na versão simples', null, 501);
            break;
            
        case 'DELETE':
            sendJsonResponse(false, 'Exclusão não implementada ainda na versão simples', null, 501);
            break;
            
        default:
            sendJsonResponse(false, 'Método não permitido', null, 405);
    }

} catch (Exception $e) {
    sendJsonResponse(false, 'Erro interno do servidor: ' . $e->getMessage(), null, 500);
} catch (Error $e) {
    sendJsonResponse(false, 'Erro fatal do servidor: ' . $e->getMessage(), null, 500);
}

// Fechar conexão
$conn = null;
?>