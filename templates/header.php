<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= $cssPath ?? 'assets/css/style.css' ?>">
</head>
<body>
    <?php
    $auth = new Auth();
    $isAdmin = $auth->isLoggedIn() && $auth->isAdmin();
    $isLoggedIn = $auth->isLoggedIn();
    ?>
    
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo"><?= $isAdmin ? SITE_NAME . ' - Admin' : SITE_NAME ?></div>
                <nav>
                    <ul class="nav-menu">
                        <?php if ($isAdmin): ?>
                            <li><a href="../index.php">Voltar ao Site</a></li>
                            <li><a href="dashboard.php">Dashboard</a></li>
                            <li><a href="reservas.php">Reservas</a></li>
                            <li><a href="salas.php">Salas</a></li>
                            <li><a href="usuarios.php">Usuários</a></li>
                            <li><a href="../logout.php">Sair</a></li>
                        <?php else: ?>
                            <li><a href="<?= $basePath ?? '' ?>index.php">Início</a></li>
                            <?php if ($isLoggedIn): ?>
                                <li><a href="<?= $basePath ?? '' ?>reservas.php">Minhas Reservas</a></li>
                                <?php if ($isAdmin): ?>
                                    <li><a href="<?= $basePath ?? '' ?>admin/dashboard.php">Administração</a></li>
                                <?php endif; ?>
                                <li><a href="<?= $basePath ?? '' ?>perfil.php">Perfil</a></li>
                                <li><a href="<?= $basePath ?? '' ?>logout.php">Sair</a></li>
                            <?php else: ?>
                                <li><a href="<?= $basePath ?? '' ?>login.php">Entrar</a></li>
                                <li><a href="<?= $basePath ?? '' ?>register.php">Cadastrar</a></li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>