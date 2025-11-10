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

function response($success, $message = '', $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

// Função para excluir arquivos físicos
function deletePhysicalFile($filePath) {
    $fullPath = __DIR__ . '/../ob/' . $filePath;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return true; // Se o arquivo não existir, consideramos como sucesso
}

try {
    // Create database connection
    $conn = getDbConnection();
    
    // Get request method
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Get user ID from URL parameters, request body, or form data
    $user_id = $_GET['usuario_id'] ?? null;
    
    if (!$user_id && ($method === 'POST' || $method === 'PUT' || $method === 'DELETE')) {
        // Try to get from form data first
        if (!empty($_POST['usuario_id'])) {
            $user_id = $_POST['usuario_id'];
        } 
        // Then try JSON body
        else {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $user_id = $input['usuario_id'] ?? null;
        }
    }
    
    // Validate user ID
    if (!$user_id) {
        response(false, 'Usuário não identificado. Por favor, faça login novamente.', null, 401);
        exit();
    }
    
    // Route the request to the appropriate handler
    switch ($method) {
        case 'GET':
            handleGet($conn, $user_id);
            break;
        case 'POST':
            handlePost($conn, $user_id);
            break;
        case 'PUT':
            handlePut($conn, $user_id);
            break;
        case 'DELETE':
            handleDelete($conn, $user_id);
            break;
        default:
            response(false, 'Método não permitido', null, 405);
    }
    
    // Close connection
    $conn = null;
    
} catch (Exception $e) {
    http_response_code(500);
    response(false, 'Erro interno do servidor: ' . $e->getMessage());
}

function handleGet($conn, $user_id) {
    try {
        // Get user_id from query parameters
        $user_id = $_GET['usuario_id'] ?? null;
        $relatorio_id = $_GET['id'] ?? null;
        
        if (!$user_id) {
            response(false, 'Usuário não identificado', null, 401);
            return;
        }
        
        if ($relatorio_id) {
            // Buscar relatório específico do cliente
            // Verificar se o relatório pertence ao usuário e se é de um cliente
            $stmt = $conn->prepare('SELECT id, obra_id, nome_relatorio, data_relatorio, data_final, status, id_cliente FROM relatorios_diarios WHERE id = ? AND usuario_id = ? AND id_cliente IS NOT NULL');
            $stmt->execute([$relatorio_id, $user_id]);
            $relatorio = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($relatorio) {
                // Verificar se o cliente do relatório pertence ao usuário
                $clienteStmt = $conn->prepare('SELECT id FROM clientes WHERE id = ? AND usuario_id = ?');
                $clienteStmt->execute([$relatorio['id_cliente'], $user_id]);
                $cliente = $clienteStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$cliente) {
                    response(false, 'Acesso não autorizado ao relatório', null, 403);
                    return;
                }
                
                // Buscar informações da obra
                $obraStmt = $conn->prepare('SELECT nome_obra FROM obras WHERE id = ?');
                $obraStmt->execute([$relatorio['obra_id']]);
                $obra = $obraStmt->fetch(PDO::FETCH_ASSOC);
                
                $relatorio['obra_nome'] = $obra ? $obra['nome_obra'] : 'Obra não encontrada';
                
                response(true, 'Relatório encontrado', $relatorio);
            } else {
                response(false, 'Relatório não encontrado');
            }
        } else {
            // Listar todos os relatórios do cliente
            // Primeiro, buscar todos os clientes do usuário
            $clientesStmt = $conn->prepare('SELECT id FROM clientes WHERE usuario_id = ?');
            $clientesStmt->execute([$user_id]);
            $clientes = $clientesStmt->fetchAll(PDO::FETCH_COLUMN, 0);
            
            if (empty($clientes)) {
                response(true, '', []);
                return;
            }
            
            // Converter array de clientes para string para usar na query
            $placeholders = str_repeat('?,', count($clientes) - 1) . '?';
            
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $order = $_GET['order'] ?? 'desc';
            
            $sql = "SELECT id, obra_id, nome_relatorio, data_relatorio, data_final, status, id_cliente FROM relatorios_diarios WHERE usuario_id = ? AND id_cliente IN ($placeholders)";
            $params = array_merge([$user_id], $clientes);
            
            if ($search) {
                $sql .= ' AND nome_relatorio LIKE ?';
                $params[] = '%' . $search . '%';
            }
            
            if ($status) {
                $sql .= ' AND status = ?';
                $params[] = $status;
            }
            
            $sql .= ' ORDER BY data_relatorio ' . ($order === 'asc' ? 'ASC' : 'DESC');
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Adicionar nome da obra para cada relatório
            foreach ($relatorios as &$relatorio) {
                $obraStmt = $conn->prepare('SELECT nome_obra FROM obras WHERE id = ?');
                $obraStmt->execute([$relatorio['obra_id']]);
                $obra = $obraStmt->fetch(PDO::FETCH_ASSOC);
                $relatorio['obra_nome'] = $obra ? $obra['nome_obra'] : 'Obra não encontrada';
            }
            
            response(true, '', $relatorios);
        }
        
    } catch (Exception $e) {
        throw new Exception('Erro ao carregar relatórios: ' . $e->getMessage());
    }
}

function handlePost($conn, $user_id) {
    try {
        // Get input data
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // If not JSON, use form data
            $input = $_POST;
        }
        
        // Get form data
        $obra_id = $input['obra_id'] ?? null;
        $data = $input['data'] ?? null;
        $data_fim = $input['data_fim'] ?? null;
        $nome_relatorio = $input['nome_relatorio'] ?? null;
        $id_cliente = $input['id_cliente'] ?? null;
        
        // Validate required fields
        if (empty($obra_id) || empty($data) || empty($nome_relatorio) || empty($id_cliente)) {
            throw new Exception('Obra, data, nome do relatório e cliente são obrigatórios');
        }
        
        // Verificar se o cliente pertence ao usuário
        $clienteStmt = $conn->prepare('SELECT id FROM clientes WHERE id = ? AND usuario_id = ?');
        $clienteStmt->execute([$id_cliente, $user_id]);
        if ($clienteStmt->rowCount() === 0) {
            throw new Exception('Cliente não encontrado ou não pertence ao usuário');
        }
        
        // Insert the new report
        $insertQuery = "
            INSERT INTO relatorios_diarios
            (usuario_id, obra_id, nome_relatorio, data_relatorio, data_final, autor, status, id_cliente)
            VALUES (?, ?, ?, ?, ?, ?, 'pendente', ?)
        ";
        
        $autor = $user_id;
        
        $stmt = $conn->prepare($insertQuery);
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $obra_id, PDO::PARAM_INT);
        $stmt->bindParam(3, $nome_relatorio, PDO::PARAM_STR);
        $stmt->bindParam(4, $data, PDO::PARAM_STR);
        $stmt->bindParam(5, $data_fim, $data_fim ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(6, $autor, PDO::PARAM_INT);
        $stmt->bindParam(7, $id_cliente, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $relatorio_id = $conn->lastInsertId();
            response(true, 'Relatório criado com sucesso', [
                'id' => $relatorio_id,
                'nome_relatorio' => $nome_relatorio,
                'data_relatorio' => $data,
                'data_final' => $data_fim,
                'id_cliente' => $id_cliente
            ]);
        } else {
            throw new Exception('Erro ao salvar o relatório: ' . $conn->error);
        }
        
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

function handlePut($conn, $user_id) {
    try {
        // Get ID from URL
        $relatorio_id = $_GET['id'] ?? null;
        
        if (!$relatorio_id) {
            throw new Exception('ID do relatório é obrigatório');
        }
        
        // Get input data from form data (multipart/form-data)
        $input = $_POST;
        
        // Get form data
        $obra_id = $input['obra_id'] ?? null;
        $data = $input['data'] ?? null;
        $data_fim = $input['data_fim'] ?? null;
        $nome_relatorio = $input['nome_relatorio'] ?? null;
        $status = $input['status'] ?? 'pendente';
        $id_cliente = $input['id_cliente'] ?? null;
        
        // Validate required fields
        if (empty($obra_id) || empty($data) || empty($nome_relatorio)) {
            throw new Exception('Obra, data e nome do relatório são obrigatórios');
        }
        
        // Verify that the report belongs to the user and is a client report
        $verifyQuery = "SELECT id, id_cliente FROM relatorios_diarios WHERE id = ? AND usuario_id = ? AND id_cliente IS NOT NULL";
        $verifyStmt = $conn->prepare($verifyQuery);
        $verifyStmt->execute([$relatorio_id, $user_id]);
        
        $relatorio = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        if (!$relatorio) {
            throw new Exception('Relatório não encontrado ou acesso não autorizado');
        }
        
        // Verificar se o cliente do relatório pertence ao usuário
        $clienteStmt = $conn->prepare('SELECT id FROM clientes WHERE id = ? AND usuario_id = ?');
        $clienteStmt->execute([$relatorio['id_cliente'], $user_id]);
        if ($clienteStmt->rowCount() === 0) {
            throw new Exception('Acesso não autorizado ao relatório');
        }
        
        // If id_cliente is provided, verify it belongs to the user
        if ($id_cliente) {
            $clienteStmt = $conn->prepare('SELECT id FROM clientes WHERE id = ? AND usuario_id = ?');
            $clienteStmt->execute([$id_cliente, $user_id]);
            if ($clienteStmt->rowCount() === 0) {
                throw new Exception('Cliente não encontrado ou não pertence ao usuário');
            }
        }
        
        // Update the report
        $updateQuery = "
            UPDATE relatorios_diarios SET 
                obra_id = ?,
                nome_relatorio = ?,
                data_relatorio = ?,
                data_final = ?,
                status = ?,
                id_cliente = ?
            WHERE id = ? AND usuario_id = ?
        ";
        
        $stmt = $conn->prepare($updateQuery);
        $stmt->bindParam(1, $obra_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $nome_relatorio, PDO::PARAM_STR);
        $stmt->bindParam(3, $data, PDO::PARAM_STR);
        $stmt->bindParam(4, $data_fim, $data_fim ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(5, $status, PDO::PARAM_STR);
        $stmt->bindParam(6, $id_cliente, $id_cliente ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindParam(7, $relatorio_id, PDO::PARAM_INT);
        $stmt->bindParam(8, $user_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            response(true, 'Relatório atualizado com sucesso', [
                'id' => $relatorio_id,
                'nome_relatorio' => $nome_relatorio,
                'data_relatorio' => $data,
                'data_final' => $data_fim,
                'status' => $status,
                'id_cliente' => $id_cliente
            ]);
        } else {
            throw new Exception('Erro ao atualizar o relatório');
        }
        
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

function handleDelete($conn, $user_id) {
    try {
        // Get ID from URL
        $relatorio_id = $_GET['id'] ?? null;
        
        if (!$relatorio_id) {
            throw new Exception('ID do relatório é obrigatório');
        }
        
        // Verify that the report belongs to the user and is a client report
        $verifyQuery = "SELECT id, obra_id, id_cliente FROM relatorios_diarios WHERE id = ? AND usuario_id = ? AND id_cliente IS NOT NULL";
        $verifyStmt = $conn->prepare($verifyQuery);
        $verifyStmt->execute([$relatorio_id, $user_id]);
        
        $relatorio = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        if (!$relatorio) {
            throw new Exception('Relatório não encontrado ou acesso não autorizado');
        }
        
        // Verificar se o cliente do relatório pertence ao usuário
        $clienteStmt = $conn->prepare('SELECT id FROM clientes WHERE id = ? AND usuario_id = ?');
        $clienteStmt->execute([$relatorio['id_cliente'], $user_id]);
        if ($clienteStmt->rowCount() === 0) {
            throw new Exception('Acesso não autorizado ao relatório');
        }
        
        // Iniciar transação para garantir consistência
        $conn->beginTransaction();
        
        try {
            // 1. Excluir tarefas do relatório
            $stmt = $conn->prepare("DELETE FROM relatorio_tarefas WHERE relatorio_id = ?");
            $stmt->execute([$relatorio_id]);
            
            // 2. Excluir ocorrências do relatório
            $stmt = $conn->prepare("DELETE FROM relatorio_ocorrencias WHERE relatorio_id = ?");
            $stmt->execute([$relatorio_id]);
            
            // 3. Excluir observações do relatório
            $stmt = $conn->prepare("DELETE FROM relatorio_observacoes WHERE relatorio_id = ?");
            $stmt->execute([$relatorio_id]);
            
            // 4. Excluir mão de obra do relatório
            $stmt = $conn->prepare("DELETE FROM relatorio_mao_obra WHERE relatorio_id = ?");
            $stmt->execute([$relatorio_id]);
            
            // 5. Excluir equipamentos do relatório
            $stmt = $conn->prepare("DELETE FROM relatorio_equipamentos WHERE relatorio_id = ?");
            $stmt->execute([$relatorio_id]);
            
            // 6. Excluir condição climática do relatório
            $stmt = $conn->prepare("DELETE FROM relatorio_clima WHERE relatorio_id = ?");
            $stmt->execute([$relatorio_id]);
            
            // 7. Excluir documentos do relatório e arquivos físicos
            $stmt = $conn->prepare("SELECT caminho_arquivo FROM relatorio_documentos WHERE relatorio_id = ?");
            $stmt->execute([$relatorio_id]);
            $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($documentos as $documento) {
                deletePhysicalFile($documento['caminho_arquivo']);
            }
            
            $stmt = $conn->prepare("DELETE FROM relatorio_documentos WHERE relatorio_id = ?");
            $stmt->execute([$relatorio_id]);
            
            // 8. Excluir arquivos de mídia do relatório e arquivos físicos
            $stmt = $conn->prepare("SELECT caminho_arquivo FROM relatorio_arquivos WHERE relatorio_id = ?");
            $stmt->execute([$relatorio_id]);
            $arquivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($arquivos as $arquivo) {
                deletePhysicalFile($arquivo['caminho_arquivo']);
            }
            
            $stmt = $conn->prepare("DELETE FROM relatorio_arquivos WHERE relatorio_id = ?");
            $stmt->execute([$relatorio_id]);
            
            // 9. Excluir o relatório principal
            $stmt = $conn->prepare("DELETE FROM relatorios_diarios WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$relatorio_id, $user_id]);
            
            // Commit da transação
            $conn->commit();
            
            response(true, 'Relatório excluído com sucesso, incluindo todos os dados e arquivos associados');
            
        } catch (Exception $e) {
            // Rollback em caso de erro
            $conn->rollBack();
            throw new Exception('Erro ao excluir o relatório: ' . $e->getMessage());
        }
        
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}
?>