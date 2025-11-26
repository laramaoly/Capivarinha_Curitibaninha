<?php
/**
 * Capivarinha_Curitibaninha - Tela de Login
 * Formulário de entrada para utilizadores registados.
 * Autor: Maoly Lara Serrano
 */

// Se já estiver logado, manda para o jogo
if (isset($_SESSION['user_id'])) {
    header("Location: index.php?page=game");
    exit;
}

require 'includes/header.php';
?>

<div class="background-container">
    <main class="app-frame" style="justify-content: center; align-items: center; background: radial-gradient(circle, #ffffff 0%, #E8F5E9 100%);">
        
        <!-- Logotipo / Mascote -->
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="assets/img/char-capivara-happy.png" style="width: 140px; filter: drop-shadow(0 10px 10px rgba(0,0,0,0.1));" alt="Capivariña">
            <h2 style="color: #2E7D32; margin-top: 10px; font-size: 1.8rem;">Capivarinha</h2>
            <p style="color: #666;">O Desafio Curitibaninha Raiz</p>
        </div>

        <!-- Caixa de Login -->
        <div class="auth-box">
            
            <?php if ($erro): ?>
                <div style="background-color: #FFEBEE; color: #C62828; padding: 10px; border-radius: 10px; margin-bottom: 15px; font-size: 0.9rem; border: 1px solid #FFCDD2;">
                    <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=login">
                <?php echo csrfInput(); ?>
                <div style="text-align: left;">
                    <label style="font-weight: bold; color: #444; font-size: 0.9rem;">Seu E-mail:</label>
                    <input type="email" name="email" class="form-control" placeholder="exemplo@ufpr.br" required autofocus>
                </div>

                <div style="text-align: left; margin-top: 15px;">
                    <label style="font-weight: bold; color: #444; font-size: 0.9rem;">Sua Senha:</label>
                    <input type="password" name="senha" class="form-control" placeholder="********" required>
                </div>

                <button type="submit" class="btn-primary" style="margin-top: 25px;">
                    ENTRAR
                </button>
            </form>

            <div style="margin-top: 20px; font-size: 0.9rem;">
                Ainda não tem conta? <br>
                <a href="index.php?page=register" style="color: #FFC107; font-weight: bold; text-decoration: none;">Cadastre-se aqui, piá!</a>
            </div>
            
        </div>

        <!-- Créditos Rodapé -->
        <div style="position: absolute; bottom: 15px; font-size: 0.75rem; color: #999;">
            UFPR - DS122 - 2025 - Lara Serrano - GRR20250025
        </div>

    </main>
</div>

<?php require 'includes/footer.php'; ?>