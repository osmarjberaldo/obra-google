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

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


try {
    $conn = getDbConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $id = $_GET['id'] ?? null;
        $usuario_id = $_GET['usuario_id'] ?? null;
        
        // Log dos parâmetros recebidos
        error_log("Parâmetros recebidos - ID: $id, Usuário ID: $usuario_id");
        
        if (!$id || !$usuario_id) {
            sendJsonResponse(false, 'ID do relatório e usuário são obrigatórios', null, 400);
        }
        
        // Verifica se a tabela relatorios_diarios existe
        $tableCheck = $conn->query("SHOW TABLES LIKE 'relatorios_diarios'")->rowCount() > 0;
        if (!$tableCheck) {
            sendJsonResponse(false, 'Tabela de relatórios não encontrada', null, 500);
        }
        
        // Log da estrutura da tabela
        $structure = $conn->query("DESCRIBE relatorios_diarios")->fetchAll(PDO::FETCH_ASSOC);
        error_log("Estrutura da tabela relatorios_diarios: " . json_encode($structure));
        
        // Verifica se a tabela obras existe
        $obrasTableCheck = $conn->query("SHOW TABLES LIKE 'obras'")->rowCount() > 0;
        if ($obrasTableCheck) {
            $obrasStructure = $conn->query("DESCRIBE obras")->fetchAll(PDO::FETCH_ASSOC);
            error_log("Estrutura da tabela obras: " . json_encode($obrasStructure));
        } else {
            error_log("Tabela obras não encontrada");
        }

        // Verifica se a coluna obra_id existe na tabela relatorios_diarios
        $columnCheck = $conn->query("SHOW COLUMNS FROM relatorios_diarios LIKE 'obra_id'")->rowCount() > 0;
        
        // Busca o relatório com os dados da obra
        // Primeiro, vamos verificar as colunas da tabela obras
        $obrasColumns = $conn->query("SHOW COLUMNS FROM obras")->fetchAll(PDO::FETCH_COLUMN);
        error_log("Colunas da tabela obras: " . json_encode($obrasColumns));
        
        // Monta a consulta com as colunas explícitas para garantir que todas as datas sejam retornadas
        $sql = 'SELECT 
                    rd.id,
                    rd.nome_relatorio,
                    rd.data_relatorio,
                    rd.data_final,
                    CASE 
                        WHEN rd.status = 1 THEN "pendente"
                        WHEN rd.status = 2 THEN "concluido"
                        WHEN rd.status = 3 THEN "cancelado"
                        ELSE "desconhecido"
                    END as status,
                    rd.obra_id,
                    DATE_FORMAT(rd.data_cadastro, "%Y-%m-%d %H:%i:%s") as data_cadastro,
                    DATE_FORMAT(rd.data_atualizacao, "%Y-%m-%d %H:%i:%s") as data_atualizacao';
        
        // Adiciona as colunas da tabela obras se existirem
        if (in_array('nome_obra', $obrasColumns)) {
            $sql .= ', o.nome_obra as obra_nome';
        } elseif (in_array('nome', $obrasColumns)) {
            $sql .= ', o.nome as obra_nome';
        }
        
        if (in_array('cidade', $obrasColumns)) {
            $sql .= ', o.cidade as obra_cidade';
        }
        
        if (in_array('estado', $obrasColumns)) {
            $sql .= ', o.estado as obra_estado';
        }
        
        $sql .= ' FROM relatorios_diarios rd';
        $sql .= ' LEFT JOIN obras o ON rd.obra_id = o.id';
        $sql .= ' WHERE rd.id = ? AND rd.usuario_id = ?';
        
        error_log("Consulta SQL gerada: $sql");
        
        error_log("Executando consulta: $sql");
        $stmt = $conn->prepare($sql);
        
        $stmt->execute([$id, $usuario_id]);
        $relatorio = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Log do resultado da consulta
        error_log("Resultado da consulta: " . json_encode($relatorio));
        
        // Log específico das datas e status
        if ($relatorio) {
            error_log("Dados do relatório:");
            error_log("- Status: " . ($relatorio['status'] ?? 'não encontrado'));
            error_log("Datas:");
            error_log("  - data_relatorio: " . ($relatorio['data_relatorio'] ?? 'não encontrada'));
            error_log("  - data_final: " . ($relatorio['data_final'] ?? 'não encontrada'));
            error_log("  - data_cadastro: " . ($relatorio['data_cadastro'] ?? 'não encontrada'));
            error_log("  - data_atualizacao: " . ($relatorio['data_atualizacao'] ?? 'não encontrada'));
        }
        
        if (!$relatorio) {
            error_log("Relatório não encontrado para ID: $id e usuário: $usuario_id");
            sendJsonResponse(false, 'Relatório não encontrado', null, 404);
        }
        
        // Log dos campos específicos para depuração
        error_log("Campos do relatório:");
        error_log("- ID: " . ($relatorio['id'] ?? 'não definido'));
        error_log("- Nome: " . ($relatorio['nome_relatorio'] ?? 'não definido'));
        error_log("- Obra ID: " . ($relatorio['obra_id'] ?? 'não definido'));
        error_log("- Obra Nome: " . ($relatorio['obra_nome'] ?? 'não definido'));
        error_log("- Obra Cidade: " . ($relatorio['obra_cidade'] ?? 'não definido'));
        error_log("- Obra Estado: " . ($relatorio['obra_estado'] ?? 'não definido'));
        
        sendJsonResponse(true, 'Relatório encontrado', $relatorio);
    } else {
        sendJsonResponse(false, 'Método não permitido', null, 405);
    }
} catch (Exception $e) {
    error_log('Erro em relatorios_diarios_view.php: ' . $e->getMessage());
    sendJsonResponse(false, 'Erro interno do servidor: ' . $e->getMessage(), null, 500);
}
