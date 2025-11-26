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

require_once 'controllers/AuthController.php';

$auth = new AuthController($pdo);
$msg = '';
$msgType = '';

// Processamento do Formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $confirmaSenha = trim($_POST['confirmar_senha']);

    // Validação básica de senhas
    if ($senha !== $confirmaSenha) {
        $msg = "Bah, piá! As senhas não batem. Tenta de novo.";
        $msgType = "error";
    } elseif (strlen($senha) < 6) {
        $msg = "A senha precisa ter pelo menos 6 caracteres pra ser segura.";
        $msgType = "error";
    } else {
        // Tenta registrar
        $resultado = $auth->register($nome, $email, $senha);

        if ($resultado === true) {
            $msg = "Cadastro feito com sucesso! Agora é só logar.";
            $msgType = "success";
        } else {
            // Exibe o erro retornado pelo Controller
            $msg = $resultado;
            $msgType = "error";
        }
    }
}

require 'includes/header.php';
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
            
            <?php if ($msg): ?>
                <div style="padding: 10px; border-radius: 10px; margin-bottom: 15px; font-size: 0.9rem; font-weight: bold;
                    background-color: <?php echo $msgType == 'success' ? '#E8F5E9' : '#FFEBEE'; ?>; 
                    color: <?php echo $msgType == 'success' ? '#2E7D32' : '#C62828'; ?>;
                    border: 1px solid <?php echo $msgType == 'success' ? '#4CAF50' : '#EF5350'; ?>;">
                    <?php echo $msg; ?>
                </div>
                
                <?php if ($msgType == 'success'): ?>
                    <div style="margin-bottom: 15px;">
                        <a href="index.php?page=login" class="btn-primary" style="text-decoration: none; display: inline-block; background-color: #2E7D32;">Ir para Login</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Formulário (Oculta se tiver sucesso para forçar login) -->
            <!-- AQUI ESTAVA O ERRO: Usamos sintaxe alternativa (:) para o HTML ficar limpo -->
            <?php if ($msgType !== 'success'): ?>
            <form method="POST" action="index.php?page=register">
                
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
            <?php endif; ?> <!-- Este endif fecha o if da linha 82 -->

            <div style="margin-top: 15px; font-size: 0.85rem; padding-top: 10px; border-top: 1px solid #eee;">
                Já tem cadastro? <br>
                <a href="index.php?page=login" style="color: #2E7D32; font-weight: bold; text-decoration: none;">Faça login aqui</a>
            </div>
            
        </div>

    </main>
</div>

<?php require 'includes/footer.php'; ?>