<?php
/*
 * ARQUIVO DE CONEXÃO COM O BANCO DE DADOS
 * Local: pasta-php/conexao.php
 */

// 1. Credenciais do Banco (Baseado nas suas imagens)
$host = '127.0.0.1'; // ou 'localhost'. Mantenha assim.
$db   = 'u831223978_bancoosers'; // O nome do seu banco
$user = 'u831223978_bancoosers'; // O seu usuário do banco
$pass = 'SUA_SENHA_DO_BANCO_DE_DADOS'; // <-- ADICIONE SUA SENHA AQUI
$charset = 'utf8mb4';

// 2. String de Conexão (DSN)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// 3. Opções do PDO (para configurar como o PHP lida com o banco)
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Mostra erros
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna dados como array
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa preparações reais
];

// 4. Tentar a conexão
try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Se falhar, mostra o erro
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>