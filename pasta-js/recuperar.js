/*
 * ARQUIVO JAVASCRIPT DA PÁGINA DE RECUPERAÇÃO
 * Local: pasta-js/recuperar.js
 */

// Espera todo o HTML ser carregado antes de executar
document.addEventListener("DOMContentLoaded", () => {
    
    // --- 1. Selecionar Todos os Elementos ---
    
    // Formulários
    const formEmail = document.getElementById("bloco_email");
    const formCodigo = document.getElementById("bloco_codigo");

    // Inputs e Botões
    const inputEmail = document.getElementById("email");
    const inputCodigo = document.getElementById("codigo");
    const btnVerificar = document.getElementById("btn_verificar");

    // Elementos de Mensagem e Texto
    const msgBox = document.querySelector(".box-msg"); // A "barrinha branca"
    const msgElemento = document.getElementById("mensagem");
    const cronometroElemento = document.getElementById("cronometro");
    const tituloElemento = document.getElementById("titulo_recuperacao");
    const subtituloElemento = document.getElementById("subtitulo_recuperacao");
    const infoCodigoElemento = document.getElementById("info_codigo");

    // --- 2. Variáveis de Estado (para controlar o processo) ---
    let tipoRecuperacao = ''; // 'senha' ou 'usuario'
    let emailParaVerificar = '';
    let tentativasRestantes = 3;
    let timerId = null; // Armazena o ID do cronômetro

    // --- 3. Inicialização (Função que roda assim que a página carrega) ---
    
    // Pega os parâmetros da URL (ex: ?tipo=senha)
    const urlParams = new URLSearchParams(window.location.search);
    tipoRecuperacao = urlParams.get('tipo'); 

    // Altera os textos da página com base na URL
    if (tipoRecuperacao === 'senha') {
        tituloElemento.textContent = "Recuperar Senha";
        subtituloElemento.textContent = "Digite seu e-mail para redefinir sua senha.";
    } else if (tipoRecuperacao === 'usuario') {
        tituloElemento.textContent = "Recuperar Usuário";
        subtituloElemento.textContent = "Digite seu e-mail para recuperar seu usuário.";
    } else {
        // Se a URL não tiver ?tipo=... , é um erro. Volta ao login.
        window.location.href = 'login.html';
        return;
    }

    // --- 4. Funções Auxiliares (Ferramentas) ---

    // Função para mostrar mensagens de status/erro
    function mostrarMensagem(texto, tipo = 'erro') {
        msgElemento.textContent = texto;
        msgElemento.style.color = (tipo === 'erro') ? '#ffcccc' : '#ccffcc'; // Vermelho ou Verde

        // Mostra a "barrinha branca" (box-msg)
        if (texto) {
            msgBox.classList.add('show');
        } else {
            msgBox.classList.remove('show');
        }
    }

    // Função que troca do formulário de e-mail para o de código
    function alternarParaBlocoCodigo() {
        formEmail.classList.add("hidden"); // Esconde o form de e-mail (usando a classe do CSS)
        formCodigo.style.display = "block"; // Mostra o form de código
        mostrarMensagem(""); // Limpa mensagens de erro antigas
        infoCodigoElemento.textContent = `Enviamos um código para ${emailParaVerificar}.`;
    }

    // Função do cronômetro de 1 minuto (como você pediu)
    function iniciarCronometro(segundos) {
        // Desabilita tudo durante o cronômetro
        inputCodigo.disabled = true;
        btnVerificar.disabled = true;
        btnVerificar.textContent = "Aguarde...";
        cronometroElemento.classList.remove("hidden"); // Mostra o cronômetro
        
        if (timerId) clearInterval(timerId); // Limpa timer anterior se houver

        let tempoRestante = segundos;

        timerId = setInterval(() => {
            tempoRestante--;
            cronometroElemento.textContent = `Tempo de espera: ${tempoRestante}s`;

            if (tempoRestante <= 0) {
                clearInterval(timerId);
                cronometroElemento.classList.add("hidden"); // Esconde o cronômetro
                
                // Se ainda há tentativas, reenvia o código
                if (tentativasRestantes > 0) {
                    mostrarMensagem("Reenviando um novo código...", 'sucesso');
                    reenviarCodigo(); // Chama a função de reenvio
                }
            }
        }, 1000); // Roda a cada 1 segundo
    }

    // --- 5. Lógica Principal (Eventos de Clique) ---

    // Evento 1: Usuário clica em "Enviar Código" (Formulário de E-mail)
    formEmail.addEventListener("submit", async (e) => {
        e.preventDefault(); // Impede o recarregamento da página
        emailParaVerificar = inputEmail.value;
        mostrarMensagem("Verificando e-mail...", 'sucesso');
        
        // Prepara os dados para enviar ao PHP
        const formData = new FormData();
        formData.append('email', emailParaVerificar);

        try {
            // Chama o arquivo PHP (que criaremos no próximo passo)
            const resposta = await fetch('pasta-php/verificar-email.php', {
                method: 'POST',
                body: formData
            });

            const json = await resposta.json(); // Espera a resposta (JSON) do PHP

            if (json.status === 'sucesso') {
                alternarParaBlocoCodigo(); // Sucesso! Troca para o formulário de código
            } else {
                mostrarMensagem(json.mensagem, 'erro'); // Mostra o erro (ex: "e-mail não encontrado")
            }
        } catch (err) {
            console.error(err); // Mostra erro de rede no console
            mostrarMensagem("Erro de comunicação com o servidor.", 'erro');
        }
    });

    // Evento 2: Usuário clica em "Verificar Código" (Formulário de Código)
    formCodigo.addEventListener("submit", async (e) => {
        e.preventDefault();
        const codigo = inputCodigo.value;
        mostrarMensagem("Verificando código...", 'sucesso');

        const formData = new FormData();
        formData.append('email', emailParaVerificar);
        formData.append('codigo', codigo);

        try {
            // Chama o arquivo PHP que verifica o código
            const resposta = await fetch('pasta-php/verificar-codigo.php', {
                method: 'POST',
                body: formData
            });

            const json = await resposta.json();

            if (json.status === 'sucesso') {
                // SUCESSO! Redireciona para a Página 2
                mostrarMensagem("Código correto! Redirecionando...", 'sucesso');
                // Passamos o tipo (senha/usuario) e o token de segurança para a próxima página
                window.location.href = `redefinir.html?tipo=${tipoRecuperacao}&token=${json.token}`;
            
            } else if (json.status === 'erro_tentativa') {
                // ERROU (1ª ou 2ª vez)
                tentativasRestantes = json.restantes;
                mostrarMensagem(`Código incorreto. Você tem ${tentativasRestantes} tentativa(s).`, 'erro');
                iniciarCronometro(60); // Inicia cronômetro de 1 minuto
            
            } else if (json.status === 'bloqueado') {
                // ERROU (3ª vez)
                mostrarMensagem(json.mensagem, 'erro');
                btnVerificar.disabled = true;
                inputCodigo.disabled = true;
                // Redireciona para o login após 5 segundos
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 5000);
            
            } else {
                // Outros erros (ex: código expirado, solicitação não encontrada)
                mostrarMensagem(json.mensagem, 'erro');
            }
        } catch (err) {
            console.error(err);
            mostrarMensagem("Erro de comunicação com o servidor.", 'erro');
        }
    });

    // Evento 3: Reenvio do Código (chamado pelo cronômetro)
    async function reenviarCodigo() {
        const formData = new FormData();
        formData.append('email', emailParaVerificar);

        try {
            // Chama o PHP de reenvio
            const resposta = await fetch('pasta-php/reenviar-codigo.php', {
                method: 'POST',
                body: formData
            });

            const json = await resposta.json();

            if (json.status === 'sucesso') {
                mostrarMensagem("Um novo código foi enviado. Tente novamente.", 'sucesso');
                // Reabilita o formulário
                inputCodigo.disabled = false;
                btnVerificar.disabled = false;
                btnVerificar.textContent = "Verificar Código";
                inputCodigo.value = ""; // Limpa o campo
            } else {
                mostrarMensagem("Falha ao reenviar o código.", 'erro');
            }
        } catch (err) {
            console.error(err);
            mostrarMensagem("Erro de comunicação com o servidor.", 'erro');
        }
    }
});