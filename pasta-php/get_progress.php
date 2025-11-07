<?php

// Define o cookie da sessão para durar 30 dias
$tempo_de_vida = 30 * 24 * 60 * 60; // 30 dias em segundos
session_set_cookie_params($tempo_de_vida);

// get_progress.php
session_start();

// 1. Verifique se o usuário está logado
if (!isset($_SESSION['usuario'])) { 
    echo json_encode(['status' => 'error', 'message' => 'Usuário não logado']);
    exit;
}

// ================================================================
// 2. DETALHES DA CONEXÃO (Copie do seu progress_handler.php)
// ================================================================
$servername = "srv791.hstgr.io"; 
$username   = "u831223978_root";
$password   = "BookSphere1";
$dbname     = "u831223978_bancousers";
// ================================================================

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Falha na conexão: ' . $e->getMessage()]);
    exit;
}

// 3. ENCONTRAR O ID NUMÉRICO DO USUÁRIO (Mesma lógica do handler)
try {
    $username_from_session = $_SESSION['usuario'];
    
    // Lembre-se, sua coluna é 'user_id' na tabela 'users'
    $stmt_user = $conn->prepare("SELECT user_id FROM users WHERE usuario = :username"); 
    $stmt_user->execute(['username' => $username_from_session]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'Usuário da sessão não encontrado no banco.']);
        exit;
    }
    
    $user_id = $user['user_id']; 

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao buscar ID do usuário: ' . $e->getMessage()]);
    exit;
}

// 4. BUSCAR TODOS OS REGISTROS DE PROGRESSO DO USUÁRIO
try {
    $stmt_progress = $conn->prepare("SELECT * FROM user_reading_progress WHERE user_id = :user_id ORDER BY last_updated DESC");
    $stmt_progress->execute(['user_id' => $user_id]);
    $progress_data = $stmt_progress->fetchAll(PDO::FETCH_ASSOC);

    // Envia os dados como JSON
    echo json_encode(['status' => 'success', 'data' => $progress_data]);
    exit;

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao buscar progresso: ' . $e->getMessage()]);
    exit;
}
?>