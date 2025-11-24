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
require_once '../config/database.php';

try {
    // Função auxiliar para buscar por dificuldade
    function getWords($pdo, $dificuldade) {
        $sql = "SELECT termo, dica FROM termos_jogo WHERE dificuldade = ? ORDER BY RAND() LIMIT 5";
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