<?php
/**
 * Capivarinha_Curitibaninha - Dashboard de Ligas
 * Permite criar, listar e entrar em ligas de competição.
 * Autor: Maoly Lara Serrano
 */

// Verifica se o utilizador está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit;
}

require_once 'controllers/LeagueController.php';

// Inicializa o controlador
$leagueCtrl = new LeagueController($pdo);
$msg = '';
$msgType = '';

// --- Processamento de Formulários (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Criar Nova Liga
    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $nome = trim($_POST['nome_liga']);
        $senha = trim($_POST['senha_liga']);
        
        if (!empty($nome) && !empty($senha)) {
            $result = $leagueCtrl->createLeague($nome, $senha, $_SESSION['user_id']);
            $msg = $result['message'];
            $msgType = $result['success'] ? 'success' : 'error';
        } else {
            $msg = "Preenche tudo, piá!";
            $msgType = 'error';
        }
    }

    // 2. Entrar em Liga Existente
    if (isset($_POST['action']) && $_POST['action'] === 'join') {
        $liga_id = (int)$_POST['liga_id'];
        $senha = trim($_POST['senha_entrada']);
        
        $result = $leagueCtrl->joinLeague($liga_id, $senha, $_SESSION['user_id']);
        $msg = $result['message'];
        $msgType = $result['success'] ? 'success' : 'error';
    }
}

// Busca dados para exibição
$minhasLigas = $leagueCtrl->getUserLeagues($_SESSION['user_id']);
$todasLigas = $leagueCtrl->getAllLeagues();

// Inclui o cabeçalho global
require 'includes/header.php';
?>

<div class="background-container">
    <main class="app-frame" style="overflow-y: auto;"> <!-- Scroll permitido aqui -->
        
        <!-- Cabeçalho Interno -->
        <header class="game-header">
            <div style="display: flex; align-items: center;">
                <img src="assets/img/icons/Gralha_Azul.png" style="width: 30px; margin-right: 10px;">
                <h3 style="margin: 0; color: #2E7D32;">Ligas da Galera</h3>
            </div>
            <!-- Botão de Ajuda Simples -->
            <div onclick="alert('Ligas são grupos privados. Crie um e passe a senha pros amigos!')" style="cursor: pointer; font-size: 1.2rem;">ℹ️</div>
        </header>

        <div style="padding: 20px; padding-bottom: 80px;"> <!-- Padding bottom extra pro menu não cobrir -->

            <!-- Mensagens de Feedback -->
            <?php if ($msg): ?>
                <div style="padding: 10px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-weight: bold; 
                    background-color: <?php echo $msgType == 'success' ? '#E8F5E9' : '#FFEBEE'; ?>; 
                    color: <?php echo $msgType == 'success' ? '#2E7D32' : '#C62828'; ?>;
                    border: 1px solid <?php echo $msgType == 'success' ? '#4CAF50' : '#EF5350'; ?>;">
                    <?php echo htmlspecialchars($msg); ?>
                </div>
            <?php endif; ?>

            <!-- Seção 1: Minhas Ligas -->
            <section style="margin-bottom: 30px;">
                <h4 style="color: #555; border-bottom: 2px solid #FFC107; display: inline-block; margin-bottom: 15px;">
                    Minhas Ligas
                </h4>
                
                <?php if (empty($minhasLigas)): ?>
                    <div style="text-align: center; padding: 20px; background: #f9f9f9; border-radius: 15px; color: #888;">
                        <p>Você ainda não tá em nenhuma liga, piazão.</p>
                        <small>Crie uma ou entre na dos amigos abaixo!</small>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($minhasLigas as $liga): ?>
                            <div style="background: white; padding: 15px; border-radius: 15px; border: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                                <div>
                                    <strong style="color: #2E7D32; font-size: 1.1rem;"><?php echo htmlspecialchars($liga['nome_liga']); ?></strong>
                                    <div style="font-size: 0.8rem; color: #777;">
                                        👥 <?php echo $liga['total_membros']; ?> membros
                                    </div>
                                </div>
                                <a href="index.php?page=ranking&liga_id=<?php echo $liga['id']; ?>" 
                                   style="text-decoration: none; background: #FFC107; color: #333; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;">
                                   Ver Ranking
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Seção 2: Entrar em uma Liga -->
            <section style="margin-bottom: 30px;">
                <h4 style="color: #555; border-bottom: 2px solid #2E7D32; display: inline-block; margin-bottom: 15px;">
                    Entrar numa Liga
                </h4>
                
                <div class="auth-box" style="width: 100%; text-align: left; background: #F1F8E9;">
                    <form method="POST">
                        <input type="hidden" name="action" value="join">
                        
                        <label style="font-size: 0.9rem; font-weight: bold; color: #444;">Escolha a Liga:</label>
                        <select name="liga_id" class="form-control" required style="background: white;">
                            <option value="">Selecione...</option>
                            <?php foreach ($todasLigas as $l): ?>
                                <option value="<?php echo $l['id']; ?>">
                                    <?php echo htmlspecialchars($l['nome_liga']); ?> (Criada por: <?php echo htmlspecialchars($l['criador']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <label style="font-size: 0.9rem; font-weight: bold; color: #444; margin-top: 10px; display: block;">Palavra-chave (Senha):</label>
                        <input type="password" name="senha_entrada" class="form-control" placeholder="Digite a senha da liga" required style="background: white;">

                        <button type="submit" class="btn-primary" style="font-size: 0.9rem; padding: 10px;">Entrar agora</button>
                    </form>
                </div>
            </section>

            <!-- Seção 3: Criar Nova Liga -->
            <section>
                <h4 style="color: #555; border-bottom: 2px solid #0288D1; display: inline-block; margin-bottom: 15px;">
                    Criar Nova Liga
                </h4>
                
                <div class="auth-box" style="width: 100%; text-align: left; border: 2px dashed #ccc;">
                    <form method="POST">
                        <input type="hidden" name="action" value="create">
                        
                        <label style="font-size: 0.9rem; font-weight: bold; color: #444;">Nome da Liga:</label>
                        <input type="text" name="nome_liga" class="form-control" placeholder="Ex: Piazada do TADS" required>

                        <label style="font-size: 0.9rem; font-weight: bold; color: #444; margin-top: 10px; display: block;">Definir Palavra-chave:</label>
                        <input type="text" name="senha_liga" class="form-control" placeholder="Senha para os amigos entrarem" required>

                        <button type="submit" class="btn-primary" style="background-color: #0288D1; box-shadow: 0 4px 0 #01579B; font-size: 0.9rem; padding: 10px;">
                            Criar Liga
                        </button>
                    </form>
                </div>
            </section>

        </div>

        <!-- Menu de Navegação -->
        <?php require 'includes/navbar.php'; ?>
        
    </main>
</div>

<?php require 'includes/footer.php'; ?>