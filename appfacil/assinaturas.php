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

function response($success, $message, $data = null, $stats = null) {
    $result = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $result['data'] = $data;
    }
    if ($stats !== null) {
        $result['stats'] = $stats;
    }
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $conn = getDbConnection();
    $usuario_id = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : 0;
    
    error_log('Assinaturas API: Requisição recebida - method: ' . $method . ', usuario_id: ' . $usuario_id);
    
    if (!$usuario_id) {
        response(false, 'ID do usuário é obrigatório');
    }
    
    switch ($method) {
        case 'GET':
            handleGet($conn, $usuario_id);
            break;
        case 'POST':
            handlePost($conn, $usuario_id);
            break;
        case 'PUT':
            handlePut($conn, $usuario_id);
            break;
        case 'DELETE':
            handleDelete($conn, $usuario_id);
            break;
        default:
            response(false, 'Método não permitido');
            break;
    }
    
} catch (Exception $e) {
    error_log('Assinaturas API: Erro interno - ' . $e->getMessage());
    response(false, 'Erro interno do servidor: ' . $e->getMessage());
}

function handleGet($conn, $usuario_id) {
    try {
        // Configurar timezone para Brasília
        date_default_timezone_set('America/Sao_Paulo');
        
        // Obter data atual do servidor (hora do Brasil)
        $current_date = date('Y-m-d H:i:s');
        error_log('Assinaturas API: Verificando assinaturas para usuario_id: ' . $usuario_id);
        error_log('Assinaturas API: Data atual do servidor (Brasil): ' . $current_date);
        
        $sql = "SELECT id, plano_id, data_inicio, data_fim, tipo_assinatura, status FROM assinaturas WHERE usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$usuario_id]);
        $assinaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log('Assinaturas API: Encontradas ' . count($assinaturas) . ' assinaturas');
        
        foreach ($assinaturas as &$assinatura) {
            $assinatura['id'] = (string)$assinatura['id'];
            
            // Tratar status vazio como 'trial' por padrão
            if (empty($assinatura['status']) || $assinatura['status'] === '') {
                error_log('Assinaturas API: Status vazio detectado para ID ' . $assinatura['id'] . ', definindo como trial');
                $assinatura['status'] = 'trial';
                
                // Atualizar no banco de dados
                $updateStatusSql = "UPDATE assinaturas SET status = 'trial' WHERE id = ?";
                $updateStatusStmt = $conn->prepare($updateStatusSql);
                $updateStatusStmt->execute([$assinatura['id']]);
            }
            
            // Verificar se a assinatura está vencida usando timezone do Brasil
            // Agora considerando data e hora completas
            $data_fim = new DateTime($assinatura['data_fim'], new DateTimeZone('America/Sao_Paulo'));
            $now = new DateTime($current_date, new DateTimeZone('America/Sao_Paulo'));
            
            error_log('Assinaturas API: Processando assinatura ID ' . $assinatura['id'] . ' - status: "' . $assinatura['status'] . '", data_fim: ' . $assinatura['data_fim']);
            error_log('Assinaturas API: Data atual: ' . $now->format('Y-m-d H:i:s') . ', Data fim: ' . $data_fim->format('Y-m-d H:i:s'));
            
            // Verificação correta: assinatura expirada quando a data/hora atual é maior que a data_fim
            $is_expired = $now > $data_fim;
            error_log('Assinaturas API: is_expired: ' . ($is_expired ? 'true' : 'false'));
            
            // Atualizar status se vencido
            if ($is_expired && in_array($assinatura['status'], ['ativo', 'trial'])) {
                error_log('Assinaturas API: Atualizando status para expirado');
                $updateSql = "UPDATE assinaturas SET status = 'expirado' WHERE id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->execute([$assinatura['id']]);
                $assinatura['status'] = 'expirado';
            }
            
            // Lógica de validação melhorada
            $assinatura['is_expired'] = $is_expired;
            // Assinatura é válida se está ativa OU em trial E não vencida
            $assinatura['is_valid'] = in_array($assinatura['status'], ['ativo', 'trial']) && !$is_expired;
            
            error_log('Assinaturas API: Resultado final - status: "' . $assinatura['status'] . '", is_expired: ' . ($assinatura['is_expired'] ? 'true' : 'false') . ', is_valid: ' . ($assinatura['is_valid'] ? 'true' : 'false'));
        }
        
        // Verificar se o usuário deve ver cupons (não tem assinatura ativa OU tem assinatura trial OU assinatura expirada)
        $should_show_coupons = false;
        $has_active_subscription = false;
        $user_has_trial = false;
        
        foreach ($assinaturas as $assinatura) {
            // Se tem alguma assinatura válida (não expirada e ativa/trial)
            if ($assinatura['is_valid']) {
                $has_active_subscription = true;
                // Se a assinatura válida é trial, mostra cupons
                if ($assinatura['status'] === 'trial') {
                    $should_show_coupons = true;
                    $user_has_trial = true;
                }
            } else {
                // Se tem assinatura mas está expirada, mostra cupons
                $should_show_coupons = true;
            }
        }
        
        // Se não tem nenhuma assinatura, mostra cupons
        if (empty($assinaturas)) {
            $should_show_coupons = true;
            $user_has_trial = true; // Usuário sem assinatura é tratado como trial
        }
        
        // Buscar cupons disponíveis se necessário
        $cupons = [];
        if ($should_show_coupons) {
            // Determinar o tipo de cupom com base no status do usuário
            // Se o usuário é trial ou não tem assinatura, mostrar cupons do tipo 'Novo'
            // Se tem assinatura expirada, mostrar cupons do tipo 'Reativar'
            $coupon_type = $user_has_trial ? 'Novo' : 'Reativar';
            
            // Buscar cupons especiais que são válidos para todos os tipos de assinatura
            $specialCouponSql = "SELECT id, cupom_nome, cupom, validade, tipo_cupom, forma_pagamento 
                                FROM Cupom 
                                WHERE validade >= ? AND tipo_cupom = 'Especial'
                                ORDER BY validade DESC";
            $specialCouponStmt = $conn->prepare($specialCouponSql);
            $specialCouponStmt->execute([date('Y-m-d')]);
            $special_cupons = $specialCouponStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Verificar se devemos incluir cupons do tipo 'Novo'
            // Cupons do tipo 'Novo' só devem aparecer para usuários trial ou sem assinatura
            $regular_cupons = [];
            if ($coupon_type === 'Novo' || $coupon_type === 'Reativar') {
                $couponSql = "SELECT id, cupom_nome, cupom, validade, tipo_cupom, forma_pagamento 
                             FROM Cupom 
                             WHERE validade >= ? AND tipo_cupom = ?
                             ORDER BY validade DESC";
                $couponStmt = $conn->prepare($couponSql);
                $couponStmt->execute([date('Y-m-d'), $coupon_type]);
                $regular_cupons = $couponStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // Combinar cupons especiais com cupons regulares
            $cupons = array_merge($special_cupons, $regular_cupons);
            
            // Remover duplicatas se houver (embora seja improvável)
            $cupons = array_unique($cupons, SORT_REGULAR);
            
            // Converter id para string
            foreach ($cupons as &$cupom) {
                $cupom['id'] = (string)$cupom['id'];
            }
        }
        
        // Incluir data atual do servidor na resposta
        $stats = [
            'current_server_date' => $current_date,
            'current_server_timezone' => 'America/Sao_Paulo',
            'total_subscriptions' => count($assinaturas),
            'has_active_subscription' => $has_active_subscription,
            'should_show_coupons' => $should_show_coupons,
            'user_has_trial' => $user_has_trial,
            'available_coupons' => $cupons
        ];
        
        error_log('Assinaturas API: Retornando resposta de sucesso');
        response(true, 'Assinaturas carregadas com sucesso', $assinaturas, $stats);
        
    } catch (Exception $e) {
        error_log('Erro ao buscar assinaturas: ' . $e->getMessage());
        response(false, 'Erro ao buscar assinaturas: ' . $e->getMessage());
    }
}

function handlePost($conn, $usuario_id) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            response(false, 'Dados inválidos');
        }
        
        $plano_id = $input['plano_id'] ?? null;
        $data_inicio = $input['data_inicio'] ?? null;
        $data_fim = $input['data_fim'] ?? null;
        $tipo_assinatura = $input['tipo_assinatura'] ?? 'trial';
        $status = $input['status'] ?? 'ativo';
        
        if (empty($data_inicio) || empty($data_fim)) {
            response(false, 'Data de início e fim são obrigatórias');
        }
        
        $sql = "INSERT INTO assinaturas (usuario_id, plano_id, data_inicio, data_fim, tipo_assinatura, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $usuario_id,
            $plano_id,
            $data_inicio,
            $data_fim,
            $tipo_assinatura,
            $status
        ]);
        
        if ($result) {
            $newId = $conn->lastInsertId();
            response(true, 'Assinatura adicionada com sucesso', ['id' => $newId]);
        } else {
            response(false, 'Falha ao inserir no banco de dados');
        }
        
    } catch (Exception $e) {
        response(false, 'Erro ao adicionar assinatura: ' . $e->getMessage());
    }
}

function handlePut($conn, $usuario_id) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id'])) {
            response(false, 'ID é obrigatório');
        }
        
        $id = (int)$input['id'];
        $plano_id = $input['plano_id'] ?? null;
        $data_inicio = $input['data_inicio'] ?? null;
        $data_fim = $input['data_fim'] ?? null;
        $tipo_assinatura = $input['tipo_assinatura'] ?? 'trial';
        $status = $input['status'] ?? 'ativo';
        $data_cancelamento = $input['data_cancelamento'] ?? null;
        $motivo_cancelamento = $input['motivo_cancelamento'] ?? null;
        
        if (empty($data_inicio) || empty($data_fim)) {
            response(false, 'Data de início e fim são obrigatórias');
        }
        
        $checkStmt = $conn->prepare("SELECT id FROM assinaturas WHERE id = ? AND usuario_id = ?");
        $checkStmt->execute([$id, $usuario_id]);
        
        if ($checkStmt->rowCount() === 0) {
            response(false, 'Assinatura não encontrada');
        }
        
        $sql = "UPDATE assinaturas SET plano_id = ?, data_inicio = ?, data_fim = ?, 
                tipo_assinatura = ?, status = ?, data_cancelamento = ?, motivo_cancelamento = ? WHERE id = ? AND usuario_id = ?";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $plano_id,
            $data_inicio,
            $data_fim,
            $tipo_assinatura,
            $status,
            $data_cancelamento,
            $motivo_cancelamento,
            $id,
            $usuario_id
        ]);
        
        if ($result) {
            response(true, 'Assinatura atualizada com sucesso');
        } else {
            response(false, 'Falha ao atualizar no banco de dados');
        }
        
    } catch (Exception $e) {
        response(false, 'Erro ao atualizar assinatura: ' . $e->getMessage());
    }
}

function handleDelete($conn, $usuario_id) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id'])) {
            response(false, 'ID é obrigatório');
        }
        
        $id = (int)$input['id'];
        
        $checkStmt = $conn->prepare("SELECT id FROM assinaturas WHERE id = ? AND usuario_id = ?");
        $checkStmt->execute([$id, $usuario_id]);
        
        if ($checkStmt->rowCount() === 0) {
            response(false, 'Assinatura não encontrada');
        }
        
        $sql = "DELETE FROM assinaturas WHERE id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$id, $usuario_id]);
        
        if ($result) {
            response(true, 'Assinatura removida com sucesso');
        } else {
            response(false, 'Falha ao deletar do banco de dados');
        }
        
    } catch (Exception $e) {
        response(false, 'Erro ao remover assinatura: ' . $e->getMessage());
    }
}

?>