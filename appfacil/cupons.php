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
    $result = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $result['data'] = $data;
    }
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $conn = getDbConnection();
    
    switch ($method) {
        case 'GET':
            handleGet($conn);
            break;
        default:
            response(false, 'Método não permitido');
            break;
    }
    
} catch (Exception $e) {
    error_log('Cupons API: Erro interno - ' . $e->getMessage());
    response(false, 'Erro interno do servidor: ' . $e->getMessage());
}

function handleGet($conn) {
    try {
        // Configurar timezone para Brasília
        date_default_timezone_set('America/Sao_Paulo');
        
        // Obter data atual do servidor (hora do Brasil)
        $current_date = date('Y-m-d');
        error_log('Cupons API: Buscando cupons válidos até: ' . $current_date);
        
        // Verificar se foi passado um tipo de cupom específico
        $tipo_cupom = isset($_GET['tipo']) ? $_GET['tipo'] : 'Novo';
        
        // Buscar cupons especiais que são válidos para todos os tipos de assinatura
        $specialCouponSql = "SELECT id, cupom_nome, cupom, validade, tipo_cupom, forma_pagamento 
                            FROM Cupom 
                            WHERE validade >= ? AND tipo_cupom = 'Especial'
                            ORDER BY validade DESC";
        $specialCouponStmt = $conn->prepare($specialCouponSql);
        $specialCouponStmt->execute([$current_date]);
        $special_cupons = $specialCouponStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Verificar se devemos incluir cupons do tipo especificado
        // Cupons do tipo 'Novo' só devem aparecer para usuários trial ou sem assinatura
        $regular_cupons = [];
        if ($tipo_cupom === 'Novo' || $tipo_cupom === 'Reativar' || $tipo_cupom === 'Anual') {
            $couponSql = "SELECT id, cupom_nome, cupom, validade, tipo_cupom, forma_pagamento 
                         FROM Cupom 
                         WHERE validade >= ? AND tipo_cupom = ?
                         ORDER BY validade DESC";
            $couponStmt = $conn->prepare($couponSql);
            $couponStmt->execute([$current_date, $tipo_cupom]);
            $regular_cupons = $couponStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Combinar cupons especiais com cupons regulares
        $cupons = array_merge($special_cupons, $regular_cupons);
        
        // Remover duplicatas se houver (embora seja improvável)
        $cupons = array_unique($cupons, SORT_REGULAR);
        
        error_log('Cupons API: Encontrados ' . count($cupons) . ' cupons no total (' . count($special_cupons) . ' especiais, ' . count($regular_cupons) . ' regulares do tipo ' . $tipo_cupom . ')');
        
        // Converter id para string
        foreach ($cupons as &$cupom) {
            $cupom['id'] = (string)$cupom['id'];
        }
        
        response(true, 'Cupons carregados com sucesso', $cupons);
        
    } catch (Exception $e) {
        error_log('Erro ao buscar cupons: ' . $e->getMessage());
        response(false, 'Erro ao buscar cupons: ' . $e->getMessage());
    }
}
?>