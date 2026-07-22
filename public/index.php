<?php
// inicia a sessao para guardar dados do usuario logado
session_start();

// variavel para guardar mensagem de erro se o login falhar
$erro = "";

// verifica se o botao entrar do formulario foi clicado
if (isset($_POST['entrar'])) {
    // pega o email e a senha limpando espacos em branco
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    // puxa o arquivo que faz a conexao com o banco de dados
    require_once __DIR__ . '/../config/conexao.php';

    // tenta rodar os comandos do banco de dados de forma segura
    try {
        // prepara a consulta sql para buscar o usuario de forma segura
        $stmt = $conn->prepare("SELECT id, username, password_hash, is_active FROM users_login WHERE email = ? LIMIT 1");
        // executa a consulta passando o email do usuario
        $stmt->execute([$email]);
        // joga os dados do usuario encontrado em um array
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // verifica se o usuario existe, se esta ativo e se a senha esta certa
        if ($user && $user['is_active'] && password_verify($senha, $user['password_hash'])) {
            // guarda o nome e o id do usuario na sessao do site
            $_SESSION['usuario'] = $user['username'];
            $_SESSION['usuario_id'] = $user['id'];
            
            // manda o usuario para a pagina de administracao
            header("Location: ../templates/index.php");
            exit(); // para a execucao do codigo aqui
        }

        // se algo der errado acima define a mensagem de erro
        $erro = "Email ou senha incorretos!";
    } catch (PDOException $e) {
        // se o banco der erro mostra essa mensagem amigavel na tela
        $erro = "Não foi possível conectar ao banco de dados.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dogão Lanches - Login</title>

<link rel="stylesheet" href="telalogin.css">

</head>

<body>

<div class="container">

    <div class="placa">

        <h1>🌭 Dogão Lanches</h1>

        <form method="POST">

            <input
            type="email"
            name="email"
            placeholder="Email"
            required>

            <input
            type="password"
            name="senha"
            placeholder="Senha"
            required>

            <button type="submit" name="entrar">
                Entrar
            </button>

        </form>

        <?php
        if($erro != ""){
            echo "<p class='erro'>$erro</p>";
        }
        ?>

    </div>

</div>

</body>
</html>
