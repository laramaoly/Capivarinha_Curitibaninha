<?php
/**
 * Capivarinha_Curitibaninha - Ponto de Entrada (Router)
 * Gerencia o redirecionamento de páginas e controlo de sessão.
 * Autor: Maoly Lara Serrano
 */

ini_set('session.cookie_lifetime', 86400);
ini_set('session.gc_maxlifetime', 86400);

session_start();

// Carrega a conexão com o banco de dados para disponibilizar $pdo globalmente
require_once __DIR__ . '/config/database.php';

// Define a página padrão como 'home' (que levará ao jogo)
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// --- Controlo de Acesso (Middleware Simples) ---
// Se o utilizador NÃO estiver logado E tentar acessar qualquer página
// que não seja 'login' ou 'register', redireciona para o login.
if (!isset($_SESSION['user_id']) && $page != 'login' && $page != 'register') {
    header("Location: index.php?page=login");
    exit;
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
