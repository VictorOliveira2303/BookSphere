<?php

// Define o cookie da sessão para durar 30 dias
$tempo_de_vida = 30 * 24 * 60 * 60; // 30 dias em segundos
session_set_cookie_params($tempo_de_vida);

session_start();

// Destrói todas as variáveis de sessão
$_SESSION = [];

// Destroi a sessão
session_destroy();

// Retorna status de sucesso
http_response_code(200);
echo json_encode(['message' => 'Logout realizado com sucesso.']);
?>
