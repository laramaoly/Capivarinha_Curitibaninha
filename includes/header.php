<?php
/**
 * Capivarinha_Curitibaninha - Cabeçalho Padrão
 * Responsável pela abertura do HTML, meta tags e inclusão de CSS.
 * Autor: Maoly Lara Serrano
 */

// Garante que a sessão está iniciada em todas as páginas que incluírem este header
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <title>Capivarinha: Desafio Curitibanha Raiz</title>
    
    <!-- Favicon (Usa o pinhão dourado como ícone da aba) -->
    <link rel="icon" type="image/png" href="assets/img/icons/Pinhao_Dourado.png">

    <!-- Fontes do Google (Varela Round para o estilo 'fofo/arredondado') -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">

    <!-- Estilos CSS Principais -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Lógica condicional: Carrega o CSS específico do jogo apenas na página do jogo -->
    <?php 
    $page = isset($_GET['page']) ? $_GET['page'] : '';
    if ($page == 'game' || $page == ''): 
    ?>
    <link rel="stylesheet" href="assets/css/game.css">
    <?php endif; ?>

</head>
<body>
    <!-- O corpo fecha no footer.php -->