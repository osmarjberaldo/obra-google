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

function response($success, $message, $data = null) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data], JSON_PRETTY_PRINT);
    exit;
}

function uploadImage($imageFile) {
    if (!$imageFile || $imageFile['error'] !== UPLOAD_ERR_OK) {
        error_log('Upload error: ' . ($imageFile['error'] ?? 'File not provided'));
        return null;
    }
    
    // Validar tamanho do arquivo (max 10MB)
    $maxSize = 10 * 1024 * 1024; // 10MB
    if ($imageFile['size'] > $maxSize) {
        error_log('File too large: ' . $imageFile['size']);
        return null;
    }
    
    // Validar tipo de arquivo
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $imageFile['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        error_log('Invalid file type: ' . $mimeType);
        return null;
    }
    
    // Define o diretório base para uploads (subindo um nível a partir do diretório atual)
    $baseDir = __DIR__ . '/../ob';
    $uploadDir = $baseDir . '/uploads/obras/';
    
    // Cria o diretório se não existir
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log('Failed to create upload directory: ' . $uploadDir);
            return null;
        }
    }
    
    // Gera um nome único para o arquivo
    $extension = strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));
    $filename = 'obra_' . time() . '_' . uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . $filename;
    
    // Move o arquivo para o diretório de uploads
    if (move_uploaded_file($imageFile['tmp_name'], $uploadPath)) {
        // Retorna apenas o caminho relativo para armazenar no banco de dados
        error_log('File uploaded successfully: ' . $filename);
        return 'uploads/obras/' . $filename;
    }
    
    error_log('Failed to move uploaded file to: ' . $uploadPath);
    return null;
}

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDbConnection();

switch ($method) {
    case 'GET':
        handleGet($conn);
        break;
    case 'POST':
        // Verificar se é uma atualização com upload (method spoofing)
        if (isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
            handlePutWithUpload($conn);
        } else {
            handlePost($conn);
        }
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

function handleGet($conn) {
    $usuario_id = $_GET['usuario_id'] ?? null;
    $obra_id = $_GET['obra_id'] ?? null;
    
    if ($obra_id) {
        // Buscar obra específica
        $stmt = $conn->prepare('SELECT * FROM obras WHERE id = ? AND usuario_id = ?');
        $stmt->execute([$obra_id, $usuario_id]);
        $obra = $stmt->fetch();
        
        if ($obra) {
            // Aplicar a mesma lógica de imagem padrão
            $defaultImage = 'https://gestaodeobrafacil.com/ob/assets/default-obra.svg';
            if (!$obra['capa']) {
                $obra['image'] = $defaultImage;
            } else {
                $obra['image'] = 'https://gestaodeobrafacil.com/ob/' . $obra['capa'];
            }
            
            response(true, 'Obra encontrada', $obra);
        } else {
            response(false, 'Obra não encontrada');
        }
    } else {
        // Listar todas as obras do usuário
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $order = $_GET['order'] ?? 'asc';
        
        $sql = 'SELECT * FROM obras WHERE usuario_id = ?';
        $params = [$usuario_id];
        
        if ($search) {
            $sql .= ' AND nome_obra LIKE ?';
            $params[] = '%' . $search . '%';
        }
        
        if ($status) {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }
        
        $sql .= ' ORDER BY nome_obra ' . ($order === 'desc' ? 'DESC' : 'ASC');
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $obras = $stmt->fetchAll();
        
        // Buscar contadores para todas as obras do usuário
        $contadores = getObrasCounters($conn, $usuario_id);
        
        // Formatar dados para o frontend
        $formattedObras = array_map(function($obra) use ($contadores) {
            $obra_id = $obra['id'];
            $counters = $contadores[$obra_id] ?? ['reports' => 0, 'photos' => 0, 'videos' => 0, 'documents' => 0];
            
            // Definir imagem padrão se não houver capa
            $defaultImage = 'https://gestaodeobrafacil.com/ob/assets/default-obra.svg';
            $imageUrl = $obra['capa'] ? 'https://gestaodeobrafacil.com/ob/' . $obra['capa'] : $defaultImage;
            
            return [
                'id' => $obra['id'],
                'name' => $obra['nome_obra'],
                'owner' => $obra['razao_social'] ?: 'Cliente não informado',
                'category' => 'Construção Civil',
                'status' => $obra['status'],
                'startDate' => date('d/m/Y', strtotime($obra['data_inicio'])),
                'endDate' => $obra['previsao_termino'] ? date('d/m/Y', strtotime($obra['previsao_termino'])) : '-',
                'image' => $imageUrl,
                'reports' => $counters['reports'],
                'photos' => $counters['photos'],
                'videos' => $counters['videos'],
                'documents' => $counters['documents']
            ];
        }, $obras);
        
        response(true, 'Obras listadas', $formattedObras);
    }
}

function handlePost($conn) {
    // Criar nova obra
    $usuario_id = $_POST['usuario_id'] ?? null;
    $nome_obra = $_POST['nome_obra'] ?? null;
    $status = $_POST['status'] ?? 'planning';
    $data_inicio = $_POST['data_inicio'] ?? date('Y-m-d'); // Set current date as default
    $previsao_termino = $_POST['previsao_termino'] ?? null;
    
    if (!$usuario_id || !$nome_obra) {
        response(false, 'Campos obrigatórios: usuario_id, nome_obra');
    }
    
    // Dados do cliente (opcionais)
    $tipo_pessoa = $_POST['tipo_pessoa'] ?? null;
    $cpf_cnpj = $_POST['cpf_cnpj'] ?? null;
    $razao_social = $_POST['razao_social'] ?? null;
    $telefone_cliente = $_POST['telefone_cliente'] ?? null;
    $responsavel = $_POST['responsavel'] ?? null;
    
    // Endereço (opcional)
    $cep = $_POST['cep'] ?? null;
    $endereco = $_POST['endereco'] ?? null;
    $numero = $_POST['numero'] ?? null;
    $complemento = $_POST['complemento'] ?? null;
    $bairro = $_POST['bairro'] ?? null;
    $cidade = $_POST['cidade'] ?? null;
    $estado = $_POST['estado'] ?? null;
    
    // Detalhes (opcional)
    $escopo = $_POST['escopo'] ?? null;
    
    // Upload de imagem
    $imagePath = null;
    if (isset($_FILES['image'])) {
        $imagePath = uploadImage($_FILES['image']);
    }
    
    try {
        $stmt = $conn->prepare('
            INSERT INTO obras (
                usuario_id, tipo_pessoa, cnpj_cpf, razao_social, telefone_cliente, 
                nome_obra, responsavel, status, data_inicio, previsao_termino,
                cep, endereco, numero, complemento, bairro, cidade, estado,
                escopo, capa, data_cadastro
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ');
        
        $success = $stmt->execute([
            $usuario_id, $tipo_pessoa, $cpf_cnpj, $nome_obra, $telefone_cliente,
            $nome_obra, $nome_obra, $status, $data_inicio, $previsao_termino,
            $cep, $endereco, $numero, $complemento, $bairro, $cidade, $estado,
            $escopo, $imagePath
        ]);
        
        if ($success) {
            $obra_id = $conn->lastInsertId();
            response(true, 'Obra cadastrada com sucesso', ['obra_id' => $obra_id]);
        } else {
            response(false, 'Erro ao cadastrar obra');
        }
    } catch (Exception $e) {
        response(false, 'Erro no banco de dados: ' . $e->getMessage());
    }
}

function handlePutWithUpload($conn) {
    // Atualizar obra existente com upload de arquivo
    $obra_id = $_POST['obra_id'] ?? null;
    $usuario_id = $_POST['usuario_id'] ?? null;
    
    if (!$obra_id || !$usuario_id) {
        response(false, 'ID da obra e usuário são obrigatórios');
    }
    
    // Verificar se a obra pertence ao usuário
    $stmt = $conn->prepare('SELECT id, capa FROM obras WHERE id = ? AND usuario_id = ?');
    $stmt->execute([$obra_id, $usuario_id]);
    $currentWork = $stmt->fetch();
    
    if (!$currentWork) {
        response(false, 'Obra não encontrada ou não autorizada');
    }
    
    // Construir query de atualização dinamicamente
    $fields = [];
    $values = [];
    
    $allowedFields = [
        'nome_obra', 'data_inicio', 'previsao_termino', 'status',
        'tipo_pessoa', 'cnpj_cpf', 'razao_social', 'telefone_cliente', 'responsavel',
        'cep', 'endereco', 'numero', 'complemento', 'bairro', 'cidade', 'estado',
        'escopo'
    ];
    
    foreach ($allowedFields as $field) {
        if (isset($_POST[$field]) && $_POST[$field] !== '') {
            $fields[] = "$field = ?";
            $values[] = $_POST[$field];
        }
    }
    
    // Processar upload de nova imagem se fornecida
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        error_log('Processing image upload for obra_id: ' . $obra_id);
        $imagePath = uploadImage($_FILES['image']);
        
        if ($imagePath) {
            error_log('Image uploaded successfully: ' . $imagePath);
            // Remover imagem antiga se existir
            if ($currentWork['capa'] && file_exists(__DIR__ . '/../ob/' . $currentWork['capa'])) {
                unlink(__DIR__ . '/../ob/' . $currentWork['capa']);
                error_log('Old image removed: ' . $currentWork['capa']);
            }
            
            $fields[] = 'capa = ?';
            $values[] = $imagePath;
        } else {
            error_log('Failed to upload image for obra_id: ' . $obra_id);
            response(false, 'Erro ao fazer upload da imagem');
        }
    } else {
        if (isset($_FILES['image'])) {
            error_log('Image upload error: ' . $_FILES['image']['error']);
        }
    }
    
    if (empty($fields)) {
        response(false, 'Nenhum campo para atualizar');
    }
    
    $fields[] = 'data_atualizacao = NOW()';
    $values[] = $obra_id;
    $values[] = $usuario_id;
    
    $sql = 'UPDATE obras SET ' . implode(', ', $fields) . ' WHERE id = ? AND usuario_id = ?';
    
    try {
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute($values);
        
        if ($success) {
            response(true, 'Obra atualizada com sucesso');
        } else {
            response(false, 'Erro ao atualizar obra');
        }
    } catch (Exception $e) {
        response(false, 'Erro no banco de dados: ' . $e->getMessage());
    }
}

function handlePut($conn) {
    // Atualizar obra existente (sem upload de arquivo)
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        response(false, 'Dados inválidos');
    }
    
    $obra_id = $input['obra_id'] ?? null;
    $usuario_id = $input['usuario_id'] ?? null;
    
    if (!$obra_id || !$usuario_id) {
        response(false, 'ID da obra e usuário são obrigatórios');
    }
    
    // Verificar se a obra pertence ao usuário
    $stmt = $conn->prepare('SELECT id FROM obras WHERE id = ? AND usuario_id = ?');
    $stmt->execute([$obra_id, $usuario_id]);
    
    if (!$stmt->fetch()) {
        response(false, 'Obra não encontrada ou não autorizada');
    }
    
    // Construir query de atualização dinamicamente
    $fields = [];
    $values = [];
    
    $allowedFields = [
        'nome_obra', 'data_inicio', 'previsao_termino', 'status',
        'tipo_pessoa', 'cnpj_cpf', 'razao_social', 'telefone_cliente', 'responsavel',
        'cep', 'endereco', 'numero', 'complemento', 'bairro', 'cidade', 'estado',
        'escopo'
    ];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $fields[] = "$field = ?";
            $values[] = $input[$field];
        }
    }
    
    if (empty($fields)) {
        response(false, 'Nenhum campo para atualizar');
    }
    
    $fields[] = 'data_atualizacao = NOW()';
    $values[] = $obra_id;
    $values[] = $usuario_id;
    
    $sql = 'UPDATE obras SET ' . implode(', ', $fields) . ' WHERE id = ? AND usuario_id = ?';
    
    try {
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute($values);
        
        if ($success) {
            response(true, 'Obra atualizada com sucesso');
        } else {
            response(false, 'Erro ao atualizar obra');
        }
    } catch (Exception $e) {
        response(false, 'Erro no banco de dados: ' . $e->getMessage());
    }
}

function handleDelete($conn) {
    // Excluir obra
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        response(false, 'Dados inválidos');
    }
    
    $obra_id = $input['obra_id'] ?? null;
    $usuario_id = $input['usuario_id'] ?? null;
    
    if (!$obra_id || !$usuario_id) {
        response(false, 'ID da obra e usuário são obrigatórios');
    }
    
    try {
        // Verificar se a obra existe e pertence ao usuário
        $stmt = $conn->prepare('SELECT capa FROM obras WHERE id = ? AND usuario_id = ?');
        $stmt->execute([$obra_id, $usuario_id]);
        $obra = $stmt->fetch();
        
        if (!$obra) {
            response(false, 'Obra não encontrada ou não autorizada');
        }
        
        // Iniciar transação para garantir consistência
        $conn->beginTransaction();
        
        try {
            // 1. Excluir todos os lançamentos financeiros da obra
            deleteFinancialData($conn, $obra_id, $usuario_id);
            
            // 2. Excluir todos os relatórios da obra (em cascata)
            deleteReportsData($conn, $obra_id, $usuario_id);
            
            // 3. Excluir lembretes vinculados à obra
            $stmt = $conn->prepare('DELETE FROM lembretes WHERE obra_id = ? AND usuario_id = ?');
            $stmt->execute([$obra_id, $usuario_id]);
            
            // 4. Excluir imagem da obra se existir
            if ($obra['capa'] && file_exists(__DIR__ . '/../ob/' . $obra['capa'])) {
                unlink(__DIR__ . '/../ob/' . $obra['capa']);
            }
            
            // 5. Excluir obra do banco
            $stmt = $conn->prepare('DELETE FROM obras WHERE id = ? AND usuario_id = ?');
            $success = $stmt->execute([$obra_id, $usuario_id]);
            
            if (!$success) {
                throw new Exception('Erro ao excluir obra do banco de dados');
            }
            
            // Commit da transação
            $conn->commit();
            
            response(true, 'Obra excluída com sucesso. Todos os dados financeiros, relatórios e arquivos associados foram removidos.');
            
        } catch (Exception $e) {
            // Rollback em caso de erro
            $conn->rollBack();
            throw new Exception('Erro ao excluir obra: ' . $e->getMessage());
        }
        
    } catch (Exception $e) {
        response(false, 'Erro: ' . $e->getMessage());
    }
}

// Função para excluir dados financeiros da obra
function deleteFinancialData($conn, $obra_id, $usuario_id) {
    try {
        // Excluir anexos de lançamentos financeiros se existirem
        $stmt = $conn->prepare('SELECT anexo FROM lancamentos_financeiros WHERE obra_id = ? AND usuario_id = ?');
        $stmt->execute([$obra_id, $usuario_id]);
        $anexos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($anexos as $anexo) {
            if ($anexo['anexo'] && file_exists(__DIR__ . '/../ob/' . $anexo['anexo'])) {
                unlink(__DIR__ . '/../ob/' . $anexo['anexo']);
            }
        }
        
        // Excluir todos os lançamentos financeiros da obra
        $stmt = $conn->prepare('DELETE FROM lancamentos_financeiros WHERE obra_id = ? AND usuario_id = ?');
        $stmt->execute([$obra_id, $usuario_id]);
        
    } catch (Exception $e) {
        throw new Exception('Erro ao excluir dados financeiros: ' . $e->getMessage());
    }
}

// Função para excluir dados de relatórios da obra
function deleteReportsData($conn, $obra_id, $usuario_id) {
    try {
        // Buscar todos os relatórios da obra
        $stmt = $conn->prepare('SELECT id FROM relatorios_diarios WHERE obra_id = ? AND usuario_id = ?');
        $stmt->execute([$obra_id, $usuario_id]);
        $relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Para cada relatório, excluir em cascata
        foreach ($relatorios as $relatorio) {
            $relatorio_id = $relatorio['id'];
            
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
                if ($documento['caminho_arquivo'] && file_exists(__DIR__ . '/../ob/' . $documento['caminho_arquivo'])) {
                    unlink(__DIR__ . '/../ob/' . $documento['caminho_arquivo']);
                }
            }
            
            $stmt = $conn->prepare("DELETE FROM relatorio_documentos WHERE relatorio_id = ?");
            $stmt->execute([$relatorio_id]);
            
            // 8. Excluir arquivos de mídia do relatório e arquivos físicos
            $stmt = $conn->prepare("SELECT caminho_arquivo FROM relatorio_arquivos WHERE relatorio_id = ?");
            $stmt->execute([$relatorio_id]);
            $arquivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($arquivos as $arquivo) {
                if ($arquivo['caminho_arquivo'] && file_exists(__DIR__ . '/../ob/' . $arquivo['caminho_arquivo'])) {
                    unlink(__DIR__ . '/../ob/' . $arquivo['caminho_arquivo']);
                }
            }
            
            $stmt = $conn->prepare("DELETE FROM relatorio_arquivos WHERE relatorio_id = ?");
            $stmt->execute([$relatorio_id]);
        }
        
        // 9. Excluir todos os relatórios da obra
        $stmt = $conn->prepare('DELETE FROM relatorios_diarios WHERE obra_id = ? AND usuario_id = ?');
        $stmt->execute([$obra_id, $usuario_id]);
        
    } catch (Exception $e) {
        throw new Exception('Erro ao excluir dados de relatórios: ' . $e->getMessage());
    }
}

// Função para buscar contadores de todas as obras do usuário
function getObrasCounters($conn, $usuario_id) {
    try {
        $sql = '
            SELECT 
                o.id as obra_id,
                COUNT(DISTINCT rd.id) as reports,
                COUNT(DISTINCT CASE 
                    WHEN ra.tipo_arquivo = "imagem" 
                    THEN ra.id 
                END) as photos,
                COUNT(DISTINCT CASE 
                    WHEN ra.tipo_arquivo = "video" 
                    THEN ra.id 
                END) as videos,
                COUNT(DISTINCT rd_doc.id) as documents
            FROM obras o
            LEFT JOIN relatorios_diarios rd ON o.id = rd.obra_id AND rd.usuario_id = o.usuario_id
            LEFT JOIN relatorio_arquivos ra ON rd.id = ra.relatorio_id
            LEFT JOIN relatorio_documentos rd_doc ON rd.id = rd_doc.relatorio_id
            WHERE o.usuario_id = ?
            GROUP BY o.id
        ';
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$usuario_id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $counters = [];
        foreach ($results as $result) {
            $counters[$result['obra_id']] = [
                'reports' => (int)$result['reports'],
                'photos' => (int)$result['photos'],
                'videos' => (int)$result['videos'],
                'documents' => (int)$result['documents']
            ];
        }
        
        return $counters;
        
    } catch (Exception $e) {
        // Em caso de erro, retornar array vazio para não quebrar a aplicação
        return [];
    }
}
?>
