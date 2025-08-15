<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth = new Auth();
$auth->requireAdmin();

$db = Database::getInstance();

$acao = $_POST['acao'] ?? '';
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($acao === 'alterar_tipo') {
        $id = (int)($_POST['id'] ?? 0);
        $tipo = $_POST['tipo'] ?? '';
        
        if (!$id || !in_array($tipo, ['usuario', 'admin'])) {
            $erro = 'Dados inválidos';
        } else {
            $sql = "UPDATE usuarios SET tipo = ? WHERE id = ?";
            $db->query($sql, [$tipo, $id]);
            $sucesso = 'Tipo de usuário alterado com sucesso';
        }
    } elseif ($acao === 'ativar_desativar') {
        $id = (int)($_POST['id'] ?? 0);
        $ativo = (int)($_POST['ativo'] ?? 0);
        
        if (!$id) {
            $erro = 'ID inválido';
        } else {
            $sql = "UPDATE usuarios SET ativo = ? WHERE id = ?";
            $db->query($sql, [$ativo, $id]);
            $sucesso = $ativo ? 'Usuário ativado com sucesso' : 'Usuário desativado com sucesso';
        }
    }
}

$sql = "SELECT u.*, COUNT(r.id) as total_reservas 
        FROM usuarios u 
        LEFT JOIN reservas r ON u.id = r.usuario_id 
        GROUP BY u.id 
        ORDER BY u.nome";

$usuarios = $db->fetchAll($sql);

$mensagem = obterMensagem();
?>

<?php
$pageTitle = 'Gerenciar Usuários - ' . SITE_NAME;
$cssPath = '../assets/css/style.css';
$jsPath = '../assets/js/app.js';
include '../templates/header.php';
?>

    <main class="main-content">
        <div class="container">
            <div id="alert-container">
                <?php if ($mensagem): ?>
                    <div class="alert alert-<?= $mensagem['tipo'] ?>">
                        <?= $mensagem['texto'] ?>
                    </div>
                <?php endif; ?>

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
            </div>

            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">Gerenciar Usuários</h1>
                    <p>Visualize e gerencie todos os usuários do sistema</p>
                </div>

                <?php if (empty($usuarios)): ?>
                    <p>Nenhum usuário cadastrado.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table" id="tabela-usuarios">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Total Reservas</th>
                                    <th>Data Cadastro</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?= $usuario['id'] ?></td>
                                        <td><?= htmlspecialchars($usuario['nome']) ?></td>
                                        <td><?= htmlspecialchars($usuario['email']) ?></td>
                                        <td>
                                            <span class="sala-status <?= $usuario['tipo'] === 'admin' ? 'status-ocupada' : 'status-disponivel' ?>">
                                                <?= ucfirst($usuario['tipo']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="sala-status <?= $usuario['ativo'] ? 'status-disponivel' : 'status-ocupada' ?>">
                                                <?= $usuario['ativo'] ? 'Ativo' : 'Inativo' ?>
                                            </span>
                                        </td>
                                        <td><?= $usuario['total_reservas'] ?></td>
                                        <td><?= formatarData($usuario['data_criacao']) ?></td>
                                        <td>
                                            <div style="display: flex; gap: 0.5rem;">
                                                <button class="btn btn-secondary btn-sm" 
                                                        onclick="alterarTipo(<?= $usuario['id'] ?>, '<?= $usuario['tipo'] ?>', '<?= htmlspecialchars($usuario['nome']) ?>')">
                                                    Alterar Tipo
                                                </button>
                                                
                                                <?php if ($usuario['id'] != $auth->getCurrentUser()['id']): ?>
                                                    <button class="btn btn-<?= $usuario['ativo'] ? 'danger' : 'success' ?> btn-sm" 
                                                            onclick="ativarDesativar(<?= $usuario['id'] ?>, <?= $usuario['ativo'] ?>, '<?= htmlspecialchars($usuario['nome']) ?>')">
                                                        <?= $usuario['ativo'] ? 'Desativar' : 'Ativar' ?>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top: 1rem;">
                        <button class="btn btn-secondary" onclick="exportarTabela('tabela-usuarios', 'usuarios-admin.csv')">
                            Exportar CSV
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <div id="modal-tipo" class="modal">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <h2>Alterar Tipo de Usuário</h2>
            <p>Usuário: <span id="usuario-nome"></span></p>
            
            <form method="POST" action="usuarios.php">
                <input type="hidden" name="acao" value="alterar_tipo">
                <input type="hidden" name="id" id="tipo_id">
                
                <div class="form-group">
                    <label class="form-label" for="tipo_select">Tipo de Usuário</label>
                    <select id="tipo_select" name="tipo" class="form-select">
                        <option value="usuario">Usuário</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Alterar Tipo</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-tipo').style.display='none'">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <form id="form-ativar" method="POST" action="usuarios.php" style="display: none;">
        <input type="hidden" name="acao" value="ativar_desativar">
        <input type="hidden" name="id" id="ativar_id">
        <input type="hidden" name="ativo" id="ativar_status">
    </form>

<?php include '../templates/footer.php'; ?>
    <script>
        function alterarTipo(id, tipoAtual, nome) {
            document.getElementById('tipo_id').value = id;
            document.getElementById('usuario-nome').textContent = nome;
            document.getElementById('tipo_select').value = tipoAtual;
            document.getElementById('modal-tipo').style.display = 'block';
        }

        function ativarDesativar(id, ativo, nome) {
            const acao = ativo ? 'desativar' : 'ativar';
            if (confirm(`Tem certeza que deseja ${acao} o usuário "${nome}"?`)) {
                document.getElementById('ativar_id').value = id;
                document.getElementById('ativar_status').value = ativo ? 0 : 1;
                document.getElementById('form-ativar').submit();
            }
        }
    </script>
</body>
</html>
