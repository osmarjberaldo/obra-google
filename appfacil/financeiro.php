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

// Log das informações da requisição
error_log('=== Nova requisição recebida ===');
error_log('Método: ' . $_SERVER['REQUEST_METHOD']);
error_log('Content-Type: ' . ($_SERVER['CONTENT_TYPE'] ?? 'não definido'));
error_log('Content-Length: ' . ($_SERVER['CONTENT_LENGTH'] ?? 'não definido'));
error_log('Tamanho do $_POST: ' . strlen(json_encode($_POST)));
error_log('Tamanho do $_FILES: ' . strlen(json_encode($_FILES)));

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

// Função para processar upload de documento
function processDocumentUpload($financeiro_id, $file) {
    error_log('=== Iniciando processDocumentUpload ===');
    error_log('financeiro_id: ' . $financeiro_id);
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
    
    // Limite de tamanho: 5MB (conforme especificação da memória)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        error_log('Arquivo muito grande. Tamanho: ' . $file['size'] . ' bytes');
        throw new Exception('Arquivo muito grande. Tamanho máximo: 5MB');
    }
    
    // Criar diretório seguindo padrão: /uploads/financeiro/{id_financeiro}/documentos/
    $uploadDir = __DIR__ . '/../ob/uploads/financeiro/' . $financeiro_id . '/documentos/';
    error_log('Diretório de upload: ' . $uploadDir);
    
    createDirectoryIfNotExists($uploadDir);
    
    // Gerar nome único para o arquivo
    $fileName = generateUniqueFileName($file['name'], $uploadDir);
    $filePath = $uploadDir . $fileName;
    
    error_log('Nome do arquivo gerado: ' . $fileName);
    error_log('Caminho completo do arquivo: ' . $filePath);
    
    // Mover arquivo para o diretório final
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        error_log('Erro ao mover arquivo de ' . $file['tmp_name'] . ' para ' . $filePath);
        throw new Exception('Erro ao salvar arquivo');
    }
    
    error_log('Arquivo salvo com sucesso!');
    
    // Retornar informações do arquivo
    $result = [
        'nome_original' => $file['name'],
        'nome_arquivo' => $fileName,
        'caminho_relativo' => 'uploads/financeiro/' . $financeiro_id . '/documentos/' . $fileName,
        'tamanho' => $file['size']
    ];
    
    error_log('Resultado do processDocumentUpload: ' . json_encode($result));
    error_log('=== Fim processDocumentUpload ===');
    
    return $result;
}

function response($success, $message, $data = null) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data], JSON_PRETTY_PRINT);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $conn = getDbConnection();
    
    switch ($method) {
        case 'GET':
            handleGet($conn);
            break;
        case 'POST':
            handlePost($conn);
            break;
        case 'PUT':
            handlePut($conn);
            break;
        case 'DELETE':
            handleDelete($conn);
            break;
        default:
            response(false, 'Método não permitido');
    }
} catch (PDOException $e) {
    error_log('Erro de conexão com o banco de dados: ' . $e->getMessage());
    response(false, 'Erro de conexão com o banco de dados: ' . $e->getMessage());
} catch (Exception $e) {
    error_log('Erro inesperado: ' . $e->getMessage());
    response(false, 'Ocorreu um erro inesperado');
}

function handleGet($conn) {
    $usuario_id = $_GET['usuario_id'] ?? null;
    $obra_id = $_GET['obra_id'] ?? null;
    
    if (!$usuario_id) {
        response(false, 'ID do usuário é obrigatório');
    }

    // Verificar se a obra pertence ao usuário
    if ($obra_id) {
        $stmt = $conn->prepare('SELECT id FROM obras WHERE id = ? AND usuario_id = ?');
        $stmt->execute([$obra_id, $usuario_id]);
        if (!$stmt->fetch()) {
            response(false, 'Obra não encontrada ou não autorizada');
        }
    }

    // Parâmetros de filtro
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;
    $tipo = $_GET['tipo'] ?? null; // 'receita' ou 'despesa'
    
    // Log dos parâmetros recebidos
    error_log('Parâmetros recebidos - start_date: ' . $start_date . ', end_date: ' . $end_date . ', tipo: ' . $tipo);
    
    $sql = 'SELECT lf.*, o.nome_obra, f.nome as fornecedor_nome FROM lancamentos_financeiros lf
            INNER JOIN obras o ON o.id = lf.obra_id
            LEFT JOIN fornecedores f ON f.id = lf.fornecedor_id
            WHERE o.usuario_id = ?';
    $params = [$usuario_id];
    
    if ($obra_id) {
        $sql .= ' AND lf.obra_id = ?';
        $params[] = $obra_id;
    }
    
    if ($start_date) {
        $sql .= ' AND lf.data_vencimento >= ?';
        $params[] = $start_date;
        error_log('Filtrando por data_vencimento >= ' . $start_date);
    }
    
    if ($end_date) {
        $sql .= ' AND lf.data_vencimento <= ?';
        $params[] = $end_date;
        error_log('Filtrando por data_vencimento <= ' . $end_date);
    }
    
    if ($tipo) {
        $sql .= ' AND lf.tipo = ?';
        $params[] = $tipo;
    }
    
    $sql .= ' ORDER BY lf.data_lancamento DESC';
    
    try {
        // Log the SQL query and parameters
        error_log('Executando consulta SQL: ' . $sql);
        error_log('Parâmetros: ' . print_r($params, true));
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $lancamentos = $stmt->fetchAll();
        
        // Log the number of results
        error_log('Número de lançamentos encontrados: ' . count($lancamentos));
        
        // Calcular totais
        $totais = [
            'receitas' => 0,
            'despesas' => 0,
            'saldo' => 0
        ];
        
        $formattedLancamentos = array_map(function($lancamento) use (&$totais) {
            if ($lancamento['tipo'] === 'receita') {
                $totais['receitas'] += $lancamento['valor'];
            } else {
                $totais['despesas'] += $lancamento['valor'];
            }
            
            return [
                'id' => $lancamento['id'],
                'obra_id' => $lancamento['obra_id'],
                'obra_nome' => $lancamento['nome_obra'],
                'fornecedor_id' => $lancamento['fornecedor_id'],
                'fornecedor_nome' => $lancamento['fornecedor_nome'],
                'tipo' => $lancamento['tipo'],
                'categoria' => $lancamento['categoria'],
                'descricao' => $lancamento['descricao'],
                'valor' => number_format($lancamento['valor'], 2, '.', ''),
                'data_lancamento' => date('d/m/Y', strtotime($lancamento['data_lancamento'])),
                'data_vencimento' => $lancamento['data_vencimento'] ? date('d/m/Y', strtotime($lancamento['data_vencimento'])) : null,
                'status' => $lancamento['status'],
                'forma_pagamento' => $lancamento['forma_pagamento'],
                'observacoes' => $lancamento['observacoes'],
                'anexo' => $lancamento['anexo'],
                'nome_documento' => $lancamento['nome_documento']
            ];
        }, $lancamentos);
        
        $totais['saldo'] = $totais['receitas'] - $totais['despesas'];
        
        response(true, 'Lançamentos listados', [
            'lancamentos' => $formattedLancamentos,
            'totais' => [
                'receitas' => number_format($totais['receitas'], 2, '.', ''),
                'despesas' => number_format($totais['despesas'], 2, '.', ''),
                'saldo' => number_format($totais['saldo'], 2, '.', '')
            ]
        ]);
    } catch (Exception $e) {
        response(false, 'Erro ao buscar lançamentos: ' . $e->getMessage());
    }
}

function handlePost($conn) {
    try {
        // Log das configurações de upload do PHP
        error_log('Configurações de upload do PHP:');
        error_log('upload_max_filesize: ' . ini_get('upload_max_filesize'));
        error_log('post_max_size: ' . ini_get('post_max_size'));
        error_log('max_file_uploads: ' . ini_get('max_file_uploads'));
        error_log('memory_limit: ' . ini_get('memory_limit'));
        
        // Verificar se é upload de arquivo (multipart/form-data) ou JSON
        $isFileUpload = !empty($_FILES) && isset($_FILES['documento']) && $_FILES['documento']['error'] !== UPLOAD_ERR_NO_FILE;
        
        error_log('=== Processando nova transação financeira ===');
        error_log('Tipo de requisição: ' . ($isFileUpload ? 'FormData com arquivo' : 'JSON sem arquivo'));
        error_log('$_FILES: ' . json_encode($_FILES));
        error_log('$_POST: ' . json_encode($_POST));
        
        if ($isFileUpload) {
            // Processar dados do formulário com arquivo
            $data = $_POST;
            error_log('Form POST data with file: ' . json_encode($data));
            error_log('Files uploaded: ' . json_encode($_FILES));
        } else {
            // Ler e validar dados JSON da requisição
            $rawData = file_get_contents('php://input');
            if (!$rawData) {
                throw new Exception('Dados da requisição vazios');
            }
            error_log('Raw POST data: ' . $rawData);
            
            $data = json_decode($rawData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Erro ao processar dados da requisição: ' . json_last_error_msg());
            }
            error_log('Decoded POST data: ' . json_encode($data));
        }
        
        // Validar campos obrigatórios
        $required_fields = [
            'obra_id' => 'ID da Obra',
            'usuario_id' => 'ID do Usuário',
            'tipo' => 'Tipo de Lançamento',
            'valor' => 'Valor',
            'data_vencimento' => 'Data de Vencimento',
            'status' => 'Status',
            'forma_pagamento' => 'Forma de Pagamento',
            'categoria' => 'Categoria'
        ];
        
        // Valores permitidos para categoria
        $categoriasPermitidas = ['mao_de_obra', 'materiais', 'servico', 'aluguel', 'outros'];
        
        foreach ($required_fields as $field => $label) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new Exception("Campo obrigatório ausente ou vazio: $label");
            }
        }
        
        // Validar valores permitidos para status
        $statusPermitidos = ['pendente', 'pago', 'atrasado', 'cancelado'];
        if (!in_array($data['status'], $statusPermitidos)) {
            throw new Exception('Status inválido. Valores permitidos: ' . implode(', ', $statusPermitidos));
        }
        
        // Validar formato da data de vencimento
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['data_vencimento'])) {
            throw new Exception('Formato de data de vencimento inválido. Use o formato AAAA-MM-DD');
        }
        
        // Validar categoria
        if (!in_array($data['categoria'], $categoriasPermitidas)) {
            throw new Exception('Categoria inválida. Valores permitidos: ' . implode(', ', array_map(function($cat) {
                return ucwords(str_replace('_', ' ', $cat));
            }, $categoriasPermitidas)));
        }
        
        // Verificar se a obra pertence ao usuário
        $stmt = $conn->prepare('SELECT id FROM obras WHERE id = ? AND usuario_id = ?');
        $stmt->execute([$data['obra_id'], $data['usuario_id']]);
        if (!$stmt->fetch()) {
            throw new Exception('Obra não encontrada ou não autorizada');
        }

        // Verificar se fornecedor_id foi fornecido e é válido
        if (isset($data['fornecedor_id']) && !empty($data['fornecedor_id'])) {
            // Verificar se o fornecedor pertence ao usuário
            $stmt = $conn->prepare('SELECT id FROM fornecedores WHERE id = ? AND usuario_id = ?');
            $stmt->execute([$data['fornecedor_id'], $data['usuario_id']]);
            if (!$stmt->fetch()) {
                throw new Exception('Fornecedor não encontrado ou não autorizado');
            }
        }

        // Iniciar transação e processar lançamentos
        $conn->beginTransaction();

        try {
            // Verificar se data_lancamento foi enviada, caso contrário usar data atual
            $dataLancamento = isset($data['data_lancamento']) && !empty($data['data_lancamento']) 
                ? $data['data_lancamento'] 
                : date('Y-m-d');
            
            error_log('Data de lançamento a ser usada: ' . $dataLancamento);
            
            $baseDate = new DateTime($dataLancamento);
            $insertCount = 1;
            $frequency = null;
            $repeatTimes = 1;

            // Verificar se é recorrente e validar
            if (isset($data['is_recurring']) && $data['is_recurring'] === '1') {
                if (!isset($data['frequency']) || !isset($data['repeat_times'])) {
                    throw new Exception('Frequência e número de repetições são obrigatórios para lançamentos recorrentes');
                }
                
                $frequency = $data['frequency'];
                $repeatTimes = intval($data['repeat_times']);
                $insertCount = $repeatTimes;

                // Validar frequência
                if (!in_array($frequency, ['day', 'week', 'biweek', 'month', 'year'])) {
                    throw new Exception('Frequência inválida');
                }

                // Validar número de repetições
                if ($repeatTimes < 1 || $repeatTimes > 12) {
                    throw new Exception('Número de repetições inválido (1-12)');
                }
            }

            // Verificar se há documento para upload
            $hasDocument = $isFileUpload && !empty($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK;
            
            if ($isFileUpload && !empty($_FILES['documento'])) {
                // Log do erro de upload se houver
                $uploadError = $_FILES['documento']['error'];
                error_log('Código de erro do upload: ' . $uploadError);
                
                switch ($uploadError) {
                    case UPLOAD_ERR_INI_SIZE:
                        throw new Exception('Arquivo muito grande (excede upload_max_filesize)');
                    case UPLOAD_ERR_FORM_SIZE:
                        throw new Exception('Arquivo muito grande (excede MAX_FILE_SIZE)');
                    case UPLOAD_ERR_PARTIAL:
                        throw new Exception('Upload foi interrompido');
                    case UPLOAD_ERR_NO_FILE:
                        error_log('Nenhum arquivo foi enviado');
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        throw new Exception('Pasta temporária não encontrada');
                    case UPLOAD_ERR_CANT_WRITE:
                        throw new Exception('Falha ao escrever arquivo no disco');
                    case UPLOAD_ERR_EXTENSION:
                        throw new Exception('Upload bloqueado por extensão');
                    case UPLOAD_ERR_OK:
                        error_log('Upload realizado com sucesso');
                        break;
                    default:
                        throw new Exception('Erro desconhecido no upload: ' . $uploadError);
                }
            }
            
            if ($hasDocument) {
                error_log('Documento detectado para upload, validando...');
                
                try {
                    // Validar arquivo antes
                    $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];
                    $fileExtension = strtolower(pathinfo($_FILES['documento']['name'], PATHINFO_EXTENSION));
                    
                    if (!in_array($fileExtension, $allowedTypes)) {
                        throw new Exception('Tipo de arquivo não permitido. Tipos aceitos: ' . implode(', ', $allowedTypes));
                    }
                    
                    // Limite de tamanho: 5MB
                    $maxSize = 5 * 1024 * 1024; // 5MB
                    if ($_FILES['documento']['size'] > $maxSize) {
                        throw new Exception('Arquivo muito grande. Tamanho máximo: 5MB');
                    }
                    
                    error_log('Documento validado com sucesso, preparando para upload');
                    
                } catch (Exception $uploadError) {
                    error_log('Erro na validação do documento: ' . $uploadError->getMessage());
                    throw new Exception('Erro na validação do documento: ' . $uploadError->getMessage());
                }
            } else {
                error_log('Nenhum documento enviado ou documento com erro - campos anexo e nome_documento permanecerão NULL');
            }

            for ($i = 0; $i < $insertCount; $i++) {
                $currentDate = clone $baseDate;

                if ($i > 0 && $frequency) {
                    switch ($frequency) {
                        case 'day':
                            $currentDate->modify('+' . $i . ' day');
                            break;
                        case 'week':
                            $currentDate->modify('+' . $i . ' week');
                            break;
                        case 'biweek':
                            $currentDate->modify('+' . ($i * 2) . ' week');
                            break;
                        case 'month':
                            $currentDate->modify('+' . $i . ' month');
                            break;
                        case 'year':
                            $currentDate->modify('+' . $i . ' year');
                            break;
                    }
                }

                // Preparar os campos para inserção
                $fields = [
                    'obra_id',
                    'tipo',
                    'categoria',
                    'descricao',
                    'valor',
                    'data_lancamento',
                    'data_vencimento',
                    'status',
                    'forma_pagamento',
                    'observacoes',
                    'anexo',
                    'nome_documento',
                    'data_cadastro',
                    'usuario_id'
                ];
                
                $values = [
                    $data['obra_id'],
                    $data['tipo'],
                    $data['categoria'] ?? null,
                    $data['descricao'] ?? null,
                    $data['valor'],
                    $currentDate->format('Y-m-d'),
                    $data['data_vencimento'] ?? null,
                    $data['status'] ?? 'pendente',
                    $data['forma_pagamento'] ?? null,
                    $data['observacoes'] ?? null,
                    null, // anexo - será atualizado depois se houver documento
                    null, // nome_documento - será atualizado depois se houver documento
                    date('Y-m-d H:i:s'), // data_cadastro
                    $data['usuario_id']
                ];
                
                // Adicionar fornecedor_id se fornecido
                if (isset($data['fornecedor_id']) && !empty($data['fornecedor_id'])) {
                    $fields[] = 'fornecedor_id';
                    $values[] = $data['fornecedor_id'];
                }
                
                // Construir a query dinamicamente
                $placeholders = str_repeat('?,', count($values) - 1) . '?';
                $fieldsStr = implode(', ', $fields);
                
                $stmt = $conn->prepare(
                    "INSERT INTO lancamentos_financeiros ($fieldsStr) VALUES ($placeholders)"
                );
                
                $stmt->execute($values);
                
                // Processar documento apenas no primeiro lançamento e apenas se houver documento
                if ($i === 0 && $hasDocument) {
                    $lancamento_id = $conn->lastInsertId();
                    
                    try {
                        error_log('Processando upload do documento para lançamento ID: ' . $lancamento_id);
                        
                        // Processar upload do documento
                        $documentInfo = processDocumentUpload($lancamento_id, $_FILES['documento']);
                        
                        error_log('Documento processado, atualizando banco de dados...');
                        error_log('Caminho relativo: ' . $documentInfo['caminho_relativo']);
                        error_log('Nome original: ' . $documentInfo['nome_original']);
                        
                        // Atualizar o lançamento com as informações do documento
                        $updateStmt = $conn->prepare(
                            'UPDATE lancamentos_financeiros SET anexo = ?, nome_documento = ? WHERE id = ?'
                        );
                        $updateResult = $updateStmt->execute([
                            $documentInfo['caminho_relativo'],
                            $documentInfo['nome_original'],
                            $lancamento_id
                        ]);
                        
                        if ($updateResult) {
                            error_log('Documento salvo com sucesso no banco de dados: ' . $documentInfo['nome_original']);
                        } else {
                            error_log('Falha ao atualizar banco de dados com informações do documento');
                        }
                        
                    } catch (Exception $uploadError) {
                        error_log('Erro no upload do documento: ' . $uploadError->getMessage());
                        // Não falha a transação, mas registra o erro
                    }
                } else if ($i === 0) {
                    error_log('Primeiro lançamento criado sem documento - campos anexo e nome_documento permanecem NULL');
                }
            }

            $conn->commit();
            
            $message = $insertCount > 1 ? 
                "$insertCount lançamentos criados com sucesso" : 
                'Lançamento criado com sucesso';
                
            response(true, $message, ['id' => $conn->lastInsertId(), 'count' => $insertCount]);

        } catch (Exception $e) {
            $conn->rollBack();
            throw new Exception('Erro ao criar lançamento: ' . $e->getMessage());
        }
    } catch (Exception $e) {
        error_log('Erro no processamento do lançamento: ' . $e->getMessage());
        response(false, $e->getMessage());
    }
}

function handlePut($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id']) || !isset($data['obra_id']) || !isset($data['usuario_id'])) {
        response(false, 'ID do lançamento, obra e usuário são obrigatórios');
    }
    
    // Verificar se a obra pertence ao usuário
    $stmt = $conn->prepare('SELECT id FROM obras WHERE id = ? AND usuario_id = ?');
    $stmt->execute([$data['obra_id'], $data['usuario_id']]);
    if (!$stmt->fetch()) {
        response(false, 'Obra não encontrada ou não autorizada');
    }
    
    // Verificar se o lançamento existe
    $stmt = $conn->prepare('SELECT id FROM lancamentos_financeiros WHERE id = ? AND obra_id = ?');
    $stmt->execute([$data['id'], $data['obra_id']]);
    if (!$stmt->fetch()) {
        response(false, 'Lançamento não encontrado');
    }
    
    $fields = [];
    $values = [];
    
    $allowed_fields = [
        'tipo', 'categoria', 'descricao', 'valor', 'data_lancamento',
        'data_vencimento', 'status', 'forma_pagamento', 'observacoes', 'anexo', 'nome_documento', 'fornecedor_id'
    ];
    
    foreach ($allowed_fields as $field) {
        if (isset($data[$field])) {
            $fields[] = "$field = ?";
            $values[] = $data[$field];
        }
    }
    
    if (empty($fields)) {
        response(false, 'Nenhum campo para atualizar');
    }
    
    $fields[] = 'data_atualizacao = NOW()';
    $values[] = $data['id'];
    $values[] = $data['obra_id'];
    
    try {
        $sql = 'UPDATE lancamentos_financeiros SET ' . implode(', ', $fields) .
               ' WHERE id = ? AND obra_id = ?';
        
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute($values);
        
        if ($success) {
            response(true, 'Lançamento atualizado com sucesso');
        } else {
            response(false, 'Erro ao atualizar lançamento');
        }
    } catch (Exception $e) {
        response(false, 'Erro no banco de dados: ' . $e->getMessage());
    }
}

function handleDelete($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id']) || !isset($data['obra_id']) || !isset($data['usuario_id'])) {
        response(false, 'ID do lançamento, obra e usuário são obrigatórios');
    }
    
    // Verificar se a obra pertence ao usuário
    $stmt = $conn->prepare('SELECT id FROM obras WHERE id = ? AND usuario_id = ?');
    $stmt->execute([$data['obra_id'], $data['usuario_id']]);
    if (!$stmt->fetch()) {
        response(false, 'Obra não encontrada ou não autorizada');
    }
    
    try {
        // Primeiro, verificar se o lançamento tem documento anexado
        $stmt = $conn->prepare('SELECT anexo, nome_documento FROM lancamentos_financeiros WHERE id = ? AND obra_id = ?');
        $stmt->execute([$data['id'], $data['obra_id']]);
        $lancamento = $stmt->fetch();
        
        if (!$lancamento) {
            response(false, 'Lançamento não encontrado');
        }
        
        // Se existir documento anexado, deletar o arquivo físico
        if (!empty($lancamento['anexo']) && !empty($lancamento['nome_documento'])) {
            error_log('Documento anexado encontrado: ' . $lancamento['anexo']);
            
            // Construir o caminho completo do arquivo
            $filePath = __DIR__ . '/../ob/' . $lancamento['anexo'];
            error_log('Tentando deletar arquivo: ' . $filePath);
            
            if (file_exists($filePath)) {
                if (unlink($filePath)) {
                    error_log('Arquivo deletado com sucesso: ' . $filePath);
                } else {
                    error_log('Erro ao deletar arquivo: ' . $filePath);
                    // Não interrompe a exclusão do registro, apenas registra o erro
                }
            } else {
                error_log('Arquivo não encontrado no sistema de arquivos: ' . $filePath);
            }
            
            // Tentar deletar diretório se estiver vazio
            $directoryPath = dirname($filePath);
            if (is_dir($directoryPath) && count(scandir($directoryPath)) === 2) { // apenas . e ..
                rmdir($directoryPath);
                error_log('Diretório vazio removido: ' . $directoryPath);
            }
        } else {
            error_log('Nenhum documento anexado para deletar');
        }
        
        // Agora excluir o registro do banco de dados
        $stmt = $conn->prepare('DELETE FROM lancamentos_financeiros WHERE id = ? AND obra_id = ?');
        $success = $stmt->execute([$data['id'], $data['obra_id']]);
        
        if ($success) {
            $message = !empty($lancamento['anexo']) ? 
                'Lançamento e documento anexado excluídos com sucesso' : 
                'Lançamento excluído com sucesso';
            response(true, $message);
        } else {
            response(false, 'Erro ao excluir lançamento');
        }
    } catch (Exception $e) {
        error_log('Erro na exclusão: ' . $e->getMessage());
        response(false, 'Erro no banco de dados: ' . $e->getMessage());
    }
}