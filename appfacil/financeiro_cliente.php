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
error_log('=== Nova requisição para financeiro_cliente.php ===');
error_log('Método: ' . $_SERVER['REQUEST_METHOD']);

require_once __DIR__ . '/config/pdo.php';

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
    $cliente_id = $_GET['cliente_id'] ?? null;

    if (!$cliente_id) {
        response(false, 'ID do cliente é obrigatório');
    }

    // Parâmetros de filtro
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;
    $tipo = $_GET['tipo'] ?? null; // 'receita' ou 'despesa'

    // Log dos parâmetros recebidos
    error_log('Parâmetros recebidos - cliente_id: ' . $cliente_id . ', start_date: ' . $start_date . ', end_date: ' . $end_date . ', tipo: ' . $tipo);

    // Buscar o usuario_id do cliente
    $stmt = $conn->prepare('SELECT usuario_id FROM clientes WHERE id = ?');
    $stmt->execute([$cliente_id]);
    $cliente = $stmt->fetch();

    if (!$cliente) {
        response(false, 'Cliente não encontrado');
    }

    $usuario_id = $cliente['usuario_id'];
    error_log('Usuario ID do cliente: ' . $usuario_id);

    // Primeiro, buscar relatórios do cliente para obter os IDs das obras
    $stmt = $conn->prepare('SELECT DISTINCT obra_id FROM relatorios_diarios WHERE id_cliente = ?');
    $stmt->execute([$cliente_id]);
    $obrasCliente = $stmt->fetchAll(PDO::FETCH_COLUMN);

    error_log('Obras encontradas via relatórios do cliente: ' . implode(', ', $obrasCliente));

    if (empty($obrasCliente)) {
        error_log('Nenhuma obra encontrada via relatórios. Verificando via usuario_id...');
        // Como fallback, buscar todas as obras associadas ao usuário do cliente
        $stmt = $conn->prepare('SELECT id FROM obras WHERE usuario_id = ?');
        $stmt->execute([$usuario_id]);
        $obrasCliente = $stmt->fetchAll(PDO::FETCH_COLUMN);
        error_log('Obras encontradas via usuario_id: ' . implode(', ', $obrasCliente));
    }

    if (empty($obrasCliente)) {
        error_log('Nenhuma obra encontrada. Verificando relatórios e lançamentos diretamente...');
        // Debug: Verificar se há relatórios para o cliente
        $stmt = $conn->prepare('SELECT COUNT(*) as count FROM relatorios_diarios WHERE id_cliente = ?');
        $stmt->execute([$cliente_id]);
        $relatoriosCount = $stmt->fetch()['count'];
        error_log('Número de relatórios para cliente_id ' . $cliente_id . ': ' . $relatoriosCount);

        // Debug: Verificar se há lançamentos para obra_id 41
        $stmt = $conn->prepare('SELECT COUNT(*) as count FROM lancamentos_financeiros WHERE obra_id = 41');
        $stmt->execute();
        $lancamentosCount = $stmt->fetch()['count'];
        error_log('Número de lançamentos para obra_id 41: ' . $lancamentosCount);

        response(true, 'Nenhuma obra encontrada para este cliente', [
            'lancamentos' => [],
            'totais' => [
                'receitas' => '0.00',
                'despesas' => '0.00',
                'saldo' => '0.00'
            ]
        ]);
    }

    // Para debugging, adicionar uma consulta direta se necessário
    if (isset($_GET['debug_obra_id'])) {
        $debug_obra_id = $_GET['debug_obra_id'];
        $stmt = $conn->prepare('SELECT * FROM lancamentos_financeiros WHERE obra_id = ? LIMIT 5');
        $stmt->execute([$debug_obra_id]);
        $debug_data = $stmt->fetchAll();
        error_log('Dados de debug para obra_id ' . $debug_obra_id . ': ' . json_encode($debug_data));
    }

    // Buscar lançamentos financeiros usando estrutura similar à consulta direta
    $sql = "SELECT lf.*, o.nome_obra FROM lancamentos_financeiros lf
            INNER JOIN relatorios_diarios rd ON rd.obra_id = lf.obra_id AND rd.id_cliente = ?
            LEFT JOIN obras o ON o.id = lf.obra_id";

    $params = [$cliente_id];

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

        // Debug: Log first few lancamentos if any
        if (count($lancamentos) > 0) {
            error_log('Primeiros lançamentos: ' . json_encode(array_slice($lancamentos, 0, 3)));
        } else {
            error_log('Nenhum lançamento encontrado para cliente_id: ' . $cliente_id);
        }

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

            // Verificar se a data de vencimento é válida
            $data_vencimento = null;
            if (!empty($lancamento['data_vencimento']) && $lancamento['data_vencimento'] !== '0000-00-00' && strtotime($lancamento['data_vencimento'])) {
                $data_vencimento = date('d/m/Y', strtotime($lancamento['data_vencimento']));
            }

            return [
                'id' => $lancamento['id'],
                'obra_id' => $lancamento['obra_id'],
                'obra_nome' => $lancamento['nome_obra'] ?? 'N/A',
                'tipo' => $lancamento['tipo'],
                'categoria' => $lancamento['categoria'],
                'descricao' => $lancamento['descricao'],
                'valor' => number_format($lancamento['valor'], 2, '.', ''),
                'data_lancamento' => date('d/m/Y', strtotime($lancamento['data_lancamento'])),
                'data_vencimento' => $data_vencimento,
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
        error_log('Erro na consulta: ' . $e->getMessage());
        response(false, 'Erro ao buscar lançamentos: ' . $e->getMessage());
    }
}
?>
