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

$method = $_SERVER['REQUEST_METHOD'];
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

function handleGet($conn) {
    $usuario_id = $_GET['usuario_id'] ?? null;
    $orcamento_id = $_GET['orcamento_id'] ?? null;
    
    if (!$usuario_id) {
        response(false, 'ID do usuário é obrigatório');
    }
    
    if ($orcamento_id) {
        // Buscar orçamento específico com seus itens
        try {
            // Buscar dados do orçamento
            $stmt = $conn->prepare('
                SELECT o.*, ob.nome_obra as obra_nome 
                FROM orcamentos o 
                LEFT JOIN obras ob ON o.obra_id = ob.id 
                WHERE o.id = ? AND o.usuario_id = ?
            ');
            $stmt->execute([$orcamento_id, $usuario_id]);
            $orcamento = $stmt->fetch();
            
            if (!$orcamento) {
                response(false, 'Orçamento não encontrado');
            }
            
            // Buscar itens do orçamento
            $stmt = $conn->prepare('SELECT * FROM orcamento_itens WHERE orcamento_id = ? ORDER BY ordem_exibicao');
            $stmt->execute([$orcamento_id]);
            $itens = $stmt->fetchAll();
            
            // Formatar dados para o frontend
            $formattedOrcamento = [
                'id' => $orcamento['id'],
                'cliente' => $orcamento['cliente'],
                'cpf_cnpj' => $orcamento['cpf_cnpj'],
                'telefone' => $orcamento['telefone'],
                'valor' => (float)$orcamento['valor'],
                'titulo' => $orcamento['titulo'],
                'escopo' => $orcamento['escopo'],
                'data' => $orcamento['data'],
                'validade' => $orcamento['validade'],
                'status' => $orcamento['status'],
                'observacoes' => $orcamento['observacoes'],
                'obra_id' => $orcamento['obra_id'],
                'obra_nome' => $orcamento['obra_nome'],
                'itens' => array_map(function($item) {
                    return [
                        'id' => $item['id'],
                        'nome' => $item['nome'],
                        'descricao' => $item['descricao'],
                        'quantidade' => (int)$item['quantidade'],
                        'valor' => (float)$item['valor_unitario']
                    ];
                }, $itens)
            ];
            
            response(true, 'Orçamento encontrado', $formattedOrcamento);
        } catch (Exception $e) {
            response(false, 'Erro ao buscar orçamento: ' . $e->getMessage());
        }
    } else {
        // Listar todos os orçamentos do usuário
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $order = $_GET['order'] ?? 'desc';
        
        try {
            $sql = '
                SELECT o.*, ob.nome_obra as obra_nome 
                FROM orcamentos o 
                LEFT JOIN obras ob ON o.obra_id = ob.id 
                WHERE o.usuario_id = ?
            ';
            $params = [$usuario_id];
            
            if ($search) {
                $sql .= ' AND (o.cliente LIKE ? OR o.observacoes LIKE ?)';
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
            }
            
            if ($status) {
                $sql .= ' AND o.status = ?';
                $params[] = $status;
            }
            
            $sql .= ' ORDER BY o.data ' . ($order === 'desc' ? 'DESC' : 'ASC');
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $orcamentos = $stmt->fetchAll();
            
            // Formatar dados para o frontend
            $formattedOrcamentos = array_map(function($orcamento) {
                return [
                    'id' => $orcamento['id'],
                    'cliente' => $orcamento['cliente'],
                    'cpf_cnpj' => $orcamento['cpf_cnpj'],
                    'telefone' => $orcamento['telefone'],
                    'valor' => (float)$orcamento['valor'],
                    'titulo' => $orcamento['titulo'],
                    'escopo' => $orcamento['escopo'],
                    'data' => $orcamento['data'],
                    'validade' => $orcamento['validade'],
                    'status' => $orcamento['status'],
                    'observacoes' => $orcamento['observacoes'],
                    'obra_id' => $orcamento['obra_id'],
                    'obra_nome' => $orcamento['obra_nome']
                ];
            }, $orcamentos);
            
            response(true, 'Orçamentos listados', $formattedOrcamentos);
        } catch (Exception $e) {
            response(false, 'Erro ao listar orçamentos: ' . $e->getMessage());
        }
    }
}

function handlePost($conn) {
    // Criar novo orçamento
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        response(false, 'Dados inválidos');
    }
    
    $usuario_id = $input['usuario_id'] ?? null;
    $cliente = $input['cliente'] ?? null;
    $cpf_cnpj = $input['cpf_cnpj'] ?? null;
    $telefone = $input['telefone'] ?? null;
    $valor = $input['valor'] ?? 0;
    $titulo = $input['titulo'] ?? null;
    $escopo = $input['escopo'] ?? null;
    $data = $input['data'] ?? date('Y-m-d');
    $validade = $input['validade'] ?? null;
    $status = $input['status'] ?? 'pending';
    $observacoes = $input['observacoes'] ?? null;
    $obra_id = $input['obra_id'] ?? null;
    $itens = $input['itens'] ?? [];
    
    if (!$usuario_id || !$cliente) {
        response(false, 'Campos obrigatórios: usuario_id, cliente');
    }
    
    try {
        // Iniciar transação para garantir consistência
        $conn->beginTransaction();
        
        // Inserir orçamento
        $stmt = $conn->prepare('
            INSERT INTO orcamentos (
                usuario_id, obra_id, cliente, cpf_cnpj, telefone, valor, titulo, escopo, data, validade, status, observacoes, data_cadastro
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ');
        
        $stmt->execute([
            $usuario_id, $obra_id, $cliente, $cpf_cnpj, $telefone, $valor, $titulo, $escopo, $data, $validade, $status, $observacoes
        ]);
        
        $orcamento_id = $conn->lastInsertId();
        
        // Inserir itens do orçamento
        if (!empty($itens)) {
            $stmt = $conn->prepare('
                INSERT INTO orcamento_itens (
                    orcamento_id, nome, descricao, quantidade, valor_unitario, ordem_exibicao, data_cadastro
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())
            ');
            
            foreach ($itens as $index => $item) {
                $stmt->execute([
                    $orcamento_id,
                    $item['nome'] ?? '',
                    $item['descricao'] ?? null,
                    $item['quantidade'] ?? 1,
                    $item['valor'] ?? 0,
                    $index
                ]);
            }
        }
        
        // Commit da transação
        $conn->commit();
        
        response(true, 'Orçamento cadastrado com sucesso', ['orcamento_id' => $orcamento_id]);
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollBack();
        response(false, 'Erro ao cadastrar orçamento: ' . $e->getMessage());
    }
}

function handlePut($conn) {
    // Atualizar orçamento existente
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        response(false, 'Dados inválidos');
    }
    
    $orcamento_id = $input['orcamento_id'] ?? null;
    $usuario_id = $input['usuario_id'] ?? null;
    
    if (!$orcamento_id || !$usuario_id) {
        response(false, 'ID do orçamento e usuário são obrigatórios');
    }
    
    // Verificar se o orçamento pertence ao usuário
    $stmt = $conn->prepare('SELECT id FROM orcamentos WHERE id = ? AND usuario_id = ?');
    $stmt->execute([$orcamento_id, $usuario_id]);
    
    if (!$stmt->fetch()) {
        response(false, 'Orçamento não encontrado ou não autorizado');
    }
    
    try {
        // Iniciar transação para garantir consistência
        $conn->beginTransaction();
        
        // Atualizar dados do orçamento
        $fields = [];
        $values = [];
        
        $allowedFields = [
            'cliente', 'cpf_cnpj', 'telefone', 'valor', 'titulo', 'escopo', 'data', 'validade', 'status', 'observacoes', 'obra_id'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $fields[] = "$field = ?";
                $values[] = $input[$field];
            }
        }
        
        if (!empty($fields)) {
            $fields[] = 'data_atualizacao = NOW()';
            $values[] = $orcamento_id;
            $values[] = $usuario_id;
            
            $sql = 'UPDATE orcamentos SET ' . implode(', ', $fields) . ' WHERE id = ? AND usuario_id = ?';
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($values);
        }
        
        // Atualizar itens do orçamento
        if (isset($input['itens'])) {
            // Excluir itens existentes
            $stmt = $conn->prepare('DELETE FROM orcamento_itens WHERE orcamento_id = ?');
            $stmt->execute([$orcamento_id]);
            
            // Inserir novos itens
            if (!empty($input['itens'])) {
                $stmt = $conn->prepare('
                    INSERT INTO orcamento_itens (
                        orcamento_id, nome, descricao, quantidade, valor_unitario, ordem_exibicao, data_cadastro
                    ) VALUES (?, ?, ?, ?, ?, ?, NOW())
                ');
                
                foreach ($input['itens'] as $index => $item) {
                    $stmt->execute([
                        $orcamento_id,
                        $item['nome'] ?? '',
                        $item['descricao'] ?? null,
                        $item['quantidade'] ?? 1,
                        $item['valor'] ?? 0,
                        $index
                    ]);
                }
            }
        }
        
        // Commit da transação
        $conn->commit();
        
        response(true, 'Orçamento atualizado com sucesso');
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollBack();
        response(false, 'Erro ao atualizar orçamento: ' . $e->getMessage());
    }
}

function handleDelete($conn) {
    // Excluir orçamento
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        response(false, 'Dados inválidos');
    }
    
    $orcamento_id = $input['orcamento_id'] ?? null;
    $usuario_id = $input['usuario_id'] ?? null;
    
    if (!$orcamento_id || !$usuario_id) {
        response(false, 'ID do orçamento e usuário são obrigatórios');
    }
    
    try {
        // Verificar se o orçamento existe e pertence ao usuário
        $stmt = $conn->prepare('SELECT id FROM orcamentos WHERE id = ? AND usuario_id = ?');
        $stmt->execute([$orcamento_id, $usuario_id]);
        
        if (!$stmt->fetch()) {
            response(false, 'Orçamento não encontrado ou não autorizado');
        }
        
        // Iniciar transação para garantir consistência
        $conn->beginTransaction();
        
        // Excluir itens do orçamento
        $stmt = $conn->prepare('DELETE FROM orcamento_itens WHERE orcamento_id = ?');
        $stmt->execute([$orcamento_id]);
        
        // Excluir orçamento
        $stmt = $conn->prepare('DELETE FROM orcamentos WHERE id = ? AND usuario_id = ?');
        $stmt->execute([$orcamento_id, $usuario_id]);
        
        // Commit da transação
        $conn->commit();
        
        response(true, 'Orçamento excluído com sucesso');
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollBack();
        response(false, 'Erro ao excluir orçamento: ' . $e->getMessage());
    }
}
?>