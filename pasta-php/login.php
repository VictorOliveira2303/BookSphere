<?php

// Define o cookie da sessão para durar 30 dias
$tempo_de_vida = 30 * 24 * 60 * 60; // 30 dias em segundos
session_set_cookie_params($tempo_de_vida);

session_start(); // Inicia a sessão

$servername = "srv791.hstgr.io"; 
$username   = "u831223978_root";
$password   = "BookSphere1";
$dbname     = "u831223978_bancousers";

// Conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Checar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Receber dados do JS
$usuario = $_POST['usuario'] ?? '';
$senha   = $_POST['senha'] ?? '';

// --- NOVA VALIDAÇÃO DE LIMITE NO SERVIDOR ---
// (usamos mb_strlen para contar caracteres multibyte, como "ç", corretamente)
if (mb_strlen($usuario, 'UTF-8') > 30) {
    echo "Usuário muito longo";
    exit;
}
// --- FIM DA VALIDAÇÃO ---

// Preparar e executar a consulta
$stmt = $conn->prepare("SELECT * FROM users WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Usuário não encontrado";
    exit;
}

$user = $result->fetch_assoc();

// Verificar senha
if (password_verify($senha, $user['senha'])) {
    // Salva dados do usuário na sessão
    $_SESSION['usuario'] = $user['usuario'];
    $_SESSION['nome']    = $user['nome']; // Certifique-se de que existe um campo 'nome'
    $_SESSION['imagem']  = $user['imagem'];
    $_SESSION['reader_theme'] = $user['reader_theme'];

    echo "sucesso";
} else {
    echo "Senha incorreta";
}

// Fechar conexão
$stmt->close();
$conn->close();
?>
