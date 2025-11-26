<?php
/**
 * Script para criar e popular a tabela 'termos_jogo' com níveis de dificuldade
 */
require 'config/database.php';
$pdo = getDatabaseConnection();

try {
    echo "🔧 Criando tabela 'termos_jogo'...\n";
    
    // Cria tabela compatível com a lógica de dificuldade
    $pdo->exec("CREATE TABLE IF NOT EXISTS termos_jogo (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        termo TEXT NOT NULL,
        dica TEXT,
        dificuldade TEXT DEFAULT 'medio'
    )");

    echo "📚 Inserindo gírias...\n";
    
    // Limpa dados antigos para não duplicar
    $pdo->exec("DELETE FROM termos_jogo");

    $sql = "INSERT INTO termos_jogo (termo, dica, dificuldade) VALUES 
    -- Fáceis
    ('Vina', 'Apelido curto para salsicha', 'facil'), 
    ('Piá', 'Garoto, menino', 'facil'), 
    ('Guria', 'Menina ou jovem mulher', 'facil'), 
    ('Capi', 'Diminutivo de capivara', 'facil'), 
    ('Chima', 'Gíria local para chimarrão', 'facil'), 
    ('Tubo', 'Estação de ônibus cilíndrica', 'facil'), 
    ('Véi', 'Forma coloquial de chamar alguém', 'facil'), 
    ('Gela', 'Cerveja gelada', 'facil'),
    ('Busão', 'Ônibus', 'facil'),

    -- Médias
    ('Capivara', 'Animal símbolo da cidade', 'medio'), 
    ('Gurizão', 'Versão aumentativa de gurizinho', 'medio'), 
    ('Friaca', 'Frio intenso', 'medio'), 
    ('Faceiro', 'Alegre, satisfeito', 'medio'),
    ('Sinaleiro', 'Semáforo', 'medio'), 
    ('Penal', 'Estojo escolar', 'medio'), 
    ('Doleira', 'Carteira de dinheiro', 'medio'), 
    ('Japona', 'Jaqueta grossa', 'medio'),
    ('Cancha', 'Quadra de esportes', 'medio'),

    -- Difíceis
    ('Leite quente', 'Bebida, ou gente que reclama de tudo', 'dificil'), 
    ('Pão com vina', 'Cachorro-quente curitibano', 'dificil'), 
    ('Chuva oblíqua', 'Chuva com vento que molha tudo', 'dificil'),
    ('Barigui lover', 'Quem adora o parque Barigui', 'dificil'), 
    ('Biarticulado', 'Ônibus vermelho gigante', 'dificil'), 
    ('Petit Pavê', 'Calçada de pedras portuguesas', 'dificil'),
    ('Deus me livre', 'Expressão de espanto', 'dificil'),
    ('Quem me dera', 'Expressão de desejo', 'dificil')";

    $pdo->exec($sql);
    
    echo "✅ Sucesso! Tabela criada e palavras inseridas.\n";
    echo "Pode rodar o jogo agora!";

} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>