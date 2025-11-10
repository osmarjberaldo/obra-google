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
            // Buscar observações de um relatório específico
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
                
                // Buscar observações do relatório
                $stmt = $conn->prepare("
                    SELECT 
                        id, 
                        titulo_observacao,
                        descricao_observacao,
                        ordem_exibicao
                    FROM relatorio_observacoes 
                    WHERE relatorio_id = :relatorio_id
                    ORDER BY ordem_exibicao ASC
                ");
                $stmt->execute([':relatorio_id' => $relatorio_id]);
                
                $observacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Mapear os resultados para o formato esperado pelo frontend
                $observacoes = array_map(function($item) {
                    return [
                        'id' => (string)$item['id'],
                        'titulo_observacao' => $item['titulo_observacao'],
                        'descricao_observacao' => $item['descricao_observacao'],
                        'ordem_exibicao' => (int)$item['ordem_exibicao']
                    ];
                }, $observacoes);
                
                error_log('Observações encontradas: ' . print_r($observacoes, true));
                
                sendJsonResponse(true, 'Observações carregadas com sucesso', $observacoes);
                
            } catch (PDOException $e) {
                error_log('Erro ao buscar observações: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao carregar observações', null, 500);
            }
            break;
            
        case 'POST':
            // Criar nova observação
            if (empty($input['relatorio_id']) || empty($input['usuario_id']) || empty($input['titulo_observacao']) || empty($input['descricao_observacao'])) {
                sendJsonResponse(false, 'Dados incompletos', null, 400);
            }
            
            // Verificar se o usuário tem permissão para adicionar observação neste relatório
            $stmt = $conn->prepare("
                SELECT id FROM relatorios_diarios 
                WHERE id = :relatorio_id AND usuario_id = :usuario_id
            ");
            $stmt->execute([
                ':relatorio_id' => $input['relatorio_id'],
                ':usuario_id' => $input['usuario_id']
            ]);
            
            if ($stmt->rowCount() === 0) {
                sendJsonResponse(false, 'Você não tem permissão para adicionar observação neste relatório', null, 403);
            }
            
            // Primeiro, obtemos a próxima ordem de exibição
            $stmt = $conn->prepare("
                SELECT COALESCE(MAX(ordem_exibicao), 0) + 1 as next_ordem 
                FROM relatorio_observacoes 
                WHERE relatorio_id = :relatorio_id
            ");
            $stmt->execute([':relatorio_id' => $input['relatorio_id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $next_ordem = $result['next_ordem'];
            
            $stmt = $conn->prepare("
                INSERT INTO relatorio_observacoes 
                (relatorio_id, usuario_id, titulo_observacao, descricao_observacao, ordem_exibicao, data_criacao)
                VALUES (:relatorio_id, :usuario_id, :titulo_observacao, :descricao_observacao, :ordem_exibicao, NOW())
            ");
            
            $stmt->execute([
                ':relatorio_id' => $input['relatorio_id'],
                ':usuario_id' => $input['usuario_id'],
                ':titulo_observacao' => $input['titulo_observacao'],
                ':descricao_observacao' => $input['descricao_observacao'],
                ':ordem_exibicao' => $next_ordem
            ]);
            
            $observacao_id = $conn->lastInsertId();
            
            // Buscar a observação recém-criada para retornar
            $stmt = $conn->prepare("SELECT * FROM relatorio_observacoes WHERE id = :id");
            $stmt->execute([':id' => $observacao_id]);
            $observacao = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendJsonResponse(true, 'Observação criada com sucesso', $observacao, 201);
            break;
            
        case 'PUT':
            // Verificar se é uma requisição de reordenação
            if (isset($input['reorder']) && $input['reorder'] === true) {
                // Atualizar ordem das observações
                if (empty($input['observations']) || !is_array($input['observations'])) {
                    sendJsonResponse(false, 'Lista de observações inválida', null, 400);
                }
                
                if (empty($input['usuario_id'])) {
                    sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
                }
                
                try {
                    $conn->beginTransaction();
                    
                    // Verificar se o usuário tem permissão para reordenar observações
                    $stmt = $conn->prepare("
                        SELECT ro.id FROM relatorio_observacoes ro
                        INNER JOIN relatorios_diarios rd ON ro.relatorio_id = rd.id
                        WHERE ro.id = :id AND rd.usuario_id = :usuario_id
                    ");
                    
                    foreach ($input['observations'] as $observationData) {
                        if (empty($observationData['id']) || !isset($observationData['ordem_exibicao'])) {
                            $conn->rollBack();
                            sendJsonResponse(false, 'Dados da observação inválidos', null, 400);
                        }
                        
                        $stmt->execute([
                            ':id' => $observationData['id'],
                            ':usuario_id' => $input['usuario_id']
                        ]);
                        
                        if ($stmt->rowCount() === 0) {
                            $conn->rollBack();
                            sendJsonResponse(false, 'Você não tem permissão para reordenar observações deste relatório', null, 403);
                        }
                    }
                    
                    // Atualizar a ordem das observações
                    $updateStmt = $conn->prepare("
                        UPDATE relatorio_observacoes
                        SET ordem_exibicao = :ordem_exibicao,
                            data_atualizacao = NOW()
                        WHERE id = :id
                    ");
                    
                    foreach ($input['observations'] as $observationData) {
                        $updateStmt->execute([
                            ':id' => $observationData['id'],
                            ':ordem_exibicao' => $observationData['ordem_exibicao']
                        ]);
                    }
                    
                    $conn->commit();
                    sendJsonResponse(true, 'Ordem das observações atualizada com sucesso');
                    
                } catch (Exception $e) {
                    $conn->rollBack();
                    error_log('Erro ao reordenar observações: ' . $e->getMessage());
                    sendJsonResponse(false, 'Erro ao reordenar observações', null, 500);
                }
            } else {
                // Atualizar observação (campos diversos)
                if (empty($input['id'])) {
                    sendJsonResponse(false, 'ID da observação é obrigatório', null, 400);
                }
                
                $usuario_id = $input['usuario_id'] ?? null;
                if (!$usuario_id) {
                    sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
                }
                
                // Verificar se o usuário tem permissão para atualizar esta observação
                $stmt = $conn->prepare("
                    SELECT ro.id FROM relatorio_observacoes ro
                    INNER JOIN relatorios_diarios rd ON ro.relatorio_id = rd.id
                    WHERE ro.id = :id AND rd.usuario_id = :usuario_id
                ");
                
                $stmt->execute([
                    ':id' => $input['id'],
                    ':usuario_id' => $usuario_id
                ]);
                
                if ($stmt->rowCount() === 0) {
                    sendJsonResponse(false, 'Você não tem permissão para atualizar esta observação', null, 403);
                }
                
                try {
                    // Preparar campos para atualização
                    $updateFields = [];
                    $params = [':id' => $input['id']];
                    
                    if (isset($input['titulo_observacao'])) {
                        $updateFields[] = 'titulo_observacao = :titulo_observacao';
                        $params[':titulo_observacao'] = $input['titulo_observacao'];
                    }
                    
                    if (isset($input['descricao_observacao'])) {
                        $updateFields[] = 'descricao_observacao = :descricao_observacao';
                        $params[':descricao_observacao'] = $input['descricao_observacao'];
                    }
                    
                    if (empty($updateFields)) {
                        sendJsonResponse(false, 'Nenhum campo para atualizar foi fornecido', null, 400);
                    }
                    
                    $updateFields[] = 'data_atualizacao = NOW()';
                    
                    $sql = "UPDATE relatorio_observacoes SET " . implode(', ', $updateFields) . " WHERE id = :id";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->execute($params);
                    
                    if ($stmt->rowCount() > 0) {
                        sendJsonResponse(true, 'Observação atualizada com sucesso');
                    } else {
                        sendJsonResponse(false, 'Nenhuma alteração foi feita', null, 404);
                    }
                    
                } catch (Exception $e) {
                    error_log('Erro ao atualizar observação: ' . $e->getMessage());
                    sendJsonResponse(false, 'Erro ao atualizar observação', null, 500);
                }
            }
            break;

        case 'DELETE':
            // Deletar observação
            if (empty($input['id'])) {
                sendJsonResponse(false, 'ID da observação é obrigatório', null, 400);
            }
            
            $usuario_id = $input['usuario_id'] ?? null;
            if (!$usuario_id) {
                sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
            }
            
            try {
                // Verificar se o usuário tem permissão para excluir esta observação
                $stmt = $conn->prepare("
                    SELECT ro.id FROM relatorio_observacoes ro
                    INNER JOIN relatorios_diarios rd ON ro.relatorio_id = rd.id
                    WHERE ro.id = :id AND rd.usuario_id = :usuario_id
                ");
                
                $stmt->execute([
                    ':id' => $input['id'],
                    ':usuario_id' => $usuario_id
                ]);
                
                if ($stmt->rowCount() === 0) {
                    sendJsonResponse(false, 'Você não tem permissão para excluir esta observação', null, 403);
                }
                
                // Excluir a observação
                $stmt = $conn->prepare("DELETE FROM relatorio_observacoes WHERE id = :id");
                $stmt->execute([':id' => $input['id']]);
                
                if ($stmt->rowCount() > 0) {
                    sendJsonResponse(true, 'Observação excluída com sucesso');
                } else {
                    sendJsonResponse(false, 'Observação não encontrada', null, 404);
                }
                
            } catch (Exception $e) {
                error_log('Erro ao excluir observação: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao excluir observação', null, 500);
            }
            break;
            
        default:
            sendJsonResponse(false, 'Método não permitido', null, 405);
    }

} catch (Exception $e) {
    error_log('Erro em relatorio_observacao.php: ' . $e->getMessage());
    sendJsonResponse(false, 'Erro interno do servidor: ' . $e->getMessage(), null, 500);
}

// Fechar conexão
$conn = null;