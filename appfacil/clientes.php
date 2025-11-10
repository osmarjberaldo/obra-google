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

// Função para validar CPF
function validateCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) != 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
    
    // Validação do primeiro dígito
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += $cpf[$i] * (10 - $i);
    }
    $resto = $soma % 11;
    $dv1 = ($resto < 2) ? 0 : 11 - $resto;
    
    if ($cpf[9] != $dv1) {
        return false;
    }
    
    // Validação do segundo dígito
    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += $cpf[$i] * (11 - $i);
    }
    $resto = $soma % 11;
    $dv2 = ($resto < 2) ? 0 : 11 - $resto;
    
    return $cpf[10] == $dv2;
}

// Função para validar CNPJ
function validateCNPJ($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    
    if (strlen($cnpj) != 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
        return false;
    }
    
    // Validação do primeiro dígito
    $peso = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $soma = 0;
    for ($i = 0; $i < 12; $i++) {
        $soma += $cnpj[$i] * $peso[$i];
    }
    $resto = $soma % 11;
    $dv1 = ($resto < 2) ? 0 : 11 - $resto;
    
    if ($cnpj[12] != $dv1) {
        return false;
    }
    
    // Validação do segundo dígito
    $peso = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $soma = 0;
    for ($i = 0; $i < 13; $i++) {
        $soma += $cnpj[$i] * $peso[$i];
    }
    $resto = $soma % 11;
    $dv2 = ($resto < 2) ? 0 : 11 - $resto;
    
    return $cnpj[13] == $dv2;
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
    $cliente_id = $_GET['cliente_id'] ?? null;
    
    if ($cliente_id) {
        // Buscar cliente específico
        $stmt = $conn->prepare('SELECT id, usuario_id, nome, tipo_pessoa, cpf_cnpj, telefone, observacoes, email, senha, token, token_expiracao, data_cadastro, ultimo_acesso, CASE WHEN ativo = 1 THEN "ativo" ELSE "inativo" END AS status FROM clientes WHERE id = ? AND usuario_id = ?');
        $stmt->execute([$cliente_id, $usuario_id]);
        $cliente = $stmt->fetch();
        
        if ($cliente) {
            response(true, 'Cliente encontrado', $cliente);
        } else {
            response(false, 'Cliente não encontrado');
        }
    } else {
        // Listar todos os clientes do usuário
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $tipo_pessoa = $_GET['tipo_pessoa'] ?? '';
        
        $sql = 'SELECT id, usuario_id, nome, tipo_pessoa, cpf_cnpj, telefone, observacoes, email, senha, token, token_expiracao, data_cadastro, ultimo_acesso, CASE WHEN ativo = 1 THEN "ativo" ELSE "inativo" END AS status FROM clientes WHERE usuario_id = ?';
        $params = [$usuario_id];
        
        if ($search) {
            $sql .= ' AND (nome LIKE ? OR email LIKE ? OR cpf_cnpj LIKE ? OR telefone LIKE ?)';
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($status) {
            $ativo = $status === 'ativo' ? 1 : 0;
            $sql .= ' AND ativo = ?';
            $params[] = $ativo;
        }
        
        if ($tipo_pessoa) {
            $sql .= ' AND tipo_pessoa = ?';
            $params[] = $tipo_pessoa;
        }
        
        $sql .= ' ORDER BY nome ASC';
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $clientes = $stmt->fetchAll();
        
        // Calcular estatísticas
        $statsStmt = $conn->prepare('
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as ativos,
                SUM(CASE WHEN ativo = 0 THEN 1 ELSE 0 END) as inativos,
                SUM(CASE WHEN tipo_pessoa = "fisica" THEN 1 ELSE 0 END) as pessoas_fisicas,
                SUM(CASE WHEN tipo_pessoa = "juridica" THEN 1 ELSE 0 END) as pessoas_juridicas,
                COUNT(DISTINCT CASE WHEN data_cadastro >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN id END) as recentes
            FROM clientes 
            WHERE usuario_id = ?
        ');
        $statsStmt->execute([$usuario_id]);
        $stats = $statsStmt->fetch();
        
        // Retornar os clientes diretamente, como nos lembretes
        response(true, 'Clientes encontrados', $clientes);
    }
}

// Função para enviar e-mail de boas-vindas
function enviarEmailBoasVindas($nome, $email, $senha) {
    if (empty($email)) {
        return false; // Retorna falso se não houver e-mail
    }
    
    $assunto = 'Bem-vindo ao Gestor de Obra Fácil';
    
    $mensagem = '<html><body>';
    $mensagem .= '<h2>Olá, ' . htmlspecialchars($nome) . '!</h2>';
    $mensagem .= '<p>Bem-vindo ao <strong>Gestor de Obra Fácil</strong>!</p>';
    $mensagem .= '<p>Seu cadastro foi realizado com sucesso em nosso sistema.</p>';
    $mensagem .= '<p>Login: ' . htmlspecialchars($email) . '</p>';
    $mensagem .= '<p>Senha: ' . htmlspecialchars($senha) . '</p>';
    $mensagem .= '<p>Você pode acessar o painel do cliente através do link abaixo:</p>';
    $mensagem .= '<p><a href="https://gestorpainel.gestaodeobrafacil.com/clientes/">https://gestorpainel.gestaodeobrafacil.com/clientes/</a></p>';
    $mensagem .= '<p>Em caso de dúvidas, entre em contato conosco.</p>';
    $mensagem .= '<p>Atenciosamente,<br>Equipe Gestor de Obra Fácil</p>';
    $mensagem .= '</body></html>';
    
    // Cabeçalhos para envio de e-mail em HTML
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
    $headers .= 'From: Gestor de Obra Fácil <noreply@gestaodeobrafacil.com>' . "\r\n";
    
    // Tenta enviar o e-mail
    $enviado = mail($email, $assunto, $mensagem, $headers);
    
    // Registra o resultado do envio no log
    error_log('Tentativa de envio de e-mail para ' . $email . ': ' . ($enviado ? 'Sucesso' : 'Falha'));
    
    return $enviado;
}

function handlePost($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validação dos campos obrigatórios - apenas nome é obrigatório
    if (empty($data['usuario_id']) || empty($data['nome'])) {
        response(false, 'Campos obrigatórios não preenchidos: nome é obrigatório');
    }
    
    // Limpar dados
    $nome = trim($data['nome']);
    $tipo_pessoa = $data['tipo_pessoa'] ?? 'fisica';
    $cpf_cnpj = !empty($data['cpf_cnpj']) ? preg_replace('/[^0-9]/', '', $data['cpf_cnpj']) : '';
    $telefone = !empty($data['telefone']) ? preg_replace('/[^0-9]/', '', $data['telefone']) : '';
    $email = trim($data['email'] ?? '');
    $senha = trim($data['senha'] ?? '');
    $observacoes = trim($data['observacoes'] ?? '');
    $status = (isset($data['status']) && $data['status'] === 'ativo') ? 1 : 0;
    
    // Validações específicas - só valida se preenchido
    if (!empty($cpf_cnpj) && $tipo_pessoa === 'fisica' && !validateCPF($cpf_cnpj)) {
        response(false, 'CPF inválido');
    }
    
    if (!empty($cpf_cnpj) && $tipo_pessoa === 'juridica' && !validateCNPJ($cpf_cnpj)) {
        response(false, 'CNPJ inválido');
    }
    
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        response(false, 'E-mail inválido');
    }
    
    // Verificar se CPF/CNPJ já existe (apenas se preenchido)
    if (!empty($cpf_cnpj)) {
        $checkStmt = $conn->prepare("SELECT id FROM clientes WHERE cpf_cnpj = ? AND usuario_id = ?");
        $checkStmt->execute([$cpf_cnpj, $data['usuario_id']]);
        if ($checkStmt->fetch()) {
            response(false, 'CPF/CNPJ já cadastrado');
        }
    }
    
    try {
        // Hash da senha se fornecida
    $senhaHash = !empty($senha) ? password_hash($senha, PASSWORD_DEFAULT) : '';
    
    $stmt = $conn->prepare('INSERT INTO clientes 
            (usuario_id, nome, tipo_pessoa, cpf_cnpj, telefone, email, senha, observacoes, ativo, data_cadastro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
            
        $success = $stmt->execute([
            $data['usuario_id'],
            $nome,
            $tipo_pessoa,
            $cpf_cnpj,
            $telefone,
            $email,
            $senhaHash,
            $observacoes,
            $status
        ]);
        
        if ($success) {
            $clienteId = $conn->lastInsertId();
            
            // Enviar e-mail de boas-vindas se houver e-mail cadastrado
            $emailEnviado = false;
            if (!empty($email) && !empty($senha)) {
                $emailEnviado = enviarEmailBoasVindas($nome, $email, $senha);
                error_log('Envio de e-mail de boas-vindas para cliente ID ' . $clienteId . ' - ' . ($emailEnviado ? 'Sucesso' : 'Falha'));
            }
            
            response(true, 'Cliente criado com sucesso' . ($emailEnviado ? ' e e-mail de boas-vindas enviado' : ''), ['id' => $clienteId]);
        } else {
            response(false, 'Erro ao criar cliente');
        }
    } catch (PDOException $e) {
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}

function handlePut($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['id']) || empty($data['usuario_id'])) {
        response(false, 'ID do cliente e usuário são obrigatórios');
    }
    
    try {
        $fieldsToUpdate = [];
        $params = [];
        
        // Campos que podem ser atualizados
        $updatableFields = [
            'nome' => 'nome',
            'tipo_pessoa' => 'tipo_pessoa',
            'cpf_cnpj' => 'cpf_cnpj',
            'telefone' => 'telefone',
            'email' => 'email',
            'observacoes' => 'observacoes',
            'status' => 'ativo'
        ];
        
        // Tratar senha separadamente (só atualiza se fornecida)
        if (!empty($data['senha'])) {
            $fieldsToUpdate[] = 'senha = ?';
            $params[] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }
        
        foreach ($updatableFields as $field => $dbField) {
            if (array_key_exists($field, $data)) {
                $fieldsToUpdate[] = "$dbField = ?";
                // Converter status de string para inteiro
                if ($field === 'status') {
                    $params[] = ($data[$field] === 'ativo') ? 1 : 0;
                } else {
                    $params[] = $data[$field];
                }
            }
        }
        
        if (empty($fieldsToUpdate)) {
            response(false, 'Nenhum campo para atualizar');
        }
        
        $fieldsToUpdate[] = 'data_atualizacao = NOW()';
        
        $sql = 'UPDATE clientes SET ' . implode(', ', $fieldsToUpdate) . ' WHERE id = ? AND usuario_id = ?';
        $params[] = $data['id'];
        $params[] = $data['usuario_id'];
        
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute($params);
        
        if ($success) {
            response(true, 'Cliente atualizado com sucesso');
        } else {
            response(false, 'Erro ao atualizar cliente');
        }
    } catch (PDOException $e) {
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}

function handleDelete($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['id']) || empty($data['usuario_id'])) {
        response(false, 'ID do cliente e usuário são obrigatórios');
    }
    
    try {
        // Verificar se o cliente existe e pertence ao usuário
        $checkStmt = $conn->prepare("SELECT id FROM clientes WHERE id = ? AND usuario_id = ?");
        $checkStmt->execute([$data['id'], $data['usuario_id']]);
        
        if (!$checkStmt->fetch()) {
            response(false, 'Cliente não encontrado ou não autorizado');
        }
        
        // Excluir o cliente
        $stmt = $conn->prepare('DELETE FROM clientes WHERE id = ? AND usuario_id = ?');
        $success = $stmt->execute([$data['id'], $data['usuario_id']]);
        
        if ($success && $stmt->rowCount() > 0) {
            response(true, 'Cliente excluído com sucesso');
        } else {
            response(false, 'Erro ao excluir cliente');
        }
    } catch (PDOException $e) {
        error_log('Erro ao excluir cliente: ' . $e->getMessage());
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}

?>