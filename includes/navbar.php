<?php
/**
 * Capivarinha_Curitibaninha - Barra de Navegação (Menu Inferior)
 * Responsável pela navegação entre Jogo, Rankings e Ligas.
 * Autor: Maoly Lara Serrano
 */

// Obtém a página atual da URL para definir classe 'active' visualmente
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'game';
?>

<!-- Navegação Inferior Fixa -->
<nav class="bottom-nav">
    
    <!-- Botão JOGAR (Home) -->
    <div class="nav-item <?php echo ($currentPage == 'game') ? 'active' : ''; ?>" 
         onclick="window.location.href='index.php?page=game'"
         title="Ir para o Jogo">
        <img src="assets/img/icons/Estacao_Tubo.png" alt="Jogar">
        <small>Jogar</small>
    </div>
    
    <!-- Botão RANKING -->
    <div class="nav-item <?php echo ($currentPage == 'ranking') ? 'active' : ''; ?>" 
         onclick="window.location.href='index.php?page=ranking'"
         title="Ver Classificação">
        <img src="assets/img/icons/Araucaria.png" alt="Ranking">
        <small>Ranking</small>
    </div>
    
    <!-- Botão LIGAS -->
    <div class="nav-item <?php echo ($currentPage == 'leagues') ? 'active' : ''; ?>" 
         onclick="window.location.href='index.php?page=leagues'"
         title="Gerenciar Ligas">
        <img src="assets/img/icons/Gralha_Azul.png" alt="Ligas">
        <small>Ligas</small>
    </div>
    
    <!-- Botão SAIR (Logout) -->
    <!-- Nota: A confirmação de saída é tratada pelo main.js -->
    <div class="nav-item" onclick="window.location.href='logout.php'" title="Sair do Sistema">
        <img src="assets/img/icons/Guarda-chuva_Petit-Pave.png" alt="Sair">
        <small>Sair</small>
    </div>
    
</nav>