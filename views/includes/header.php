<?php
/******************************************************************************
Curso: Engenharia de Software
Disciplina: Linguagem e Técnicas de Programação
Professor: Flores
Turma: ESOFT2
Componentes:
            26012215-2 - Willian Cezar De Souza Moreira
            26012217-2 - Pedro Machado Gebauer
            26004820-2 - Thiago Tratch
            26011025-2 - Maria Clara Mendes Ramalho
            26014987-2 - Maria Eduarda Delbone Cardoso
Data: 21 de Outubro de 2026
Descritivo: Cabeçalho comum a todas as páginas: início do HTML, menu lateral
            (no computador) e barra superior (no celular).
*******************************************************************************/

// Página atual, usada para destacar o item certo do menu.
$paginaAtiva = $_GET['page'] ?? 'home';

// Mensagem de aviso guardada na sessão (exibida no rodapé como um aviso flutuante).
$mensagem = obterMensagem();

// Itens do menu: página => texto e ícone. O rodapé usa a mesma lista no menu do celular.
$menu = [
    'home'       => ['rotulo' => 'Painel',     'icone' => 'painel'],
    'produtos'   => ['rotulo' => 'Produtos',   'icone' => 'produtos'],
    'categorias' => ['rotulo' => 'Categorias', 'icone' => 'categorias'],
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo ?? 'Início') ?> | <?= e(NOME_LOJA) ?></title>
    <link rel="icon" href="<?= asset('img/logo.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <script>
        // Aplica o tema escolhido antes de desenhar a página, para não piscar o tema claro.
        try {
            if (localStorage.getItem('tema') === 'escuro') {
                document.documentElement.dataset.tema = 'escuro';
            }
        } catch (erro) {}
    </script>
</head>
<body>
    <a class="pular" href="#conteudo">Pular para o conteúdo</a>

    <div class="app">
        <aside class="menu-lateral">
            <a href="<?= url('home') ?>" class="marca">
                <span class="marca-logo"><?= icone('chip', 22) ?></span>
                <span class="marca-texto">
                    <strong>TecnoByte</strong>
                    <span>Controle de estoque</span>
                </span>
            </a>

            <nav class="menu" aria-label="Menu principal">
                <span class="menu-titulo">Menu</span>
                <?php foreach ($menu as $paginaMenu => $item): ?>
                    <a href="<?= url($paginaMenu) ?>"
                       class="menu-item <?= $paginaAtiva === $paginaMenu ? 'ativo' : '' ?>"
                       <?= $paginaAtiva === $paginaMenu ? 'aria-current="page"' : '' ?>>
                        <?= icone($item['icone']) ?>
                        <span><?= e($item['rotulo']) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="menu-atalho">
                <strong>Atalho rápido</strong>
                <span>Cadastre um produto sem sair do que está fazendo.</span>
                <a href="<?= url('produtos', 'create') ?>" class="botao botao-primario">
                    <?= icone('mais', 18) ?> Novo produto
                </a>
            </div>

            <button type="button" class="botao-tema js-alternar-tema" aria-pressed="false">
                <?= icone('lua', 18) ?>
                <span data-rotulo-tema>Modo escuro</span>
            </button>
        </aside>

        <div class="area">
            <header class="topo-movel">
                <a href="<?= url('home') ?>" class="marca">
                    <span class="marca-logo"><?= icone('chip', 18) ?></span>
                    <span class="marca-texto"><strong>TecnoByte</strong></span>
                </a>
                <button type="button" class="botao-topo js-alternar-tema" aria-pressed="false" aria-label="Modo escuro">
                    <?= icone('lua', 22) ?>
                </button>
            </header>

            <main id="conteudo" class="conteudo">
