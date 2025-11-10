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
    $usuario_id = $_GET['usuario_id'] ?? null;
    $obra_id = $_GET['obra_id'] ?? null;
    
    if (!$usuario_id) {
        response(false, 'ID do usuário é obrigatório');
        return;
    }
    
    if ($obra_id) {
        // Buscar contadores de uma obra específica
        $counters = getObraCounters($conn, $obra_id, $usuario_id);
        response(true, 'Contadores da obra obtidos', $counters);
    } else {
        // Buscar contadores de todas as obras do usuário
        $allCounters = getAllObrasCounters($conn, $usuario_id);
        response(true, 'Contadores de todas as obras obtidos', $allCounters);
    }
}

function getObraCounters($conn, $obra_id, $usuario_id) {
    try {
        // Verificar se a obra pertence ao usuário
        $stmt = $conn->prepare('SELECT id FROM obras WHERE id = ? AND usuario_id = ?');
        $stmt->execute([$obra_id, $usuario_id]);
        
        if (!$stmt->fetch()) {
            return [
                'obra_id' => $obra_id,
                'reports' => 0,
                'photos' => 0,
                'videos' => 0,
                'documents' => 0,
                'error' => 'Obra não encontrada ou acesso não autorizado'
            ];
        }
        
        // Contar relatórios da obra
        $stmt = $conn->prepare('
            SELECT COUNT(*) as total 
            FROM relatorios_diarios 
            WHERE obra_id = ? AND usuario_id = ?
        ');
        $stmt->execute([$obra_id, $usuario_id]);
        $reports = $stmt->fetch()['total'] ?? 0;
        
        // Contar fotos da obra (através dos relatórios)
        // Baseado em relatorio_arquivo.php - tipo_arquivo = "imagem"
        $stmt = $conn->prepare('
            SELECT COUNT(*) as total 
            FROM relatorio_arquivos ra
            INNER JOIN relatorios_diarios rd ON ra.relatorio_id = rd.id
            WHERE rd.obra_id = ? 
            AND rd.usuario_id = ?
            AND ra.tipo_arquivo = "imagem"
        ');
        $stmt->execute([$obra_id, $usuario_id]);
        $photos = $stmt->fetch()['total'] ?? 0;
        
        // Contar vídeos da obra (através dos relatórios)
        // Baseado em relatorio_arquivo.php - tipo_arquivo = "video"
        $stmt = $conn->prepare('
            SELECT COUNT(*) as total 
            FROM relatorio_arquivos ra
            INNER JOIN relatorios_diarios rd ON ra.relatorio_id = rd.id
            WHERE rd.obra_id = ? 
            AND rd.usuario_id = ?
            AND ra.tipo_arquivo = "video"
        ');
        $stmt->execute([$obra_id, $usuario_id]);
        $videos = $stmt->fetch()['total'] ?? 0;
        
        // Contar documentos da obra (através dos relatórios)
        // Baseado em relatorio_documento.php - tabela relatorio_documentos
        $stmt = $conn->prepare('
            SELECT COUNT(*) as total 
            FROM relatorio_documentos rd_doc
            INNER JOIN relatorios_diarios rd ON rd_doc.relatorio_id = rd.id
            WHERE rd.obra_id = ? 
            AND rd.usuario_id = ?
        ');
        $stmt->execute([$obra_id, $usuario_id]);
        $documents = $stmt->fetch()['total'] ?? 0;
        
        return [
            'obra_id' => $obra_id,
            'reports' => (int)$reports,
            'photos' => (int)$photos,
            'videos' => (int)$videos,
            'documents' => (int)$documents
        ];
        
    } catch (Exception $e) {
        return [
            'obra_id' => $obra_id,
            'reports' => 0,
            'photos' => 0,
            'videos' => 0,
            'documents' => 0,
            'error' => $e->getMessage()
        ];
    }
}

function getAllObrasCounters($conn, $usuario_id) {
    try {
        // Buscar todas as obras do usuário
        $stmt = $conn->prepare('SELECT id FROM obras WHERE usuario_id = ?');
        $stmt->execute([$usuario_id]);
        $obras = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $allCounters = [];
        
        foreach ($obras as $obra_id) {
            $allCounters[$obra_id] = getObraCounters($conn, $obra_id, $usuario_id);
        }
        
        return $allCounters;
        
    } catch (Exception $e) {
        return [
            'error' => $e->getMessage()
        ];
    }
}

// Query alternativa mais eficiente para buscar todos os contadores de uma vez
function getAllObrasCountersOptimized($conn, $usuario_id) {
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
                'obra_id' => $result['obra_id'],
                'reports' => (int)$result['reports'],
                'photos' => (int)$result['photos'],
                'videos' => (int)$result['videos'],
                'documents' => (int)$result['documents']
            ];
        }
        
        return $counters;
        
    } catch (Exception $e) {
        return [
            'error' => $e->getMessage()
        ];
    }
}

?>