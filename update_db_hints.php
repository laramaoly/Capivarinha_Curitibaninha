<?php
require 'config/database.php';

echo "Atualizando banco com dicas...\n";

// 1. Adiciona a coluna 'dica' se ela não existir
$colExists = false;
try {
    $result = $pdo->query("SHOW COLUMNS FROM termos_jogo LIKE 'dica'");
    if ($result && $result->rowCount() > 0) {
        $colExists = true;
        echo "Coluna 'dica' já existe.\n";
    }
} catch (PDOException $e) {
    // Ignorar
}
if (!$colExists) {
    try {
        $pdo->exec("ALTER TABLE termos_jogo ADD COLUMN dica VARCHAR(255) DEFAULT NULL;");
        echo "Coluna 'dica' criada.\n";
    } catch (PDOException $e) {
        echo "Erro ao criar coluna 'dica': ".$e->getMessage()."\n";
    }
}

// 2. Lista de palavras e suas dicas (Pista -> Resposta)
$updates = [
    // Fáceis
    'Vina' => 'Salsicha do cachorro-quente',
    'Piá' => 'Menino / Garoto',
    'Guria' => 'Menina / Garota',
    'Capi' => 'Apelido carinhoso da Capivara',
    'Chima' => 'Bebida quente típica (abreviada)',
    'Tube' => 'Estação de ônibus de vidro',
    'Véi' => 'Vocativo para amigo / Cara',
    'Gela' => 'Cerveja gelada',
    'Busão' => 'Transporte coletivo / Ônibus',

    // Médias
    'Capivara' => 'Maior roedor do mundo / Mascote da cidade',
    'Gurizão' => 'Rapaz grande / Adulto jovem',
    'Friaca' => 'Frio muito intenso',
    'Faceiro' => 'Muito feliz / Contente',
    'Sinaleiro' => 'Semáforo de trânsito',
    'Penal' => 'Estojo escolar',
    'Doleira' => 'Pochete usada por dentro da roupa',
    'Japona' => 'Jaqueta grossa de nylon',
    'Cancha' => 'Quadra de esportes',

    // Difíceis
    'Leite quente' => 'Apelido da cidade ("Curitiba leite...")',
    'Pão com vina' => 'Como chamamos o Hot Dog',
    'Chuva oblíqua' => 'Chuva com vento que molha tudo',
    'Barigui lover' => 'Quem adora o parque mais famoso',
    'Biarticulado' => 'Ônibus vermelho gigante',
    'Petit Pavê' => 'Calçada de pedrinhas (mosaico)',
    'Deus me livre' => 'Expressão de negação enfática',
    'Quem me dera' => 'Expressão de desejo intenso'
];

// 3. Executa as atualizações
foreach ($updates as $termo => $dica) {
    $stmt = $pdo->prepare("UPDATE termos_jogo SET dica = ? WHERE termo = ?");
    $stmt->execute([$dica, $termo]);
}

echo "✅ Banco atualizado com sucesso! Agora as palavras têm dicas.";
?>
