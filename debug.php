<?php
// 1. FORÇA O PHP A MOSTRAR TODOS OS ERROS NA TELA
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico do Capi-Typer 🧉</h1>";

// 2. TESTA SE O DRIVER MYSQL ESTÁ INSTALADO
echo "<p>Testando drivers instalados...</p>";
if (!extension_loaded('pdo_mysql')) {
    die("<h3 style='color:red'>ERRO CRÍTICO: O driver pdo_mysql NÃO está instalado/ativado. Rode 'sudo apt-get install php-mysql' no terminal.</h3>");
} else {
    echo "<span style='color:green'>Driver MySQL OK!</span><br>";
}

// 3. TESTA A CONEXÃO COM O BANCO
echo "<p>Tentando conectar ao banco...</p>";
try {
    // Tenta incluir o arquivo de configuração
    if (!file_exists('config/database.php')) {
        throw new Exception("Arquivo config/database.php não encontrado!");
    }
    require 'config/database.php';
    
    if (isset($pdo)) {
        echo "<h2 style='color:green'>SUCESSO! Conexão com o banco realizada!</h2>";
        echo "O problema provavelmente está no seu index.php ou nas views.";
    } else {
        echo "<h3 style='color:red'>ERRO: Arquivo carregado, mas a variável \$pdo não existe.</h3>";
    }

} catch (Exception $e) {
    echo "<h3 style='color:red'>ERRO DE CONEXÃO:</h3>";
    echo "<strong>" . $e->getMessage() . "</strong>";
    echo "<br><br>Verifique se o usuário/senha no arquivo <em>config/database.php</em> estão iguais aos que você criou no terminal.";
}
?>