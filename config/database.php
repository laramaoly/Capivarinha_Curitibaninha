<?php
/**
 * Capivarinha_Curitibaninha - Configuração de Base de Dados
 * Responsável pela conexão PDO com MySQL ou SQLite (fallback).
 * Suporta variáveis de ambiente para deploy seguro.
 * Autor: Maoly Lara Serrano
 */

// Carregar .env se existir (para desenvolvimento)
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

// Tentar primeiro MySQL, se falhar tenta SQLite
$dsn = null;
$pdo = null;

// Configurações para MySQL com suporte a variáveis de ambiente
$host = getenv('DB_HOST') ?: '127.0.0.1';
$db   = getenv('DB_NAME') ?: 'capityper';
$user = getenv('DB_USER') ?: 'admin';
$pass = getenv('DB_PASS') ?: 'admin';
$charset = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT         => false  // Melhor para production
];

try {
    // Tentar conexão MySQL
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // SEGURANÇA: Logar erro sem expor detalhes ao usuário
    $error_msg = "MySQL Error: " . $e->getMessage() . " | DSN: $dsn";
    error_log($error_msg, 3, __DIR__ . '/../logs/db_errors.log');
    
    // Se MySQL falhar, usar SQLite como fallback
    $db_dir = dirname(dirname(__FILE__)) . '/data';
    if (!is_dir($db_dir)) {
        mkdir($db_dir, 0755, true);
    }
    
    $sqlite_path = $db_dir . '/capityper.db';
    $dsn = "sqlite:$sqlite_path";
    
    try {
        $pdo = new PDO($dsn, null, null, $options);
        // Inicializar schema SQLite se necessário
        $pdo->exec("PRAGMA foreign_keys = ON");
    } catch (\PDOException $sqlite_e) {
        // SEGURANÇA: Logar erro detalhado e mostrar mensagem genérica
        $error_msg = "SQLite Error: " . $sqlite_e->getMessage() . " | Path: $sqlite_path";
        error_log($error_msg, 3, __DIR__ . '/../logs/db_errors.log');
        
        die("⚠️ Erro ao acessar o banco de dados. Tente novamente mais tarde.");
    }
}
?>