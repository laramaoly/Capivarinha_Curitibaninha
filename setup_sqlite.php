<?php
/**
 * Script para inicializar o banco SQLite com o schema corrigido
 */

$db_dir = __DIR__ . '/data';
if (!is_dir($db_dir)) {
    mkdir($db_dir, 0755, true);
}

$sqlite_path = $db_dir . '/capityper.db';

try {
    $pdo = new PDO("sqlite:$sqlite_path");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA foreign_keys = ON");
    
    // 1. Tabela de Usuários
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        senha TEXT NOT NULL,
        data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
    )
    ");
    
    // 2. Tabela de Ligas
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
    
    // 3. Membros da Liga
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
    
    // 4. Palavras do Jogo
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS palavras (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        palavra TEXT NOT NULL UNIQUE,
        dica TEXT,
        categoria TEXT DEFAULT 'curitiba'
    )
    ");
    
    // 5. Tentativas de Login (Segurança)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT,
        ip TEXT,
        sucesso INTEGER DEFAULT 0,
        tentativa_em DATETIME DEFAULT CURRENT_TIMESTAMP
    )
    ");

    // 6. Tabela de Partidas (IMPORTANTE: Nome 'partidas' para compatibilidade)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS partidas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id INTEGER,
        pontuacao INTEGER DEFAULT 0,
        palavras_acertadas INTEGER DEFAULT 0,
        data_partida DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )
    ");
    
    // 7. Índices para Performance
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_partidas_usuario ON partidas(usuario_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_partidas_data ON partidas(data_partida)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_palavras_categoria ON palavras(categoria)");

    echo "✅ Banco de dados SQLite inicializado com sucesso!\n";
    echo "Caminho: $sqlite_path\n";
    
} catch (PDOException $e) {
    echo "❌ Erro ao criar banco SQLite: " . $e->getMessage() . "\n";
    exit(1);
}
?>
