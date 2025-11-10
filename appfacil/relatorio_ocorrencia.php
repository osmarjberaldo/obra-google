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
            // Buscar ocorrências de um relatório específico
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
                
                // Buscar ocorrências do relatório
                $stmt = $conn->prepare("
                    SELECT 
                        id, 
                        tipo, 
                        descricao, 
                        gravidade,
                        hora_ocorrencia,
                        ordem_exibicao
                    FROM relatorio_ocorrencias 
                    WHERE relatorio_id = :relatorio_id
                    ORDER BY ordem_exibicao ASC
                ");
                $stmt->execute([':relatorio_id' => $relatorio_id]);
                
                $ocorrencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Mapear os resultados para o formato esperado pelo frontend
                $ocorrencias = array_map(function($ocorrencia) {
                    return [
                        'id' => (string)$ocorrencia['id'],
                        'tipo' => $ocorrencia['tipo'],
                        'descricao' => $ocorrencia['descricao'],
                        'gravidade' => $ocorrencia['gravidade'],
                        'hora_ocorrencia' => $ocorrencia['hora_ocorrencia'],
                        'ordem_exibicao' => (int)$ocorrencia['ordem_exibicao']
                    ];
                }, $ocorrencias);
                
                error_log('Ocorrências encontradas: ' . print_r($ocorrencias, true));
                
                sendJsonResponse(true, 'Ocorrências carregadas com sucesso', $ocorrencias);
                
            } catch (PDOException $e) {
                error_log('Erro ao buscar ocorrências: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao carregar ocorrências', null, 500);
            }
            break;
            
        case 'POST':
            // Criar nova ocorrência
            if (empty($input['relatorio_id']) || empty($input['usuario_id']) || empty($input['tipo']) || empty($input['descricao'])) {
                sendJsonResponse(false, 'Dados incompletos', null, 400);
            }
            
            // Verificar se o usuário tem permissão para adicionar ocorrências neste relatório
            $stmt = $conn->prepare("
                SELECT id FROM relatorios_diarios 
                WHERE id = :relatorio_id AND usuario_id = :usuario_id
            ");
            $stmt->execute([
                ':relatorio_id' => $input['relatorio_id'],
                ':usuario_id' => $input['usuario_id']
            ]);
            
            if ($stmt->rowCount() === 0) {
                sendJsonResponse(false, 'Você não tem permissão para adicionar ocorrências neste relatório', null, 403);
            }
            
            // Primeiro, obtemos a próxima ordem de exibição
            $stmt = $conn->prepare("
                SELECT COALESCE(MAX(ordem_exibicao), 0) + 1 as next_ordem 
                FROM relatorio_ocorrencias 
                WHERE relatorio_id = :relatorio_id
            ");
            $stmt->execute([':relatorio_id' => $input['relatorio_id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $next_ordem = $result['next_ordem'];
            
            $stmt = $conn->prepare("
                INSERT INTO relatorio_ocorrencias 
                (relatorio_id, usuario_id, tipo, descricao, gravidade, hora_ocorrencia, ordem_exibicao, data_criacao)
                VALUES (:relatorio_id, :usuario_id, :tipo, :descricao, :gravidade, :hora_ocorrencia, :ordem_exibicao, NOW())
            ");
            
            $stmt->execute([
                ':relatorio_id' => $input['relatorio_id'],
                ':usuario_id' => $input['usuario_id'],
                ':tipo' => $input['tipo'],
                ':descricao' => $input['descricao'],
                ':gravidade' => $input['gravidade'] ?? 'Média',
                ':hora_ocorrencia' => $input['hora_ocorrencia'] ?? null,
                ':ordem_exibicao' => $next_ordem
            ]);
            
            $ocorrencia_id = $conn->lastInsertId();
            
            // Buscar a ocorrência recém-criada para retornar
            $stmt = $conn->prepare("SELECT * FROM relatorio_ocorrencias WHERE id = :id");
            $stmt->execute([':id' => $ocorrencia_id]);
            $ocorrencia = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendJsonResponse(true, 'Ocorrência criada com sucesso', $ocorrencia, 201);
            break;
            
        case 'PUT':
            // Verificar se é uma requisição de reordenação
            if (isset($input['reorder']) && $input['reorder'] === true) {
                // Atualizar ordem das ocorrências
                if (empty($input['occurrences']) || !is_array($input['occurrences'])) {
                    sendJsonResponse(false, 'Lista de ocorrências inválida', null, 400);
                }
                
                if (empty($input['usuario_id'])) {
                    sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
                }
                
                try {
                    $conn->beginTransaction();
                    
                    // Verificar se o usuário tem permissão para reordenar ocorrências
                    $stmt = $conn->prepare("
                        SELECT ro.id FROM relatorio_ocorrencias ro
                        INNER JOIN relatorios_diarios rd ON ro.relatorio_id = rd.id
                        WHERE ro.id = :id AND rd.usuario_id = :usuario_id
                    ");
                    
                    foreach ($input['occurrences'] as $occurrenceData) {
                        if (empty($occurrenceData['id']) || !isset($occurrenceData['ordem_exibicao'])) {
                            $conn->rollBack();
                            sendJsonResponse(false, 'Dados da ocorrência inválidos', null, 400);
                        }
                        
                        $stmt->execute([
                            ':id' => $occurrenceData['id'],
                            ':usuario_id' => $input['usuario_id']
                        ]);
                        
                        if ($stmt->rowCount() === 0) {
                            $conn->rollBack();
                            sendJsonResponse(false, 'Você não tem permissão para reordenar ocorrências deste relatório', null, 403);
                        }
                    }
                    
                    // Atualizar a ordem das ocorrências
                    $updateStmt = $conn->prepare("
                        UPDATE relatorio_ocorrencias
                        SET ordem_exibicao = :ordem_exibicao,
                            data_atualizacao = NOW()
                        WHERE id = :id
                    ");
                    
                    foreach ($input['occurrences'] as $occurrenceData) {
                        $updateStmt->execute([
                            ':id' => $occurrenceData['id'],
                            ':ordem_exibicao' => $occurrenceData['ordem_exibicao']
                        ]);
                    }
                    
                    $conn->commit();
                    sendJsonResponse(true, 'Ordem das ocorrências atualizada com sucesso');
                    
                } catch (Exception $e) {
                    $conn->rollBack();
                    error_log('Erro ao reordenar ocorrências: ' . $e->getMessage());
                    sendJsonResponse(false, 'Erro ao reordenar ocorrências', null, 500);
                }
            } else {
                // Atualizar ocorrência (campos diversos)
                if (empty($input['id'])) {
                    sendJsonResponse(false, 'ID da ocorrência é obrigatório', null, 400);
                }
                
                $usuario_id = $input['usuario_id'] ?? null;
                if (!$usuario_id) {
                    sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
                }
                
                // Verificar se o usuário tem permissão para atualizar esta ocorrência
                $stmt = $conn->prepare("
                    SELECT ro.id FROM relatorio_ocorrencias ro
                    INNER JOIN relatorios_diarios rd ON ro.relatorio_id = rd.id
                    WHERE ro.id = :id AND rd.usuario_id = :usuario_id
                ");
                
                $stmt->execute([
                    ':id' => $input['id'],
                    ':usuario_id' => $usuario_id
                ]);
                
                if ($stmt->rowCount() === 0) {
                    sendJsonResponse(false, 'Você não tem permissão para atualizar esta ocorrência', null, 403);
                }
                
                try {
                    // Preparar campos para atualização
                    $updateFields = [];
                    $params = [':id' => $input['id']];
                    
                    if (isset($input['tipo'])) {
                        $updateFields[] = 'tipo = :tipo';
                        $params[':tipo'] = $input['tipo'];
                    }
                    
                    if (isset($input['descricao'])) {
                        $updateFields[] = 'descricao = :descricao';
                        $params[':descricao'] = $input['descricao'];
                    }
                    
                    if (isset($input['gravidade'])) {
                        $updateFields[] = 'gravidade = :gravidade';
                        $params[':gravidade'] = $input['gravidade'];
                    }
                    
                    if (isset($input['hora_ocorrencia'])) {
                        $updateFields[] = 'hora_ocorrencia = :hora_ocorrencia';
                        $params[':hora_ocorrencia'] = $input['hora_ocorrencia'];
                    }
                    
                    if (empty($updateFields)) {
                        sendJsonResponse(false, 'Nenhum campo para atualizar foi fornecido', null, 400);
                    }
                    
                    $updateFields[] = 'data_atualizacao = NOW()';
                    
                    $sql = "UPDATE relatorio_ocorrencias SET " . implode(', ', $updateFields) . " WHERE id = :id";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->execute($params);
                    
                    if ($stmt->rowCount() > 0) {
                        sendJsonResponse(true, 'Ocorrência atualizada com sucesso');
                    } else {
                        sendJsonResponse(false, 'Nenhuma alteração foi feita', null, 404);
                    }
                    
                } catch (Exception $e) {
                    error_log('Erro ao atualizar ocorrência: ' . $e->getMessage());
                    sendJsonResponse(false, 'Erro ao atualizar ocorrência', null, 500);
                }
            }
            break;

        case 'DELETE':
            // Deletar ocorrência
            if (empty($input['id'])) {
                sendJsonResponse(false, 'ID da ocorrência é obrigatório', null, 400);
            }
            
            $usuario_id = $input['usuario_id'] ?? null;
            if (!$usuario_id) {
                sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
            }
            
            try {
                // Verificar se o usuário tem permissão para excluir esta ocorrência
                $stmt = $conn->prepare("
                    SELECT ro.id FROM relatorio_ocorrencias ro
                    INNER JOIN relatorios_diarios rd ON ro.relatorio_id = rd.id
                    WHERE ro.id = :id AND rd.usuario_id = :usuario_id
                ");
                
                $stmt->execute([
                    ':id' => $input['id'],
                    ':usuario_id' => $usuario_id
                ]);
                
                if ($stmt->rowCount() === 0) {
                    sendJsonResponse(false, 'Você não tem permissão para excluir esta ocorrência', null, 403);
                }
                
                // Excluir a ocorrência
                $stmt = $conn->prepare("DELETE FROM relatorio_ocorrencias WHERE id = :id");
                $stmt->execute([':id' => $input['id']]);
                
                if ($stmt->rowCount() > 0) {
                    sendJsonResponse(true, 'Ocorrência excluída com sucesso');
                } else {
                    sendJsonResponse(false, 'Ocorrência não encontrada', null, 404);
                }
                
            } catch (Exception $e) {
                error_log('Erro ao excluir ocorrência: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao excluir ocorrência', null, 500);
            }
            break;
            
        default:
            sendJsonResponse(false, 'Método não permitido', null, 405);
    }

} catch (Exception $e) {
    error_log('Erro em relatorio_ocorrencia.php: ' . $e->getMessage());
    sendJsonResponse(false, 'Erro interno do servidor: ' . $e->getMessage(), null, 500);
}

// Fechar conexão
$conn = null;