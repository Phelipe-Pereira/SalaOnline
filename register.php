<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();

if ($auth->isLoggedIn()) {
    redirecionar('index.php');
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = limparInput($_POST['nome'] ?? '');
    $email = limparInput($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';
    
    if (empty($nome) || empty($email) || empty($senha) || empty($confirmarSenha)) {
        $erro = 'Por favor, preencha todos os campos';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Por favor, insira um e-mail válido';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres';
    } elseif ($senha !== $confirmarSenha) {
        $erro = 'As senhas não coincidem';
    } else {
        $resultado = $auth->register($nome, $email, $senha);
        
        if ($resultado['success']) {
            $sucesso = $resultado['message'];
        } else {
            $erro = $resultado['message'];
        }
    }
}
?>

<?php
$pageTitle = 'Cadastro - ' . SITE_NAME;
include 'templates/header.php';
?>

    <main class="main-content">
        <div class="container">
            <div class="card" style="max-width: 500px; margin: 2rem auto;">
                <div class="card-header">
                    <h1 class="card-title">Criar Conta</h1>
                    <p>Cadastre-se para começar a usar o sistema</p>
                </div>

                <?php if ($erro): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($sucesso) ?>
                        <br><br>
                        <a href="login.php" class="btn btn-primary">Fazer Login</a>
                    </div>
                <?php endif; ?>

                <?php if (!$sucesso): ?>
                    <form method="POST" action="register.php">
                        <div class="form-group">
                            <label class="form-label" for="nome">Nome Completo</label>
                            <input type="text" id="nome" name="nome" class="form-control" 
                                   value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="email">E-mail</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="senha">Senha</label>
                            <div style="position: relative;">
                                <input type="password" id="senha" name="senha" class="form-control" 
                                       minlength="6" required>
                                <button type="button" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;" 
                                        onclick="togglePasswordVisibility('senha')">👁️‍🗨️</button>
                            </div>
                            <small style="color: #666;">Mínimo 6 caracteres</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="confirmar_senha">Confirmar Senha</label>
                            <div style="position: relative;">
                                <input type="password" id="confirmar_senha" name="confirmar_senha" 
                                       class="form-control" minlength="6" required>
                                <button type="button" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;" 
                                        onclick="togglePasswordVisibility('confirmar_senha')">👁️‍🗨️</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">
                                Criar Conta
                            </button>
                        </div>

                        <div style="text-align: center; margin-top: 1rem;">
                            <p>Já tem uma conta? <a href="login.php">Faça login</a></p>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>

<?php include 'templates/footer.php'; ?>
