<?php
// Script para inicializar el banco de datos automáticamente
echo "<h1>Inicializando Capi-Typer DB... 🧉</h1>";

// 1. Conectar como ROOT (sin contraseña en Codespaces)
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión con MariaDB exitosa.<br>";
} catch (PDOException $e) {
    die("❌ Error conectando a MariaDB: " . $e->getMessage() . "<br>Asegúrate de haber ejecutado 'bash fix_env.sh' primero.");
}

// 2. Crear Usuario 'admin' y Base de Datos
try {
    // Crear usuario admin si no existe
    $pdo->exec("CREATE USER IF NOT EXISTS 'admin'@'localhost' IDENTIFIED BY 'admin';");
    $pdo->exec("GRANT ALL PRIVILEGES ON *.* TO 'admin'@'localhost';");
    $pdo->exec("FLUSH PRIVILEGES;");
    echo "✅ Usuario 'admin' creado/verificado.<br>";

    // Crear base de datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS capityper CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "✅ Base de datos 'capityper' creada.<br>";
    
    // Seleccionar la DB
    $pdo->exec("USE capityper");

    // 3. Ejecutar el SQL de Tablas (Leemos tu archivo SQL)
    $sqlFile = file_get_contents(__DIR__ . '/sql/database_setup.sql');
    
    // El archivo SQL tiene multiples comandos, PDO no puede ejecutar multiples a la vez facilmente
    // así que vamos a ejecutar comando por comando separando por ';'
    // PERO, tu archivo tiene una estructura limpia, vamos a forzar la creación aqui mismo para asegurar.
    
    $queries = [
        "DROP TABLE IF EXISTS partidas, liga_membros, ligas, termos_jogo, usuarios",
        "CREATE TABLE usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            senha VARCHAR(255) NOT NULL,
            data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE ligas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome_liga VARCHAR(100) NOT NULL,
            palavra_chave VARCHAR(50) NOT NULL,
            criador_id INT,
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (criador_id) REFERENCES usuarios(id) ON DELETE CASCADE
        )",
        "CREATE TABLE liga_membros (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT,
            liga_id INT,
            data_entrada DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            FOREIGN KEY (liga_id) REFERENCES ligas(id) ON DELETE CASCADE
        )",
        "CREATE TABLE partidas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT,
            pontuacao INT NOT NULL,
            palavras_acertadas INT,
            data_partida DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        )",
        "CREATE TABLE termos_jogo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            termo VARCHAR(255) NOT NULL,
            dificuldade ENUM('facil', 'medio', 'dificil') DEFAULT 'medio'
        )",
        "INSERT INTO termos_jogo (termo, dificuldade) VALUES 
        ('Vina', 'facil'), ('Piá', 'facil'), ('Guria', 'facil'), 
        ('Capi', 'facil'), ('Chima', 'facil'), ('Tube', 'facil'), 
        ('Véi', 'facil'), ('Gela', 'facil'), ('Busão', 'facil'),
        ('Capivara', 'medio'), ('Gurizão', 'medio'), ('Friaca', 'medio'), 
        ('Faceiro', 'medio'), ('Sinaleiro', 'medio'), ('Penal', 'medio'), 
        ('Doleira', 'medio'), ('Japona', 'medio'), ('Cancha', 'medio'),
        ('Leite quente', 'dificil'), ('Pão com vina', 'dificil'), 
        ('Chuva oblíqua', 'dificil'), ('Barigui lover', 'dificil'), 
        ('Biarticulado', 'dificil'), ('Petit Pavê', 'dificil'),
        ('Deus me livre', 'dificil'), ('Quem me dera', 'dificil')"
    ];

    foreach ($queries as $query) {
        $pdo->exec($query);
    }
    
    echo "✅ Tablas creadas y datos insertados.<br>";
    echo "<h2>🎉 ¡TODO LISTO! <a href='index.php'>Haz clic aquí para ir al juego</a></h2>";

} catch (PDOException $e) {
    die("❌ Error SQL: " . $e->getMessage());
}
?>