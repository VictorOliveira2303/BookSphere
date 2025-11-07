/*
 * ARQUIVO JAVASCRIPT DA PÁGINA DE REDEFINIÇÃO
 * Local: pasta-js/redefinir.js
 */

document.addEventListener("DOMContentLoaded", () => {

    // --- 1. Selecionar Elementos ---
    const formSenha = document.getElementById("bloco_nova_senha");
    const formUsuario = document.getElementById("bloco_novo_usuario");
    const msgBox = document.querySelector(".box-msg");
    const msgElemento = document.getElementById("mensagem");

    // Inputs do formulário de senha
    const inputNovaSenha = document.getElementById("nova_senha");
    const inputConfirmaSenha = document.getElementById("confirmar_senha");
    
    // Inputs do formulário de usuário
    const inputNovoUsuario = document.getElementById("novo_usuario");
    const inputConfirmaUsuario = document.getElementById("confirmar_usuario");

    // --- 2. Ler a URL ---
    const urlParams = new URLSearchParams(window.location.search);
    const tipo = urlParams.get('tipo');  // 'senha' ou 'usuario'
    const token = urlParams.get('token'); // O token de segurança

    // --- 3. Inicialização (Mostrar o formulário correto) ---
    if (tipo === 'senha') {
        formSenha.style.display = "block"; // Mostra o form de senha
    } else if (tipo === 'usuario') {
        formUsuario.style.display = "block"; // Mostra o form de usuário
    } else {
        mostrarMensagem("Tipo de redefinição inválido.");
        return;
    }

    // Segurança: Se não houver token, o processo é inválido
    if (!token) {
        mostrarMensagem("Token de segurança ausente. Acesso negado.");
        // Desabilita os formulários se não houver token
        formSenha.innerHTML = ""; 
        formUsuario.innerHTML = "";
        return;
    }

    // --- 4. Funções Auxiliares ---
    function mostrarMensagem(texto, tipo = 'erro') {
        msgElemento.textContent = texto;
        msgElemento.style.color = (tipo === 'erro') ? '#ffcccc' : '#ccffcc';
        
        if (texto) {
            msgBox.classList.add('show');
        } else {
            msgBox.classList.remove('show');
        }
    }

    // --- 5. Lógica de Eventos ---

    // Evento 1: Envio do Formulário de Senha
    formSenha.addEventListener("submit", async (e) => {
        e.preventDefault();
        
        // Validação
        if (inputNovaSenha.value !== inputConfirmaSenha.value) {
            mostrarMensagem("As senhas não coincidem.", 'erro');
            return;
        }
        if (inputNovaSenha.value.length < 6) { // Regra de senha
            mostrarMensagem("A senha deve ter pelo menos 6 caracteres.", 'erro');
            return;
        }

        // Se passou na validação, envia para o PHP
        enviarRedefinicao('senha', inputNovaSenha.value);
    });

    // Evento 2: Envio do Formulário de Usuário
    formUsuario.addEventListener("submit", async (e) => {
        e.preventDefault();

        // Validação
        if (inputNovoUsuario.value !== inputConfirmaUsuario.value) {
            mostrarMensagem("Os nomes de usuário não coincidem.", 'erro');
            return;
        }
         if (inputNovoUsuario.value.length < 3) { // Regra de usuário
            mostrarMensagem("O usuário deve ter pelo menos 3 caracteres.", 'erro');
            return;
        }
        
        // Se passou na validação, envia para o PHP
        enviarRedefinicao('usuario', inputNovoUsuario.value);
    });

    // Função Genérica de Envio (usada por ambos os formulários)
    async function enviarRedefinicao(tipoForm, valor) {
        mostrarMensagem("Salvando...", 'sucesso');
        
        const formData = new FormData();
        formData.append('tipo', tipoForm);   // 'senha' ou 'usuario'
        formData.append('valor', valor);     // A nova senha ou novo usuário
        formData.append('token', token);     // O token de segurança da URL

        try {
            // Chama o arquivo PHP final
            const resposta = await fetch('pasta-php/executar-redefinicao.php', {
                method: 'POST',
                body: formData
            });

            const json = await resposta.json();

            if (json.status === 'sucesso') {
                mostrarMensagem(json.mensagem, 'sucesso');
                // Redireciona para o login após 3 segundos
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 3000);
            } else {
                mostrarMensagem(json.mensagem, 'erro');
            }
        } catch (err) {
            console.error(err);
            mostrarMensagem("Erro de comunicação com o servidor.", 'erro');
        }
    }
});