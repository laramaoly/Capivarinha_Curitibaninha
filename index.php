<?php
/**
 * Capivarinha_Curitibaninha - Ponto de Entrada (Router)
 * Gerencia o redirecionamento de páginas, processamento de POST e controlo de sessão.
 * Autor: Maoly Lara Serrano
 */

// Configurações de Sessão
ini_set('session.cookie_lifetime', 86400);
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_httponly', 1);     // Segurança: Impede acesso por JavaScript
ini_set('session.cookie_samesite', 'Strict'); // Segurança: CSRF protection

session_start();

// Carrega a conexão com o banco de dados para disponibilizar $pdo globalmente
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/csrf.php';

// Define a página padrão como 'home' (que levará ao jogo)
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// --- Controlo de Acesso (Middleware Simples) ---
// Se o utilizador NÃO estiver logado E tentar acessar qualquer página
// que não seja 'login' ou 'register', redireciona para o login.
if (!isset($_SESSION['user_id']) && !in_array($page, ['login', 'register', 'home'])) {
    header("Location: index.php?page=login");
    exit;
}

// --- Processamento de Formulários (POST) - Deve vir ANTES das Views ---
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação CSRF obrigatória
    if (!validateCsrfToken()) {
        http_response_code(403);
        die("⚠️ Ação não autorizada (Token inválido). Atualize a página.");
    }
    
    // Carrega os controladores necessários
    require_once __DIR__ . '/controllers/AuthController.php';
    require_once __DIR__ . '/controllers/LeagueController.php';
    
    $authController = new AuthController($pdo);
    $leagueController = new LeagueController($pdo);
    
    // Processamento por página
    switch ($page) {
        case 'login':
            if (isset($_POST['email']) && isset($_POST['senha'])) {
                if ($authController->login($_POST['email'], $_POST['senha'])) {
                    header("Location: index.php?page=game");
                    exit;
                } else {
                    $erro = "Email ou senha incorretos.";
                }
            }
            break;
        
        case 'register':
            if (isset($_POST['nome']) && isset($_POST['email']) && isset($_POST['senha'])) {
                $result = $authController->register($_POST['nome'], $_POST['email'], $_POST['senha']);
                if ($result === true) {
                    $sucesso = "Conta criada com sucesso! Faça login para continuar.";
                } else {
                    $erro = $result;
                }
            }
            break;
        
        case 'leagues':
            if (isset($_POST['action'])) {
                if ($_POST['action'] === 'create' && isset($_SESSION['user_id'])) {
                    $result = $leagueController->createLeague(
                        $_POST['nome_liga'] ?? '',
                        $_POST['palavra_chave'] ?? '',
                        $_SESSION['user_id']
                    );
                    if ($result === true) {
                        $sucesso = "Liga criada com sucesso!";
                    } else {
                        $erro = $result;
                    }
                }
            }
            break;
    }
}

// --- Roteamento ---

switch ($page) {
    case 'login':
        // Página de Login
        require 'views/login.php';
        break;

    case 'register':
        // Página de Cadastro
        require 'views/register.php';
        break;

    case 'game':
        // Tela Principal do Jogo
        require 'views/game.php';
        break;

    case 'ranking':
        // Tabelas de Classificação (Geral e Semanal)
        require 'views/ranking.php';
        break;

    case 'leagues':
        // Dashboard de Ligas (Criar/Entrar)
        require 'views/dashboard.php';
        break;

    default:
        // Se a página não existir ou for 'home', vai para o jogo
        require 'views/game.php';
        break;
}
?>
