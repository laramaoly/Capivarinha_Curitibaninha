<?php
/**
 * Capivarinha_Curitibaninha - API de Palavras
 * Retorna gírias ordenadas por dificuldade: Fácil -> Médio -> Difícil
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
session_start();

// 1. Requer autenticação
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado.']);
    exit;
}

// (CSRF REMOVIDO AQUI)

try {
    $pdo = getDatabaseConnection();

    // Função auxiliar para buscar por dificuldade
    function getWords($pdo, $dificuldade) {
        // Compatibilidade SQLite vs MySQL para função "Random"
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $orderByRand = ($driver === 'sqlite') ? 'ORDER BY RANDOM()' : 'ORDER BY RAND()';

        // Tenta buscar da tabela termos_jogo
        $sql = "SELECT termo, dica FROM termos_jogo WHERE dificuldade = ? $orderByRand LIMIT 5";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dificuldade]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Busca 5 de cada tipo
    $facil = getWords($pdo, 'facil');
    $medio = getWords($pdo, 'medio');
    $dificil = getWords($pdo, 'dificil');

    // Junta tudo. 
    // Colocamos Difícil primeiro no array, depois Médio, depois Fácil.
    // Como o JavaScript usa .pop() (pega do final), a ordem de jogo será:
    // Sai Fácil (final) -> depois Médio -> depois Difícil (início)
    $deck = array_merge($dificil, $medio, $facil);
    
    // Se o banco estiver vazio, retorna erro ou array vazio
    if (empty($deck)) {
        // Opcional: Retornar palavras de backup se o banco falhar
        echo json_encode([]); 
    } else {
        echo json_encode($deck);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro no banco: ' . $e->getMessage()]);
}
?>