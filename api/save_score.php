<?php
/**
 * Capivarinha_Curitibaninha - API de Salvar Pontuação
 * Recebe o resultado da partida e salva no histórico do usuário.
 * Autor: Maoly Lara Serrano
 */

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';

// 1. Segurança: Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
    exit;
}

// 1.1 Verifica CSRF: aceita token no corpo JSON ou header `X-CSRF-Token`
$csrf_token = null;
$json_input = file_get_contents('php://input');
$data = json_decode($json_input, true);
$csrf_token = $data['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
if (!validateCsrfToken($csrf_token)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Token CSRF inválido.']);
    exit;
}

// 2. Recebe os dados crus da requisição (JSON)
$json_input = file_get_contents('php://input');
$data = json_decode($json_input, true);

// 3. Validação dos dados recebidos
if (isset($data['pontos']) && isset($data['acertos'])) {
    // Sanitização básica (garante que são números inteiros)
    $pontos = filter_var($data['pontos'], FILTER_VALIDATE_INT);
    $acertos = filter_var($data['acertos'], FILTER_VALIDATE_INT);
    $user_id = (int)$_SESSION['user_id'];

    // Validações de negócio básicas para evitar trapaças óbvias
    if ($pontos === false || $acertos === false || $pontos < 0 || $pontos > 1000000 || $acertos < 0 || $acertos > 10000) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Dados de pontuação inválidos.']);
        exit;
    }

    try {
        // 4. Insere no banco de dados (Tabela 'scores' / 'partidas')
        // Usamos timestamp gerado pelo PHP para compatibilidade entre MySQL e SQLite
        $now = date('Y-m-d H:i:s');

        // Tenta usar tabela 'scores' se existir, senão 'partidas' (compatibilidade)
        $table = 'scores';
        $check = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name = 'scores'");
        $ok = false;
        try {
            $check->execute();
            $ok = (bool)$check->fetch();
        } catch (Exception $e) {
            // Ignore, fallback para tentar INSERT direto
        }

        if ($ok) {
            $stmt = $pdo->prepare("INSERT INTO scores (usuario_id, palavras_acertadas, palavras_erradas, tempo_gasto, data_jogo) VALUES (?, ?, ?, ?, ?)");
            // tempo_gasto não vem aqui — usamos NULL
            $stmt->execute([$user_id, $acertos, 0, null, $now]);
        } else {
            // tabela antigas / compatibilidade
            $stmt = $pdo->prepare("INSERT INTO partidas (usuario_id, pontuacao, palavras_acertadas, data_partida) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $pontos, $acertos, $now]);
        }

        echo json_encode(['status' => 'success', 'message' => 'Pontuação salva com sucesso.']);

    } catch (PDOException $e) {
        // Erro de banco de dados: logar e retornar mensagem genérica
        error_log("[save_score] DB error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Erro interno ao salvar pontuação.']);
    }

} else {
    // Dados inválidos ou incompletos
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Dados inválidos recebidos.']);
}
?>