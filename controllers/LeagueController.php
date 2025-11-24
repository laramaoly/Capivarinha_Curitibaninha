<?php
/**
 * Capivarinha_Curitibaninha - Controlador de Ligas
 * Responsável por criar ligas, listar e permitir entrada de membros.
 * Autor: Maoly Lara Serrano
 */

require_once __DIR__ . '/../config/database.php';

class LeagueController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Cria uma nova liga
     * @param string $nome Nome da liga
     * @param string $palavra_chave Senha para entrar
     * @param int $criador_id ID do usuário criador
     * @return array ['success' => bool, 'message' => string]
     */
    public function createLeague($nome, $palavra_chave, $criador_id) {
        try {
            // Verifica se já existe liga com esse nome
            $stmt = $this->pdo->prepare("SELECT id FROM ligas WHERE nome_liga = ?");
            $stmt->execute([$nome]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Bah! Já existe uma liga com esse nome.'];
            }

            $this->pdo->beginTransaction();

            // 1. Cria a Liga
            $stmt = $this->pdo->prepare("INSERT INTO ligas (nome_liga, palavra_chave, criador_id) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $palavra_chave, $criador_id]);
            $liga_id = $this->pdo->lastInsertId();

            // 2. Adiciona o criador como membro automaticamente
            $stmtMember = $this->pdo->prepare("INSERT INTO liga_membros (usuario_id, liga_id) VALUES (?, ?)");
            $stmtMember->execute([$criador_id, $liga_id]);

            $this->pdo->commit();
            return ['success' => true, 'message' => 'Liga criada! Agora chama a piazada.'];

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => 'Erro ao criar liga: ' . $e->getMessage()];
        }
    }

    /**
     * Entra em uma liga existente
     * @param int $liga_id
     * @param string $palavra_chave
     * @param int $usuario_id
     * @return array
     */
    public function joinLeague($liga_id, $palavra_chave, $usuario_id) {
        try {
            // 1. Verifica se a liga existe e a senha bate
            $stmt = $this->pdo->prepare("SELECT id, palavra_chave FROM ligas WHERE id = ?");
            $stmt->execute([$liga_id]);
            $liga = $stmt->fetch();

            if (!$liga) {
                return ['success' => false, 'message' => 'Liga não encontrada.'];
            }

            // Verifica senha (aqui estamos comparando texto plano conforme requisito simples, 
            // mas poderia ser hash se fosse crítico)
            if ($liga['palavra_chave'] !== $palavra_chave) {
                return ['success' => false, 'message' => 'Palavra-chave errada, piá!'];
            }

            // 2. Verifica se já é membro
            $stmtCheck = $this->pdo->prepare("SELECT id FROM liga_membros WHERE liga_id = ? AND usuario_id = ?");
            $stmtCheck->execute([$liga_id, $usuario_id]);
            if ($stmtCheck->fetch()) {
                return ['success' => false, 'message' => 'Você já está nessa liga!'];
            }

            // 3. Adiciona
            $stmtInsert = $this->pdo->prepare("INSERT INTO liga_membros (usuario_id, liga_id) VALUES (?, ?)");
            if ($stmtInsert->execute([$usuario_id, $liga_id])) {
                return ['success' => true, 'message' => 'Sucesso! Agora você é membro.'];
            }

            return ['success' => false, 'message' => 'Erro desconhecido ao entrar.'];

        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erro técnico: ' . $e->getMessage()];
        }
    }

    /**
     * Lista todas as ligas disponíveis (para o usuário procurar)
     */
    public function getAllLeagues() {
        try {
            $stmt = $this->pdo->query("SELECT ligas.id, ligas.nome_liga, usuarios.nome as criador 
                                       FROM ligas 
                                       JOIN usuarios ON ligas.criador_id = usuarios.id
                                       ORDER BY ligas.data_criacao DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Lista as ligas que o usuário já participa
     */
    public function getUserLeagues($usuario_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT l.id, l.nome_liga, COUNT(lm_total.id) as total_membros
                FROM ligas l
                JOIN liga_membros lm ON l.id = lm.liga_id
                LEFT JOIN liga_membros lm_total ON l.id = lm_total.liga_id
                WHERE lm.usuario_id = ?
                GROUP BY l.id
            ");
            $stmt->execute([$usuario_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>