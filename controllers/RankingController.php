<?php
/**
 * Capivarinha_Curitibaninha - Controlador de Ranking e Pontuação
 * Responsável por calcular e exibir rankings globais, semanais e por liga.
 * Autor: Maoly Lara Serrano
 */

class RankingController {
    private $pdo;

    /**
     * Constructor accepts an optional PDO instance. If none is provided,
     * it will try to create one using the application's database config.
     */
    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->pdo = $pdo;
            return;
        }

        // Tenta carregar a configuração padrão
        $configPath = __DIR__ . '/../config/database.php';
        if (file_exists($configPath)) {
            require_once $configPath;
            if (isset($pdo) && $pdo instanceof PDO) {
                $this->pdo = $pdo;
            } elseif (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
                $this->pdo = $GLOBALS['pdo'];
            } elseif (isset($pdo) && !$pdo) {
                // nothing
            } else {
                // If `database.php` sets `$pdo` in the local scope, grab it.
                if (isset($pdo) && $pdo instanceof PDO) {
                    $this->pdo = $pdo;
                } elseif (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
                    $this->pdo = $GLOBALS['pdo'];
                }
            }
        }

        // Se ainda não temos um PDO válido, tenta buscar $pdo criado por include anterior
        if (!($this->pdo instanceof PDO) && isset($pdo) && $pdo instanceof PDO) {
            $this->pdo = $pdo;
        }

        // Último recurso: verifica variável global $pdo
        if (!($this->pdo instanceof PDO) && isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
            $this->pdo = $GLOBALS['pdo'];
        }

        // Se não encontrou, lança exceção clara para facilitar debugging
        if (!($this->pdo instanceof PDO)) {
            throw new \RuntimeException('RankingController: PDO instance is not available. Ensure config/database.php is loaded or pass a PDO to the constructor.');
        }
    }

    /**
     * Obtém o Ranking Global (Todos os tempos)
     * @param int $limit Número de utilizadores a retornar
     */
    public function getGlobalRanking($limit = 10) {
        try {
            // Soma a pontuação de todas as partidas agrupadas por utilizador
                $sql = "SELECT u.nome, SUM(p.pontuacao) as total_pontos
                FROM partidas p
                JOIN usuarios u ON p.usuario_id = u.id
                GROUP BY u.id
                ORDER BY total_pontos DESC
                LIMIT :limit";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtém o Ranking Global (Apenas desta semana)
     * Usa a função YEARWEEK do MySQL para filtrar
     */
    public function getGlobalRankingWeekly($limit = 10) {
        try {
                $sql = "SELECT u.id AS user_id, u.nome, SUM(p.pontuacao) as total_pontos
                    FROM partidas p
                    JOIN usuarios u ON p.usuario_id = u.id
                    WHERE YEARWEEK(p.data_partida, 1) = YEARWEEK(CURDATE(), 1)
                    GROUP BY u.id
                    ORDER BY total_pontos DESC
                    LIMIT :limit";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtém o Ranking de uma Liga Específica (Geral)
     * Filtra apenas os utilizadores que pertencem à liga
     */
    public function getLeagueRanking($liga_id, $limit = 10) {
        try {
                $sql = "SELECT u.id AS user_id, u.nome, SUM(p.pontuacao) as total_pontos
                    FROM partidas p
                    JOIN usuarios u ON p.usuario_id = u.id
                    JOIN liga_membros lm ON u.id = lm.usuario_id
                    WHERE lm.liga_id = :liga_id
                    -- Opcional: Contar apenas pontos feitos APÓS entrar na liga
                    -- AND p.data_partida >= lm.data_entrada 
                    GROUP BY u.id
                    ORDER BY total_pontos DESC
                    LIMIT :limit";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':liga_id', (int)$liga_id, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtém o Ranking de uma Liga Específica (Semanal)
     */
    public function getLeagueRankingWeekly($liga_id, $limit = 10) {
        try {
                $sql = "SELECT u.id AS user_id, u.nome, SUM(p.pontuacao) as total_pontos
                    FROM partidas p
                    JOIN usuarios u ON p.usuario_id = u.id
                    JOIN liga_membros lm ON u.id = lm.usuario_id
                    WHERE lm.liga_id = :liga_id
                    AND YEARWEEK(p.data_partida, 1) = YEARWEEK(CURDATE(), 1)
                    GROUP BY u.id
                    ORDER BY total_pontos DESC
                    LIMIT :limit";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':liga_id', (int)$liga_id, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtém o histórico completo de partidas de um utilizador
     * Requisito: "Acesso a relatório com dados de todas as partidas jogadas"
     */
    public function getUserHistory($usuario_id) {
        try {
                 $sql = "SELECT p.pontuacao, p.palavras_acertadas, p.data_partida,
                           DATE_FORMAT(p.data_partida, '%d/%m/%Y %H:%i') as data_formatada
                    FROM partidas p
                    WHERE p.usuario_id = :usuario_id
                    ORDER BY p.data_partida DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':usuario_id', (int)$usuario_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtém os pontos totais do utilizador atual (para exibir no HUD)
     */
    public function getUserTotalScore($usuario_id) {
        try {
            $sql = "SELECT SUM(pontuacao) as total FROM partidas WHERE usuario_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$usuario_id]);
            $result = $stmt->fetch();
            return $result && $result['total'] ? (int)$result['total'] : 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
}
?>