<?php
/**
 * Capivarinha_Curitibaninha - Configuração de Base de Dados
 * Responsável pela conexão PDO com MySQL.
 * Autor: Maoly Lara Serrano
 */

// Configurações para o Codespaces (conforme criamos no terminal)
$host = 'localhost';
$db   = 'capityper';
$user = 'admin';     // Mudamos de 'root' para 'admin'
$pass = 'admin';     // Senha 'admin' que definimos no SQL
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT         => true
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Se der erro de driver, a mensagem será clara
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>