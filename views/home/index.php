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
Descritivo: View do painel inicial: busca rápida, indicadores da loja, produtos
            que precisam de atenção, produtos por categoria e últimos cadastrados.
*******************************************************************************/
?>
<section class="cabecalho-pagina">
    <div>
        <span class="sobretitulo">Visão geral</span>
        <h1>Painel do estoque</h1>
    </div>
    <div class="cabecalho-acoes">
        <!-- Busca rápida: leva para a lista de produtos já filtrada -->
        <form method="get" action="<?= BASE_URL ?>/index.php" role="search">
            <input type="hidden" name="page" value="produtos">
            <label class="busca-global">
                <?= icone('busca', 18) ?>
                <input type="search" name="busca" placeholder="Buscar produto ou marca" aria-label="Buscar produto ou marca">
            </label>
        </form>
        <a href="<?= url('categorias', 'create') ?>" class="botao botao-secundario"><?= icone('mais', 18) ?> Categoria</a>
        <a href="<?= url('produtos', 'create') ?>" class="botao botao-primario"><?= icone('mais', 18) ?> Novo produto</a>
    </div>
</section>

<section class="indicadores" aria-label="Resumo do estoque">
    <article class="cartao indicador">
        <div class="indicador-topo">
            <span class="indicador-rotulo">Produtos cadastrados</span>
            <span class="indicador-icone"><?= icone('produtos', 18) ?></span>
        </div>
        <strong class="indicador-valor"><?= (int) $resumo['total_produtos'] ?></strong>
        <?php if ((int) $resumo['esgotados'] > 0): ?>
            <span class="indicador-detalhe alerta"><span class="ponto"></span><?= (int) $resumo['esgotados'] ?> esgotado(s)</span>
        <?php else: ?>
            <span class="indicador-detalhe">nenhum esgotado</span>
        <?php endif; ?>
    </article>

    <article class="cartao indicador">
        <div class="indicador-topo">
            <span class="indicador-rotulo">Categorias</span>
            <span class="indicador-icone"><?= icone('categorias', 18) ?></span>
        </div>
        <strong class="indicador-valor"><?= count($categorias) ?></strong>
        <span class="indicador-detalhe">
            <?= $categoriasVazias > 0 ? $categoriasVazias . ' ainda sem produtos' : 'todas com produtos' ?>
        </span>
    </article>

    <article class="cartao indicador">
        <div class="indicador-topo">
            <span class="indicador-rotulo">Unidades em estoque</span>
            <span class="indicador-icone"><?= icone('camadas', 18) ?></span>
        </div>
        <strong class="indicador-valor"><?= number_format((int) $resumo['total_unidades'], 0, ',', '.') ?></strong>
        <span class="indicador-detalhe">somando todos os produtos</span>
    </article>

    <article class="cartao indicador indicador-destaque">
        <div class="indicador-topo">
            <span class="indicador-rotulo">Valor do estoque</span>
            <span class="indicador-icone"><?= icone('dinheiro', 18) ?></span>
        </div>
        <strong class="indicador-valor"><?= formatarPreco($resumo['valor_estoque']) ?></strong>
        <span class="indicador-detalhe">preço × quantidade de cada produto</span>
    </article>
</section>

<div class="colunas">
    <section class="cartao bloco coluna-larga">
        <div class="bloco-cabecalho">
            <div class="bloco-titulo">
                <span class="icone-titulo"><?= icone('alerta', 18) ?></span>
                <h2>Precisa de atenção</h2>
            </div>
            <a href="<?= url('produtos', 'index', ['ordem' => 'estoque']) ?>" class="link-forte">Ver todos</a>
        </div>
        <p class="bloco-descricao">Produtos esgotados ou com até <?= LIMITE_ESTOQUE_BAIXO ?> unidades</p>

        <?php if (!$estoqueBaixo): ?>
            <p class="vazio-texto">Nenhum produto com estoque baixo. Tudo certo!</p>
        <?php else: ?>
            <ul class="lista-itens">
                <?php foreach ($estoqueBaixo as $item): ?>
                    <li>
                        <?= miniaturaProduto($item, 'p') ?>
                        <span class="item-texto">
                            <a href="<?= url('produtos', 'show', ['id' => $item['id']]) ?>" class="item-nome"><?= e($item['nome']) ?></a>
                            <span class="item-detalhe"><?= e($item['marca']) ?> · <?= e($item['categoria_nome']) ?></span>
                        </span>
                        <?= seloEstoque((int) $item['estoque']) ?>
                        <a href="<?= url('produtos', 'edit', ['id' => $item['id']]) ?>" class="botao botao-secundario botao-pequeno">Repor</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="cartao bloco coluna-media">
        <div class="bloco-cabecalho">
            <h2>Produtos por categoria</h2>
            <a href="<?= url('categorias') ?>" class="link-forte">Gerenciar</a>
        </div>

        <?php if (!$categorias): ?>
            <p class="vazio-texto">Nenhuma categoria cadastrada.</p>
        <?php else: ?>
            <ul class="barras">
                <?php foreach ($categorias as $categoria): ?>
                    <?php $largura = $maiorCategoria > 0 ? round($categoria['total_produtos'] / $maiorCategoria * 100) : 0; ?>
                    <li>
                        <a href="<?= url('produtos', 'index', ['categoria' => $categoria['id']]) ?>" class="barra-rotulo">
                            <span class="barra-nome">
                                <span class="miniatura miniatura-mini tom-<?= e($categoria['icone']) ?>">
                                    <img src="<?= e(urlIconeCategoria($categoria['icone'])) ?>" alt="" class="miniatura-icone">
                                </span>
                                <?= e($categoria['nome']) ?>
                            </span>
                            <strong><?= (int) $categoria['total_produtos'] ?></strong>
                        </a>
                        <span class="barra"><span class="barra-preenchida" style="width: <?= $largura ?>%"></span></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

<section class="cartao bloco">
    <div class="bloco-cabecalho">
        <h2>Últimos cadastrados</h2>
        <a href="<?= url('produtos', 'index', ['ordem' => 'recentes']) ?>" class="link-forte">Ver lista completa</a>
    </div>

    <?php if (!$recentes): ?>
        <p class="vazio-texto">Nenhum produto cadastrado ainda.</p>
    <?php else: ?>
        <div class="vitrine">
            <?php foreach ($recentes as $item): ?>
                <a href="<?= url('produtos', 'show', ['id' => $item['id']]) ?>" class="cartao-produto">
                    <?= miniaturaProduto($item, 'g') ?>
                    <span class="rotulo-categoria"><?= e($item['categoria_nome']) ?></span>
                    <strong class="item-nome"><?= e($item['nome']) ?></strong>
                    <span class="linha-preco">
                        <span class="preco-destaque"><?= formatarPreco($item['preco']) ?></span>
                        <?= seloEstoque((int) $item['estoque']) ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
