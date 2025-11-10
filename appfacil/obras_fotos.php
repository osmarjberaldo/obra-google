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
    // Log para debug
    error_log("Resposta da API: success=" . json_encode($success) . ", message=" . $message . ", data_count=" . (is_array($data) ? count($data) : 'null'));
    
    if (is_array($data)) {
        foreach ($data as $index => $foto) {
            error_log("Foto $index: " . json_encode($foto));
        }
    }
    
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
    
    error_log("Requisição GET: obra_id=$obra_id, user_id=$user_id");
    
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
        
        // Buscar fotos da obra através dos relatórios
        $sql = '
            SELECT 
                ra.id,
                ra.nome_arquivo as nome,
                ra.descricao,
                ra.caminho_arquivo as url,
                ra.data_criacao as data_upload,
                rd.nome_relatorio,
                rd.data_relatorio
            FROM relatorio_arquivos ra
            INNER JOIN relatorios_diarios rd ON ra.relatorio_id = rd.id
            WHERE rd.obra_id = ? 
            AND rd.usuario_id = ?
            AND ra.tipo_arquivo = "imagem"
            ORDER BY ra.data_criacao DESC
        ';
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$obra_id, $user_id]);
        $fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Fotos encontradas: " . count($fotos));
        
        // Formatar URLs completas
        $fotosFormatadas = array_map(function($foto) {
            error_log("Processando foto: " . json_encode($foto));
            
            // Garantir que a descrição nunca seja null
            $descricao = isset($foto['descricao']) ? $foto['descricao'] : '';
            
            $fotoFormatada = [
                'id' => $foto['id'],
                'nome' => $foto['nome'],
                'descricao' => $descricao,
                'url' => 'https://gestaodeobrafacil.com/ob/' . $foto['url'],
                'data_upload' => $foto['data_upload'],
                'relatorio' => $foto['nome_relatorio'],
                'data_relatorio' => $foto['data_relatorio']
            ];
            
            error_log("Foto formatada: " . json_encode($fotoFormatada));
            
            return $fotoFormatada;
        }, $fotos);
        
        response(true, 'Fotos carregadas com sucesso', $fotosFormatadas);
        
    } catch (Exception $e) {
        error_log("Erro ao carregar fotos: " . $e->getMessage());
        response(false, 'Erro ao carregar fotos: ' . $e->getMessage());
    }
}
?>