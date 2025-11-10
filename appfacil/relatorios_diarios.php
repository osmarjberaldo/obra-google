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

function uploadImage($imageFile, $obraId) {
    if (!$imageFile || $imageFile['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Define o diretório base para uploads
    $baseDir = __DIR__ . '/../ob';
    $uploadDir = $baseDir . '/uploads/relatorios/' . $obraId . '/';
    
    // Cria o diretório se não existir
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Gera um nome único para o arquivo
    $extension = strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));
    $filename = 'relatorio_' . time() . '_' . uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . $filename;
    
    // Move o arquivo para o diretório de uploads
    if (move_uploaded_file($imageFile['tmp_name'], $uploadPath)) {
        return '../ob/uploads/relatorios/' . $obraId . '/' . $filename;
    }
    
    return null;
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
            handleGet($conn);
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

function handleGet($conn) {
    try {
        // Get user_id from query parameters
        $user_id = $_GET['usuario_id'] ?? null;
        $relatorio_id = $_GET['id'] ?? null;
        
        if (!$user_id) {
            response(false, 'Usuário não identificado', null, 401);
            return;
        }
        
        if ($relatorio_id) {
            // Buscar relatório específico
            $stmt = $conn->prepare('SELECT id, obra_id, nome_relatorio, data_relatorio, data_final, status FROM relatorios_diarios WHERE id = ? AND usuario_id = ?');
            $stmt->execute([$relatorio_id, $user_id]);
            $relatorio = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($relatorio) {
                response(true, 'Relatório encontrado', $relatorio);
            } else {
                response(false, 'Relatório não encontrado');
            }
        } else {
            // Listar todos os relatórios do usuário
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $order = $_GET['order'] ?? 'desc';
            
            $sql = 'SELECT id, obra_id, nome_relatorio, data_relatorio, data_final, status FROM relatorios_diarios WHERE usuario_id = ?';
            $params = [$user_id];
            
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
        
        // Validate required fields
        if (empty($obra_id) || empty($data) || empty($nome_relatorio)) {
            throw new Exception('Obra, data e nome do relatório são obrigatórios');
        }
        
        // Removed duplicate report check to allow multiple reports for the same work and date range
        
        // Insert the new report
        $insertQuery = "
            INSERT INTO relatorios_diarios
            (usuario_id, obra_id, nome_relatorio, data_relatorio, data_final, autor, status)
            VALUES (?, ?, ?, ?, ?, ?, 'pendente')
        ";
        
        $status = 'pendente';
        $autor = $user_id;
        
        $stmt = $conn->prepare($insertQuery);
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $obra_id, PDO::PARAM_INT);
        $stmt->bindParam(3, $nome_relatorio, PDO::PARAM_STR);
        $stmt->bindParam(4, $data, PDO::PARAM_STR);
        $stmt->bindParam(5, $data_fim, $data_fim ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(6, $autor, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $relatorio_id = $conn->lastInsertId();
            response(true, 'Relatório criado com sucesso', [
                'id' => $relatorio_id,
                'nome_relatorio' => $nome_relatorio,
                'data_relatorio' => $data,
                'data_final' => $data_fim
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
        
        // Verify that the report belongs to the user
        $verifyQuery = "SELECT id FROM relatorios_diarios WHERE id = ? AND usuario_id = ?";
        $verifyStmt = $conn->prepare($verifyQuery);
        $verifyStmt->execute([$relatorio_id, $user_id]);
        
        if ($verifyStmt->rowCount() === 0) {
            throw new Exception('Relatório não encontrado ou acesso não autorizado');
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
        
        // Verify that the report belongs to the user
        $verifyQuery = "SELECT id, obra_id FROM relatorios_diarios WHERE id = ? AND usuario_id = ?";
        $verifyStmt = $conn->prepare($verifyQuery);
        $verifyStmt->execute([$relatorio_id, $user_id]);
        
        $relatorio = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        if (!$relatorio) {
            throw new Exception('Relatório não encontrado ou acesso não autorizado');
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