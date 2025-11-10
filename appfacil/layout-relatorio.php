<?php
// Desabilitar exibição de erros para evitar HTML no JSON
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Garantir que não haja output antes dos headers
ob_start();

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

// Função para validar cor hexadecimal
function validateHexColor($color) {
    return preg_match('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $color);
}

// Função para upload de logo
function uploadLogo() {
    if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
        sendJsonResponse(false, 'Erro no upload do arquivo', null, 400);
    }

    $file = $_FILES['logo'];
    $usuario_id = $_POST['usuario_id'] ?? null;
    
    if (!$usuario_id) {
        sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
    }

    // Validar tipo de arquivo
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        sendJsonResponse(false, 'Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WebP', null, 400);
    }

    // Validar tamanho (máximo 3MB)
    if ($file['size'] > 3 * 1024 * 1024) {
        sendJsonResponse(false, 'Arquivo muito grande. Máximo 3MB', null, 400);
    }

    // Validar se é uma imagem válida
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        sendJsonResponse(false, 'Arquivo não é uma imagem válida', null, 400);
    }

    try {
        $conn = getDbConnection();
        
        // Criar diretório se não existir
        $uploadDir = __DIR__ . '/../ob/uploads/logos/';
        createDirectoryIfNotExists($uploadDir);

        // Gerar nome único para o arquivo
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . $usuario_id . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Mover arquivo
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            sendJsonResponse(false, 'Erro ao salvar arquivo', null, 500);
        }

        // Salvar no banco de dados
        $relativePath = '/uploads/logos/' . $filename;
        
        // Obter cor primária do POST ou usar padrão
        $cor_primaria = $_POST['cor_primaria'] ?? '#f54a06';
        $ativo = $_POST['ativo'] ?? 1;
        
        // Verificar se já existe configuração para o usuário
        $stmt = $conn->prepare('SELECT id FROM layout_relatorio_cliente WHERE usuario_id = ?');
        $stmt->execute([$usuario_id]);
        $existingConfig = $stmt->fetch();
        
        if ($existingConfig) {
            // Atualizar registro existente com a cor primária fornecida
            $stmt = $conn->prepare('UPDATE layout_relatorio_cliente SET logo_path = ?, logo_nome = ?, cor_primaria = ?, ativo = ?, data_atualizacao = NOW() WHERE usuario_id = ?');
            $stmt->execute([$relativePath, $file['name'], $cor_primaria, $ativo, $usuario_id]);
        } else {
            // Criar novo registro com a cor primária fornecida
            $stmt = $conn->prepare('INSERT INTO layout_relatorio_cliente (usuario_id, logo_path, logo_nome, cor_primaria, ativo, data_criacao) VALUES (?, ?, ?, ?, ?, NOW())');
            $stmt->execute([$usuario_id, $relativePath, $file['name'], $cor_primaria, $ativo]);
        }

        sendJsonResponse(true, 'Logo enviado com sucesso', [
            'logo_path' => $relativePath,
            'logo_nome' => $file['name']
        ]);

    } catch (PDOException $e) {
        error_log('Erro ao fazer upload: ' . $e->getMessage());
        sendJsonResponse(false, 'Erro no servidor: ' . $e->getMessage(), null, 500);
    } catch (Exception $e) {
        error_log('Erro ao fazer upload: ' . $e->getMessage());
        sendJsonResponse(false, 'Erro no servidor: ' . $e->getMessage(), null, 500);
    }
}

// Função para upload genérico de arquivo (retorna relative path and name)
function handleFileUpload($fileField, $uploadDirBase, $prefix, $allowedTypes = ['image/jpeg','image/png','image/gif','image/webp'], $maxSize = 3145728) {
    if (!isset($_FILES[$fileField]) || $_FILES[$fileField]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $file = $_FILES[$fileField];

    // Validate
    if (!in_array($file['type'], $allowedTypes)) {
        sendJsonResponse(false, 'Tipo de arquivo não permitido para ' . $fileField, null, 400);
    }
    if ($file['size'] > $maxSize) {
        sendJsonResponse(false, 'Arquivo muito grande para ' . $fileField, null, 400);
    }
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        sendJsonResponse(false, 'Arquivo não é uma imagem válida: ' . $fileField, null, 400);
    }

    // Ensure dir
    createDirectoryIfNotExists($uploadDirBase);
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . '_' . time() . '.' . $extension;
    $filepath = rtrim($uploadDirBase, '/') . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        sendJsonResponse(false, 'Erro ao salvar arquivo ' . $fileField, null, 500);
    }
    // Return relative path used by app (match other uploads)
    $relative = '/uploads/signatures/' . $filename;
    return ['path' => $relative, 'name' => $file['name']];
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
            $usuario_id = $_GET['usuario_id'] ?? null;
            
            if (!$usuario_id) {
                sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
            }
            
            try {
                $stmt = $conn->prepare('SELECT * FROM layout_relatorio_cliente WHERE usuario_id = ?');
                $stmt->execute([$usuario_id]);
                $config = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($config) {
                    // Converter valores booleanos
                    $config['ativo'] = (bool)$config['ativo'];
                    
                    sendJsonResponse(true, 'Configuração encontrada', $config);
                } else {
                    // Retornar configuração padrão simplificada
                    $defaultConfig = [
                        'id' => null,
                        'usuario_id' => $usuario_id,
                        'logo_path' => null,
                        'logo_nome' => null,
                        'cor_primaria' => '#f54a06',
                        'ativo' => true
                    ];
                    sendJsonResponse(true, 'Configuração padrão', $defaultConfig);
                }
            } catch (PDOException $e) {
                error_log('Erro ao buscar configuração: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao buscar configuração: ' . $e->getMessage(), null, 500);
            }
            break;
        case 'POST':
            if (isset($_FILES['logo'])) {
                uploadLogo();
            } else {
                // New: accept signature file uploads as multipart as well
                if (!empty($_FILES['assinatura_imagem']) || !empty($_FILES['assinatura_desenhada'])) {
                    $usuario_id = $_POST['usuario_id'] ?? null;
                    if (!$usuario_id) {
                        sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
                    }
                    try {
                        $conn = getDbConnection();
                        $uploadDir = __DIR__ . '/../ob/uploads/signatures';
                        $resultData = [];

                        // Ensure there is a config row for this user (insert stub if missing)
                        $stmt = $conn->prepare('SELECT id FROM layout_relatorio_cliente WHERE usuario_id = ?');
                        $stmt->execute([$usuario_id]);
                        $existingConfig = $stmt->fetch(PDO::FETCH_ASSOC);
                        if (!$existingConfig) {
                            // Insert minimal record so UPDATE statements later succeed
                            $stmtIns = $conn->prepare('INSERT INTO layout_relatorio_cliente (usuario_id, cor_primaria, ativo, data_criacao) VALUES (?, ?, ?, NOW())');
                            $stmtIns->execute([$usuario_id, '#f54a06', 1]);
                        }

                        // handle assinatura_imagem (camera/gallery)
                        if (!empty($_FILES['assinatura_imagem']) && $_FILES['assinatura_imagem']['error'] === UPLOAD_ERR_OK) {
                            $saved = handleFileUpload('assinatura_imagem', $uploadDir, 'assinatura_'.$usuario_id);
                            if ($saved) {
                                // update DB fields
                                $stmt = $conn->prepare('UPDATE layout_relatorio_cliente SET assinatura_imagem_path = ?, assinatura_imagem_nome = ?, data_atualizacao = NOW() WHERE usuario_id = ?');
                                $stmt->execute([$saved['path'], $saved['name'], $usuario_id]);
                                $resultData['assinatura_imagem'] = $saved;
                            }
                        }

                        // handle assinatura_desenhada (drawn signature sent as file)
                        if (!empty($_FILES['assinatura_desenhada']) && $_FILES['assinatura_desenhada']['error'] === UPLOAD_ERR_OK) {
                            $saved = handleFileUpload('assinatura_desenhada', $uploadDir, 'assinatura_desenhada_'.$usuario_id);
                            if ($saved) {
                                $stmt = $conn->prepare('UPDATE layout_relatorio_cliente SET assinatura_desenhada_path = ?, data_atualizacao = NOW() WHERE usuario_id = ?');
                                $stmt->execute([$saved['path'], $usuario_id]);
                                $resultData['assinatura_desenhada'] = $saved;
                            }
                        }

                        // If we saved any signature files, fetch and return the updated config
                        if (!empty($resultData)) {
                            $stmt = $conn->prepare('SELECT * FROM layout_relatorio_cliente WHERE usuario_id = ?');
                            $stmt->execute([$usuario_id]);
                            $config = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($config) $config['ativo'] = (bool)$config['ativo'];
                            sendJsonResponse(true, 'Assinatura enviada com sucesso', $config);
                        } else {
                            // No files saved (shouldn't happen due to earlier checks), return informative error
                            sendJsonResponse(false, 'Nenhuma assinatura recebida para upload', null, 400);
                        }
                    } catch (PDOException $e) {
                        error_log('Erro ao salvar assinatura: ' . $e->getMessage());
                        sendJsonResponse(false, 'Erro no servidor: ' . $e->getMessage(), null, 500);
                    }
                    // end signature multipart handling
                } else {
                    // Criar ou atualizar configuração (com ou sem upload)
                    $usuario_id = $input['usuario_id'] ?? null;
                    $cor_primaria = $input['cor_primaria'] ?? '#f54a06';
                    $ativo = $input['ativo'] ?? true;
                    
                    if (!$usuario_id) {
                        sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
                    }
                    
                    // Validar cor primária
                    if (isset($input['cor_primaria']) && !validateHexColor($input['cor_primaria'])) {
                        sendJsonResponse(false, "Cor primária inválida", null, 400);
                    }
                    
                    try {
                        // Verificar se já existe configuração para o usuário
                        $stmt = $conn->prepare('SELECT id FROM layout_relatorio_cliente WHERE usuario_id = ?');
                        $stmt->execute([$usuario_id]);
                        $existingConfig = $stmt->fetch();
                        
                        $logo_path = $input['logo_path'] ?? null;
                        $logo_nome = $input['logo_nome'] ?? null;
                        
                        if ($existingConfig) {
                            // Atualizar registro existente
                            $stmt = $conn->prepare('UPDATE layout_relatorio_cliente SET 
                                logo_path = COALESCE(?, logo_path), 
                                logo_nome = COALESCE(?, logo_nome), 
                                cor_primaria = ?, 
                                ativo = ?, 
                                data_atualizacao = NOW() 
                            WHERE usuario_id = ?');
                            
                            $success = $stmt->execute([
                                $logo_path,
                                $logo_nome,
                                $cor_primaria,
                                $ativo ? 1 : 0,
                                $usuario_id
                            ]);
                            
                            if ($success && $stmt->rowCount() > 0) {
                                $stmt = $conn->prepare('SELECT * FROM layout_relatorio_cliente WHERE usuario_id = ?');
                                $stmt->execute([$usuario_id]);
                                $config = $stmt->fetch(PDO::FETCH_ASSOC);
                                $config['ativo'] = (bool)$config['ativo'];
                                sendJsonResponse(true, 'Configuração atualizada com sucesso', $config);
                            } else {
                                sendJsonResponse(false, 'Erro ao atualizar configuração ou nenhuma alteração foi feita', null, 404);
                            }
                        } else {
                            // Criar novo registro
                            $stmt = $conn->prepare('INSERT INTO layout_relatorio_cliente (
                                usuario_id, logo_path, logo_nome, cor_primaria, ativo, data_criacao
                            ) VALUES (?, ?, ?, ?, ?, NOW())');
                            
                            $success = $stmt->execute([
                                $usuario_id,
                                $logo_path,
                                $logo_nome,
                                $cor_primaria,
                                $ativo ? 1 : 0
                            ]);
                            
                            if ($success) {
                                $id = $conn->lastInsertId();
                                $stmt = $conn->prepare('SELECT * FROM layout_relatorio_cliente WHERE id = ?');
                                $stmt->execute([$id]);
                                $config = $stmt->fetch(PDO::FETCH_ASSOC);
                                $config['ativo'] = (bool)$config['ativo'];
                                sendJsonResponse(true, 'Configuração criada com sucesso', $config);
                            } else {
                                sendJsonResponse(false, 'Erro ao criar configuração', null, 500);
                            }
                        }
                    } catch (PDOException $e) {
                        error_log('Erro ao salvar configuração: ' . $e->getMessage());
                        sendJsonResponse(false, 'Erro no servidor: ' . $e->getMessage(), null, 500);
                    }
                }
            }
            break;
        case 'PUT':
            if (empty($input['usuario_id'])) {
                sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
            }
            
            // Validar cor primária
            if (isset($input['cor_primaria']) && !validateHexColor($input['cor_primaria'])) {
                sendJsonResponse(false, "Cor primária inválida", null, 400);
            }
            
            try {
                $fieldsToUpdate = [];
                $params = [];
                
                $updatableFields = [
                    'logo_path', 'logo_nome', 'cor_primaria', 'ativo'
                ];
                
                foreach ($updatableFields as $field) {
                    if (array_key_exists($field, $input)) {
                        $fieldsToUpdate[] = "$field = ?";
                        if ($field === 'ativo') {
                            $params[] = $input[$field] ? 1 : 0;
                        } else {
                            $params[] = $input[$field];
                        }
                    }
                }
                
                if (empty($fieldsToUpdate)) {
                    sendJsonResponse(false, 'Nenhum campo para atualizar', null, 400);
                }
                
                $fieldsToUpdate[] = 'data_atualizacao = NOW()';
                
                $sql = 'UPDATE layout_relatorio_cliente SET ' . implode(', ', $fieldsToUpdate) . ' WHERE usuario_id = ?';
                $params[] = $input['usuario_id'];
                
                $stmt = $conn->prepare($sql);
                $success = $stmt->execute($params);
                
                if ($success && $stmt->rowCount() > 0) {
                    $stmt = $conn->prepare('SELECT * FROM layout_relatorio_cliente WHERE usuario_id = ?');
                    $stmt->execute([$input['usuario_id']]);
                    $config = $stmt->fetch(PDO::FETCH_ASSOC);
                    $config['ativo'] = (bool)$config['ativo'];
                    sendJsonResponse(true, 'Configuração atualizada com sucesso', $config);
                } else {
                    sendJsonResponse(false, 'Erro ao atualizar configuração ou nenhuma alteração foi feita', null, 404);
                }
            } catch (PDOException $e) {
                error_log('Erro ao atualizar configuração: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro no servidor: ' . $e->getMessage(), null, 500);
            }
            break;
        case 'DELETE':
            if (empty($input['usuario_id'])) {
                sendJsonResponse(false, 'ID do usuário é obrigatório', null, 400);
            }

            try {
                $usuario_id = $input['usuario_id'];
                // if client requested deletion of assinatura specifically
                if (!empty($input['delete']) && $input['delete'] === 'assinatura') {
                    // fetch current paths
                    $stmt = $conn->prepare('SELECT assinatura_imagem_path, assinatura_desenhada_path FROM layout_relatorio_cliente WHERE usuario_id = ?');
                    $stmt->execute([$usuario_id]);
                    $config = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($config) {
                        // delete physical files if exist
                        if (!empty($config['assinatura_imagem_path'])) {
                            $path = __DIR__ . '/../ob' . $config['assinatura_imagem_path'];
                            if (file_exists($path)) @unlink($path);
                        }
                        if (!empty($config['assinatura_desenhada_path'])) {
                            $path = __DIR__ . '/../ob' . $config['assinatura_desenhada_path'];
                            if (file_exists($path)) @unlink($path);
                        }

                        // clear fields in DB
                        $stmt = $conn->prepare('UPDATE layout_relatorio_cliente SET assinatura_imagem_path = NULL, assinatura_imagem_nome = NULL, assinatura_desenhada_path = NULL, data_atualizacao = NOW() WHERE usuario_id = ?');
                        $stmt->execute([$usuario_id]);

                        sendJsonResponse(true, 'Assinatura excluída com sucesso');
                    } else {
                        sendJsonResponse(false, 'Configuração não encontrada', null, 404);
                    }
                } else {
                    // existing logo deletion flow (keep behavior)
                    // Primeiro, obter o caminho da logo atual para excluí-la fisicamente
                    $stmt = $conn->prepare('SELECT logo_path FROM layout_relatorio_cliente WHERE usuario_id = ?');
                    $stmt->execute([$input['usuario_id']]);
                    $config = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Excluir o arquivo fisicamente se existir
                    if ($config && !empty($config['logo_path'])) {
                        $logoPath = __DIR__ . '/../ob' . $config['logo_path'];
                        if (file_exists($logoPath)) {
                            unlink($logoPath);
                        }
                    }

                    // Atualizar o registro no banco de dados, removendo a referência da logo
                    $stmt = $conn->prepare('UPDATE layout_relatorio_cliente SET 
                        logo_path = NULL, 
                        logo_nome = NULL, 
                        data_atualizacao = NOW() 
                        WHERE usuario_id = ?');
                    $success = $stmt->execute([$input['usuario_id']]);

                    if ($success && $stmt->rowCount() > 0) {
                        sendJsonResponse(true, 'Logo excluída com sucesso');
                    } else {
                        sendJsonResponse(false, 'Configuração não encontrada', null, 404);
                    }
                }
            } catch (PDOException $e) {
                error_log('Erro ao excluir: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro no servidor: ' . $e->getMessage(), null, 500);
            } catch (Exception $e) {
                error_log('Erro ao excluir: ' . $e->getMessage());
                sendJsonResponse(false, 'Erro ao excluir: ' . $e->getMessage(), null, 500);
            }
            break;
        default:
            sendJsonResponse(false, 'Método não permitido', null, 405);
    }
} catch (PDOException $e) {
    error_log('Erro de conexão com o banco de dados: ' . $e->getMessage());
    sendJsonResponse(false, 'Erro de conexão com o banco de dados: ' . $e->getMessage(), null, 500);
} catch (Exception $e) {
    error_log('Erro inesperado: ' . $e->getMessage());
    sendJsonResponse(false, 'Ocorreu um erro inesperado', null, 500);
}

// Limpar qualquer output buffer restante
ob_end_clean();
?>