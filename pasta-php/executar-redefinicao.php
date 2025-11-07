<?php
/*
 * ARQUIVO API: EXECUTAR-REDEFINIÇÃO
 * Local: pasta-php/executar-redefinicao.php
 * Função: O passo final. Verifica o token e atualiza a senha ou o usuário.
 */

session_start();
include 'conexao.php';
header('Content-Type: application/json');

// --- 1. Verificação de Segurança (Token de Sessão) ---
//
// Esta é a parte mais importante. 
// Verifica se o token enviado pelo JS (via URL) é o mesmo
// que salvamos na sessão no 'verificar-codigo.php'.
// Isso prova que o usuário passou pela verificação do código.
//
if (!isset($_POST['token']) || !isset($_SESSION['token_recuperacao']) || $_POST['token'] != $_SESSION['token_recuperacao']) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Token de segurança inválido ou expirado. Por favor, reinicie o processo.']);
    exit;
}

// Se o token bateu, podemos prosseguir com segurança.
$email = $_SESSION['email_recuperacao']; // Pega o e-mail da sessão
$tipo = $_POST['tipo'];   // 'senha' ou 'usuario'
$valor = $_POST['valor']; // A nova senha ou novo usuário

try {
    // --- 2. Lógica de Atualização ---

    if ($tipo == 'senha') {
        // Ação: Redefinir Senha
        
        // CRÍTICO: Sempre armazene senhas usando HASH.
        $senha_hash = password_hash($valor, PASSWORD_DEFAULT);
        
        // Atualiza na sua tabela 'users'
        $stmt = $pdo->prepare("UPDATE users SET senha = ? WHERE email = ?");
        $stmt->execute([$senha_hash, $email]);
        
        $mensagem_sucesso = "Senha atualizada com sucesso! Redirecionando...";
        
    } elseif ($tipo == 'usuario') {
        // Ação: Redefinir Nome de Usuário
        
        // Verificação extra: O novo nome de usuário já está em uso?
        $stmt_check = $pdo->prepare("SELECT * FROM users WHERE usuario = ?");
        $stmt_check->execute([$valor]);
        if ($stmt_check->fetch()) {
            // ERRO: Nome de usuário já existe
            echo json_encode(['status' => 'erro', 'mensagem' => 'Este nome de usuário já está em uso. Tente outro.']);
            exit;
        }

        // Atualiza na sua tabela 'users'
        $stmt = $pdo->prepare("UPDATE users SET usuario = ? WHERE email = ?");
        $stmt->execute([$valor, $email]);

        $mensagem_sucesso = "Nome de usuário atualizado com sucesso! Redirecionando...";
        
    } else {
        // ERRO: Se o JS enviar um tipo inválido
        echo json_encode(['status' => 'erro', 'mensagem' => 'Tipo de operação inválido.']);
        exit;
    }

    // --- 3. Limpeza e Sucesso ---
    
    // Sucesso! Limpa o token e o e-mail da sessão para
    // que este processo não possa ser repetido.
    unset($_SESSION['token_recuperacao']);
    unset($_SESSION['email_recuperacao']);

    echo json_encode(['status' => 'sucesso', 'mensagem' => $mensagem_sucesso]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro no banco de dados: ' . $e->getMessage()]);
}
?>