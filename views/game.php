<?php
/**
 * Capivarinha_Curitibaninha - Tela Principal do Jogo
 * Onde a mágica acontece: HUD, Personagem e Canvas de Digitação.
 * Autor: Maoly Lara Serrano
 */

// Verifica se o utilizador está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit;
}

require 'controllers/RankingController.php';

// Inicializa controlador para buscar pontuação total do utilizador
$rankingCtrl = new RankingController($pdo);
$totalScore = $rankingCtrl->getUserTotalScore($_SESSION['user_id']);

require 'includes/header.php';
?>

<div class="background-container">
    
    <!-- O "Telemóvel" Central -->
    <main class="app-frame">
        
        <!-- HUD Superior (Cabeçalho do Jogo) -->
        <header class="game-header">
            <!-- Pontuação da Sessão Atual -->
            <div class="currency-badge" title="Pontuação da Partida">
                <img src="assets/img/icons/Pinhao_Dourado.png" alt="Pontos">
                <span id="score">0</span>
            </div>
            
            <!-- Vidas Restantes -->
            <div class="currency-badge" title="Vidas (Gengibirra)">
                <img src="assets/img/icons/Gengibirra.png" alt="Vidas">
                <span id="lives">3</span>
            </div>

            <!-- Pontuação Total Acumulada (Histórico) -->
            <div class="currency-badge" title="Total Acumulado (Ranking)">
                <img src="assets/img/icons/Cartao_Transporte.png" alt="Total">
                <span style="font-size: 0.9rem;"><?php echo $totalScore; ?></span>
            </div>
        </header>

        <!-- Área Central Interativa -->
        <div id="game-canvas">
            
            <!-- Tela 1: Início -->
            <div id="start-screen" class="screen-section">
                <img src="assets/img/icons/Araucaria.png" style="width: 80px; margin-bottom: 20px; filter: drop-shadow(2px 4px 6px rgba(0,0,0,0.2));">
                <h2>Desafio Raiz</h2>
                <p>Digite as gírias curitibanas antes que o tempo acabe, piá!</p>
                
                <button id="btn-start" class="btn-game">
                    JOGAR AGORA
                </button>
                
                <div style="margin-top: 20px; font-size: 0.8rem; color: #888;">
                    Dica: Acentos não são obrigatórios.
                </div>
            </div>

            <!-- Tela 2: Gameplay (Oculta inicialmente) -->
            <div id="gameplay-screen" class="screen-section hidden">
                <!-- Barra de Tempo -->
                <div class="timer-container">
                    <div id="timer-bar"></div>
                </div>

                <!-- Palavra do Jogo -->
                <h2 class="word-display" id="current-word">Carregando...</h2>
                
                <!-- Input de Digitação -->
                <input type="text" id="typing-input" autocomplete="off" placeholder="Digite aqui..." spellcheck="false">
                
                <!-- Mensagem de Feedback (Errou/Acertou) -->
                <p id="feedback-msg"></p>
            </div>

        </div>

        <!-- Personagem Reativo (Capivariña) -->
        <!-- Fica fora do game-canvas para sobrepor a borda se necessário -->
        <div class="mascot-container">
            <img src="assets/img/char-capivara-happy.png" id="mascot" class="mascot-img" alt="Capivariña">
        </div>

        <!-- Navegação Inferior (Importada) -->
        <?php require 'includes/navbar.php'; ?>

    </main>
</div>

<!-- Carrega lógica específica do jogo -->
<script src="assets/js/game.js"></script>

<?php require 'includes/footer.php'; ?>