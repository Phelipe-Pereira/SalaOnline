<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$usuario = $auth->getCurrentUser();

$sql = "SELECT r.*, s.nome as sala_nome, s.capacidade 
        FROM reservas r 
        JOIN salas s ON r.sala_id = s.id 
        WHERE r.usuario_id = ? 
        ORDER BY r.data_reserva DESC, r.hora_inicio DESC";

$reservas = $db->fetchAll($sql, [$usuario['id']]);

$mensagem = obterMensagem();
?>

<?php
$pageTitle = 'Minhas Reservas - ' . SITE_NAME;
include 'templates/header.php';
?>

    <main class="main-content">
        <div class="container">
            <div id="alert-container">
                <?php if ($mensagem): ?>
                    <div class="alert alert-<?= $mensagem['tipo'] ?>">
                        <?= $mensagem['texto'] ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">Minhas Reservas</h1>
                    <p>Visualize e gerencie suas reservas de salas</p>
                </div>

                <div style="margin-bottom: 1rem;">
                    <a href="index.php" class="btn btn-primary">Nova Reserva</a>
                </div>

                <?php if (empty($reservas)): ?>
                    <div class="alert alert-warning">
                        Você ainda não possui reservas. <a href="index.php">Faça sua primeira reserva</a>.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table" id="tabela-reservas">
                            <thead>
                                <tr>
                                    <th>Sala</th>
                                    <th>Data</th>
                                    <th>Horário</th>
                                    <th>Título</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservas as $reserva): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($reserva['sala_nome']) ?></strong><br>
                                            <small>Capacidade: <?= $reserva['capacidade'] ?> pessoas</small>
                                        </td>
                                        <td><?= formatarData($reserva['data_reserva']) ?></td>
                                        <td>
                                            <?= formatarHora($reserva['hora_inicio']) ?> - 
                                            <?= formatarHora($reserva['hora_fim']) ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($reserva['titulo']) ?></strong>
                                            <?php if ($reserva['descricao']): ?>
                                                <br><small><?= htmlspecialchars($reserva['descricao']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            $statusText = '';
                                            switch ($reserva['status']) {
                                                case 'confirmada':
                                                    $statusClass = 'status-disponivel';
                                                    $statusText = 'Confirmada';
                                                    break;
                                                case 'cancelada':
                                                    $statusClass = 'status-ocupada';
                                                    $statusText = 'Cancelada';
                                                    break;
                                                case 'pendente':
                                                    $statusClass = 'alert-warning';
                                                    $statusText = 'Pendente';
                                                    break;
                                            }
                                            ?>
                                            <span class="sala-status <?= $statusClass ?>">
                                                <?= $statusText ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($reserva['status'] === 'confirmada' && $reserva['data_reserva'] >= date('Y-m-d')): ?>
                                                <button class="btn btn-danger btn-sm btn-cancelar" 
                                                        data-reserva-id="<?= $reserva['id'] ?>">
                                                    Cancelar
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top: 1rem;">
                        <button class="btn btn-secondary" onclick="exportarTabela('tabela-reservas', 'minhas-reservas.csv')">
                            Exportar CSV
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

<?php include 'templates/footer.php'; ?>
