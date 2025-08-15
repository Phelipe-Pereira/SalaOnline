<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();

if ($auth->isLoggedIn()) {
    redirecionar('index.php');
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = limparInput($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos';
    } else {
        $resultado = $auth->login($email, $senha);
        
        if ($resultado['success']) {
            mostrarMensagem('success', $resultado['message']);
            redirecionar('index.php');
        } else {
            $erro = $resultado['message'];
        }
    }
}
?>

<?php
$pageTitle = 'Login - ' . SITE_NAME;
include 'templates/header.php';
?>

    <main class="main-content">
        <div class="container">
            <div class="card" style="max-width: 400px; margin: 2rem auto;">
                <div class="card-header">
                    <h1 class="card-title">Entrar no Sistema</h1>
                    <p>Faça login para acessar suas reservas</p>
                </div>

                <?php if ($erro): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="form-group">
                        <label class="form-label" for="email">E-mail</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="senha">Senha</label>
                        <div style="position: relative;">
                            <input type="password" id="senha" name="senha" class="form-control" required>
                            <button type="button" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;" 
                                    onclick="togglePasswordVisibility('senha')">👁️‍🗨️</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            Entrar
                        </button>
                    </div>

                    <div style="text-align: center; margin-top: 1rem;">
                        <p>Não tem uma conta? <a href="register.php">Cadastre-se</a></p>
                    </div>
                </form>
            </div>
        </div>
    </main>

<?php include 'templates/footer.php'; ?>
