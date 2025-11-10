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
    default:
        response(false, 'Método não permitido');
}

function handleGet($conn) {
    $obra_id = $_GET['obra_id'] ?? null;
    $user_id = $_GET['user_id'] ?? null;
    
    if (!$obra_id || !$user_id) {
        response(false, 'obra_id e user_id são obrigatórios');
    }
    
    try {
        // Verificar se a obra pertence ao usuário
        $stmt = $conn->prepare('SELECT id FROM obras WHERE id = ? AND usuario_id = ?');
        $stmt->execute([$obra_id, $user_id]);
        
        if (!$stmt->fetch()) {
            response(false, 'Obra não encontrada ou acesso não autorizado');
        }
        
        // Buscar documentos da obra através dos relatórios
        $sql = '
            SELECT 
                rd_doc.id,
                rd_doc.nome_arquivo as nome,
                rd_doc.caminho_arquivo as url,
                rd_doc.data_criacao as data_upload,
                rd_doc.tamanho_arquivo as tamanho,
                rd.nome_relatorio,
                rd.data_relatorio
            FROM relatorio_documentos rd_doc
            INNER JOIN relatorios_diarios rd ON rd_doc.relatorio_id = rd.id
            WHERE rd.obra_id = ? 
            AND rd.usuario_id = ?
            ORDER BY rd_doc.data_criacao DESC
        ';
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$obra_id, $user_id]);
        $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatar URLs completas e extrair extensão do arquivo
        $documentosFormatados = array_map(function($documento) {
            $extensao = pathinfo($documento['nome'], PATHINFO_EXTENSION);
            return [
                'id' => $documento['id'],
                'nome' => $documento['nome'],
                'url' => 'https://gestaodeobrafacil.com/ob/' . $documento['url'],
                'data_upload' => $documento['data_upload'],
                'tipo' => $extensao ?: 'doc',
                'tamanho' => $documento['tamanho'],
                'relatorio' => $documento['nome_relatorio'],
                'data_relatorio' => $documento['data_relatorio']
            ];
        }, $documentos);
        
        response(true, 'Documentos carregados com sucesso', $documentosFormatados);
        
    } catch (Exception $e) {
        response(false, 'Erro ao carregar documentos: ' . $e->getMessage());
    }
}
?>
