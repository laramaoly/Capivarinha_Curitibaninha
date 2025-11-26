<?php
/**
 * Capivarinha_Curitibaninha - Roteador Principal
 * Versão simplificada sem CSRF para facilitar o desenvolvimento.
 */

// Configurações de Sessão
ini_set('session.cookie_lifetime', 86400);
ini_set('session.cookie_httponly', 1);
// Removido samesite strict para evitar problemas em alguns ambientes de dev
// ini_set('session.cookie_samesite', 'Strict'); 

session_start();

require_once __DIR__ . '/config/database.php';
// require_once __DIR__ . '/includes/csrf.php'; // DESATIVADO

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Controle de Acesso
if (!isset($_SESSION['user_id']) && !in_array($page, ['login', 'register', 'home'])) {
    header("Location: index.php?page=login");
    exit;
}

$erro = '';
$sucesso = '';

// --- PROCESSAMENTO DE POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Sem validação CSRF aqui
    
    require_once __DIR__ . '/controllers/AuthController.php';
    require_once __DIR__ . '/controllers/LeagueController.php';
    
    $authController = new AuthController($pdo);
    $leagueController = new LeagueController($pdo);
    
    switch ($page) {
        case 'login':
            if ($authController->login($_POST['email'] ?? '', $_POST['senha'] ?? '')) {
                header("Location: index.php?page=game");
                exit;
            } else {
                $erro = "E-mail ou senha incorretos.";
            }
            break;
        
        case 'register':
            $senha = $_POST['senha'] ?? '';
            $confirmar = $_POST['confirmar_senha'] ?? '';
            
            if ($senha !== $confirmar) {
                $erro = "As senhas não conferem.";
            } else {
                $result = $authController->register(
                    $_POST['nome'] ?? '', 
                    $_POST['email'] ?? '', 
                    $senha
                );
                
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
                    if ($result['success']) {
                        $sucesso = $result['message'];
                    } else {
                        $erro = $result['message'];
                    }
                } elseif (isset($_POST['action']) && $_POST['action'] === 'join') {
                    $result = $leagueController->joinLeague(
                        $_POST['liga_id'] ?? 0, 
                        $_POST['senha_entrada'] ?? '', 
                        $_SESSION['user_id']
                    );
                    if ($result['success']) {
                        $sucesso = $result['message'];
                    } else {
                        $erro = $result['message'];
                    }
                }
            }
            break;
    }
}

// --- ROTEAMENTO DE VIEWS ---
switch ($page) {
    case 'login':    require 'views/login.php'; break;
    case 'register': require 'views/register.php'; break;
    case 'game':     require 'views/game.php'; break;
    case 'ranking':  require 'views/ranking.php'; break;
    case 'leagues':  require 'views/dashboard.php'; break;
    default:         require 'views/game.php'; break;
}
?>