<?php
/*
 * ARQUIVO API: VERIFICAR-CÓDIGO
 * Local: pasta-php/verificar-codigo.php
 * Função: Verifica o código, gerencia tentativas e bloqueia se errar 3x.
 */

session_start();
include 'conexao.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Método inválido.']);
    exit;
}

$email = $_POST['email'];
$codigo_usuario = $_POST['codigo'];
$agora = date('Y-m-d H:i:s');

// 1. Buscar o código mais recente, não usado, para este e-mail
$stmt = $pdo->prepare("SELECT * FROM recuperacao_senha WHERE email = ? AND usado = 0 ORDER BY id DESC LIMIT 1");
$stmt->execute([$email]);
$registro = $stmt->fetch();

if (!$registro) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Nenhuma solicitação de recuperação encontrada.']);
    exit;
}

// 2. Verificar expiração
if ($agora > $registro['expira_em']) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Código expirado. Por favor, reinicie o processo.']);
    exit;
}

// 3. Verificar o código
if ($registro['codigo'] == $codigo_usuario) {
    // SUCESSO!
    // Marcar como usado
    $pdo->prepare("UPDATE recuperacao_senha SET usado = 1 WHERE id = ?")->execute([$registro['id']]);

    // Gerar um token de sessão seguro para a próxima etapa (Página 2)
    $token = bin2hex(random_bytes(32));
    $_SESSION['token_recuperacao'] = $token;
    $_SESSION['email_recuperacao'] = $email; // Salva o e-mail na sessão

    // Retorna o token para o JS
    echo json_encode(['status' => 'sucesso', 'token' => $token]);
    exit;
}

// 4. CÓDIGO INCORRETO - Gerenciar tentativas
$tentativas_feitas = $registro['tentativas'] + 1;
$pdo->prepare("UPDATE recuperacao_senha SET tentativas = ? WHERE id = ?")->execute([$tentativas_feitas, $registro['id']]);
$tentativas_restantes = 3 - $tentativas_feitas;

if ($tentativas_restantes > 0) {
    // Ainda tem tentativas (1 ou 2)
    echo json_encode(['status' => 'erro_tentativa', 'restantes' => $tentativas_restantes]);
    exit;
}

// 5. BLOQUEADO! (Acabou as 3 tentativas)
// Define um bloqueio de 5 minutos na conta do usuário (na tabela 'users')
$bloqueio_expira = date('Y-m-d H:i:s', strtotime('+5 minutes'));
$pdo->prepare("UPDATE users SET bloqueado_ate = ? WHERE email = ?")->execute([$bloqueio_expira, $email]);

echo json_encode([
    'status' => 'bloqueado', 
    'mensagem' => 'Você excedeu o número de tentativas. Sua conta foi bloqueada por 5 minutos.'
]);
?>