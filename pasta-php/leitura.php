<?php

// Define o cookie da sessão para durar 30 dias
$tempo_de_vida = 30 * 24 * 60 * 60; // 30 dias em segundos
session_set_cookie_params($tempo_de_vida);

    session_start();
    // 1. Define o tema padrão (para visitantes)
    $reader_theme = 'modo-claro'; 

    // 2. Se o usuário estiver logado, busca o TEMA DO LEITOR da sessão
    if (isset($_SESSION['reader_theme'])) {
        $reader_theme = $_SESSION['reader_theme'];
    }
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leitura Online</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.14.305/pdf.min.js"></script>
    <link rel="stylesheet" href="../pasta-css/leitura.css">
    <link rel="shortcut icon" href="../LOGOS/Favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>

<body class="<?php echo $reader_theme; ?>" data-is-logged-in="<?php echo isset($_SESSION['usuario']) ? 'true' : 'false'; ?>">
    <nav class="menu-lateral">

        <!-- ⬇️ NOVA BARRA DE FERRAMENTAS MOBILE (visível apenas em mobile) ⬇️ -->
        <div class="mobile-toolbar">
            <div id="mobile-zoom-in" class="toolbar-btn" title="Aumentar Zoom"><i class="bi bi-plus-lg"></i></div>
            <div id="mobile-zoom-out" class="toolbar-btn" title="Diminuir Zoom"><i class="bi bi-dash-lg"></i></div>
            <div id="mobile-theme-btn" class="toolbar-btn" title="Mudar Modo de Leitura"><i
                    class="bi bi-brightness-high-fill"></i></div>
            <div id="mobile-annotate-btn" class="toolbar-btn" title="Adicionar Anotação Pessoal"><i
                    class="bi bi-pencil"></i></div>
            <div id="mobile-view-annotations-btn" class="toolbar-btn" title="Ver Anotações"><i
                    class="bi bi-journal-text"></i></div>
        </div>
      
        <div class="btn-expandir"><i id="btn-exp" class="bi bi-list"></i></div>

        <ul>
            <li class="item-menu">
                <div id="zoom-in" class="itens" title="Aumentar Zoom"><span class="icon"><i
                            class="bi bi-plus-lg"></i></span><span class="txt-link">Aumentar o zoom</span></div>
            </li>
            <li class="item-menu">
                <div id="zoom-out" class="itens" title="Diminuir Zoom"><span class="icon"><i
                            class="bi bi-dash-lg"></i></span><span class="txt-link">Diminuir o zoom</span></div>
            </li>
            <li class="item-menu">
                <div id="theme-btn" class="itens" title="Mudar Modo de Leitura"><span id="theme-icon-container"
                        class="icon"><i class="bi bi-brightness-high-fill"></i></span><span class="txt-link">Modo de
                        leitura</span></div>
            </li>
            <li class="item-menu">
                <div id="annotate-btn" class="itens" title="Adicionar Anotação Pessoal"><span class="icon"><i
                            class="bi bi-pencil"></i></span><span class="txt-link">Anotação Pessoal</span></div>
            </li>
            <li class="item-menu">
                <div id="view-annotations-btn" class="itens" title="Ver Anotações"><span class="icon"><i
                            class="bi bi-journal-text"></i></span><span class="txt-link">Ver Anotações</span></div>
            </li>
        </ul>
    </nav>
    <main id="main-content">
       
        <div class="flipbook-container">
 <button class="button-left"><i class="bi bi-arrow-left-circle-fill"></i></button>
            <div class="page-container">
                <div id="left-page-wrapper" class="page-wrapper"><canvas id="leftPageCanvas"></canvas></div>
                <div id="bookmark-icon-left" class="bookmark-icon left"><i class="bi bi-bookmark-fill"></i></div>
            </div>
            <div class="page-container">
                <div id="right-page-wrapper" class="page-wrapper"><canvas id="rightPageCanvas"></canvas></div>
                <div id="bookmark-icon-right" class="bookmark-icon right"><i class="bi bi-bookmark-fill"></i></div>
            </div>
            <button class="button-right"><i class="bi bi-arrow-right-circle-fill"></i></button>
            <div id="page-indicator"></div>
            <div class="page-controls">
                <button class="button-left2"><i class="bi bi-arrow-left-circle-fill"></i></button>
                <div id="page-indicator2"></div>
                <button class="button-right2"><i class="bi bi-arrow-right-circle-fill"></i></button>
            </div>
        </div>
    </main>
    <div id="note-modal" class="modal">
        <div class="modal-content">
            <h3 id="note-modal-title" class="modal-title">Adicionar Anotação Pessoal</h3><textarea id="note-input"
                class="modal-textarea" placeholder="Digite sua anotação pessoal aqui..."></textarea>
            <div class="modal-actions"><button id="note-cancel" class="modal-btn-secondary">Cancelar</button><button
                    id="note-save" class="modal-btn-primary">Salvar</button></div>
        </div>
    </div>
    <div id="annotation-action-modal" class="modal">
        <div class="modal-content"><button id="annotation-close" title="Fechar" class="close-modal-btn">&times;</button>
            <h3 class="modal-title">Sua Anotação</h3>
            <p class="modal-note-text"></p>
            <div class="modal-actions space-between"><button id="annotation-edit"
                    class="modal-btn-yellow">Editar</button><button id="annotation-delete"
                    class="modal-btn-red">Excluir</button></div>
        </div>
    </div>
    <div id="confirm-delete-modal" class="modal">
    <div class="modal-content text-center">
        <h3 class="modal-title">Confirmar Exclusão</h3>
        <p class="modal-text">Tem certeza que deseja excluir esta anotação permanentemente?</p>
        <div class="modal-actions">
            <button id="confirm-cancel-btn" class="modal-btn-secondary">Cancelar</button>
            <button id="confirm-delete-btn" class="modal-btn-red">Excluir</button>
        </div>
    </div>
</div>
    <div id="alert-modal" class="modal">
        <div class="modal-content text-center">
            <h3 class="modal-title">Aviso</h3>
            <p id="alert-message" class="modal-text"></p><button id="alert-ok" class="modal-btn-primary">OK</button>
        </div>
    </div>
    <div id="annotations-panel" class="annotations-panel-class">
        <div class="annotations-panel-header">
            <div class="header-top-row">
                <h3 class="annotations-panel-title">Anotações Pessoais</h3><button id="close-annotations-btn"
                    title="Fechar" class="close-annotations-btn-class"><i class="bi bi-x-lg"></i></button>
            </div><input type="search" id="annotation-search-input" placeholder="Pesquisar anotações...">
        </div>
        <div id="annotations-list" class="annotations-list-class"></div>
    </div>
    <script src="../pasta-js/leitura.js"></script>
</body>

</html>