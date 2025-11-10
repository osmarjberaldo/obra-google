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

require_once __DIR__ . '/config/pdo.php';

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
    // Initialize database connection
    try {
        $conn = getDbConnection();
    } catch (RuntimeException $e) {
        error_log('Database connection error: ' . $e->getMessage());
        sendJsonResponse(false, 'Erro ao conectar ao banco de dados', null, 500);
    }
    
    // Get request data
    $input = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        if (json_last_error() !== JSON_ERROR_NONE) {
            $input = $_POST; // Fallback to form data if JSON parsing fails
        }
    }
    
    // Handle request based on method
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Buscar condição climática de um relatório específico
            $relatorio_id = $_GET['relatorio_id'] ?? null;
            $usuario_id = $_GET['usuario_id'] ?? null;
            
            if (!$relatorio_id || !$usuario_id) {
                sendJsonResponse(false, 'ID do relatório e ID do usuário são obrigatórios', null, 400);
            }
            
            try {
                // Verificar se o relatório pertence ao usuário
                $stmt = $conn->prepare("
                    SELECT id FROM relatorios_diarios 
                    WHERE id = :relatorio_id AND usuario_id = :usuario_id
                ");
                $stmt->execute([
                    ':relatorio_id' => $relatorio_id,
                    ':usuario_id' => $usuario_id
                ]);
                
                if ($stmt->rowCount() === 0) {
                    sendJsonResponse(false, 'Relatório não encontrado ou acesso não autorizado', null, 404);
                }
                
                // Buscar condição climática do relatório (máximo 1)
                $stmt = $conn->prepare("
                    SELECT 
                        id, 
                        tipo_clima,
                        temperatura,
                        umidade,
                        velocidade_vento,
                        condicao_trabalho,
                        observacoes
                    FROM relatorio_clima 
                    WHERE relatorio_id = :relatorio_id
                    LIMIT 1
                ");
                $stmt->execute([':relatorio_id' => $relatorio_id]);
                
                $clima = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Mapear o resultado para o formato esperado pelo frontend
                if ($clima) {
                    $clima = [
                        'id' => (string)$clima['id'],
                        'tipo_clima' => $clima['tipo_clima'],
                        'temperatura' => (int)$clima['temperatura'],
                        'umidade' => (int)$clima['umidade'],
                        'velocidade_vento' => (int)$clima['velocidade_vento'],
                        'condicao_trabalho' => $clima['condicao_trabalho'],
                        'observacoes' => $clima['observacoes'] ?: ''
                    ];
                    
                    error_log('Condição climática encontrada: ' . print_r($clima, true));
                    sendJsonResponse(true, 'Condição climática carregada com sucesso', $clima);
                } else {
                    sendJsonResponse(true, 'Nenhuma condição climática registrada', null);
                }
                
            } catch (PDOException $e) {
                error_log('Erro ao buscar condição climática: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao carregar condição climática', null, 500);
            }
            break;
            
        case 'POST':
            // Criar nova condição climática
            if (empty($input['relatorio_id']) || empty($input['usuario_id']) || empty($input['tipo_clima'])) {
                sendJsonResponse(false, 'Dados incompletos', null, 400);
            }
            
            // Verificar se o usuário tem permissão para adicionar condição climática neste relatório
            $stmt = $conn->prepare("
                SELECT id FROM relatorios_diarios 
                WHERE id = :relatorio_id AND usuario_id = :usuario_id
            ");
            $stmt->execute([
                ':relatorio_id' => $input['relatorio_id'],
                ':usuario_id' => $input['usuario_id']
            ]);
            
            if ($stmt->rowCount() === 0) {
                sendJsonResponse(false, 'Você não tem permissão para adicionar condição climática neste relatório', null, 403);
            }
            
            // Verificar se já existe uma condição climática para este relatório
            $stmt = $conn->prepare("
                SELECT id FROM relatorio_clima 
                WHERE relatorio_id = :relatorio_id
            ");
            $stmt->execute([':relatorio_id' => $input['relatorio_id']]);
            
            if ($stmt->rowCount() > 0) {
                sendJsonResponse(false, 'Já existe uma condição climática registrada para este relatório. Apenas uma condição por relatório é permitida.', null, 409);
            }
            
            $stmt = $conn->prepare("
                INSERT INTO relatorio_clima 
                (relatorio_id, usuario_id, tipo_clima, temperatura, umidade, velocidade_vento, condicao_trabalho, observacoes, data_criacao)
                VALUES (:relatorio_id, :usuario_id, :tipo_clima, :temperatura, :umidade, :velocidade_vento, :condicao_trabalho, :observacoes, NOW())
            ");
            
            $stmt->execute([
                ':relatorio_id' => $input['relatorio_id'],
                ':usuario_id' => $input['usuario_id'],
                ':tipo_clima' => $input['tipo_clima'],
                ':temperatura' => $input['temperatura'] ?? 0,
                ':umidade' => $input['umidade'] ?? 0,
                ':velocidade_vento' => $input['velocidade_vento'] ?? 0,
                ':condicao_trabalho' => $input['condicao_trabalho'] ?? 'ideal',
                ':observacoes' => $input['observacoes'] ?? ''
            ]);
            
            $clima_id = $conn->lastInsertId();
            
            // Buscar a condição climática recém-criada para retornar
            $stmt = $conn->prepare("SELECT * FROM relatorio_clima WHERE id = :id");
            $stmt->execute([':id' => $clima_id]);
            $clima = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendJsonResponse(true, 'Condição climática criada com sucesso', $clima, 201);
            break;
            
        case 'PUT':
            // Atualizar condição climática
            if (empty($input['id'])) {
                sendJsonResponse(false, 'ID da condição climática é obrigatório', null, 400);
            }
            
            $usuario_id = $input['usuario_id'] ?? null;
            if (!$usuario_id) {
                sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
            }
            
            // Verificar se o usuário tem permissão para atualizar esta condição climática
            $stmt = $conn->prepare("
                SELECT rc.id FROM relatorio_clima rc
                INNER JOIN relatorios_diarios rd ON rc.relatorio_id = rd.id
                WHERE rc.id = :id AND rd.usuario_id = :usuario_id
            ");
            
            $stmt->execute([
                ':id' => $input['id'],
                ':usuario_id' => $usuario_id
            ]);
            
            if ($stmt->rowCount() === 0) {
                sendJsonResponse(false, 'Você não tem permissão para atualizar esta condição climática', null, 403);
            }
            
            try {
                // Preparar campos para atualização
                $updateFields = [];
                $params = [':id' => $input['id']];
                
                if (isset($input['tipo_clima'])) {
                    $updateFields[] = 'tipo_clima = :tipo_clima';
                    $params[':tipo_clima'] = $input['tipo_clima'];
                }
                
                if (isset($input['temperatura'])) {
                    $updateFields[] = 'temperatura = :temperatura';
                    $params[':temperatura'] = $input['temperatura'];
                }
                
                if (isset($input['umidade'])) {
                    $updateFields[] = 'umidade = :umidade';
                    $params[':umidade'] = $input['umidade'];
                }
                
                if (isset($input['velocidade_vento'])) {
                    $updateFields[] = 'velocidade_vento = :velocidade_vento';
                    $params[':velocidade_vento'] = $input['velocidade_vento'];
                }
                
                if (isset($input['condicao_trabalho'])) {
                    $updateFields[] = 'condicao_trabalho = :condicao_trabalho';
                    $params[':condicao_trabalho'] = $input['condicao_trabalho'];
                }
                
                if (isset($input['observacoes'])) {
                    $updateFields[] = 'observacoes = :observacoes';
                    $params[':observacoes'] = $input['observacoes'];
                }
                
                if (empty($updateFields)) {
                    sendJsonResponse(false, 'Nenhum campo para atualizar foi fornecido', null, 400);
                }
                
                $updateFields[] = 'data_atualizacao = NOW()';
                
                $sql = "UPDATE relatorio_clima SET " . implode(', ', $updateFields) . " WHERE id = :id";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                
                if ($stmt->rowCount() > 0) {
                    sendJsonResponse(true, 'Condição climática atualizada com sucesso');
                } else {
                    sendJsonResponse(false, 'Nenhuma alteração foi feita', null, 404);
                }
                
            } catch (Exception $e) {
                error_log('Erro ao atualizar condição climática: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao atualizar condição climática', null, 500);
            }
            break;

        case 'DELETE':
            // Deletar condição climática
            if (empty($input['id'])) {
                sendJsonResponse(false, 'ID da condição climática é obrigatório', null, 400);
            }
            
            $usuario_id = $input['usuario_id'] ?? null;
            if (!$usuario_id) {
                sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
            }
            
            try {
                // Verificar se o usuário tem permissão para excluir esta condição climática
                $stmt = $conn->prepare("
                    SELECT rc.id FROM relatorio_clima rc
                    INNER JOIN relatorios_diarios rd ON rc.relatorio_id = rd.id
                    WHERE rc.id = :id AND rd.usuario_id = :usuario_id
                ");
                
                $stmt->execute([
                    ':id' => $input['id'],
                    ':usuario_id' => $usuario_id
                ]);
                
                if ($stmt->rowCount() === 0) {
                    sendJsonResponse(false, 'Você não tem permissão para excluir esta condição climática', null, 403);
                }
                
                // Excluir a condição climática
                $stmt = $conn->prepare("DELETE FROM relatorio_clima WHERE id = :id");
                $stmt->execute([':id' => $input['id']]);
                
                if ($stmt->rowCount() > 0) {
                    sendJsonResponse(true, 'Condição climática excluída com sucesso');
                } else {
                    sendJsonResponse(false, 'Condição climática não encontrada', null, 404);
                }
                
            } catch (Exception $e) {
                error_log('Erro ao excluir condição climática: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao excluir condição climática', null, 500);
            }
            break;
            
        default:
            sendJsonResponse(false, 'Método não permitido', null, 405);
    }

} catch (Exception $e) {
    error_log('Erro em relatorio_clima.php: ' . $e->getMessage());
    sendJsonResponse(false, 'Erro interno do servidor: ' . $e->getMessage(), null, 500);
}

// Fechar conexão
$conn = null;