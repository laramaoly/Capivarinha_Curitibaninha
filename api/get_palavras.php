<?php
/**
 * Capivarinha_Curitibaninha - API de Palavras
 * Retorna uma lista aleatória de gírias curitibanas em formato JSON.
 * Autor: Maoly Lara Serrano
 */

header('Content-Type: application/json');
require_once '../config/database.php';

try {
    // Busca 15 palavras aleatórias misturando níveis de dificuldade
    // 5 Fáceis + 5 Médias + 5 Difíceis
    $query = "(SELECT termo FROM termos_jogo WHERE dificuldade='facil' ORDER BY RAND() LIMIT 5)
              UNION
              (SELECT termo FROM termos_jogo WHERE dificuldade='medio' ORDER BY RAND() LIMIT 5)
              UNION
              (SELECT termo FROM termos_jogo WHERE dificuldade='dificil' ORDER BY RAND() LIMIT 5)";
    
    $stmt = $pdo->query($query);
    
    // FETCH_COLUMN retorna um array simples de strings ["Vina", "Piá", ...]
    // em vez de um array de objetos [{"termo": "Vina"}, ...]
    $palavras = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($palavras)) {
        // Fallback caso o banco esteja vazio (para não quebrar o jogo)
        $palavras = ['Vina', 'Piá', 'Capivara', 'Guria', 'Chima'];
    } else {
        // Embaralha o resultado final para que a dificuldade não seja linear
        shuffle($palavras);
    }
    
    echo json_encode($palavras);

} catch (PDOException $e) {
    // Retorna erro JSON em caso de falha no banco
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar palavras: ' . $e->getMessage()]);
}
?>