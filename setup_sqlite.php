<?php
/**
 * Script para inicializar o banco SQLite com o schema
 */

$db_dir = __DIR__ . '/data';
if (!is_dir($db_dir)) {
    mkdir($db_dir, 0755, true);
}

$sqlite_path = $db_dir . '/capityper.db';

// Remover banco anterior se existir (para reset)
// unlink($sqlite_path);

try {
    $pdo = new PDO("sqlite:$sqlite_path");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA foreign_keys = ON");
    
    // Criar tabelas
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        senha TEXT NOT NULL,
        data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
    )
    ");
    
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS ligas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome_liga TEXT NOT NULL,
        palavra_chave TEXT NOT NULL,
        criador_id INTEGER,
        data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (criador_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )
    ");
    
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS liga_membros (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        liga_id INTEGER NOT NULL,
        usuario_id INTEGER NOT NULL,
        data_entrada DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(liga_id, usuario_id),
        FOREIGN KEY (liga_id) REFERENCES ligas(id) ON DELETE CASCADE,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )
    ");
    
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS palavras (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        palavra TEXT NOT NULL UNIQUE,
        dica TEXT,
        categoria TEXT DEFAULT 'curitiba'
    )
    ");
    
    $pdo->exec("

    // Tabela para tentativas de login (bloqueio por força bruta)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT,
        ip TEXT,
        sucesso INTEGER DEFAULT 0,
        tentativa_em DATETIME DEFAULT CURRENT_TIMESTAMP
    )
    ");

    // Índices para melhorar performance nas queries de ranking e buscas
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_scores_usuario ON scores(usuario_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_scores_data ON scores(data_jogo)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_palavras_categoria ON palavras(categoria)");
    CREATE TABLE IF NOT EXISTS scores (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id INTEGER,
        liga_id INTEGER,
        palavras_acertadas INTEGER DEFAULT 0,
        palavras_erradas INTEGER DEFAULT 0,
        tempo_gasto INTEGER,
        data_jogo DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (liga_id) REFERENCES ligas(id) ON DELETE CASCADE
    )
    ");
    
    echo "✅ Banco de dados SQLite inicializado com sucesso!\n";
    echo "Caminho: $sqlite_path\n";
    
} catch (PDOException $e) {
    echo "❌ Erro ao criar banco SQLite: " . $e->getMessage() . "\n";
    exit(1);
}
?>
