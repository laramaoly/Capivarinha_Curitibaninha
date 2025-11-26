<?php
/**
 * Capivarinha_Curitibaninha - Configuração de Base de Dados
 * Responsável pela conexão PDO com MySQL ou SQLite (fallback).
 * Suporta variáveis de ambiente para deploy seguro.
 * Autor: Maoly Lara Serrano
 */

/**
 * Retorna a instância única de conexão com o banco.
 * Evita múltiplas conexões e centraliza a lógica de fallback.
 */
function getDatabaseConnection() {
    static $pdo = null; // Mantém a conexão na memória

    if ($pdo !== null) {
        return $pdo;
    }

    // Carregar variáveis de ambiente (se necessário)
    if (file_exists(__DIR__ . '/../.env')) {
        $env = parse_ini_file(__DIR__ . '/../.env');
        foreach ($env as $key => $value) putenv("$key=$value");
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $db   = getenv('DB_NAME') ?: 'capityper';
    $user = getenv('DB_USER') ?: 'admin';
    $pass = getenv('DB_PASS') ?: 'admin';
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        // Tenta MySQL
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, $options);
    } catch (\PDOException $e) {
        // Fallback para SQLite
        try {
            $db_path = __DIR__ . '/../data/capityper.db';
            $pdo = new PDO("sqlite:$db_path", null, null, $options);
            $pdo->exec("PRAGMA foreign_keys = ON");
        } catch (\PDOException $e2) {
            // Log de erro real no servidor, mensagem genérica para o usuário
            error_log("DB Connection Error: " . $e2->getMessage());
            die("Erro interno de conexão. Tente novamente mais tarde.");
        }
    }

    return $pdo;
}

// Inicializa para manter compatibilidade com scripts antigos que esperam $pdo
$pdo = getDatabaseConnection();
?>