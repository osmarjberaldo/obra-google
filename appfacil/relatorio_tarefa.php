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
            // Buscar tarefas de um relatório específico
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
                
                // Buscar tarefas do relatório
                $stmt = $conn->prepare("
                    SELECT 
                        id, 
                        descricao, 
                        status,
                        ordem_exibicao
                    FROM relatorio_tarefas 
                    WHERE relatorio_id = :relatorio_id
                    ORDER BY ordem_exibicao ASC
                ");
                $stmt->execute([':relatorio_id' => $relatorio_id]);
                
                $tarefas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Mapear os resultados para o formato esperado pelo frontend
                $tarefas = array_map(function($tarefa) {
                    return [
                        'id' => (string)$tarefa['id'],
                        'descricao' => $tarefa['descricao'],
                        'status' => $tarefa['status'],
                        'ordem_exibicao' => (int)$tarefa['ordem_exibicao']
                    ];
                }, $tarefas);
                
                error_log('Tarefas encontradas: ' . print_r($tarefas, true));
                
                sendJsonResponse(true, 'Tarefas carregadas com sucesso', $tarefas);
                
            } catch (PDOException $e) {
                error_log('Erro ao buscar tarefas: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao carregar tarefas', null, 500);
            }
            break;
            
        case 'POST':
        // Criar nova tarefa
        if (empty($input['relatorio_id']) || empty($input['usuario_id']) || empty($input['descricao'])) {
            sendJsonResponse(false, 'Dados incompletos', null, 400);
        }
        
        // Verificar se o usuário tem permissão para adicionar tarefas neste relatório
        $stmt = $conn->prepare("
            SELECT id FROM relatorios_diarios 
            WHERE id = :relatorio_id AND usuario_id = :usuario_id
        ");
        $stmt->execute([
            ':relatorio_id' => $input['relatorio_id'],
            ':usuario_id' => $input['usuario_id']
        ]);
        
        if ($stmt->rowCount() === 0) {
            sendJsonResponse(false, 'Você não tem permissão para adicionar tarefas neste relatório', null, 403);
        }
        
        // Primeiro, obtemos a próxima ordem de exibição
        $stmt = $conn->prepare("
            SELECT COALESCE(MAX(ordem_exibicao), 0) + 1 as next_ordem 
            FROM relatorio_tarefas 
            WHERE relatorio_id = :relatorio_id
        ");
        $stmt->execute([':relatorio_id' => $input['relatorio_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $next_ordem = $result['next_ordem'];
        
        $stmt = $conn->prepare("
            INSERT INTO relatorio_tarefas 
            (relatorio_id, usuario_id, descricao, status, ordem_exibicao, data_criacao)
            VALUES (:relatorio_id, :usuario_id, :descricao, 'pendente', :ordem_exibicao, NOW())
        ");
        
        $stmt->execute([
            ':relatorio_id' => $input['relatorio_id'],
            ':usuario_id' => $input['usuario_id'],
            ':descricao' => $input['descricao'],
            ':ordem_exibicao' => $next_ordem
        ]);
        
        $tarefa_id = $conn->lastInsertId();
        
        // Buscar a tarefa recém-criada para retornar
        $stmt = $conn->prepare("SELECT * FROM relatorio_tarefas WHERE id = :id");
        $stmt->execute([':id' => $tarefa_id]);
        $tarefa = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendJsonResponse(true, 'Tarefa criada com sucesso', $tarefa, 201);
        break;
            
        case 'PUT':
            // Verificar se é uma requisição de reordenação
            if (isset($input['reorder']) && $input['reorder'] === true) {
                // Atualizar ordem das tarefas
                if (empty($input['tasks']) || !is_array($input['tasks'])) {
                    sendJsonResponse(false, 'Lista de tarefas inválida', null, 400);
                }
                
                if (empty($input['usuario_id'])) {
                    sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
                }
                
                try {
                    $conn->beginTransaction();
                    
                    // Verificar se o usuário tem permissão para reordenar tarefas
                    $stmt = $conn->prepare("
                        SELECT rt.id FROM relatorio_tarefas rt
                        INNER JOIN relatorios_diarios rd ON rt.relatorio_id = rd.id
                        WHERE rt.id = :id AND rd.usuario_id = :usuario_id
                    ");
                    
                    foreach ($input['tasks'] as $taskData) {
                        if (empty($taskData['id']) || !isset($taskData['ordem_exibicao'])) {
                            $conn->rollBack();
                            sendJsonResponse(false, 'Dados da tarefa inválidos', null, 400);
                        }
                        
                        $stmt->execute([
                            ':id' => $taskData['id'],
                            ':usuario_id' => $input['usuario_id']
                        ]);
                        
                        if ($stmt->rowCount() === 0) {
                            $conn->rollBack();
                            sendJsonResponse(false, 'Você não tem permissão para reordenar tarefas deste relatório', null, 403);
                        }
                    }
                    
                    // Atualizar a ordem das tarefas
                    $updateStmt = $conn->prepare("
                        UPDATE relatorio_tarefas
                        SET ordem_exibicao = :ordem_exibicao,
                            data_atualizacao = NOW()
                        WHERE id = :id
                    ");
                    
                    foreach ($input['tasks'] as $taskData) {
                        $updateStmt->execute([
                            ':id' => $taskData['id'],
                            ':ordem_exibicao' => $taskData['ordem_exibicao']
                        ]);
                    }
                    
                    $conn->commit();
                    sendJsonResponse(true, 'Ordem das tarefas atualizada com sucesso');
                    
                } catch (Exception $e) {
                    $conn->rollBack();
                    error_log('Erro ao reordenar tarefas: ' . $e->getMessage());
                    sendJsonResponse(false, 'Erro ao reordenar tarefas', null, 500);
                }
            } else {
                // Atualizar tarefa (status ou descrição)
                if (empty($input['id'])) {
                    sendJsonResponse(false, 'ID da tarefa é obrigatório', null, 400);
                }
                
                $usuario_id = $input['usuario_id'] ?? null;
                if (!$usuario_id) {
                    sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
                }
                
                // Verificar se o usuário tem permissão para atualizar esta tarefa
                $stmt = $conn->prepare("
                    SELECT rt.id FROM relatorio_tarefas rt
                    INNER JOIN relatorios_diarios rd ON rt.relatorio_id = rd.id
                    WHERE rt.id = :id AND rd.usuario_id = :usuario_id
                ");
                
                $stmt->execute([
                    ':id' => $input['id'],
                    ':usuario_id' => $usuario_id
                ]);
                
                if ($stmt->rowCount() === 0) {
                    sendJsonResponse(false, 'Você não tem permissão para atualizar esta tarefa', null, 403);
                }
                
                try {
                    // Preparar campos para atualização
                    $updateFields = [];
                    $params = [':id' => $input['id']];
                    
                    if (isset($input['status'])) {
                        $updateFields[] = 'status = :status';
                        $params[':status'] = $input['status'];
                    }
                    
                    if (isset($input['descricao'])) {
                        $updateFields[] = 'descricao = :descricao';
                        $params[':descricao'] = $input['descricao'];
                    }
                    
                    if (empty($updateFields)) {
                        sendJsonResponse(false, 'Nenhum campo para atualizar foi fornecido', null, 400);
                    }
                    
                    $updateFields[] = 'data_atualizacao = NOW()';
                    
                    $sql = "UPDATE relatorio_tarefas SET " . implode(', ', $updateFields) . " WHERE id = :id";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->execute($params);
                    
                    if ($stmt->rowCount() > 0) {
                        sendJsonResponse(true, 'Tarefa atualizada com sucesso');
                    } else {
                        sendJsonResponse(false, 'Nenhuma alteração foi feita', null, 404);
                    }
                    
                } catch (Exception $e) {
                    error_log('Erro ao atualizar tarefa: ' . $e->getMessage());
                    sendJsonResponse(false, 'Erro ao atualizar tarefa', null, 500);
                }
            }
            break;

        case 'DELETE':
            // Deletar tarefa
            if (empty($input['id'])) {
                sendJsonResponse(false, 'ID da tarefa é obrigatório', null, 400);
            }
            
            $usuario_id = $input['usuario_id'] ?? null;
            if (!$usuario_id) {
                sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
            }
            
            try {
                // Verificar se o usuário tem permissão para excluir esta tarefa
                $stmt = $conn->prepare("
                    SELECT rt.id FROM relatorio_tarefas rt
                    INNER JOIN relatorios_diarios rd ON rt.relatorio_id = rd.id
                    WHERE rt.id = :id AND rd.usuario_id = :usuario_id
                ");
                
                $stmt->execute([
                    ':id' => $input['id'],
                    ':usuario_id' => $usuario_id
                ]);
                
                if ($stmt->rowCount() === 0) {
                    sendJsonResponse(false, 'Você não tem permissão para excluir esta tarefa', null, 403);
                }
                
                // Excluir a tarefa
                $stmt = $conn->prepare("DELETE FROM relatorio_tarefas WHERE id = :id");
                $stmt->execute([':id' => $input['id']]);
                
                if ($stmt->rowCount() > 0) {
                    sendJsonResponse(true, 'Tarefa excluída com sucesso');
                } else {
                    sendJsonResponse(false, 'Tarefa não encontrada', null, 404);
                }
                
            } catch (Exception $e) {
                error_log('Erro ao excluir tarefa: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao excluir tarefa', null, 500);
            }
            break;
    default:
        sendJsonResponse(false, 'Método não permitido', null, 405);
}

} catch (Exception $e) {
    error_log('Erro em relatorio_tarefa.php: ' . $e->getMessage());
    sendJsonResponse(false, 'Erro interno do servidor: ' . $e->getMessage(), null, 500);
}

// Fechar conexão
$conn = null;
