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

            // Verifica se o utilizador existe e se a senha bate com o hash
            if ($user && password_verify($senha, $user['senha'])) {
                // Inicia a sessão (se ainda não estiver iniciada)
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                // Salva dados essenciais na sessão
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nome'] = $user['nome'];
                $_SESSION['user_email'] = $user['email'];
                
                return true;
            }
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

            // 2. Cria o hash seguro da senha
            $hash = password_hash($senha, PASSWORD_DEFAULT);

            // 3. Insere no banco
            $stmt = $this->pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            $result = $stmt->execute([$nome, $email, $hash]);

            if ($result) {
                return true;
            } else {
                return "Erro ao salvar no banco. Tente novamente.";
            }
        } catch (PDOException $e) {
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