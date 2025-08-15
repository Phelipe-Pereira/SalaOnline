<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
$db = Database::getInstance();

$dataAtual = date('Y-m-d');
$dataSelecionada = $_GET['data'] ?? $dataAtual;

$sql = "SELECT s.*, 
        CASE WHEN r.id IS NULL THEN 1 ELSE 0 END as disponivel
        FROM salas s 
        LEFT JOIN reservas r ON s.id = r.sala_id 
        AND r.data_reserva = ? 
        AND r.status = 'confirmada'
        WHERE s.ativo = 1
        GROUP BY s.id";

$salas = $db->fetchAll($sql, [$dataSelecionada]);

$mensagem = obterMensagem();
?>

<?php
$pageTitle = SITE_NAME . ' - Sistema de Reservas';
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
                    <h1 class="card-title">Sistema de Reservas de Salas</h1>
                    <p>Visualize a disponibilidade das salas e faça suas reservas</p>
                </div>

                <?php if (!$auth->isLoggedIn()): ?>
                    <div class="alert alert-warning">
                        <strong>Atenção!</strong> Você precisa estar logado para fazer reservas. 
                        <a href="login.php">Faça login</a> ou <a href="register.php">cadastre-se</a>.
                    </div>
                <?php endif; ?>
            </div>

            <div class="grid grid-2">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Calendário</h2>
                    </div>
                    <div class="calendar">
                        <?php
                        $mes = date('m', strtotime($dataSelecionada));
                        $ano = date('Y', strtotime($dataSelecionada));
                        $primeiroDia = mktime(0, 0, 0, $mes, 1, $ano);
                        $diasNoMes = date('t', $primeiroDia);
                        $primeiroDiaSemana = date('w', $primeiroDia);
                        
                        $diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
                        foreach ($diasSemana as $dia) {
                            echo "<div class='calendar-header'>$dia</div>";
                        }
                        
                        for ($i = 0; $i < $primeiroDiaSemana; $i++) {
                            echo "<div class='calendar-day disabled'></div>";
                        }
                        
                        for ($dia = 1; $dia <= $diasNoMes; $dia++) {
                            $data = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
                            $classe = 'calendar-day';
                            if ($data == $dataAtual) $classe .= ' today';
                            if ($data == $dataSelecionada) $classe .= ' selected';
                            if ($data < $dataAtual) $classe .= ' disabled';
                            
                            echo "<div class='$classe' data-date='$data'>$dia</div>";
                        }
                        ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Salas Disponíveis - <?= formatarData($dataSelecionada) ?></h2>
                    </div>
                    <div class="salas-grid grid grid-2">
                        <?php foreach ($salas as $sala): ?>
                            <div class="sala-card <?= $sala['disponivel'] ? 'disponivel' : 'ocupada' ?>">
                                <h3><?= htmlspecialchars($sala['nome']) ?></h3>
                                <p><strong>Capacidade:</strong> <?= $sala['capacidade'] ?> pessoas</p>
                                <p><strong>Recursos:</strong> <?= htmlspecialchars($sala['recursos']) ?></p>
                                <span class="sala-status <?= $sala['disponivel'] ? 'status-disponivel' : 'status-ocupada' ?>">
                                    <?= $sala['disponivel'] ? 'Disponível' : 'Ocupada' ?>
                                </span>
                                
                                <?php if ($auth->isLoggedIn()): ?>
                                    <?php if ($sala['disponivel']): ?>
                                        <button class="btn btn-primary btn-reservar" 
                                                data-sala-id="<?= $sala['id'] ?>" 
                                                data-sala-nome="<?= htmlspecialchars($sala['nome']) ?>">
                                            Reservar
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary" disabled>Indisponível</button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>Faça login para reservar</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div id="modal-reserva" class="modal">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <h2>Nova Reserva</h2>
            <p>Sala: <span class="sala-nome"></span></p>
            
            <form action="api/criar_reserva.php" method="POST" data-ajax>
                <input type="hidden" name="sala_id" value="">
                
                <div class="form-group">
                    <label class="form-label">Data da Reserva</label>
                    <input type="date" name="data_reserva" class="form-control" required 
                           min="<?= date('Y-m-d') ?>" value="<?= $dataSelecionada ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Hora de Início</label>
                    <select name="hora_inicio" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach (gerarHorarios() as $hora): ?>
                            <option value="<?= $hora ?>"><?= $hora ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Hora de Fim</label>
                    <select name="hora_fim" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach (gerarHorarios() as $hora): ?>
                            <option value="<?= $hora ?>"><?= $hora ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Título da Reserva</label>
                    <input type="text" name="titulo" class="form-control" required 
                           placeholder="Ex: Reunião de Equipe">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Descrição (opcional)</label>
                    <textarea name="descricao" class="form-control" rows="3" 
                              placeholder="Detalhes sobre a reserva..."></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Confirmar Reserva</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-reserva').style.display='none'">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php include 'templates/footer.php'; ?>
