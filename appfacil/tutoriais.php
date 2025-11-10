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
    $categoria = $_GET['categoria'] ?? '';
    $nivel = $_GET['nivel'] ?? '';
    $ativo = $_GET['ativo'] ?? '1';
    $plataforma = $_GET['plataforma'] ?? ''; // app ou web
    
    $sql = 'SELECT * FROM tutoriais WHERE 1=1';
    $params = [];
    
    if ($categoria) {
        $sql .= ' AND categoria = ?';
        $params[] = $categoria;
    }
    
    if ($nivel) {
        $sql .= ' AND nivel = ?';
        $params[] = $nivel;
    }
    
    // Filtro por plataforma (se fornecido)
    if ($plataforma) {
        // Agora usamos o campo 'tipo' da tabela para filtrar
        // Se a plataforma for 'app', mostrar apenas tutoriais com tipo = 'App'
        // Se a plataforma for 'web', mostrar apenas tutoriais com tipo = 'Web'
        if ($plataforma === 'app') {
            $sql .= ' AND (tipo = ? OR tipo IS NULL)';
            $params[] = 'App';
        } elseif ($plataforma === 'web') {
            $sql .= ' AND (tipo = ? OR tipo IS NULL)';
            $params[] = 'Web';
        }
    }
    
    $sql .= ' AND ativo = ? ORDER BY ordem_exibicao ASC, data_criacao DESC';
    $params[] = $ativo;
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $tutoriais = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatar dados para o frontend
        $formattedTutoriais = array_map(function($tutorial) {
            // Extrair ID do YouTube do link
            $youtubeId = '';
            if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\n?#]+)/', $tutorial['link_youtube'], $matches)) {
                $youtubeId = $matches[1];
            }
            
            return [
                'id' => (int)$tutorial['id'],
                'titulo' => $tutorial['titulo'],
                'descricao' => $tutorial['descricao'],
                'link_youtube' => $tutorial['link_youtube'],
                'youtube_id' => $youtubeId,
                'duracao' => $tutorial['duracao'],
                'nivel' => $tutorial['nivel'],
                'categoria' => $tutorial['categoria'],
                'icone' => $tutorial['icone'],
                'visualizacoes' => (int)$tutorial['visualizacoes'],
                'ativo' => (bool)$tutorial['ativo'],
                'data_criacao' => $tutorial['data_criacao']
            ];
        }, $tutoriais);
        
        response(true, 'Tutoriais listados com sucesso', $formattedTutoriais);
        
    } catch (Exception $e) {
        response(false, 'Erro ao buscar tutoriais: ' . $e->getMessage());
    }
}

function handlePost($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    // Validação dos campos obrigatórios
    if (empty($data['titulo']) || empty($data['descricao']) || empty($data['link_youtube'])) {
        response(false, 'Campos obrigatórios: titulo, descricao e link_youtube');
    }
    
    try {
        $stmt = $conn->prepare('INSERT INTO tutoriais 
            (titulo, descricao, link_youtube, duracao, nivel, categoria, icone, ordem_exibicao, ativo, data_criacao)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
            
        $success = $stmt->execute([
            $data['titulo'],
            $data['descricao'],
            $data['link_youtube'],
            $data['duracao'] ?? null,
            $data['nivel'] ?? 'basico',
            $data['categoria'] ?? 'geral',
            $data['icone'] ?? 'fas fa-play-circle',
            $data['ordem_exibicao'] ?? 0,
            $data['ativo'] ?? 1
        ]);
        
        if ($success) {
            response(true, 'Tutorial criado com sucesso', ['id' => $conn->lastInsertId()]);
        } else {
            response(false, 'Erro ao criar tutorial');
        }
        
    } catch (Exception $e) {
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}

function handlePut($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || empty($data['id'])) {
        response(false, 'ID do tutorial é obrigatório');
    }
    
    $fieldsToUpdate = [];
    $params = [];
    
    $updatableFields = [
        'titulo', 'descricao', 'link_youtube', 'duracao', 
        'nivel', 'categoria', 'icone', 'ordem_exibicao', 'ativo'
    ];
    
    foreach ($updatableFields as $field) {
        if (isset($data[$field])) {
            $fieldsToUpdate[] = "$field = ?";
            $params[] = $data[$field];
        }
    }
    
    if (empty($fieldsToUpdate)) {
        response(false, 'Nenhum campo para atualizar');
    }
    
    $fieldsToUpdate[] = 'data_atualizacao = NOW()';
    $params[] = $data['id'];
    
    $sql = 'UPDATE tutoriais SET ' . implode(', ', $fieldsToUpdate) . ' WHERE id = ?';
    
    try {
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute($params);
        
        if ($success) {
            response(true, 'Tutorial atualizado com sucesso');
        } else {
            response(false, 'Erro ao atualizar tutorial');
        }
        
    } catch (Exception $e) {
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}

function handleDelete($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || empty($data['id'])) {
        response(false, 'ID do tutorial é obrigatório');
    }
    
    try {
        $stmt = $conn->prepare('DELETE FROM tutoriais WHERE id = ?');
        $success = $stmt->execute([$data['id']]);
        
        if ($success) {
            response(true, 'Tutorial excluído com sucesso');
        } else {
            response(false, 'Erro ao excluir tutorial');
        }
        
    } catch (Exception $e) {
        response(false, 'Erro no servidor: ' . $e->getMessage());
    }
}
?>