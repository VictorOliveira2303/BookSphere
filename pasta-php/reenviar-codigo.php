<?php
/*
 * ARQUIVO API: REENVIAR-CÓDIGO
 * Local: pasta-php/reenviar-codigo.php
 * Função: Chamado pelo cronômetro. Envia um NOVO código.
 */

session_start();
include 'conexao.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php'; // Ajuste o caminho se necessário
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

header('Content-Type: application/json');

$email = $_POST['email'];

// 1. Gerar novo código
$codigo = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
$expira = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// 2. Criar um NOVO registro (para resetar as tentativas)
$stmt_insert = $pdo->prepare("INSERT INTO recuperacao_senha (email, codigo, expira_em, tentativas) VALUES (?, ?, ?, 0)");
$stmt_insert->execute([$email, $codigo, $expira]);

// 3. Enviar e-mail
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'seu-email@seudominio.com'; // <-- MUDE ISSO
    $mail->Password   = 'sua-senha-de-email';     // <-- MUDE ISSO
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom('nao-responda@booksphere.com', 'Book Sphere');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Book Sphere - Seu NOVO Código de Recuperação';
    $mail->Body    = "Seu novo código é: <h1>$codigo</h1>";
    $mail->send();

    echo json_encode(['status' => 'sucesso']);

} catch (Exception $e) {
    echo json_encode(['status' => 'erro']);
}
?>