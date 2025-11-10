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
            // Buscar mão de obra de um relatório específico
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
                
                // Buscar mão de obra do relatório
                $stmt = $conn->prepare("
                    SELECT 
                        id, 
                        tipo_mao_obra,
                        quantidade,
                        ordem_exibicao
                    FROM relatorio_mao_obra 
                    WHERE relatorio_id = :relatorio_id
                    ORDER BY ordem_exibicao ASC
                ");
                $stmt->execute([':relatorio_id' => $relatorio_id]);
                
                $maoDeObra = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Mapear os resultados para o formato esperado pelo frontend
                $maoDeObra = array_map(function($item) {
                    return [
                        'id' => (string)$item['id'],
                        'tipo_mao_obra' => $item['tipo_mao_obra'],
                        'quantidade' => (int)$item['quantidade'],
                        'ordem_exibicao' => (int)$item['ordem_exibicao']
                    ];
                }, $maoDeObra);
                
                error_log('Mão de obra encontrada: ' . print_r($maoDeObra, true));
                
                sendJsonResponse(true, 'Mão de obra carregada com sucesso', $maoDeObra);
                
            } catch (PDOException $e) {
                error_log('Erro ao buscar mão de obra: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao carregar mão de obra', null, 500);
            }
            break;
            
        case 'POST':
            // Criar nova mão de obra
            if (empty($input['relatorio_id']) || empty($input['usuario_id']) || empty($input['tipo_mao_obra']) || empty($input['quantidade'])) {
                sendJsonResponse(false, 'Dados incompletos', null, 400);
            }
            
            // Verificar se o usuário tem permissão para adicionar mão de obra neste relatório
            $stmt = $conn->prepare("
                SELECT id FROM relatorios_diarios 
                WHERE id = :relatorio_id AND usuario_id = :usuario_id
            ");
            $stmt->execute([
                ':relatorio_id' => $input['relatorio_id'],
                ':usuario_id' => $input['usuario_id']
            ]);
            
            if ($stmt->rowCount() === 0) {
                sendJsonResponse(false, 'Você não tem permissão para adicionar mão de obra neste relatório', null, 403);
            }
            
            // Primeiro, obtemos a próxima ordem de exibição
            $stmt = $conn->prepare("
                SELECT COALESCE(MAX(ordem_exibicao), 0) + 1 as next_ordem 
                FROM relatorio_mao_obra 
                WHERE relatorio_id = :relatorio_id
            ");
            $stmt->execute([':relatorio_id' => $input['relatorio_id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $next_ordem = $result['next_ordem'];
            
            $stmt = $conn->prepare("
                INSERT INTO relatorio_mao_obra 
                (relatorio_id, usuario_id, tipo_mao_obra, quantidade, ordem_exibicao, data_criacao)
                VALUES (:relatorio_id, :usuario_id, :tipo_mao_obra, :quantidade, :ordem_exibicao, NOW())
            ");
            
            $stmt->execute([
                ':relatorio_id' => $input['relatorio_id'],
                ':usuario_id' => $input['usuario_id'],
                ':tipo_mao_obra' => $input['tipo_mao_obra'],
                ':quantidade' => $input['quantidade'],
                ':ordem_exibicao' => $next_ordem
            ]);
            
            $mao_obra_id = $conn->lastInsertId();
            
            // Buscar a mão de obra recém-criada para retornar
            $stmt = $conn->prepare("SELECT * FROM relatorio_mao_obra WHERE id = :id");
            $stmt->execute([':id' => $mao_obra_id]);
            $maoObra = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendJsonResponse(true, 'Mão de obra criada com sucesso', $maoObra, 201);
            break;
            
        case 'PUT':
            // Verificar se é uma requisição de reordenação
            if (isset($input['reorder']) && $input['reorder'] === true) {
                // Atualizar ordem da mão de obra
                if (empty($input['laborers']) || !is_array($input['laborers'])) {
                    sendJsonResponse(false, 'Lista de mão de obra inválida', null, 400);
                }
                
                if (empty($input['usuario_id'])) {
                    sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
                }
                
                try {
                    $conn->beginTransaction();
                    
                    // Verificar se o usuário tem permissão para reordenar mão de obra
                    $stmt = $conn->prepare("
                        SELECT rmo.id FROM relatorio_mao_obra rmo
                        INNER JOIN relatorios_diarios rd ON rmo.relatorio_id = rd.id
                        WHERE rmo.id = :id AND rd.usuario_id = :usuario_id
                    ");
                    
                    foreach ($input['laborers'] as $laborerData) {
                        if (empty($laborerData['id']) || !isset($laborerData['ordem_exibicao'])) {
                            $conn->rollBack();
                            sendJsonResponse(false, 'Dados da mão de obra inválidos', null, 400);
                        }
                        
                        $stmt->execute([
                            ':id' => $laborerData['id'],
                            ':usuario_id' => $input['usuario_id']
                        ]);
                        
                        if ($stmt->rowCount() === 0) {
                            $conn->rollBack();
                            sendJsonResponse(false, 'Você não tem permissão para reordenar mão de obra deste relatório', null, 403);
                        }
                    }
                    
                    // Atualizar a ordem da mão de obra
                    $updateStmt = $conn->prepare("
                        UPDATE relatorio_mao_obra
                        SET ordem_exibicao = :ordem_exibicao,
                            data_atualizacao = NOW()
                        WHERE id = :id
                    ");
                    
                    foreach ($input['laborers'] as $laborerData) {
                        $updateStmt->execute([
                            ':id' => $laborerData['id'],
                            ':ordem_exibicao' => $laborerData['ordem_exibicao']
                        ]);
                    }
                    
                    $conn->commit();
                    sendJsonResponse(true, 'Ordem da mão de obra atualizada com sucesso');
                    
                } catch (Exception $e) {
                    $conn->rollBack();
                    error_log('Erro ao reordenar mão de obra: ' . $e->getMessage());
                    sendJsonResponse(false, 'Erro ao reordenar mão de obra', null, 500);
                }
            } else {
                // Atualizar mão de obra (campos diversos)
                if (empty($input['id'])) {
                    sendJsonResponse(false, 'ID da mão de obra é obrigatório', null, 400);
                }
                
                $usuario_id = $input['usuario_id'] ?? null;
                if (!$usuario_id) {
                    sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
                }
                
                // Verificar se o usuário tem permissão para atualizar esta mão de obra
                $stmt = $conn->prepare("
                    SELECT rmo.id FROM relatorio_mao_obra rmo
                    INNER JOIN relatorios_diarios rd ON rmo.relatorio_id = rd.id
                    WHERE rmo.id = :id AND rd.usuario_id = :usuario_id
                ");
                
                $stmt->execute([
                    ':id' => $input['id'],
                    ':usuario_id' => $usuario_id
                ]);
                
                if ($stmt->rowCount() === 0) {
                    sendJsonResponse(false, 'Você não tem permissão para atualizar esta mão de obra', null, 403);
                }
                
                try {
                    // Preparar campos para atualização
                    $updateFields = [];
                    $params = [':id' => $input['id']];
                    
                    if (isset($input['tipo_mao_obra'])) {
                        $updateFields[] = 'tipo_mao_obra = :tipo_mao_obra';
                        $params[':tipo_mao_obra'] = $input['tipo_mao_obra'];
                    }
                    
                    if (isset($input['quantidade'])) {
                        $updateFields[] = 'quantidade = :quantidade';
                        $params[':quantidade'] = $input['quantidade'];
                    }
                    
                    if (empty($updateFields)) {
                        sendJsonResponse(false, 'Nenhum campo para atualizar foi fornecido', null, 400);
                    }
                    
                    $updateFields[] = 'data_atualizacao = NOW()';
                    
                    $sql = "UPDATE relatorio_mao_obra SET " . implode(', ', $updateFields) . " WHERE id = :id";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->execute($params);
                    
                    if ($stmt->rowCount() > 0) {
                        sendJsonResponse(true, 'Mão de obra atualizada com sucesso');
                    } else {
                        sendJsonResponse(false, 'Nenhuma alteração foi feita', null, 404);
                    }
                    
                } catch (Exception $e) {
                    error_log('Erro ao atualizar mão de obra: ' . $e->getMessage());
                    sendJsonResponse(false, 'Erro ao atualizar mão de obra', null, 500);
                }
            }
            break;

        case 'DELETE':
            // Deletar mão de obra
            if (empty($input['id'])) {
                sendJsonResponse(false, 'ID da mão de obra é obrigatório', null, 400);
            }
            
            $usuario_id = $input['usuario_id'] ?? null;
            if (!$usuario_id) {
                sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
            }
            
            try {
                // Verificar se o usuário tem permissão para excluir esta mão de obra
                $stmt = $conn->prepare("
                    SELECT rmo.id FROM relatorio_mao_obra rmo
                    INNER JOIN relatorios_diarios rd ON rmo.relatorio_id = rd.id
                    WHERE rmo.id = :id AND rd.usuario_id = :usuario_id
                ");
                
                $stmt->execute([
                    ':id' => $input['id'],
                    ':usuario_id' => $usuario_id
                ]);
                
                if ($stmt->rowCount() === 0) {
                    sendJsonResponse(false, 'Você não tem permissão para excluir esta mão de obra', null, 403);
                }
                
                // Excluir a mão de obra
                $stmt = $conn->prepare("DELETE FROM relatorio_mao_obra WHERE id = :id");
                $stmt->execute([':id' => $input['id']]);
                
                if ($stmt->rowCount() > 0) {
                    sendJsonResponse(true, 'Mão de obra excluída com sucesso');
                } else {
                    sendJsonResponse(false, 'Mão de obra não encontrada', null, 404);
                }
                
            } catch (Exception $e) {
                error_log('Erro ao excluir mão de obra: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao excluir mão de obra', null, 500);
            }
            break;
            
        default:
            sendJsonResponse(false, 'Método não permitido', null, 405);
    }

} catch (Exception $e) {
    error_log('Erro em relatorio_mao_obra.php: ' . $e->getMessage());
    sendJsonResponse(false, 'Erro interno do servidor: ' . $e->getMessage(), null, 500);
}

// Fechar conexão
$conn = null;