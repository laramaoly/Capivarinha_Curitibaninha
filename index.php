<?php
/**
 * Capivarinha_Curitibaninha - Roteador Principal
 */

// 1. Configurações de Sessão
ini_set('session.cookie_lifetime', 86400);
ini_set('session.cookie_httponly', 1);
session_start();

// 2. Importações (Apenas uma vez)
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/LeagueController.php';

// 3. Inicialização
$pdo = getDatabaseConnection();
$page = $_GET['page'] ?? 'game'; // Pega a página ou define 'game' como padrão
$erro = '';
$sucesso = '';

// 4. Controle de Acesso (Segurança Básica)
// Se não estiver logado e tentar acessar páginas internas, manda pro login
$paginasPublicas = ['login', 'register'];
if (!isset($_SESSION['user_id']) && !in_array($page, $paginasPublicas)) {
    header("Location: index.php?page=login");
    exit;
}

// 5. PROCESSAMENTO DE FORMULÁRIOS (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $authController = new AuthController($pdo);
    $leagueController = new LeagueController($pdo);
    
    // Roteamento das ações POST
    if ($page === 'login') {
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        if ($authController->login($email, $senha)) {
            header("Location: index.php?page=game");
            exit;
        } else {
            $erro = "E-mail ou senha incorretos.";
        }
    } 
    elseif ($page === 'register') {
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $confirmar = $_POST['confirmar_senha'] ?? '';
        
        if ($senha !== $confirmar) {
            $erro = "As senhas não conferem.";
        } else {
            $result = $authController->register($nome, $email, $senha);
            
            if ($result === true) {
                $sucesso = "Conta criada com sucesso! Faça login para continuar.";
            } else {
                $erro = $result; // Exibe erro do banco (ex: email já existe)
            }
        }
    }
    elseif ($page === 'leagues') {
        // Lógica de Ligas
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create') {
            $result = $leagueController->createLeague(
                $_POST['nome_liga'] ?? '',
                $_POST['palavra_chave'] ?? '',
                $_SESSION['user_id']
            );
        } elseif ($action === 'join') {
            $result = $leagueController->joinLeague(
                $_POST['liga_id'] ?? 0, 
                $_POST['senha_entrada'] ?? '', 
                $_SESSION['user_id']
            );
        }

        if (isset($result)) {
            if ($result['success']) {
                $sucesso = $result['message'];
            } else {
                $erro = $result['message'];
            }
        }
    }
}

// 6. ROTEAMENTO DE VIEWS (Telas)
switch ($page) {
    case 'login':    require 'views/login.php'; break;
    case 'register': require 'views/register.php'; break;
    case 'game':     require 'views/game.php'; break;
    case 'ranking':  require 'views/ranking.php'; break;
    case 'leagues':  require 'views/dashboard.php'; break;
    default:         require 'views/game.php'; break;
}
?>