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
        
        // Buscar vídeos da obra através dos relatórios
        $sql = '
            SELECT 
                ra.id,
                ra.nome_arquivo as nome,
                ra.descricao,
                ra.caminho_arquivo as url,
                ra.data_criacao as data_upload,
                ra.tamanho_arquivo as tamanho,
                rd.nome_relatorio,
                rd.data_relatorio
            FROM relatorio_arquivos ra
            INNER JOIN relatorios_diarios rd ON ra.relatorio_id = rd.id
            WHERE rd.obra_id = ? 
            AND rd.usuario_id = ?
            AND ra.tipo_arquivo = "video"
            ORDER BY ra.data_criacao DESC
        ';
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$obra_id, $user_id]);
        $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatar URLs completas
        $videosFormatados = array_map(function($video) {
            return [
                'id' => $video['id'],
                'nome' => $video['nome'],
                'descricao' => $video['descricao'] ?? '',
                'url' => 'https://gestaodeobrafacil.com/ob/' . $video['url'],
                'data_upload' => $video['data_upload'],
                'tamanho' => $video['tamanho'],
                'relatorio' => $video['nome_relatorio'],
                'data_relatorio' => $video['data_relatorio']
            ];
        }, $videos);
        
        response(true, 'Vídeos carregados com sucesso', $videosFormatados);
        
    } catch (Exception $e) {
        response(false, 'Erro ao carregar vídeos: ' . $e->getMessage());
    }
}
?>