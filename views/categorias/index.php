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
Descritivo: View da listagem de categorias em cartões (com a quantidade e a
            proporção de produtos de cada uma) e o cadastro rápido ao lado.
*******************************************************************************/

// Total de produtos da loja, para calcular a proporção de cada categoria.
$totalProdutos = 0;
foreach ($categorias as $categoria) {
    $totalProdutos += (int) $categoria['total_produtos'];
}

// O cadastro rápido usa os mesmos campos do formulário completo, começando vazio.
$dados = ['nome' => '', 'descricao' => '', 'icone' => 'caixa'];
$erros = [];
?>
<section class="cabecalho-pagina">
    <div>
        <nav class="trilha" aria-label="Você está em"><a href="<?= url('home') ?>">Painel</a> / <span>Categorias</span></nav>
        <h1>Categorias</h1>
        <p class="subtitulo"><?= count($categorias) ?> categoria(s) · <?= $totalProdutos ?> produto(s) distribuídos</p>
    </div>
</section>

<div class="colunas">
    <section class="coluna-larga" aria-label="Lista de categorias">
        <?php if (!$categorias): ?>
            <div class="vazio">
                <span class="vazio-icone"><?= icone('categorias', 26) ?></span>
                <strong>Nenhuma categoria cadastrada</strong>
                <p>Use o formulário ao lado para cadastrar a primeira.</p>
            </div>
        <?php else: ?>
            <div class="grade-categorias">
                <?php foreach ($categorias as $categoria): ?>
                    <?php
                    $quantidade = (int) $categoria['total_produtos'];
                    $largura = $totalProdutos > 0 ? round($quantidade / $totalProdutos * 100) : 0;
                    ?>
                    <article class="cartao cartao-categoria">
                        <div class="cartao-categoria-topo">
                            <span class="miniatura miniatura-p tom-<?= e($categoria['icone']) ?>">
                                <img src="<?= e(urlIconeCategoria($categoria['icone'])) ?>" alt="" class="miniatura-icone">
                            </span>
                            <span class="item-texto">
                                <strong><?= e($categoria['nome']) ?></strong>
                                <span class="item-detalhe"><?= $quantidade === 1 ? '1 produto' : $quantidade . ' produtos' ?></span>
                            </span>
                            <?php if ($quantidade === 0): ?>
                                <span class="selo selo-neutro">Vazia</span>
                            <?php endif; ?>
                        </div>

                        <p><?= $categoria['descricao'] ? e($categoria['descricao']) : 'Sem descrição.' ?></p>

                        <span class="barra" title="<?= $largura ?>% dos produtos da loja">
                            <span class="barra-preenchida" style="width: <?= $largura ?>%"></span>
                        </span>

                        <div class="cartao-categoria-acoes">
                            <a href="<?= url('produtos', 'index', ['categoria' => $categoria['id']]) ?>" class="link-forte">
                                Ver produtos <?= icone('proxima', 16) ?>
                            </a>
                            <a href="<?= url('produtos', 'create', ['categoria' => $categoria['id']]) ?>" class="botao botao-icone"
                               aria-label="<?= e('Novo produto em ' . $categoria['nome']) ?>" title="Novo produto nesta categoria"><?= icone('mais', 18) ?></a>
                            <a href="<?= url('categorias', 'edit', ['id' => $categoria['id']]) ?>" class="botao botao-icone"
                               aria-label="<?= e('Editar ' . $categoria['nome']) ?>" title="Editar"><?= icone('lapis', 18) ?></a>

                            <?php if ($quantidade > 0): ?>
                                <!-- Categoria com produtos não pode ser excluída (o servidor também confere) -->
                                <button type="button" class="botao botao-icone perigo" disabled
                                        aria-label="<?= e('Excluir ' . $categoria['nome'] . ' (indisponível: a categoria tem produtos)') ?>"
                                        title="Mova ou exclua os produtos antes"><?= icone('lixeira', 18) ?></button>
                            <?php else: ?>
                                <form method="post" action="<?= url('categorias', 'delete', ['id' => $categoria['id']]) ?>"
                                      data-confirmar="A categoria está vazia e será apagada. Essa ação não pode ser desfeita."
                                      data-confirmar-titulo="Excluir esta categoria?"
                                      data-confirmar-item="<?= e($categoria['nome']) ?>"
                                      data-confirmar-detalhe="Nenhum produto cadastrado"
                                      data-confirmar-botao="Excluir categoria">
                                    <?= campoCsrf() ?>
                                    <button type="submit" class="botao botao-icone perigo"
                                            aria-label="<?= e('Excluir ' . $categoria['nome']) ?>" title="Excluir"><?= icone('lixeira', 18) ?></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <aside class="coluna-estreita formulario-lateral">
        <form method="post" action="<?= url('categorias', 'create') ?>" class="cartao secao" novalidate>
            <?= campoCsrf() ?>
            <div>
                <h2>Nova categoria</h2>
                <p class="subtitulo">Cadastre sem sair desta tela.</p>
            </div>
            <?php include __DIR__ . '/_campos.php'; ?>
            <button type="submit" class="botao botao-primario"><?= icone('mais', 18) ?> Adicionar categoria</button>
        </form>

        <div class="nota" role="note">
            <?= icone('info', 20) ?>
            <span>Só dá para excluir uma categoria vazia. Mova ou exclua os produtos dela antes.</span>
        </div>
    </aside>
</div>
