<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

// 1. Apenas usuários logados
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Não autorizado']);
    exit;
}

// 2. Receber e Sanitar Dados
$input = json_decode(file_get_contents('php://input'), true);
$pontos = filter_var($input['pontos'] ?? 0, FILTER_VALIDATE_INT);
$acertos = filter_var($input['acertos'] ?? 0, FILTER_VALIDATE_INT);

// 3. REGRA DE NEGÓCIO (Anti-Cheat)
$maximoPossivel = ($acertos * 15) + 20;

if ($pontos > $maximoPossivel) {
    // Logar tentativa de trapaça
    error_log("CHEAT DETECTED: User {$_SESSION['user_id']} tentou enviar $pontos pts com $acertos acertos.");
    
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Pontuação inconsistente.']);
    exit;
}

// 4. Salvar no Banco
try {
    $pdo = getDatabaseConnection();
    
    // CORREÇÃO: Data gerada pelo PHP para compatibilidade total (MySQL e SQLite)
    $agora = date('Y-m-d H:i:s'); 
    
    // Nota: Certifique-se que sua tabela se chama 'partidas'. 
    // Se o setup do SQLite criou como 'scores', mude abaixo para 'scores'.
    $stmt = $pdo->prepare("INSERT INTO partidas (usuario_id, pontuacao, palavras_acertadas, data_partida) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $pontos, $acertos, $agora]);

    echo json_encode(['status' => 'success']);
    
} catch (PDOException $e) {
    // Log do erro real
    error_log("Erro ao salvar score: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro interno ao salvar.']);
}
?>