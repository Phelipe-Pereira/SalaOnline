<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth = new Auth();
$auth->requireAdmin();

$db = Database::getInstance();

$sql = "SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'usuario'";
$totalUsuarios = $db->fetch($sql)['total'];

$sql = "SELECT COUNT(*) as total FROM salas WHERE ativo = 1";
$totalSalas = $db->fetch($sql)['total'];

$sql = "SELECT COUNT(*) as total FROM reservas WHERE status = 'confirmada' AND data_reserva >= CURDATE()";
$reservasAtivas = $db->fetch($sql)['total'];

$sql = "SELECT COUNT(*) as total FROM reservas WHERE data_reserva = CURDATE()";
$reservasHoje = $db->fetch($sql)['total'];

$sql = "SELECT r.*, s.nome as sala_nome, u.nome as usuario_nome 
        FROM reservas r 
        JOIN salas s ON r.sala_id = s.id 
        JOIN usuarios u ON r.usuario_id = u.id 
        WHERE r.data_reserva >= CURDATE() 
        ORDER BY r.data_reserva ASC, r.hora_inicio ASC 
        LIMIT 10";

$proximasReservas = $db->fetchAll($sql);

$sql = "SELECT s.nome, COUNT(r.id) as total_reservas 
        FROM salas s 
        LEFT JOIN reservas r ON s.id = r.sala_id AND r.status = 'confirmada' 
        WHERE s.ativo = 1 
        GROUP BY s.id 
        ORDER BY total_reservas DESC";

$salasMaisUsadas = $db->fetchAll($sql);
?>

<?php
$pageTitle = 'Dashboard Administrativo - ' . SITE_NAME;
$cssPath = '../assets/css/style.css';
$jsPath = '../assets/js/app.js';
include '../templates/header.php';
?>

    <main class="main-content">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">Dashboard Administrativo</h1>
                    <p>Visão geral do sistema de reservas</p>
                </div>
            </div>

            <div class="grid grid-4">
                <div class="card">
                    <div style="text-align: center; padding: 2rem;">
                        <h2 style="font-size: 3rem; color: #667eea; margin-bottom: 0.5rem;"><?= $totalUsuarios ?></h2>
                        <p style="font-size: 1.2rem; color: #666;">Usuários Cadastrados</p>
                    </div>
                </div>

                <div class="card">
                    <div style="text-align: center; padding: 2rem;">
                        <h2 style="font-size: 3rem; color: #28a745; margin-bottom: 0.5rem;"><?= $totalSalas ?></h2>
                        <p style="font-size: 1.2rem; color: #666;">Salas Disponíveis</p>
                    </div>
                </div>

                <div class="card">
                    <div style="text-align: center; padding: 2rem;">
                        <h2 style="font-size: 3rem; color: #ffc107; margin-bottom: 0.5rem;"><?= $reservasAtivas ?></h2>
                        <p style="font-size: 1.2rem; color: #666;">Reservas Ativas</p>
                    </div>
                </div>

                <div class="card">
                    <div style="text-align: center; padding: 2rem;">
                        <h2 style="font-size: 3rem; color: #dc3545; margin-bottom: 0.5rem;"><?= $reservasHoje ?></h2>
                        <p style="font-size: 1.2rem; color: #666;">Reservas Hoje</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-2">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Próximas Reservas</h2>
                    </div>
                    
                    <?php if (empty($proximasReservas)): ?>
                        <p>Nenhuma reserva agendada.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sala</th>
                                        <th>Usuário</th>
                                        <th>Data</th>
                                        <th>Horário</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($proximasReservas as $reserva): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($reserva['sala_nome']) ?></td>
                                            <td><?= htmlspecialchars($reserva['usuario_nome']) ?></td>
                                            <td><?= formatarData($reserva['data_reserva']) ?></td>
                                            <td><?= formatarHora($reserva['hora_inicio']) ?> - <?= formatarHora($reserva['hora_fim']) ?></td>
                                            <td>
                                                <span class="sala-status <?= $reserva['status'] === 'confirmada' ? 'status-disponivel' : 'status-ocupada' ?>">
                                                    <?= ucfirst($reserva['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Salas Mais Utilizadas</h2>
                    </div>
                    
                    <?php if (empty($salasMaisUsadas)): ?>
                        <p>Nenhuma estatística disponível.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sala</th>
                                        <th>Total de Reservas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($salasMaisUsadas as $sala): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($sala['nome']) ?></td>
                                            <td><?= $sala['total_reservas'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Ações Rápidas</h2>
                </div>
                
                <div class="grid grid-4">
                    <a href="reservas.php" class="btn btn-primary" style="text-align: center; padding: 1rem;">
                        <strong>Gerenciar Reservas</strong><br>
                        <small>Visualizar e editar todas as reservas</small>
                    </a>
                    
                    <a href="salas.php" class="btn btn-success" style="text-align: center; padding: 1rem;">
                        <strong>Gerenciar Salas</strong><br>
                        <small>Adicionar, editar ou remover salas</small>
                    </a>
                    
                    <a href="usuarios.php" class="btn btn-secondary" style="text-align: center; padding: 1rem;">
                        <strong>Gerenciar Usuários</strong><br>
                        <small>Visualizar e gerenciar usuários</small>
                    </a>
                    
                    <a href="../index.php" class="btn btn-danger" style="text-align: center; padding: 1rem;">
                        <strong>Voltar ao Site</strong><br>
                        <small>Retornar à área pública</small>
                    </a>
                </div>
            </div>
        </div>
    </main>

<?php include '../templates/footer.php'; ?>
