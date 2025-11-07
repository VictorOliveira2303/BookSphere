<?php
/*
 * ARQUIVO API: VERIFICAR-EMAIL
 * Local: pasta-php/verificar-email.php
 * Função: Verifica se o e-mail existe, se não está bloqueado, e envia o 1º código.
 */

session_start();
include 'conexao.php'; // Inclui sua conexão com o banco

// Inclui o PHPMailer (você precisará ter essa pasta)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php'; // Ajuste o caminho se necessário
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Define que a resposta será em formato JSON
header('Content-Type: application/json');

// 1. Validar Método (só aceita POST)
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Método inválido.']);
    exit;
}

$email = $_POST['email'];

// 2. Verificar se o e-mail existe na SUA tabela 'users'
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch();

if (!$usuario) {
    // ERRO: E-mail não encontrado
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não possui nenhum perfil com esse e-mail! Por favor, tente outro e-mail!']);
    exit;
}

// 3. Verificar se o usuário está bloqueado (coluna 'bloqueado_ate')
if ($usuario['bloqueado_ate'] && new DateTime() < new DateTime($usuario['bloqueado_ate'])) {
    $tempoRestante = (new DateTime($usuario['bloqueado_ate']))->diff(new DateTime());
    // ERRO: Conta bloqueada
    echo json_encode(['status' => 'erro', 'mensagem' => 'Conta bloqueada. Tente novamente em ' . $tempoRestante->i . ' minutos e ' . $tempoRestante->s . ' segundos.']);
    exit;
}

// 4. Gerar código, salvar e enviar
// (Gera de 0 a 9999 e preenche com zeros à esquerda, ex: "0042")
$codigo = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
$expira = date('Y-m-d H:i:s', strtotime('+10 minutes')); // Válido por 10 min

// Salva na sua tabela 'recuperacao_senha'
$stmt_insert = $pdo->prepare("INSERT INTO recuperacao_senha (email, codigo, expira_em, tentativas) VALUES (?, ?, ?, 0)");
$stmt_insert->execute([$email, $codigo, $expira]);

// 5. Enviar e-mail (Configure seu SMTP da Hostinger)
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com'; // SMTP da Hostinger
    $mail->SMTPAuth   = true;
    $mail->Username   = 'seu-email@seudominio.com'; // <-- MUDE ISSO
    $mail->Password   = 'sua-senha-de-email';     // <-- MUDE ISSO
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';
    
    $mail->setFrom('nao-responda@booksphere.com', 'Book Sphere'); // E-mail de envio
    $mail->addAddress($email); // E-mail do usuário

    $mail->isHTML(true);
    $mail->Subject = 'Book Sphere - Código de Recuperação';
    $mail->Body    = "Seu código de 4 dígitos para recuperação de conta é: <h1>$codigo</h1><br>Este código expira em 10 minutos.";
    $mail->send();

    // SUCESSO!
    echo json_encode(['status' => 'sucesso', 'mensagem' => 'Código enviado.']);

} catch (Exception $e) {
    // ERRO: Falha no envio do e-mail
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não foi possível enviar o e-mail de recuperação.']);
}
?>