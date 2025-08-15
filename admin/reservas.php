<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth = new Auth();
$auth->requireAdmin();

$db = Database::getInstance();

$filtro = $_GET['filtro'] ?? '';
$status = $_GET['status'] ?? '';
$data = $_GET['data'] ?? '';

$where = [];
$params = [];

if ($filtro) {
    $where[] = "(u.nome LIKE ? OR s.nome LIKE ? OR r.titulo LIKE ?)";
    $params[] = "%$filtro%";
    $params[] = "%$filtro%";
    $params[] = "%$filtro%";
}

if ($status) {
    $where[] = "r.status = ?";
    $params[] = $status;
}

if ($data) {
    $where[] = "r.data_reserva = ?";
    $params[] = $data;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT r.*, s.nome as sala_nome, u.nome as usuario_nome, u.email as usuario_email 
        FROM reservas r 
        JOIN salas s ON r.sala_id = s.id 
        JOIN usuarios u ON r.usuario_id = u.id 
        $whereClause
        ORDER BY r.data_reserva DESC, r.hora_inicio DESC";

$reservas = $db->fetchAll($sql, $params);

$mensagem = obterMensagem();
?>

<?php
$pageTitle = 'Gerenciar Reservas - ' . SITE_NAME;
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
            </div>

            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">Gerenciar Reservas</h1>
                    <p>Visualize e gerencie todas as reservas do sistema</p>
                </div>

                <form method="GET" action="reservas.php" style="margin-bottom: 2rem;">
                    <div class="grid grid-3">
                        <div class="form-group">
                            <label class="form-label">Buscar</label>
                            <input type="text" name="filtro" class="form-control" 
                                   value="<?= htmlspecialchars($filtro) ?>" 
                                   placeholder="Usuário, sala ou título...">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Todos</option>
                                <option value="confirmada" <?= $status === 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                                <option value="cancelada" <?= $status === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                <option value="pendente" <?= $status === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Data</label>
                            <input type="date" name="data" class="form-control" value="<?= $data ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="reservas.php" class="btn btn-secondary">Limpar</a>
                    </div>
                </form>

                <?php if (empty($reservas)): ?>
                    <div class="alert alert-warning">
                        Nenhuma reserva encontrada com os filtros aplicados.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table" id="tabela-reservas">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuário</th>
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
                                        <td><?= $reserva['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($reserva['usuario_nome']) ?></strong><br>
                                            <small><?= htmlspecialchars($reserva['usuario_email']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($reserva['sala_nome']) ?></td>
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
                                            <div style="display: flex; gap: 0.5rem;">
                                                <?php if ($reserva['status'] === 'confirmada'): ?>
                                                    <button class="btn btn-danger btn-sm" 
                                                            onclick="cancelarReservaAdmin(<?= $reserva['id'] ?>)">
                                                        Cancelar
                                                    </button>
                                                <?php elseif ($reserva['status'] === 'cancelada'): ?>
                                                    <button class="btn btn-success btn-sm" 
                                                            onclick="confirmarReservaAdmin(<?= $reserva['id'] ?>)">
                                                        Confirmar
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <button class="btn btn-secondary btn-sm" 
                                                        onclick="verDetalhesReserva(<?= $reserva['id'] ?>)">
                                                    Detalhes
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top: 1rem;">
                        <button class="btn btn-secondary" onclick="exportarTabela('tabela-reservas', 'reservas-admin.csv')">
                            Exportar CSV
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <div id="modal-detalhes" class="modal">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <h2>Detalhes da Reserva</h2>
            <div id="detalhes-conteudo"></div>
        </div>
    </div>

<?php include '../templates/footer.php'; ?>
    <script>
        function cancelarReservaAdmin(reservaId) {
            if (confirm('Tem certeza que deseja cancelar esta reserva?')) {
                fetch('api/cancelar_reserva_admin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `reserva_id=${reservaId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', data.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showAlert('danger', data.message);
                    }
                })
                .catch(error => {
                    showAlert('danger', 'Erro ao cancelar reserva');
                });
            }
        }

        function confirmarReservaAdmin(reservaId) {
            if (confirm('Tem certeza que deseja confirmar esta reserva?')) {
                fetch('api/confirmar_reserva_admin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `reserva_id=${reservaId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', data.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showAlert('danger', data.message);
                    }
                })
                .catch(error => {
                    showAlert('danger', 'Erro ao confirmar reserva');
                });
            }
        }

        function verDetalhesReserva(reservaId) {
            fetch('api/detalhes_reserva.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `reserva_id=${reservaId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('detalhes-conteudo').innerHTML = data.html;
                    document.getElementById('modal-detalhes').style.display = 'block';
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                showAlert('danger', 'Erro ao carregar detalhes');
            });
        }
    </script>
</body>
</html>
