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

// Função para criar diretório se não existir
function createDirectoryIfNotExists($directory) {
    if (!file_exists($directory)) {
        if (!mkdir($directory, 0755, true)) {
            throw new Exception('Erro ao criar diretório: ' . $directory);
        }
    }
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
        // Para uploads de arquivo, usar $_POST e $_FILES
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES)) {
            $input = $_POST;
        } else {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            if (json_last_error() !== JSON_ERROR_NONE) {
                $input = $_POST; // Fallback to form data if JSON parsing fails
            }
        }
    }
    
    // Handle request based on method
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Buscar documentos de um relatório específico
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
                
                // Buscar documentos do relatório
                $stmt = $conn->prepare("
                    SELECT 
                        id, 
                        nome_arquivo as nome_documento,
                        categoria as tipo_documento,
                        caminho_arquivo,
                        tamanho_arquivo,
                        descricao,
                        0 as ordem_exibicao,
                        data_criacao
                    FROM relatorio_documentos 
                    WHERE relatorio_id = :relatorio_id
                    ORDER BY data_criacao ASC
                ");
                $stmt->execute([':relatorio_id' => $relatorio_id]);
                
                $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Log para debug
                error_log('Query executada: SELECT documentos WHERE relatorio_id = ' . $relatorio_id);
                error_log('Documentos encontrados: ' . count($documentos));
                
                // Mapear os resultados para o formato esperado pelo frontend
                $documentos = array_map(function($documento) {
                    return [
                        'id' => (string)$documento['id'],
                        'nome_documento' => $documento['nome_documento'],
                        'tipo_documento' => $documento['tipo_documento'],
                        'caminho_arquivo' => $documento['caminho_arquivo'],
                        'tamanho_arquivo' => (int)$documento['tamanho_arquivo'],
                        'ordem_exibicao' => (int)$documento['ordem_exibicao'],
                        'data_criacao' => $documento['data_criacao'],
                        'descricao' => $documento['descricao'] ?? ''
                    ];
                }, $documentos);
                
                sendJsonResponse(true, 'Documentos carregados com sucesso', $documentos);
                
            } catch (PDOException $e) {
                error_log('Erro ao buscar documentos: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao carregar documentos', null, 500);
            }
            break;
            
        case 'POST':
            // Upload de novo documento
            if (empty($input['relatorio_id']) || empty($input['usuario_id'])) {
                sendJsonResponse(false, 'ID do relatório e ID do usuário são obrigatórios', null, 400);
            }
            
            if (empty($_FILES['documento'])) {
                sendJsonResponse(false, 'Nenhum arquivo foi enviado', null, 400);
            }
            
            // Verificar se o usuário tem permissão para adicionar documentos neste relatório
            // Se for um documento financeiro (id_financeiro preenchido), validar de forma diferente
            if (!empty($input['id_financeiro'])) {
                // Para documentos financeiros, verificar se o lançamento financeiro pertence ao usuário
                $stmt = $conn->prepare("
                    SELECT lf.id FROM lancamentos_financeiros lf
                    INNER JOIN obras o ON o.id = lf.obra_id 
                    WHERE lf.id = :id_financeiro AND o.usuario_id = :usuario_id
                ");
                $stmt->execute([
                    ':id_financeiro' => $input['id_financeiro'],
                    ':usuario_id' => $input['usuario_id']
                ]);
                
                if ($stmt->rowCount() === 0) {
                    sendJsonResponse(false, 'Você não tem permissão para adicionar documentos neste lançamento financeiro', null, 403);
                }
            } else {
                // Para documentos de relatório normal, verificar como antes
                $stmt = $conn->prepare("
                    SELECT id FROM relatorios_diarios 
                    WHERE id = :relatorio_id AND usuario_id = :usuario_id
                ");
                $stmt->execute([
                    ':relatorio_id' => $input['relatorio_id'],
                    ':usuario_id' => $input['usuario_id']
                ]);
                
                if ($stmt->rowCount() === 0) {
                    sendJsonResponse(false, 'Você não tem permissão para adicionar documentos neste relatório', null, 403);
                }
            }
            
            $file = $_FILES['documento'];
            
            // Validar arquivo
            if ($file['error'] !== UPLOAD_ERR_OK) {
                sendJsonResponse(false, 'Erro no upload do arquivo', null, 400);
            }
            
            $allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'txt'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($fileExtension, $allowedTypes)) {
                sendJsonResponse(false, 'Tipo de arquivo não permitido. Tipos aceitos: ' . implode(', ', $allowedTypes), null, 400);
            }
            
            // Limite de tamanho: 5MB (conforme especificação da memória)
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $maxSize) {
                sendJsonResponse(false, 'Arquivo muito grande. Tamanho máximo: 5MB', null, 400);
            }
            
            try {
                // Criar diretório se não existir
                $uploadDir = __DIR__ . '/../ob/uploads/relatorios/' . $input['relatorio_id'] . '/documentos/';
                createDirectoryIfNotExists($uploadDir);
                
                // Gerar nome único para o arquivo
                $fileName = generateUniqueFileName($file['name'], $uploadDir);
                $filePath = $uploadDir . $fileName;
                
                // Mover arquivo para o diretório final
                if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                    sendJsonResponse(false, 'Erro ao salvar arquivo', null, 500);
                }
                
                // Primeiro, obtemos a próxima ordem de exibição
                $stmt = $conn->prepare("
                    SELECT COALESCE(MAX(id), 0) + 1 as next_ordem 
                    FROM relatorio_documentos 
                    WHERE relatorio_id = :relatorio_id
                ");
                $stmt->execute([':relatorio_id' => $input['relatorio_id']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $next_ordem = $result['next_ordem'];
                
                // Salvar informações no banco de dados
                $caminhoRelativo = 'uploads/relatorios/' . $input['relatorio_id'] . '/documentos/' . $fileName;
                
                $stmt = $conn->prepare("
                    INSERT INTO relatorio_documentos 
                    (relatorio_id, usuario_id, id_financeiro, nome_arquivo, nome_original, caminho_arquivo, tamanho_arquivo, categoria, descricao, data_criacao)
                    VALUES (:relatorio_id, :usuario_id, :id_financeiro, :nome_arquivo, :nome_original, :caminho_arquivo, :tamanho_arquivo, :categoria, :descricao, NOW())
                ");
                
                $categoria = strtolower($input['tipo_documento'] ?? 'outro');
                $categoriasValidas = ['contrato','orcamento','projeto','licenca','relatorio','financeiro','outro'];
                if (!in_array($categoria, $categoriasValidas)) {
                    $categoria = 'outro';
                }
                
                $stmt->execute([
                    ':relatorio_id' => $input['relatorio_id'],
                    ':usuario_id' => $input['usuario_id'],
                    ':id_financeiro' => $input['id_financeiro'] ?? null,
                    ':nome_arquivo' => $input['nome_documento'] ?? $file['name'],
                    ':nome_original' => $file['name'],
                    ':caminho_arquivo' => $caminhoRelativo,
                    ':tamanho_arquivo' => $file['size'],
                    ':categoria' => $categoria,
                    ':descricao' => $input['descricao'] ?? ''
                ]);
                
                $documento_id = $conn->lastInsertId();
                
                // Buscar o documento recém-criado para retornar
                $stmt = $conn->prepare("SELECT * FROM relatorio_documentos WHERE id = :id");
                $stmt->execute([':id' => $documento_id]);
                $documento = $stmt->fetch(PDO::FETCH_ASSOC);
                
                sendJsonResponse(true, 'Documento enviado com sucesso', $documento, 201);
                
            } catch (Exception $e) {
                error_log('Erro ao processar upload: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao processar upload: ' . $e->getMessage(), null, 500);
            }
            break;
            
        case 'PUT':
            // Atualizar documento (apenas nome e tipo)
            if (empty($input['id'])) {
                sendJsonResponse(false, 'ID do documento é obrigatório', null, 400);
            }
            
            $usuario_id = $input['usuario_id'] ?? null;
            if (!$usuario_id) {
                sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
            }
            
            // Verificar se o usuário tem permissão para atualizar este documento
            $stmt = $conn->prepare("
                SELECT rd.id FROM relatorio_documentos rd
                INNER JOIN relatorios_diarios rel ON rd.relatorio_id = rel.id
                WHERE rd.id = :id AND rel.usuario_id = :usuario_id
            ");
            
            $stmt->execute([
                ':id' => $input['id'],
                ':usuario_id' => $usuario_id
            ]);
            
            if ($stmt->rowCount() === 0) {
                sendJsonResponse(false, 'Você não tem permissão para atualizar este documento', null, 403);
            }
            
            try {
                // Preparar campos para atualização
                $updateFields = [];
                $params = [':id' => $input['id']];
                
                if (isset($input['nome_documento'])) {
                    $updateFields[] = 'nome_arquivo = :nome_arquivo';
                    $params[':nome_arquivo'] = $input['nome_documento'];
                }
                
                if (isset($input['tipo_documento'])) {
                    $categoria = strtolower($input['tipo_documento']);
                    $categoriasValidas = ['contrato','orcamento','projeto','licenca','relatorio','financeiro','outro'];
                    if (!in_array($categoria, $categoriasValidas)) {
                        $categoria = 'outro';
                    }
                    $updateFields[] = 'categoria = :categoria';
                    $params[':categoria'] = $categoria;
                }
                
                if (isset($input['descricao'])) {
                    $updateFields[] = 'descricao = :descricao';
                    $params[':descricao'] = $input['descricao'];
                }
                
                if (empty($updateFields)) {
                    sendJsonResponse(false, 'Nenhum campo para atualizar foi fornecido', null, 400);
                }
                
                $updateFields[] = 'data_atualizacao = NOW()';
                
                $sql = "UPDATE relatorio_documentos SET " . implode(', ', $updateFields) . " WHERE id = :id";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                
                if ($stmt->rowCount() > 0) {
                    sendJsonResponse(true, 'Documento atualizado com sucesso');
                } else {
                    sendJsonResponse(false, 'Nenhuma alteração foi feita', null, 404);
                }
                
            } catch (Exception $e) {
                error_log('Erro ao atualizar documento: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao atualizar documento', null, 500);
            }
            break;

        case 'DELETE':
            // Deletar documento
            if (empty($input['id'])) {
                sendJsonResponse(false, 'ID do documento é obrigatório', null, 400);
            }
            
            $usuario_id = $input['usuario_id'] ?? null;
            if (!$usuario_id) {
                sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
            }
            
            try {
                // Verificar se o usuário tem permissão e obter caminho do arquivo
                $stmt = $conn->prepare("
                    SELECT rd.id, rd.caminho_arquivo FROM relatorio_documentos rd
                    INNER JOIN relatorios_diarios rel ON rd.relatorio_id = rel.id
                    WHERE rd.id = :id AND rel.usuario_id = :usuario_id
                ");
                
                $stmt->execute([
                    ':id' => $input['id'],
                    ':usuario_id' => $usuario_id
                ]);
                
                $documento = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$documento) {
                    sendJsonResponse(false, 'Você não tem permissão para excluir este documento', null, 403);
                }
                
                // Excluir o documento do banco de dados
                $stmt = $conn->prepare("DELETE FROM relatorio_documentos WHERE id = :id");
                $stmt->execute([':id' => $input['id']]);
                
                if ($stmt->rowCount() > 0) {
                    // Tentar excluir o arquivo físico
                    $filePath = __DIR__ . '/../ob/' . $documento['caminho_arquivo'];
                       if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    
                    sendJsonResponse(true, 'Documento excluído com sucesso');
                } else {
                    sendJsonResponse(false, 'Documento não encontrado', null, 404);
                }
                
            } catch (Exception $e) {
                error_log('Erro ao excluir documento: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao excluir documento', null, 500);
            }
            break;
            
        default:
            sendJsonResponse(false, 'Método não permitido', null, 405);
    }

} catch (Exception $e) {
    error_log('Erro em relatorio_documento.php: ' . $e->getMessage());
    sendJsonResponse(false, 'Erro interno do servidor: ' . $e->getMessage(), null, 500);
}

// Fechar conexão
$conn = null;
?>