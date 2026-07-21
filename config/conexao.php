<?php
// DADOS DA CONEXAO ( PADRAO PARA O XAMPP)

// endereco do servidor
$host = "localhost";

// porta padrao do Mysql 3306
$port = "8080";

// nome do banco de dados
$dbname = "restaurante";

// usuario que acessa o servidor
$user = "root";

// senha do xampp
$password = "";

// conexao com o banco
try {
    // cria o pdo e abre a conexao com o mysql usando as variaveis acima
    $conn = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $password
    );
    // configura o modo de erros para lancar excecoes se algo der errado no banco
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // se a conexao falhar mata o script e mostra o erro na tela
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

?>