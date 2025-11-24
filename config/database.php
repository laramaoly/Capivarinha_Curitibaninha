<?php
/**
 * Capivarinha_Curitibaninha - Configuração de Base de Dados
 * Responsável pela conexão PDO com MySQL.
 * Autor: Maoly Lara Serrano
 */

// Configurações do ambiente (XAMPP/Localhost)
$host = 'localhost';
$db   = 'capityper';
$user = 'admin';     // Utilizador padrão do XAMPP (geralmente 'root')
$pass = 'admin';         // Palavra-passe padrão do XAMPP (geralmente vazia)
$charset = 'utf8mb4';

// Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Opções do PDO para segurança e facilidade de debug
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lança exceções em caso de erro
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      // Retorna arrays associativos por padrão
    PDO::ATTR_EMULATE_PREPARES   => false,                 // Usa prepared statements reais do MySQL
    PDO::ATTR_PERSISTENT         => true                   // Conexões persistentes (opcional, melhora performance)
];

try {
    // Cria a instância da conexão
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Em caso de erro fatal na conexão
    // Em produção, não deves mostrar $e->getMessage() diretamente ao utilizador
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>