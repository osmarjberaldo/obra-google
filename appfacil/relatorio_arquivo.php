<?php
// Habilitar exibição de erros para debug (remover em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Verificar se o arquivo de configuração existe
if (!file_exists(__DIR__ . '/config/pdo.php')) {
    sendJsonResponse(false, 'Arquivo de configuração do banco de dados não encontrado', null, 500);
}

require_once __DIR__ . '/config/pdo.php';

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
    
    $timestamp = time();
    $randomString = uniqid();
    $finalName = $randomString . '_' . $timestamp . '.' . $extension;
    
    // Garantir que o nome é único
    $counter = 1;
    while (file_exists($directory . '/' . $finalName)) {
        $finalName = $randomString . '_' . $timestamp . '_' . $counter . '.' . $extension;
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
            // Buscar arquivos de um relatório específico
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
                
                // Buscar arquivos do relatório
                $stmt = $conn->prepare("
                    SELECT 
                        id, 
                        nome_arquivo,
                        nome_original,
                        tipo_arquivo,
                        tamanho_arquivo,
                        caminho_arquivo,
                        descricao,
                        categoria,
                        data_criacao
                    FROM relatorio_arquivos 
                    WHERE relatorio_id = :relatorio_id
                    ORDER BY data_criacao ASC
                ");
                $stmt->execute([':relatorio_id' => $relatorio_id]);
                
                $arquivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Log para debug
                error_log('Query executada: SELECT arquivos WHERE relatorio_id = ' . $relatorio_id);
                error_log('Arquivos encontrados: ' . count($arquivos));
                
                // Mapear os resultados para o formato esperado pelo frontend
                $arquivos = array_map(function($arquivo) {
                    return [
                        'id' => (string)$arquivo['id'],
                        'nome_arquivo' => $arquivo['nome_arquivo'],
                        'nome_original' => $arquivo['nome_original'],
                        'tipo_arquivo' => $arquivo['tipo_arquivo'],
                        'tamanho_arquivo' => (int)$arquivo['tamanho_arquivo'],
                        'caminho_arquivo' => $arquivo['caminho_arquivo'],
                        'descricao' => $arquivo['descricao'] ?? '',
                        'categoria' => $arquivo['categoria'],
                        'data_criacao' => $arquivo['data_criacao']
                    ];
                }, $arquivos);
                
                sendJsonResponse(true, 'Arquivos carregados com sucesso', $arquivos);
                
            } catch (PDOException $e) {
                error_log('Erro ao buscar arquivos: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao carregar arquivos', null, 500);
            }
            break;
            
        case 'POST':
            // Upload de novo arquivo
            if (empty($input['relatorio_id']) || empty($input['usuario_id'])) {
                sendJsonResponse(false, 'ID do relatório e ID do usuário são obrigatórios', null, 400);
            }
            
            if (empty($_FILES['arquivo'])) {
                sendJsonResponse(false, 'Nenhum arquivo foi enviado', null, 400);
            }
            
            // Verificar se o usuário tem permissão para adicionar arquivos neste relatório
            $stmt = $conn->prepare("
                SELECT id FROM relatorios_diarios 
                WHERE id = :relatorio_id AND usuario_id = :usuario_id
            ");
            $stmt->execute([
                ':relatorio_id' => $input['relatorio_id'],
                ':usuario_id' => $input['usuario_id']
            ]);
            
            if ($stmt->rowCount() === 0) {
                sendJsonResponse(false, 'Você não tem permissão para adicionar arquivos neste relatório', null, 403);
            }
            
            $file = $_FILES['arquivo'];
            
            // Validar arquivo
            if ($file['error'] !== UPLOAD_ERR_OK) {
                sendJsonResponse(false, 'Erro no upload do arquivo', null, 400);
            }
            
            // Tipos permitidos para fotos e vídeos
            $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $allowedVideoTypes = ['mp4', 'avi', 'mov', 'wmv', 'flv', '3gp'];
            $allowedTypes = array_merge($allowedImageTypes, $allowedVideoTypes);
            
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($fileExtension, $allowedTypes)) {
                sendJsonResponse(false, 'Tipo de arquivo não permitido. Tipos aceitos: ' . implode(', ', $allowedTypes), null, 400);
            }
            
            // Determinar tipo do arquivo
            $tipoArquivo = in_array($fileExtension, $allowedImageTypes) ? 'imagem' : 'video';
            
            // Limite de tamanho: 100MB para vídeos, 50MB para imagens
            $maxSize = $tipoArquivo === 'video' ? 100 * 1024 * 1024 : 50 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                $maxSizeText = $tipoArquivo === 'video' ? '100MB' : '50MB';
                sendJsonResponse(false, 'Arquivo muito grande. Tamanho máximo para ' . $tipoArquivo . ': ' . $maxSizeText, null, 400);
            }
            
            try {
                // Criar diretório se não existir
                $uploadDir = __DIR__ . '/../ob/uploads/relatorios/' . $input['relatorio_id'] . '/';
                createDirectoryIfNotExists($uploadDir);
                
                // Gerar nome único para o arquivo
                $fileName = generateUniqueFileName($file['name'], $uploadDir);
                $filePath = $uploadDir . $fileName;
                
                // Mover arquivo para o diretório final
                if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                    sendJsonResponse(false, 'Erro ao salvar arquivo', null, 500);
                }
                
                // Salvar informações no banco de dados
                $caminhoRelativo = 'uploads/relatorios/' . $input['relatorio_id'] . '/' . $fileName;
                
                $stmt = $conn->prepare("
                    INSERT INTO relatorio_arquivos 
                    (relatorio_id, usuario_id, nome_arquivo, nome_original, tipo_arquivo, tamanho_arquivo, caminho_arquivo, descricao, categoria, data_criacao)
                    VALUES (:relatorio_id, :usuario_id, :nome_arquivo, :nome_original, :tipo_arquivo, :tamanho_arquivo, :caminho_arquivo, :descricao, :categoria, NOW())
                ");
                
                $categoria = $input['categoria'] ?? 'progresso';
                $categoriasValidas = ['progresso','problema','material','equipe','antes_depois','outro'];
                if (!in_array($categoria, $categoriasValidas)) {
                    $categoria = 'progresso';
                }
                
                $stmt->execute([
                    ':relatorio_id' => $input['relatorio_id'],
                    ':usuario_id' => $input['usuario_id'],
                    ':nome_arquivo' => $fileName,
                    ':nome_original' => $file['name'],
                    ':tipo_arquivo' => $tipoArquivo,
                    ':tamanho_arquivo' => $file['size'],
                    ':caminho_arquivo' => $caminhoRelativo,
                    ':descricao' => $input['descricao'] ?? '',
                    ':categoria' => $categoria
                ]);
                
                $arquivo_id = $conn->lastInsertId();
                
                // Buscar o arquivo recém-criado para retornar
                $stmt = $conn->prepare("SELECT * FROM relatorio_arquivos WHERE id = :id");
                $stmt->execute([':id' => $arquivo_id]);
                $arquivo = $stmt->fetch(PDO::FETCH_ASSOC);
                
                sendJsonResponse(true, 'Arquivo enviado com sucesso', $arquivo, 201);
                
            } catch (Exception $e) {
                error_log('Erro ao processar upload: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao processar upload: ' . $e->getMessage(), null, 500);
            }
            break;
            
        case 'PUT':
            // Atualizar arquivo (apenas descrição e categoria)
            if (empty($input['id'])) {
                sendJsonResponse(false, 'ID do arquivo é obrigatório', null, 400);
            }
            
            $usuario_id = $input['usuario_id'] ?? null;
            if (!$usuario_id) {
                sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
            }
            
            // Verificar se o usuário tem permissão para atualizar este arquivo
            $stmt = $conn->prepare("
                SELECT ra.id FROM relatorio_arquivos ra
                INNER JOIN relatorios_diarios rel ON ra.relatorio_id = rel.id
                WHERE ra.id = :id AND rel.usuario_id = :usuario_id
            ");
            
            $stmt->execute([
                ':id' => $input['id'],
                ':usuario_id' => $usuario_id
            ]);
            
            if ($stmt->rowCount() === 0) {
                sendJsonResponse(false, 'Você não tem permissão para atualizar este arquivo', null, 403);
            }
            
            try {
                // Preparar campos para atualização
                $updateFields = [];
                $params = [':id' => $input['id']];
                
                if (isset($input['descricao'])) {
                    $updateFields[] = 'descricao = :descricao';
                    $params[':descricao'] = $input['descricao'];
                }
                
                if (isset($input['categoria'])) {
                    $categoria = $input['categoria'];
                    $categoriasValidas = ['progresso','problema','material','equipe','antes_depois','outro'];
                    if (!in_array($categoria, $categoriasValidas)) {
                        $categoria = 'progresso';
                    }
                    $updateFields[] = 'categoria = :categoria';
                    $params[':categoria'] = $categoria;
                }
                
                if (empty($updateFields)) {
                    sendJsonResponse(false, 'Nenhum campo para atualizar foi fornecido', null, 400);
                }
                
                $updateFields[] = 'data_atualizacao = NOW()';
                
                $sql = "UPDATE relatorio_arquivos SET " . implode(', ', $updateFields) . " WHERE id = :id";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                
                if ($stmt->rowCount() > 0) {
                    sendJsonResponse(true, 'Arquivo atualizado com sucesso');
                } else {
                    sendJsonResponse(false, 'Nenhuma alteração foi feita', null, 404);
                }
                
            } catch (Exception $e) {
                error_log('Erro ao atualizar arquivo: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao atualizar arquivo', null, 500);
            }
            break;

        case 'DELETE':
            // Deletar arquivo
            if (empty($input['id'])) {
                sendJsonResponse(false, 'ID do arquivo é obrigatório', null, 400);
            }
            
            $usuario_id = $input['usuario_id'] ?? null;
            if (!$usuario_id) {
                sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
            }
            
            try {
                // Verificar se o usuário tem permissão e obter caminho do arquivo
                $stmt = $conn->prepare("
                    SELECT ra.id, ra.caminho_arquivo FROM relatorio_arquivos ra
                    INNER JOIN relatorios_diarios rel ON ra.relatorio_id = rel.id
                    WHERE ra.id = :id AND rel.usuario_id = :usuario_id
                ");
                
                $stmt->execute([
                    ':id' => $input['id'],
                    ':usuario_id' => $usuario_id
                ]);
                
                $arquivo = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$arquivo) {
                    sendJsonResponse(false, 'Você não tem permissão para excluir este arquivo', null, 403);
                }
                
                // Excluir o arquivo do banco de dados
                $stmt = $conn->prepare("DELETE FROM relatorio_arquivos WHERE id = :id");
                $stmt->execute([':id' => $input['id']]);
                
                if ($stmt->rowCount() > 0) {
                    // Tentar excluir o arquivo físico
                    $filePath = __DIR__ . '/../ob/'. $arquivo['caminho_arquivo'];
                    
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    
                    sendJsonResponse(true, 'Arquivo excluído com sucesso');
                } else {
                    sendJsonResponse(false, 'Arquivo não encontrado', null, 404);
                }
                
            } catch (Exception $e) {
                error_log('Erro ao excluir arquivo: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao excluir arquivo', null, 500);
            }
            break;
            
        default:
            sendJsonResponse(false, 'Método não permitido', null, 405);
    }

} catch (Exception $e) {
    error_log('Erro em relatorio_arquivo.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    sendJsonResponse(false, 'Erro interno do servidor: ' . $e->getMessage(), null, 500);
} catch (Error $e) {
    error_log('Erro fatal em relatorio_arquivo.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    sendJsonResponse(false, 'Erro fatal do servidor: ' . $e->getMessage(), null, 500);
}

// Fechar conexão
$conn = null;
?>