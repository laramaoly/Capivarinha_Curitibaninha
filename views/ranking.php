<?php
/**
 * Capivarinha_Curitibaninha - Tela de Rankings
 * Exibe pontuações gerais, semanais, por liga e histórico pessoal.
 * Autor: Maoly Lara Serrano
 */

// Verifica se o utilizador está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit;
}

require_once 'controllers/RankingController.php';
require_once 'controllers/LeagueController.php';

$rankingCtrl = new RankingController($pdo);
$leagueCtrl = new LeagueController($pdo);

// Determina qual visualização carregar (padrão: global)
$view = isset($_GET['view']) ? $_GET['view'] : 'global';
$liga_id = isset($_GET['liga_id']) ? (int)$_GET['liga_id'] : null;

// Dados para renderização
$titulo = "Ranking Geral";
$subtitulo = "Quem manda na cidade";
$dadosRanking = [];
$meuHistorico = [];

// Lógica de Seleção de Dados
if ($view === 'history') {
    // Visualização: Histórico Pessoal
    $titulo = "Meu Diário";
    $subtitulo = "Suas partidas jogadas";
    $meuHistorico = $rankingCtrl->getUserHistory($_SESSION['user_id']);

} elseif ($liga_id) {
    // Visualização: Ranking de uma Liga Específica
    // Busca nome da liga para o título (uma query rápida inline ou idealmente no controller)
    $stmt = $pdo->prepare("SELECT nome_liga FROM ligas WHERE id = ?");
    $stmt->execute([$liga_id]);
    $nomeLiga = $stmt->fetchColumn();
    
    $titulo = "Liga: " . htmlspecialchars($nomeLiga);
    
    if ($view === 'weekly') {
        $subtitulo = "Top da Semana";
        $dadosRanking = $rankingCtrl->getLeagueRankingWeekly($liga_id);
    } else {
        $subtitulo = "Top de Todos os Tempos";
        $dadosRanking = $rankingCtrl->getLeagueRanking($liga_id);
    }

} else {
    // Visualização: Ranking Global (Padrão)
    if ($view === 'weekly') {
        $titulo = "Ranking Semanal";
        $subtitulo = "Os melhores desta semana";
        $dadosRanking = $rankingCtrl->getGlobalRankingWeekly();
    } else {
        $titulo = "Hall da Fama";
        $subtitulo = "Os maiores pontuadores de sempre";
        $dadosRanking = $rankingCtrl->getGlobalRanking();
    }
}

require 'includes/header.php';
?>

<div class="background-container">
    <main class="app-frame" style="overflow-y: auto;">
        
        <!-- Cabeçalho Interno -->
        <header class="game-header" style="flex-direction: column; align-items: flex-start;">
            <div style="display: flex; align-items: center; width: 100%; justify-content: space-between;">
                <div style="display: flex; align-items: center;">
                    <img src="assets/img/icons/Araucaria.png" style="width: 30px; margin-right: 10px;">
                    <div>
                        <h3 style="margin: 0; color: #2E7D32; font-size: 1.1rem;"><?php echo $titulo; ?></h3>
                        <small style="color: #777;"><?php echo $subtitulo; ?></small>
                    </div>
                </div>
                
                <!-- Botão Voltar (Se estiver vendo liga) -->
                <?php if ($liga_id): ?>
                    <a href="index.php?page=leagues" style="text-decoration: none; font-size: 1.5rem;">↩️</a>
                <?php endif; ?>
            </div>

            <!-- Navegação de Abas (Tabs) -->
            <div style="display: flex; gap: 10px; margin-top: 15px; width: 100%;">
                
                <!-- Link Geral/Total -->
                <a href="index.php?page=ranking<?php echo $liga_id ? '&liga_id='.$liga_id : ''; ?>" 
                   style="flex: 1; text-align: center; padding: 8px; border-radius: 15px; font-size: 0.8rem; font-weight: bold; text-decoration: none; 
                   <?php echo ($view != 'weekly' && $view != 'history') ? 'background: #2E7D32; color: white;' : 'background: #eee; color: #555;'; ?>">
                   Geral
                </a>

                <!-- Link Semanal -->
                <a href="index.php?page=ranking&view=weekly<?php echo $liga_id ? '&liga_id='.$liga_id : ''; ?>" 
                   style="flex: 1; text-align: center; padding: 8px; border-radius: 15px; font-size: 0.8rem; font-weight: bold; text-decoration: none;
                   <?php echo ($view == 'weekly') ? 'background: #2E7D32; color: white;' : 'background: #eee; color: #555;'; ?>">
                   Semanal
                </a>

                <!-- Link Histórico (Só aparece se não estiver em liga específica) -->
                <?php if (!$liga_id): ?>
                <a href="index.php?page=ranking&view=history" 
                   style="flex: 1; text-align: center; padding: 8px; border-radius: 15px; font-size: 0.8rem; font-weight: bold; text-decoration: none;
                   <?php echo ($view == 'history') ? 'background: #FFC107; color: #333;' : 'background: #eee; color: #555;'; ?>">
                   Meu Histórico
                </a>
                <?php endif; ?>
            </div>
        </header>

        <div style="padding: 15px; padding-bottom: 80px;">

            <!-- TABELA: Histórico Pessoal -->
            <?php if ($view === 'history'): ?>
                <?php if (empty($meuHistorico)): ?>
                    <div style="text-align: center; padding: 40px; color: #999;">
                        <p>Você ainda não jogou nenhuma partida, piá.</p>
                        <button class="btn-primary" onclick="window.location.href='index.php?page=game'" style="width: auto; font-size: 0.9rem;">Jogar Agora</button>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($meuHistorico as $index => $partida): ?>
                            <div style="background: white; padding: 15px; border-radius: 15px; border-left: 5px solid #FFC107; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                                <div style="display: flex; justify-content: space-between;">
                                    <strong>Partida #<?php echo count($meuHistorico) - $index; ?></strong>
                                    <span style="color: #888; font-size: 0.8rem;"><?php echo $partida['data_formatada']; ?></span>
                                </div>
                                <div style="margin-top: 5px; display: flex; gap: 15px;">
                                    <span style="color: #2E7D32;">🏆 <?php echo $partida['pontuacao']; ?> pts</span>
                                    <span style="color: #555;">📝 <?php echo $partida['palavras_acertadas']; ?> palavras</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <!-- TABELA: Rankings (Geral, Semanal, Ligas) -->
            <?php else: ?>
                
                <?php if (empty($dadosRanking)): ?>
                    <div style="text-align: center; padding: 40px; color: #999;">
                        <img src="assets/img/char-capivara-sad.png" style="width: 80px; opacity: 0.5;">
                        <p>Ninguém pontuou ainda. Seja o primeiro!</p>
                    </div>
                <?php else: ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="color: #888; font-size: 0.8rem; text-align: left;">
                                <th style="padding: 10px;">#</th>
                                <th style="padding: 10px;">Jogador</th>
                                <th style="padding: 10px; text-align: right;">Pontos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $posicao = 1;
                            foreach ($dadosRanking as $rank): 
                                // Destaque para top 3
                                $medalha = '';
                                if ($posicao == 1) $medalha = '🥇';
                                if ($posicao == 2) $medalha = '🥈';
                                if ($posicao == 3) $medalha = '🥉';
                                
                                // Destaque se for o próprio usuário
                                $isMe = ($rank['nome'] === $_SESSION['user_nome']);
                                $bgStyle = $isMe ? 'background: #E8F5E9; border: 1px solid #C8E6C9;' : 'background: white; border-bottom: 1px solid #f0f0f0;';
                            ?>
                                <tr style="<?php echo $bgStyle; ?> border-radius: 10px;">
                                    <td style="padding: 15px 10px; font-weight: bold; color: #555;">
                                        <?php echo $medalha ? $medalha : $posicao; ?>
                                    </td>
                                    <td style="padding: 15px 10px; font-weight: <?php echo $isMe ? 'bold' : 'normal'; ?>;">
                                        <?php echo htmlspecialchars($rank['nome']); ?>
                                        <?php if($isMe) echo '<span style="font-size:0.7rem; color:#2E7D32;">(Você)</span>'; ?>
                                    </td>
                                    <td style="padding: 15px 10px; text-align: right; color: #2E7D32; font-weight: bold;">
                                        <?php echo $rank['total_pontos']; ?>
                                    </td>
                                </tr>
                            <?php 
                                $posicao++;
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            <?php endif; ?>

        </div>

        <!-- Menu de Navegação -->
        <?php require 'includes/navbar.php'; ?>
        
    </main>
</div>

<?php require 'includes/footer.php'; ?>