<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$usuario = $auth->getCurrentUser();

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    if ($acao === 'atualizar_perfil') {
        $nome = limparInput($_POST['nome'] ?? '');
        $email = limparInput($_POST['email'] ?? '');
        
        if (empty($nome) || empty($email)) {
            $erro = 'Por favor, preencha todos os campos';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Por favor, insira um e-mail válido';
        } else {
            $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
            $db->query($sql, [$nome, $email, $usuario['id']]);
            
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_email'] = $email;
            
            $sucesso = 'Perfil atualizado com sucesso';
            $usuario['nome'] = $nome;
            $usuario['email'] = $email;
        }
    } elseif ($acao === 'alterar_senha') {
        $senhaAtual = $_POST['senha_atual'] ?? '';
        $novaSenha = $_POST['nova_senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';
        
        if (empty($senhaAtual) || empty($novaSenha) || empty($confirmarSenha)) {
            $erro = 'Por favor, preencha todos os campos';
        } elseif (strlen($novaSenha) < 6) {
            $erro = 'A nova senha deve ter pelo menos 6 caracteres';
        } elseif ($novaSenha !== $confirmarSenha) {
            $erro = 'As senhas não coincidem';
        } else {
            $sql = "SELECT senha FROM usuarios WHERE id = ?";
            $usuarioAtual = $db->fetch($sql, [$usuario['id']]);
            
            if (!password_verify($senhaAtual, $usuarioAtual['senha'])) {
                $erro = 'Senha atual incorreta';
            } else {
                $novaSenhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET senha = ? WHERE id = ?";
                $db->query($sql, [$novaSenhaHash, $usuario['id']]);
                
                $sucesso = 'Senha alterada com sucesso';
            }
        }
    }
}

$sql = "SELECT COUNT(*) as total FROM reservas WHERE usuario_id = ?";
$totalReservas = $db->fetch($sql, [$usuario['id']])['total'];

$sql = "SELECT COUNT(*) as total FROM reservas WHERE usuario_id = ? AND status = 'confirmada' AND data_reserva >= CURDATE()";
$reservasAtivas = $db->fetch($sql, [$usuario['id']])['total'];
?>

<?php
$pageTitle = 'Meu Perfil - ' . SITE_NAME;
include 'templates/header.php';
?>

    <main class="main-content">
        <div class="container">
            <?php if ($erro): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <?php if ($sucesso): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($sucesso) ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-2">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Informações do Perfil</h2>
                    </div>
                    
                    <div style="margin-bottom: 2rem;">
                        <p><strong>Nome:</strong> <?= htmlspecialchars($usuario['nome']) ?></p>
                        <p><strong>E-mail:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
                        <p><strong>Tipo de Conta:</strong> <?= ucfirst($usuario['tipo']) ?></p>
                        <p><strong>Membro desde:</strong> <?= formatarDataHora($usuario['data_criacao'] ?? date('Y-m-d H:i:s')) ?></p>
                    </div>

                    <div class="grid grid-2">
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                            <h3><?= $totalReservas ?></h3>
                            <p>Total de Reservas</p>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                            <h3><?= $reservasAtivas ?></h3>
                            <p>Reservas Ativas</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Editar Perfil</h2>
                    </div>
                    
                    <form method="POST" action="perfil.php">
                        <input type="hidden" name="acao" value="atualizar_perfil">
                        
                        <div class="form-group">
                            <label class="form-label" for="nome">Nome Completo</label>
                            <input type="text" id="nome" name="nome" class="form-control" 
                                   value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="email">E-mail</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?= htmlspecialchars($usuario['email']) ?>" required>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Atualizar Perfil</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Alterar Senha</h2>
                </div>
                
                <form method="POST" action="perfil.php">
                    <input type="hidden" name="acao" value="alterar_senha">
                    
                    <div class="grid grid-3">
                        <div class="form-group">
                            <label class="form-label" for="senha_atual">Senha Atual</label>
                            <div style="position: relative;">
                                <input type="password" id="senha_atual" name="senha_atual" class="form-control" required>
                                <button type="button" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;" 
                                        onclick="togglePasswordVisibility('senha_atual')">👁️‍🗨️</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="nova_senha">Nova Senha</label>
                            <div style="position: relative;">
                                <input type="password" id="nova_senha" name="nova_senha" class="form-control" minlength="6" required>
                                <button type="button" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;" 
                                        onclick="togglePasswordVisibility('nova_senha')">👁️‍🗨️</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="confirmar_senha">Confirmar Nova Senha</label>
                            <div style="position: relative;">
                                <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" minlength="6" required>
                                <button type="button" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;" 
                                        onclick="togglePasswordVisibility('confirmar_senha')">👁️‍🗨️</button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Alterar Senha</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

<?php include 'templates/footer.php'; ?>
