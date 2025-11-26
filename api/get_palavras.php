<?php
/**
 * Capivarinha_Curitibaninha - API de Palavras
 * Retorna uma lista aleatória de gírias curitibanas em formato JSON.
 * Autor: Maoly Lara Serrano
 */


/**
 * Capivarinha_Curitibaninha - API de Palavras com Dicas
 * Retorna objetos {termo, dica} ordenados por dificuldade
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';
session_start();

// Requer autenticação: somente usuários logados podem receber palavras
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado.']);
    exit;
}

// Aceita token CSRF via header ou query para chamadas XHR
$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_GET['csrf_token'] ?? null);
if (!validateCsrfToken($csrf)) {
    http_response_code(403);
    echo json_encode(['error' => 'Token CSRF inválido.']);
    exit;
}

try {
    // Função auxiliar para buscar por dificuldade (compatibilidade SQLite/MySQL)
    function getWords($pdo, $dificuldade) {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $orderByRand = 'ORDER BY RANDOM()';
        } else {
            $orderByRand = 'ORDER BY RAND()';
        }

        $sql = "SELECT termo, dica FROM termos_jogo WHERE dificuldade = ? $orderByRand LIMIT 5";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dificuldade]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $facil = getWords($pdo, 'facil');
    $medio = getWords($pdo, 'medio');
    $dificil = getWords($pdo, 'dificil');

    // Junta tudo (Dificil primeiro no array para sair por último no .pop())
    // Ordem de saída no jogo: Fácil -> Médio -> Difícil
    $deck = array_merge($dificil, $medio, $facil);
    
    echo json_encode($deck);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro: ' . $e->getMessage()]);
}
?>