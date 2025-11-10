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

// Função para criar diretório se não existir
function createDirectoryIfNotExists($directory) {
    error_log('Verificando/criando diretório: ' . $directory);
    
    if (!file_exists($directory)) {
        error_log('Diretório não existe, criando...');
        
        if (!mkdir($directory, 0755, true)) {
            error_log('Erro ao criar diretório: ' . $directory);
            throw new Exception('Erro ao criar diretório: ' . $directory);
        }
        
        error_log('Diretório criado com sucesso: ' . $directory);
    } else {
        error_log('Diretório já existe: ' . $directory);
    }
    
    // Verificar se o diretório é gravável
    if (!is_writable($directory)) {
        error_log('Diretório não é gravável: ' . $directory);
        throw new Exception('Diretório não é gravável: ' . $directory);
    }
    
    error_log('Diretório verificado e gravável: ' . $directory);
}

// Função para gerar nome único para arquivo
function generateUniqueFileName($originalName, $directory) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $filename = pathinfo($originalName, PATHINFO_FILENAME);
    $cleanFilename = preg_replace('/[^A-Za-z0-9\-_]/', '_', $filename);
    
    $counter = 1;
    $finalName = $cleanFilename . '.' . $extension;
    
    while (file_exists($directory . '/' . $finalName)) {
        $finalName = $cleanFilename . '_' . $counter . '.' . $extension;
        $counter++;
    }
    
    return $finalName;
}

// Função para deletar arquivos e diretórios do contrato
function deleteContractFiles($contrato_id) {
    error_log('=== Iniciando deleteContractFiles ===');
    error_log('contrato_id: ' . $contrato_id);
    
    // Caminho do diretório do contrato
    $contractDir = __DIR__ . '/../ob/uploads/contratos/' . $contrato_id;
    error_log('Diretório do contrato: ' . $contractDir);
    
    if (!is_dir($contractDir)) {
        error_log('Diretório não existe: ' . $contractDir);
        return true; // Se não existe, não há nada para deletar
    }
    
    try {
        // Função recursiva para deletar diretório e arquivos
        function deleteDirectory($dir) {
            if (!is_dir($dir)) {
                return false;
            }
            
            $files = array_diff(scandir($dir), array('.', '..'));
            
            foreach ($files as $file) {
                $filePath = $dir . DIRECTORY_SEPARATOR . $file;
                
                if (is_dir($filePath)) {
                    deleteDirectory($filePath);
                } else {
                    error_log('Deletando arquivo: ' . $filePath);
                    unlink($filePath);
                }
            }
            
            error_log('Deletando diretório: ' . $dir);
            return rmdir($dir);
        }
        
        $result = deleteDirectory($contractDir);
        error_log('Resultado da exclusão: ' . ($result ? 'sucesso' : 'falha'));
        
        return $result;
        
    } catch (Exception $e) {
        error_log('Erro ao deletar arquivos do contrato: ' . $e->getMessage());
        return false;
    }
}

// Função para processar upload de documento
function processDocumentUpload($contrato_id, $file) {
    error_log('=== Iniciando processDocumentUpload ===');
    error_log('contrato_id: ' . $contrato_id);
    error_log('Dados do arquivo: ' . json_encode($file));
    
    // Validar arquivo
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log('Erro no upload do arquivo. Código: ' . $file['error']);
        throw new Exception('Erro no upload do arquivo');
    }
    
    $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    error_log('Extensão do arquivo: ' . $fileExtension);
    
    if (!in_array($fileExtension, $allowedTypes)) {
        error_log('Tipo de arquivo não permitido: ' . $fileExtension);
        throw new Exception('Tipo de arquivo não permitido. Tipos aceitos: ' . implode(', ', $allowedTypes));
    }
    
    // Limite de tamanho: 5MB
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        error_log('Arquivo muito grande. Tamanho: ' . $file['size'] . ' bytes');
        throw new Exception('Arquivo muito grande. Tamanho máximo: 5MB');
    }
    
    // Criar diretório seguindo padrão: /uploads/contratos/{id_contrato}/documentos/
    $uploadDir = __DIR__ . '/../ob/uploads/contratos/' . $contrato_id . '/documentos/';
    error_log('Diretório de upload: ' . $uploadDir);
    
    createDirectoryIfNotExists($uploadDir);
    
    // Gerar nome único para o arquivo
    $uniqueFileName = generateUniqueFileName($file['name'], $uploadDir);
    $fullPath = $uploadDir . $uniqueFileName;
    
    error_log('Caminho completo do arquivo: ' . $fullPath);
    
    // Mover arquivo para o destino
    if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
        error_log('Erro ao mover arquivo para: ' . $fullPath);
        throw new Exception('Erro ao salvar arquivo');
    }
    
    error_log('Arquivo salvo com sucesso: ' . $fullPath);
    
    // Retornar caminho relativo para salvar no banco
    $relativePath = 'uploads/contratos/' . $contrato_id . '/documentos/' . $uniqueFileName;
    error_log('Caminho relativo: ' . $relativePath);
    
    return [
        'caminho' => $relativePath,
        'nome_original' => $file['name'],
        'nome_arquivo' => $uniqueFileName
    ];
}

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

try {
    // Create database connection
    $conn = getDbConnection();
    
    // Get request method
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Get user ID from URL parameters, request body, or form data
    $user_id = $_GET['usuario_id'] ?? null;
    
    // Se não encontrou no GET, tentar obter de outras fontes
    if (!$user_id && ($method === 'POST' || $method === 'PUT' || $method === 'DELETE')) {
        // Para requisições PUT/POST/DELETE, tentar obter de múltiplas fontes
        // 1. Primeiro tentar do $_POST (FormData)
        if (!empty($_POST['usuario_id'])) {
            $user_id = $_POST['usuario_id'];
        } 
        // 2. Tentar do corpo JSON
        else {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            if (!empty($input['usuario_id'])) {
                $user_id = $input['usuario_id'];
            }
        }
        // 3. Para PUT, também verificar no próprio input decodificado
        if (!$user_id && $method === 'PUT') {
            // Ler o conteúdo raw para debug
            $rawInput = file_get_contents('php://input');
            error_log('Raw input PUT: ' . $rawInput);
            
            // Tentar decodificar novamente
            if (!empty($rawInput)) {
                parse_str($rawInput, $parsedInput);
                if (!empty($parsedInput['usuario_id'])) {
                    $user_id = $parsedInput['usuario_id'];
                }
            }
        }
    }
    
    // Validate user ID
    if (!$user_id) {
        error_log('user_id não encontrado. Método: ' . $method . ', $_GET: ' . print_r($_GET, true) . ', $_POST: ' . print_r($_POST, true) . ', php://input: ' . file_get_contents('php://input'));
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
        $contrato_id = $_GET['id'] ?? null;
        
        if (!$user_id) {
            response(false, 'Usuário não identificado', null, 401);
            return;
        }
        
        if ($contrato_id) {
            // Buscar contrato específico
            $stmt = $conn->prepare('
                SELECT c.*, cl.nome as cliente_nome, o.nome_obra as obra_nome 
                FROM contratos c 
                LEFT JOIN clientes cl ON c.cliente_id = cl.id 
                LEFT JOIN obras o ON c.obra_id = o.id 
                WHERE c.id = ? AND c.usuario_id = ?
            ');
            $stmt->execute([$contrato_id, $user_id]);
            $contrato = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($contrato) {
                response(true, 'Contrato encontrado', $contrato);
            } else {
                response(false, 'Contrato não encontrado');
            }
        } else {
            // Listar todos os contratos do usuário
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $order = $_GET['order'] ?? 'desc';
            
            $sql = '
                SELECT c.*, cl.nome as cliente_nome, o.nome_obra as obra_nome 
                FROM contratos c 
                LEFT JOIN clientes cl ON c.cliente_id = cl.id 
                LEFT JOIN obras o ON c.obra_id = o.id 
                WHERE c.usuario_id = ?
            ';
            $params = [$user_id];
            
            if ($search) {
                $sql .= ' AND (c.titulo LIKE ? OR c.numero_contrato LIKE ? OR cl.nome LIKE ?)';
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            if ($status) {
                $sql .= ' AND c.status = ?';
                $params[] = $status;
            }
            
            $sql .= ' ORDER BY c.data_inicio ' . ($order === 'asc' ? 'ASC' : 'DESC');
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            response(true, '', $contratos);
        }
        
    } catch (Exception $e) {
        throw new Exception('Erro ao carregar contratos: ' . $e->getMessage());
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
        $cliente_id = $input['cliente_id'] ?? $_POST['cliente_id'] ?? null;
        $obra_id = $input['obra_id'] ?? $_POST['obra_id'] ?? null;
        $numero_contrato = $input['numero_contrato'] ?? $_POST['numero_contrato'] ?? null;
        $titulo = $input['titulo'] ?? $_POST['titulo'] ?? null;
        $descricao = $input['descricao'] ?? $_POST['descricao'] ?? '';
        $valor_total = $input['valor_total'] ?? $_POST['valor_total'] ?? 0;
        $data_inicio = $input['data_inicio'] ?? $_POST['data_inicio'] ?? null;
        $data_fim = $input['data_fim'] ?? $_POST['data_fim'] ?? null;
        $status = $input['status'] ?? $_POST['status'] ?? 'ativo';
        $user_id = $input['usuario_id'] ?? $_POST['usuario_id'] ?? $user_id;
        
        // Validate required fields (cliente_id, obra_id, data_fim e valor_total agora são opcionais)
        if (empty($titulo) || empty($data_inicio)) {
            throw new Exception('Título e data de início são obrigatórios');
        }
        
        // Check if contract number already exists for this user
        if ($numero_contrato) {
            $checkQuery = "SELECT id FROM contratos WHERE numero_contrato = ? AND usuario_id = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->execute([$numero_contrato, $user_id]);
            
            if ($checkStmt->rowCount() > 0) {
                throw new Exception('Já existe um contrato com este número');
            }
        }
        
        // Insert the new contract
        $insertQuery = "
            INSERT INTO contratos
            (usuario_id, cliente_id, obra_id, numero_contrato, titulo, descricao, valor_total, data_inicio, data_fim, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        
        $stmt = $conn->prepare($insertQuery);
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $cliente_id, $cliente_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindParam(3, $obra_id, $obra_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindParam(4, $numero_contrato, PDO::PARAM_STR);
        $stmt->bindParam(5, $titulo, PDO::PARAM_STR);
        $stmt->bindParam(6, $descricao, PDO::PARAM_STR);
        $stmt->bindParam(7, $valor_total, PDO::PARAM_STR);
        $stmt->bindParam(8, $data_inicio, PDO::PARAM_STR);
        $stmt->bindParam(9, $data_fim, PDO::PARAM_STR);
        $stmt->bindParam(10, $status, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $contrato_id = $conn->lastInsertId();
            
            // Processar upload de documento se fornecido
            $anexoPath = null;
            $nomeDocumento = null;
            
            if (isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
                error_log('Documento encontrado, processando upload...');
                
                try {
                    $uploadResult = processDocumentUpload($contrato_id, $_FILES['documento']);
                    $anexoPath = $uploadResult['caminho'];
                    $nomeDocumento = $uploadResult['nome_original'];
                    
                    // Atualizar contrato com informações do documento
                    $updateStmt = $conn->prepare("
                        UPDATE contratos 
                        SET anexo = ?, nome_documento = ? 
                        WHERE id = ?
                    ");
                    $updateStmt->execute([$anexoPath, $nomeDocumento, $contrato_id]);
                    
                    error_log('Documento processado e contrato atualizado com sucesso');
                } catch (Exception $uploadError) {
                    error_log('Erro no upload do documento: ' . $uploadError->getMessage());
                    // Não falhar a criação do contrato por causa do documento
                }
            }
            
            response(true, 'Contrato criado com sucesso', [
                'id' => $contrato_id,
                'titulo' => $titulo,
                'numero_contrato' => $numero_contrato,
                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim,
                'anexo' => $anexoPath,
                'nome_documento' => $nomeDocumento
            ]);
        } else {
            throw new Exception('Erro ao salvar o contrato');
        }
        
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

function handlePut($conn, $user_id) {
    try {
        // Para requisições PUT com JSON (como no lembretes.php)
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Verificar se os dados obrigatórios estão presentes
        if (empty($input['id']) || empty($input['usuario_id'])) {
            response(false, 'ID do contrato e usuário são obrigatórios', null, 400);
            return;
        }
        
        // Verificar se o contrato pertence ao usuário
        $contrato_id = $input['id'];
        $user_id = $input['usuario_id']; // Usar o user_id do input para consistência
        
        // Log para debug
        error_log('=== Debug handlePut ===');
        error_log('contrato_id: ' . var_export($contrato_id, true));
        error_log('user_id: ' . var_export($user_id, true));
        error_log('input data: ' . json_encode($input));
        
        // Verificar se o contrato pertence ao usuário
        $verifyQuery = "SELECT id FROM contratos WHERE id = ? AND usuario_id = ?";
        $verifyStmt = $conn->prepare($verifyQuery);
        $verifyStmt->execute([$contrato_id, $user_id]);
        
        if ($verifyStmt->rowCount() === 0) {
            response(false, 'Contrato não encontrado ou acesso não autorizado', null, 403);
            return;
        }
        
        // Preparar campos para atualização
        $fieldsToUpdate = [];
        $params = [];
        
        // Campos que podem ser atualizados
        $updatableFields = [
            'cliente_id' => 'cliente_id',
            'obra_id' => 'obra_id',
            'numero_contrato' => 'numero_contrato',
            'titulo' => 'titulo',
            'descricao' => 'descricao',
            'valor_total' => 'valor_total',
            'data_inicio' => 'data_inicio',
            'data_fim' => 'data_fim',
            'status' => 'status'
        ];
        
        foreach ($updatableFields as $field => $dbField) {
            if (array_key_exists($field, $input)) {
                $fieldsToUpdate[] = "$dbField = ?";
                $params[] = $input[$field];
            }
        }
        
        // Adicionar ID e user_id para a cláusula WHERE
        $params[] = $contrato_id;
        $params[] = $user_id;
        
        if (empty($fieldsToUpdate)) {
            response(false, 'Nenhum campo para atualizar', null, 400);
            return;
        }
        
        $sql = 'UPDATE contratos SET ' . implode(', ', $fieldsToUpdate) . ' WHERE id = ? AND usuario_id = ?';
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute($params);
        
        if ($success) {
            response(true, 'Contrato atualizado com sucesso');
        } else {
            response(false, 'Erro ao atualizar contrato', null, 500);
        }
        
    } catch (Exception $e) {
        error_log('Erro em handlePut: ' . $e->getMessage());
        response(false, 'Erro interno do servidor: ' . $e->getMessage(), null, 500);
    }
}

function handleDelete($conn, $user_id) {
    try {
        // Get ID from URL
        $contrato_id = $_GET['id'] ?? null;
        
        if (!$contrato_id) {
            throw new Exception('ID do contrato é obrigatório');
        }
        
        // Verify that the contract belongs to the user and get contract data
        $verifyQuery = "SELECT id, anexo FROM contratos WHERE id = ? AND usuario_id = ?";
        $verifyStmt = $conn->prepare($verifyQuery);
        $verifyStmt->execute([$contrato_id, $user_id]);
        
        $contrato = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        if (!$contrato) {
            throw new Exception('Contrato não encontrado ou acesso não autorizado');
        }
        
        // Delete the contract from database first
        $stmt = $conn->prepare("DELETE FROM contratos WHERE id = ? AND usuario_id = ?");
        
        if ($stmt->execute([$contrato_id, $user_id])) {
            // After successful database deletion, delete physical files
            if ($contrato['anexo']) {
                error_log('Contrato possui anexo, deletando arquivos físicos...');
                $filesDeleted = deleteContractFiles($contrato_id);
                
                if (!$filesDeleted) {
                    error_log('Aviso: Não foi possível deletar todos os arquivos físicos do contrato ' . $contrato_id);
                    // Não falhar a operação por causa dos arquivos, apenas logar
                }
            }
            
            response(true, 'Contrato excluído com sucesso');
        } else {
            throw new Exception('Erro ao excluir o contrato');
        }
        
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}
?>
