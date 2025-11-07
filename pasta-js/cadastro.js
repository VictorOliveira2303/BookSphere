document.addEventListener('DOMContentLoaded', () => {
    
    // --- LÓGICA DO FORMULÁRIO DE CADASTRO ---
    
    const form = document.getElementById('formCadastro');
    const mensagem = document.getElementById('mensagem');
    const boxMsg = document.querySelector('.box-msg');

    const CORES = {
        sucesso: "green",
        erro: "red",
        alerta: "orange",
        info: "blue"
    };

    function mostrarMensagem(texto, cor = CORES.info) {
        if (mensagem && boxMsg) {
            mensagem.textContent = texto;
            mensagem.style.color = cor;
            boxMsg.style.display = texto.trim() ? 'block' : 'none';
        }
    }
    if (form) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault(); 
            const nome = document.getElementById('nome').value.trim();
            const email = document.getElementById('email').value.trim();
            const usuario = document.getElementById('usuario').value.trim();
            const senha = document.getElementById('senha').value;
            const confirmarSenha = document.getElementById('confirmar_senha').value;

            if (!nome || !email || !usuario || !senha) {
                mostrarMensagem("Preencha todos os campos obrigatórios.", CORES.alerta);
                return;
            }
            if (nome.length > 100) {
                mostrarMensagem("O nome completo não pode ter mais de 100 caracteres.", CORES.alerta);
                return;
            }
            if (senha !== confirmarSenha) {
                mostrarMensagem("As senhas não coincidem!", CORES.alerta);
                return;
            }

            const formData = new FormData(form);

            try {
                mostrarMensagem("Enviando dados...", CORES.info);
                const resposta = await fetch("/pasta-php/cadastrar.php", {
                    method: "POST",
                    body: formData
                });
                const texto = await resposta.text();

                if (texto.includes("sucesso")) {
                    mostrarMensagem("Usuário cadastrado com sucesso!", CORES.sucesso);
                    form.reset();
                    setTimeout(() => {
                        window.history.go(-1); // Volta 1 página
                    }, 3000); 

                } else if (texto.includes("senhas_diferentes")) {
                    mostrarMensagem("As senhas não conferem (servidor).", CORES.alerta);
                } else if (texto.includes("nome_muito_longo")) {
                    mostrarMensagem("O nome completo não pode ter mais de 100 caracteres.", CORES.erro);
                } else {
                    mostrarMensagem("Erro ao cadastrar. Verifique os dados.", CORES.erro);
                    console.error("Resposta do servidor:", texto);
                }
            } catch (erro) {
                mostrarMensagem("Erro na comunicação com o servidor.", CORES.erro);
                console.error("Erro fetch:", erro);
            }
        });
    }

    if (boxMsg) {
        boxMsg.style.display = 'none';
    }


    // --- LÓGICA DE OCULTAR/MOSTRAR SENHA (Para <i>) ---

    const senhaInput = document.getElementById('senha');
    const toggleSenha = document.getElementById('toggleSenha'); // O <i>
    const confirmarInput = document.getElementById('confirmar_senha');
    const toggleConfirmar = document.getElementById('toggleConfirmar'); // O <i>

    // Função agora recebe o input e o ÍCONE
    function togglePasswordVisibility(input, icon) {
        if (!input || !icon) return; 

        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);

        if (type === 'password') {
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    // Adiciona o 'click' diretamente no <i>
    if (toggleSenha) {
        toggleSenha.addEventListener('click', () => {
            togglePasswordVisibility(senhaInput, toggleSenha);
        });
    }
    if (toggleConfirmar) {
        toggleConfirmar.addEventListener('click', () => {
            togglePasswordVisibility(confirmarInput, toggleConfirmar);
        });
    }
    
    
    // ==============================================
    // ✅ LÓGICA DO PREVIEW (COM O AJUSTE DE 30%)
    // ==============================================
    
    const fileInput = document.getElementById('imagem');
    const imagePreview = document.getElementById('image-preview');
    const uploadPrompt = document.querySelector('.upload-prompt');

    if (fileInput && imagePreview && uploadPrompt) {
        
        fileInput.addEventListener('change', function() {
            const file = this.files[0];

            if (file) {
                if (!file.type.startsWith('image/')) {
                    alert("Por favor, selecione um arquivo de imagem (jpeg, png, etc.)");
                    fileInput.value = ""; 
                    return;
                }

                const reader = new FileReader();

                reader.onload = function(e) {
                    const imageUrl = e.target.result;
                    const img = new Image();
                    img.onload = function() {
                        
                        if (img.naturalWidth > img.naturalHeight * 1.5) {
                            imagePreview.style.objectPosition = 'center center';
                        } else {
                            imagePreview.style.objectPosition = 'center 30%'; 
                        }
                        
                        imagePreview.src = imageUrl;
                        imagePreview.style.display = 'block';
                        uploadPrompt.style.display = 'none';
                    };
                    img.src = imageUrl;
                };

                reader.readAsDataURL(file);

            } else {
                imagePreview.src = "#";
                imagePreview.style.display = 'none';
                uploadPrompt.style.display = 'flex';
                imagePreview.style.objectPosition = 'center center';
            }
        });
    }

}); // Fim do DOMContentLoaded