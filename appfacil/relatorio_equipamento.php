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
            // Buscar equipamentos de um relatório específico
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
                
                // Buscar equipamentos do relatório
                $stmt = $conn->prepare("
                    SELECT 
                        id, 
                        nome_equipamento, 
                        tipo_equipamento,
                        quantidade,
                        ordem_exibicao
                    FROM relatorio_equipamentos 
                    WHERE relatorio_id = :relatorio_id
                    ORDER BY ordem_exibicao ASC
                ");
                $stmt->execute([':relatorio_id' => $relatorio_id]);
                
                $equipamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Mapear os resultados para o formato esperado pelo frontend
                $equipamentos = array_map(function($equipamento) {
                    return [
                        'id' => (string)$equipamento['id'],
                        'nome_equipamento' => $equipamento['nome_equipamento'],
                        'tipo_equipamento' => $equipamento['tipo_equipamento'],
                        'quantidade' => (int)$equipamento['quantidade'],
                        'ordem_exibicao' => (int)$equipamento['ordem_exibicao']
                    ];
                }, $equipamentos);
                
                error_log('Equipamentos encontrados: ' . print_r($equipamentos, true));
                
                sendJsonResponse(true, 'Equipamentos carregados com sucesso', $equipamentos);
                
            } catch (PDOException $e) {
                error_log('Erro ao buscar equipamentos: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao carregar equipamentos', null, 500);
            }
            break;
            
        case 'POST':
            // Criar novo equipamento
            if (empty($input['relatorio_id']) || empty($input['usuario_id']) || empty($input['nome_equipamento']) || empty($input['quantidade'])) {
                sendJsonResponse(false, 'Dados incompletos', null, 400);
            }
            
            // Verificar se o usuário tem permissão para adicionar equipamentos neste relatório
            $stmt = $conn->prepare("
                SELECT id FROM relatorios_diarios 
                WHERE id = :relatorio_id AND usuario_id = :usuario_id
            ");
            $stmt->execute([
                ':relatorio_id' => $input['relatorio_id'],
                ':usuario_id' => $input['usuario_id']
            ]);
            
            if ($stmt->rowCount() === 0) {
                sendJsonResponse(false, 'Você não tem permissão para adicionar equipamentos neste relatório', null, 403);
            }
            
            // Primeiro, obtemos a próxima ordem de exibição
            $stmt = $conn->prepare("
                SELECT COALESCE(MAX(ordem_exibicao), 0) + 1 as next_ordem 
                FROM relatorio_equipamentos 
                WHERE relatorio_id = :relatorio_id
            ");
            $stmt->execute([':relatorio_id' => $input['relatorio_id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $next_ordem = $result['next_ordem'];
            
            $stmt = $conn->prepare("
                INSERT INTO relatorio_equipamentos 
                (relatorio_id, usuario_id, nome_equipamento, tipo_equipamento, quantidade, ordem_exibicao, data_criacao)
                VALUES (:relatorio_id, :usuario_id, :nome_equipamento, :tipo_equipamento, :quantidade, :ordem_exibicao, NOW())
            ");
            
            $stmt->execute([
                ':relatorio_id' => $input['relatorio_id'],
                ':usuario_id' => $input['usuario_id'],
                ':nome_equipamento' => $input['nome_equipamento'],
                ':tipo_equipamento' => $input['tipo_equipamento'] ?? 'Ferramenta',
                ':quantidade' => $input['quantidade'],
                ':ordem_exibicao' => $next_ordem
            ]);
            
            $equipamento_id = $conn->lastInsertId();
            
            // Buscar o equipamento recém-criado para retornar
            $stmt = $conn->prepare("SELECT * FROM relatorio_equipamentos WHERE id = :id");
            $stmt->execute([':id' => $equipamento_id]);
            $equipamento = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendJsonResponse(true, 'Equipamento criado com sucesso', $equipamento, 201);
            break;
            
        case 'PUT':
            // Verificar se é uma requisição de reordenação
            if (isset($input['reorder']) && $input['reorder'] === true) {
                // Atualizar ordem dos equipamentos
                if (empty($input['equipments']) || !is_array($input['equipments'])) {
                    sendJsonResponse(false, 'Lista de equipamentos inválida', null, 400);
                }
                
                if (empty($input['usuario_id'])) {
                    sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
                }
                
                try {
                    $conn->beginTransaction();
                    
                    // Verificar se o usuário tem permissão para reordenar equipamentos
                    $stmt = $conn->prepare("
                        SELECT re.id FROM relatorio_equipamentos re
                        INNER JOIN relatorios_diarios rd ON re.relatorio_id = rd.id
                        WHERE re.id = :id AND rd.usuario_id = :usuario_id
                    ");
                    
                    foreach ($input['equipments'] as $equipmentData) {
                        if (empty($equipmentData['id']) || !isset($equipmentData['ordem_exibicao'])) {
                            $conn->rollBack();
                            sendJsonResponse(false, 'Dados do equipamento inválidos', null, 400);
                        }
                        
                        $stmt->execute([
                            ':id' => $equipmentData['id'],
                            ':usuario_id' => $input['usuario_id']
                        ]);
                        
                        if ($stmt->rowCount() === 0) {
                            $conn->rollBack();
                            sendJsonResponse(false, 'Você não tem permissão para reordenar equipamentos deste relatório', null, 403);
                        }
                    }
                    
                    // Atualizar a ordem dos equipamentos
                    $updateStmt = $conn->prepare("
                        UPDATE relatorio_equipamentos
                        SET ordem_exibicao = :ordem_exibicao,
                            data_atualizacao = NOW()
                        WHERE id = :id
                    ");
                    
                    foreach ($input['equipments'] as $equipmentData) {
                        $updateStmt->execute([
                            ':id' => $equipmentData['id'],
                            ':ordem_exibicao' => $equipmentData['ordem_exibicao']
                        ]);
                    }
                    
                    $conn->commit();
                    sendJsonResponse(true, 'Ordem dos equipamentos atualizada com sucesso');
                    
                } catch (Exception $e) {
                    $conn->rollBack();
                    error_log('Erro ao reordenar equipamentos: ' . $e->getMessage());
                    sendJsonResponse(false, 'Erro ao reordenar equipamentos', null, 500);
                }
            } else {
                // Atualizar equipamento (campos diversos)
                if (empty($input['id'])) {
                    sendJsonResponse(false, 'ID do equipamento é obrigatório', null, 400);
                }
                
                $usuario_id = $input['usuario_id'] ?? null;
                if (!$usuario_id) {
                    sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
                }
                
                // Verificar se o usuário tem permissão para atualizar este equipamento
                $stmt = $conn->prepare("
                    SELECT re.id FROM relatorio_equipamentos re
                    INNER JOIN relatorios_diarios rd ON re.relatorio_id = rd.id
                    WHERE re.id = :id AND rd.usuario_id = :usuario_id
                ");
                
                $stmt->execute([
                    ':id' => $input['id'],
                    ':usuario_id' => $usuario_id
                ]);
                
                if ($stmt->rowCount() === 0) {
                    sendJsonResponse(false, 'Você não tem permissão para atualizar este equipamento', null, 403);
                }
                
                try {
                    // Preparar campos para atualização
                    $updateFields = [];
                    $params = [':id' => $input['id']];
                    
                    if (isset($input['nome_equipamento'])) {
                        $updateFields[] = 'nome_equipamento = :nome_equipamento';
                        $params[':nome_equipamento'] = $input['nome_equipamento'];
                    }
                    
                    if (isset($input['tipo_equipamento'])) {
                        $updateFields[] = 'tipo_equipamento = :tipo_equipamento';
                        $params[':tipo_equipamento'] = $input['tipo_equipamento'];
                    }
                    
                    if (isset($input['quantidade'])) {
                        $updateFields[] = 'quantidade = :quantidade';
                        $params[':quantidade'] = $input['quantidade'];
                    }
                    
                    if (empty($updateFields)) {
                        sendJsonResponse(false, 'Nenhum campo para atualizar foi fornecido', null, 400);
                    }
                    
                    $updateFields[] = 'data_atualizacao = NOW()';
                    
                    $sql = "UPDATE relatorio_equipamentos SET " . implode(', ', $updateFields) . " WHERE id = :id";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->execute($params);
                    
                    if ($stmt->rowCount() > 0) {
                        sendJsonResponse(true, 'Equipamento atualizado com sucesso');
                    } else {
                        sendJsonResponse(false, 'Nenhuma alteração foi feita', null, 404);
                    }
                    
                } catch (Exception $e) {
                    error_log('Erro ao atualizar equipamento: ' . $e->getMessage());
                    sendJsonResponse(false, 'Erro ao atualizar equipamento', null, 500);
                }
            }
            break;

        case 'DELETE':
            // Deletar equipamento
            if (empty($input['id'])) {
                sendJsonResponse(false, 'ID do equipamento é obrigatório', null, 400);
            }
            
            $usuario_id = $input['usuario_id'] ?? null;
            if (!$usuario_id) {
                sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
            }
            
            try {
                // Verificar se o usuário tem permissão para excluir este equipamento
                $stmt = $conn->prepare("
                    SELECT re.id FROM relatorio_equipamentos re
                    INNER JOIN relatorios_diarios rd ON re.relatorio_id = rd.id
                    WHERE re.id = :id AND rd.usuario_id = :usuario_id
                ");
                
                $stmt->execute([
                    ':id' => $input['id'],
                    ':usuario_id' => $usuario_id
                ]);
                
                if ($stmt->rowCount() === 0) {
                    sendJsonResponse(false, 'Você não tem permissão para excluir este equipamento', null, 403);
                }
                
                // Excluir o equipamento
                $stmt = $conn->prepare("DELETE FROM relatorio_equipamentos WHERE id = :id");
                $stmt->execute([':id' => $input['id']]);
                
                if ($stmt->rowCount() > 0) {
                    sendJsonResponse(true, 'Equipamento excluído com sucesso');
                } else {
                    sendJsonResponse(false, 'Equipamento não encontrado', null, 404);
                }
                
            } catch (Exception $e) {
                error_log('Erro ao excluir equipamento: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao excluir equipamento', null, 500);
            }
            break;
            
        default:
            sendJsonResponse(false, 'Método não permitido', null, 405);
    }

} catch (Exception $e) {
    error_log('Erro em relatorio_equipamento.php: ' . $e->getMessage());
    sendJsonResponse(false, 'Erro interno do servidor: ' . $e->getMessage(), null, 500);
}

// Fechar conexão
$conn = null;