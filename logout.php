<?php
/**
 * Capivarinha_Curitibaninha - Script de Logout
 * Encerra a sessão do usuário e redireciona para o login.
 * Autor: Maoly Lara Serrano
 */

// Inicia a sessão se ainda não estiver iniciada
session_start();

// Importa as dependências necessárias
require_once 'config/database.php';
require_once 'controllers/AuthController.php';

// Instancia o controlador de autenticação
$auth = new AuthController($pdo);

// Executa o método de logout (que destrói a sessão e redireciona)
$auth->logout();
?>