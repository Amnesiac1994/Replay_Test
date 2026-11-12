<?php
session_start();
require_once 'api/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if (!empty($name)) {
        $_SESSION['player_name'] = htmlspecialchars($name);
        $_SESSION['user_role'] = (strtolower($name) === 'admin' || strtolower($name) === 'vip') ? 'vip' : 'standard';
        header('Location: index.php');
        exit;
    } else {
        $error = 'Por favor, digite seu nome para continuar.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Replay Quadra</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="logo-area">
            <span class="icon">⚽</span>
            <h1>Replay Sports</h1>
            <p>Escaneie, jogue e guarde seus melhores lances!</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="name">Seu Nome / Apelido:</label>
                <input type="text" id="name" name="name" placeholder="Ex: Lucas Silva" required autofocus autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Entrar na Quadra 🚀</button>
        </form>

        <p class="login-footer">Desenvolvido para Quadras Inteligentes Ubiquiti</p>
    </div>
</body>
</html>
