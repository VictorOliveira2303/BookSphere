document.addEventListener('DOMContentLoaded', () => {
    // Busca os elementos da página de perfil
    const inProgressGrid = document.getElementById('inprogress-grid');
    const concludedGrid = document.getElementById('concluded-grid');
    const readingTimeEl = document.getElementById('tempo-leitura');
    const loginModal = document.getElementById('login-modal');
    const loginNowBtn = document.getElementById('login-now');

    if (loginNowBtn) {
        loginNowBtn.addEventListener('click', () => {
            window.location.href = '../pasta-html/login.html'; // Redireciona para o login
        });
    }
    
    // Função principal para carregar o perfil
    async function loadProfileData() {
        try {
            const response = await fetch('../pasta-php/get_progress.php', {
                credentials: 'include' // Envia cookies (importante para a sessão)
            });

            if (!response.ok) {
                throw new Error('Erro de rede ou permissão');
            }

            const result = await response.json();

            if (result.status === 'success') {
                processBooks(result.data);
            } else {
                throw new Error(result.message || 'Erro desconhecido ao buscar dados.');
            }

        } catch (error) {
            console.error('Falha ao carregar dados do perfil:', error);
            // Se der qualquer outro erro, também mostra o modal
            if (loginModal) loginModal.hidden = false;
        }
    }

    // Função para processar os dados e construir o HTML
    function processBooks(books) {
        let inProgressHTML = '';
        let concludedHTML = '';
        let totalMinutesRead = 0;
        const MINUTES_PER_PAGE = 1.5; // Estimativa de 1.5 min por página

        books.forEach(book => {
            let percentage = 0;
            if (book.total_pages > 0) {
                percentage = (book.last_page / book.total_pages) * 100;
            }

            // Arredonda a porcentagem
            const percentageRounded = Math.floor(percentage);

            // Calcula o tempo de leitura
            totalMinutesRead += book.last_page * MINUTES_PER_PAGE;

            // Cria o HTML do cartão do livro
            const bookCardHTML = `
                <div class="book-card" data-pdf="${book.book_identifier}" data-genre="${book.book_genre}" data-title="${book.book_title}" data-cover="${book.book_cover}">
                    <img src="${book.book_cover}" alt="${book.book_title}">
                    <h4>${book.book_title}</h4>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: ${percentageRounded}%;"></div>
                    </div>
                    <span class="progress-percentage">${percentageRounded}%</span>
                </div>
            `;

            // Separa os livros
            if (percentageRounded >= 100) {
                concludedHTML += bookCardHTML;
            } else if (book.last_page > 1) { // Só mostra se tiver lido > página 1
                inProgressHTML += bookCardHTML;
            }
        });

        // Adiciona mensagens de "vazio" se não houver livros
        if (inProgressHTML === '') {
            inProgressHTML = '<p class="empty-message">Sem livros em progresso.</p>';
        }
        if (concludedHTML === '') {
            concludedHTML = '<p class="empty-message">Sem livros concluídos.</p>';
        }

        // Insere o HTML nas grids
        inProgressGrid.innerHTML = inProgressHTML;
        concludedGrid.innerHTML = concludedHTML;

        // Adiciona o clique para continuar lendo
        addBookClickListeners();
    }

    // Função para adicionar o evento de clique nos cartões
    function addBookClickListeners() {
        document.querySelectorAll('.book-card').forEach(card => {
            card.addEventListener('click', () => {
                const params = new URLSearchParams();
                params.set('pdf', card.dataset.pdf);
                params.set('genre', card.dataset.genre);
                params.set('title', card.dataset.title);
                params.set('cover', card.dataset.cover);

                // Redireciona para o leitor com todos os dados
                window.location.href = `../pasta-php/leitura.php?${params.toString()}`;
            });
        });
    }

    // Inicia o carregamento
    loadProfileData();
});