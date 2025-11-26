<?php
/**
 * Capivarinha_Curitibaninha - Tela de Cadastro
 * Formulário para registro de novos jogadores.
 * Autor: Maoly Lara Serrano
 */

// Se já estiver logado, não precisa criar conta
if (isset($_SESSION['user_id'])) {
    header("Location: index.php?page=game");
    exit;
}

require 'includes/header.php';

// Inicializa variáveis se não estiverem definidas (boas práticas)
$erro = $erro ?? '';
$sucesso = $sucesso ?? '';
?>

<div class="background-container">
    <main class="app-frame" style="justify-content: center; align-items: center; background: radial-gradient(circle, #ffffff 0%, #E1F5FE 100%);">
        
        <!-- Cabeçalho Simples -->
        <div style="text-align: center; margin-bottom: 15px;">
            <img src="assets/img/icons/Gralha_Azul.png" style="width: 60px;" alt="Gralha Azul">
            <h2 style="color: #0277BD; margin-top: 5px; font-size: 1.5rem;">Criar Conta</h2>
            <p style="color: #666; font-size: 0.9rem;">Venha fazer parte da piazada!</p>
        </div>

        <!-- Caixa de Cadastro -->
        <div class="auth-box" style="max-height: 75vh; overflow-y: auto;">
            
            <?php if ($erro): ?>
                <div style="background-color: #FFEBEE; color: #C62828; padding: 10px; border-radius: 10px; margin-bottom: 15px; font-size: 0.9rem; border: 1px solid #FFCDD2;">
                    <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($sucesso): ?>
                <div style="background-color: #E8F5E9; color: #2E7D32; padding: 10px; border-radius: 10px; margin-bottom: 15px; font-size: 0.9rem; border: 1px solid #4CAF50; font-weight: bold;">
                    <?php echo htmlspecialchars($sucesso); ?>
                </div>
                <div style="margin-bottom: 15px;">
                    <a href="index.php?page=login" class="btn-primary" style="text-decoration: none; display: inline-block; background-color: #2E7D32;">Ir para Login</a>
                </div>
            <?php else: ?>
                <!-- Formulário de Cadastro -->
                <form method="POST" action="index.php?page=register">
                    <!-- Token CSRF para segurança -->
                    <?php if (function_exists('csrfInput')) echo csrfInput(); ?>
                    
                    <div style="text-align: left;">
                        <label style="font-weight: bold; color: #444; font-size: 0.85rem;">Nome ou Apelido:</label>
                        <input type="text" name="nome" class="form-control" placeholder="Ex: Zé do Pinhão" required value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>">
                    </div>

                    <div style="text-align: left; margin-top: 10px;">
                        <label style="font-weight: bold; color: #444; font-size: 0.85rem;">E-mail:</label>
                        <input type="email" name="email" class="form-control" placeholder="seu@email.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>

                    <div style="text-align: left; margin-top: 10px;">
                        <label style="font-weight: bold; color: #444; font-size: 0.85rem;">Senha:</label>
                        <input type="password" name="senha" class="form-control" placeholder="Mínimo 6 caracteres" required>
                    </div>

                    <div style="text-align: left; margin-top: 10px;">
                        <label style="font-weight: bold; color: #444; font-size: 0.85rem;">Confirmar Senha:</label>
                        <input type="password" name="confirmar_senha" class="form-control" placeholder="Repita a senha" required>
                    </div>

                    <button type="submit" class="btn-primary" style="margin-top: 20px; background-color: #0288D1; box-shadow: 0 4px 0 #01579B;">
                        CADASTRAR
                    </button>
                </form>

                <div style="margin-top: 15px; font-size: 0.85rem; padding-top: 10px; border-top: 1px solid #eee;">
                    Já tem cadastro? <br>
                    <a href="index.php?page=login" style="color: #2E7D32; font-weight: bold; text-decoration: none;">Faça login aqui</a>
                </div>
            <?php endif; ?>
            
        </div>

    </main>
</div>

<?php require 'includes/footer.php'; ?>