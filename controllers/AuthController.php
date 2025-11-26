<?php
/**
 * Capivarinha_Curitibaninha - Controlador de Autenticação
 * Responsável por Login, Registo e Logout de utilizadores.
 * Autor: Maoly Lara Serrano
 */

require_once __DIR__ . '/../config/database.php';

class AuthController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Realiza o login do utilizador
     * @param string $email
     * @param string $senha
     * @return boolean Sucesso ou falha
     */
    public function login($email, $senha) {
        try {
            // Busca o utilizador pelo email
            $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            // Bloqueio por tentativas: se houver muitas tentativas falhas recentes, bloquear
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $threshold = 5; // tentativas
            $windowMinutes = 15; // janela em minutos
            $cutoff = date('Y-m-d H:i:s', strtotime("-{$windowMinutes} minutes"));
            $stmtAttempts = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM login_attempts WHERE (email = ? OR ip = ?) AND sucesso = 0 AND tentativa_em > ?");
            $stmtAttempts->execute([$email, $ip, $cutoff]);
            $cnt = $stmtAttempts->fetchColumn();
            if ($cnt !== false && $cnt >= $threshold) {
                return false; // bloqueado temporariamente
            }

            // Verifica se o utilizador existe e se a senha bate com o hash
            if ($user && password_verify($senha, $user['senha'])) {
                // Inicia a sessão (se ainda não estiver iniciada)
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                // SEGURANÇA: Previne Session Fixation regenerando o ID após login
                session_regenerate_id(true);

                // Salva dados essenciais na sessão
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nome'] = $user['nome'];
                $_SESSION['user_email'] = $user['email'];

                // Registrar tentativa de login bem-sucedida
                $stmtLog = $this->pdo->prepare("INSERT INTO login_attempts (email, ip, sucesso) VALUES (?, ?, 1)");
                $stmtLog->execute([$email, $ip]);

                return true;
            }
            // Registrar tentativa falha
            $stmtLog = $this->pdo->prepare("INSERT INTO login_attempts (email, ip, sucesso) VALUES (?, ?, 0)");
            $stmtLog->execute([$email, $ip]);
            return false;
        } catch (PDOException $e) {
            // Em produção, logar o erro em arquivo
            return false;
        }
    }

    /**
     * Regista um novo utilizador
     * @param string $nome
     * @param string $email
     * @param string $senha
     * @return boolean|string True se sucesso, mensagem de erro se falha
     */
    public function register($nome, $email, $senha) {
        try {
            // 1. Verifica se o email já está cadastrado
            $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                return "Eita! Esse e-mail já está sendo usado por outro piá.";
            }

            // 2. Política de senha: mínimo 8 caracteres, ao menos letra e número
            if (strlen($senha) < 8 || !preg_match('/[A-Za-z]/', $senha) || !preg_match('/[0-9]/', $senha)) {
                return "A senha deve ter ao menos 8 caracteres e incluir letras e números.";
            }

            // 3. Cria o hash seguro da senha
            $hash = password_hash($senha, PASSWORD_DEFAULT);

            // 4. Insere no banco
            $stmt = $this->pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            $result = $stmt->execute([$nome, $email, $hash]);

            if ($result) {
                return true;
            } else {
                return "Erro ao salvar no banco. Tente novamente.";
            }
        } catch (PDOException $e) {
            // Log do erro real para debug
            error_log("Erro no registo: " . $e->getMessage());
            return "Erro técnico: " . $e->getMessage();
        }
    }

    /**
     * Encerra a sessão do utilizador
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Limpa todas as variáveis de sessão
        $_SESSION = array();

        // Destrói o cookie da sessão se existir
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destrói a sessão
        session_destroy();

        // Redireciona para a página inicial (Login)
        header("Location: index.php?page=login");
        exit;
    }
    
    /**
     * Verifica se o utilizador está logado
     * @return boolean
     */
    public static function isAuthenticated() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }
}
?>