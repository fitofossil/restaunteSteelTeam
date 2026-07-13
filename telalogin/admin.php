<?php
session_start();

if (empty($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Dogão Lanches</title>
    <link rel="stylesheet" href="telalogin.css">
</head>
<body>
    <div class="container">
        <div class="placa">
            <h1>Login realizado com sucesso!</h1>
            <p>Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario']); ?>.</p>
        </div>
    </div>
</body>
</html>
