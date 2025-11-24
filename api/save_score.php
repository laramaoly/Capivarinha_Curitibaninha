<?php
/**
 * Capivarinha_Curitibaninha - API de Salvar Pontuação
 * Recebe o resultado da partida e salva no histórico do usuário.
 * Autor: Maoly Lara Serrano
 */

session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

// 1. Segurança: Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Bah! Você precisa estar logado para salvar pontos.']);
    exit;
}

// 2. Recebe os dados crus da requisição (JSON)
$json_input = file_get_contents('php://input');
$data = json_decode($json_input, true);

// 3. Validação dos dados recebidos
if (isset($data['pontos']) && isset($data['acertos'])) {
    
    // Sanitização básica (garante que são números inteiros)
    $pontos = (int)$data['pontos'];
    $acertos = (int)$data['acertos'];
    $user_id = $_SESSION['user_id'];

    try {
        // 4. Insere no banco de dados (Tabela 'partidas')
        $stmt = $pdo->prepare("INSERT INTO partidas (usuario_id, pontuacao, palavras_acertadas, data_partida) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $pontos, $acertos]);
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Boa, piá! Pontuação salva com sucesso.'
        ]);

    } catch (PDOException $e) {
        // Erro de banco de dados
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar no banco: ' . $e->getMessage()]);
    }

} else {
    // Dados inválidos ou incompletos
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Dados inválidos recebidos.']);
}
?>