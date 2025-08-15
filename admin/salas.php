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
    if ($acao === 'adicionar') {
        $nome = limparInput($_POST['nome'] ?? '');
        $capacidade = (int)($_POST['capacidade'] ?? 0);
        $recursos = limparInput($_POST['recursos'] ?? '');
        
        if (empty($nome) || $capacidade <= 0) {
            $erro = 'Nome e capacidade são obrigatórios';
        } else {
            $sql = "INSERT INTO salas (nome, capacidade, recursos) VALUES (?, ?, ?)";
            $db->query($sql, [$nome, $capacidade, $recursos]);
            $sucesso = 'Sala adicionada com sucesso';
        }
    } elseif ($acao === 'editar') {
        $id = (int)($_POST['id'] ?? 0);
        $nome = limparInput($_POST['nome'] ?? '');
        $capacidade = (int)($_POST['capacidade'] ?? 0);
        $recursos = limparInput($_POST['recursos'] ?? '');
        $status = $_POST['status'] ?? 'disponivel';
        
        if (!$id || empty($nome) || $capacidade <= 0) {
            $erro = 'Dados inválidos';
        } else {
            $sql = "UPDATE salas SET nome = ?, capacidade = ?, recursos = ?, status = ? WHERE id = ?";
            $db->query($sql, [$nome, $capacidade, $recursos, $status, $id]);
            $sucesso = 'Sala atualizada com sucesso';
        }
    } elseif ($acao === 'excluir') {
        $id = (int)($_POST['id'] ?? 0);
        
        if (!$id) {
            $erro = 'ID inválido';
        } else {
            $sql = "UPDATE salas SET ativo = 0 WHERE id = ?";
            $db->query($sql, [$id]);
            $sucesso = 'Sala removida com sucesso';
        }
    }
}

$sql = "SELECT * FROM salas WHERE ativo = 1 ORDER BY nome";
$salas = $db->fetchAll($sql);

$mensagem = obterMensagem();
?>

<?php
$pageTitle = 'Gerenciar Salas - ' . SITE_NAME;
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

            <div class="grid grid-2">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Adicionar Nova Sala</h2>
                    </div>
                    
                    <form method="POST" action="salas.php">
                        <input type="hidden" name="acao" value="adicionar">
                        
                        <div class="form-group">
                            <label class="form-label" for="nome">Nome da Sala</label>
                            <input type="text" id="nome" name="nome" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="capacidade">Capacidade</label>
                            <input type="number" id="capacidade" name="capacidade" class="form-control" min="1" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="recursos">Recursos</label>
                            <textarea id="recursos" name="recursos" class="form-control" rows="3" 
                                      placeholder="Ex: Projetor, Quadro Branco, Wi-Fi"></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Adicionar Sala</button>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Salas Existentes</h2>
                    </div>
                    
                    <?php if (empty($salas)): ?>
                        <p>Nenhuma sala cadastrada.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Capacidade</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($salas as $sala): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($sala['nome']) ?></strong>
                                                <?php if ($sala['recursos']): ?>
                                                    <br><small><?= htmlspecialchars($sala['recursos']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $sala['capacidade'] ?> pessoas</td>
                                            <td>
                                                <span class="sala-status <?= $sala['status'] === 'disponivel' ? 'status-disponivel' : 'status-ocupada' ?>">
                                                    <?= ucfirst($sala['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-secondary btn-sm" 
                                                        onclick="editarSala(<?= $sala['id'] ?>, '<?= htmlspecialchars($sala['nome']) ?>', <?= $sala['capacidade'] ?>, '<?= htmlspecialchars($sala['recursos']) ?>', '<?= $sala['status'] ?>')">
                                                    Editar
                                                </button>
                                                <button class="btn btn-danger btn-sm" 
                                                        onclick="excluirSala(<?= $sala['id'] ?>, '<?= htmlspecialchars($sala['nome']) ?>')">
                                                    Excluir
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <div id="modal-editar" class="modal">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <h2>Editar Sala</h2>
            
            <form method="POST" action="salas.php">
                <input type="hidden" name="acao" value="editar">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group">
                    <label class="form-label" for="edit_nome">Nome da Sala</label>
                    <input type="text" id="edit_nome" name="nome" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit_capacidade">Capacidade</label>
                    <input type="number" id="edit_capacidade" name="capacidade" class="form-control" min="1" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit_recursos">Recursos</label>
                    <textarea id="edit_recursos" name="recursos" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit_status">Status</label>
                    <select id="edit_status" name="status" class="form-select">
                        <option value="disponivel">Disponível</option>
                        <option value="indisponivel">Indisponível</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Atualizar Sala</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-editar').style.display='none'">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <form id="form-excluir" method="POST" action="salas.php" style="display: none;">
        <input type="hidden" name="acao" value="excluir">
        <input type="hidden" name="id" id="excluir_id">
    </form>

<?php include '../templates/footer.php'; ?>
    <script>
        function editarSala(id, nome, capacidade, recursos, status) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nome').value = nome;
            document.getElementById('edit_capacidade').value = capacidade;
            document.getElementById('edit_recursos').value = recursos;
            document.getElementById('edit_status').value = status;
            document.getElementById('modal-editar').style.display = 'block';
        }

        function excluirSala(id, nome) {
            if (confirm(`Tem certeza que deseja excluir a sala "${nome}"?`)) {
                document.getElementById('excluir_id').value = id;
                document.getElementById('form-excluir').submit();
            }
        }
    </script>
</body>
</html>
