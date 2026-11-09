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
Descritivo: View de detalhes de um produto: foto, preço, situação do estoque,
            dados de cadastro, descrição, botões de editar e excluir e outros
            produtos da mesma categoria.
*******************************************************************************/

$estoque = (int) $produto['estoque'];
?>
<section class="cabecalho-pagina">
    <nav class="trilha" aria-label="Você está em">
        <a href="<?= url('home') ?>">Painel</a> /
        <a href="<?= url('produtos') ?>">Produtos</a> /
        <a href="<?= url('produtos', 'index', ['categoria' => $produto['categoria_id']]) ?>"><?= e($produto['categoria_nome']) ?></a> /
        <span><?= e($produto['nome']) ?></span>
    </nav>

    <div class="cabecalho-acoes">
        <a href="<?= url('produtos') ?>" class="botao botao-secundario"><?= icone('voltar', 18) ?> Voltar</a>
        <form method="post" action="<?= url('produtos', 'delete', ['id' => $produto['id']]) ?>"
              data-confirmar="O produto sai do estoque e a foto dele é apagada. Essa ação não pode ser desfeita."
              data-confirmar-titulo="Excluir este produto?"
              data-confirmar-item="<?= e($produto['nome']) ?>"
              data-confirmar-detalhe="<?= e($produto['marca'] . ' · ' . $produto['categoria_nome'] . ' · ' . formatarPreco($produto['preco'])) ?>"
              data-confirmar-botao="Excluir produto">
            <?= campoCsrf() ?>
            <button type="submit" class="botao botao-perigo-contorno"><?= icone('lixeira', 18) ?> Excluir</button>
        </form>
        <a href="<?= url('produtos', 'edit', ['id' => $produto['id']]) ?>" class="botao botao-primario"><?= icone('lapis', 18) ?> Editar produto</a>
    </div>
</section>

<article class="cartao detalhe">
    <div class="detalhe-foto">
        <?= miniaturaProduto($produto, 'xg') ?>
        <?php if (!urlFotoProduto($produto['imagem'])): ?>
            <span class="legenda-foto">
                Produto sem foto · <a href="<?= url('produtos', 'edit', ['id' => $produto['id']]) ?>">adicionar foto</a>
            </span>
        <?php endif; ?>
    </div>

    <div class="detalhe-info">
        <span class="detalhe-marca"><?= e($produto['marca']) ?></span>
        <h1><?= e($produto['nome']) ?></h1>

        <div class="linha-preco-detalhe">
            <span class="detalhe-preco"><?= formatarPreco($produto['preco']) ?></span>
            <?= seloEstoque($estoque) ?>
        </div>

        <?php if ($estoque <= LIMITE_ESTOQUE_BAIXO): ?>
            <div class="alerta-caixa alerta-aviso" role="note">
                <?= icone('alerta', 20) ?>
                <span class="alerta-caixa-texto">
                    <?php if ($estoque === 0): ?>
                        <strong>Produto esgotado.</strong> Não há unidades em estoque.
                    <?php else: ?>
                        <strong>Estoque baixo.</strong> Restam <?= $estoque ?> unidade(s), o limite de atenção é <?= LIMITE_ESTOQUE_BAIXO ?>.
                    <?php endif; ?>
                </span>
                <a href="<?= url('produtos', 'edit', ['id' => $produto['id']]) ?>" class="botao botao-pequeno">Atualizar estoque</a>
            </div>
        <?php endif; ?>

        <dl class="dados">
            <dt>Categoria</dt>
            <dd><a href="<?= url('produtos', 'index', ['categoria' => $produto['categoria_id']]) ?>"><?= e($produto['categoria_nome']) ?></a></dd>

            <dt>Marca</dt>
            <dd><?= e($produto['marca']) ?></dd>

            <dt>Valor em estoque</dt>
            <dd><?= formatarPreco($produto['preco'] * $estoque) ?></dd>

            <dt>Cadastrado em</dt>
            <dd><?= formatarData($produto['criado_em']) ?></dd>

            <dt>Última alteração</dt>
            <dd><?= formatarData($produto['atualizado_em']) ?></dd>
        </dl>

        <h2>Descrição</h2>
        <?php if ($produto['descricao']): ?>
            <p class="detalhe-descricao"><?= nl2br(e($produto['descricao'])) ?></p>
        <?php else: ?>
            <p class="vazio-texto">Produto sem descrição.</p>
        <?php endif; ?>
    </div>
</article>

<?php if ($relacionados): ?>
    <section>
        <div class="bloco-cabecalho">
            <h2>Outros produtos em <?= e($produto['categoria_nome']) ?></h2>
            <a href="<?= url('produtos', 'index', ['categoria' => $produto['categoria_id']]) ?>" class="link-forte">Ver a categoria</a>
        </div>
        <div class="relacionados">
            <?php foreach ($relacionados as $item): ?>
                <a href="<?= url('produtos', 'show', ['id' => $item['id']]) ?>" class="cartao-relacionado">
                    <?= miniaturaProduto($item, 'm') ?>
                    <span class="item-texto">
                        <strong><?= e($item['nome']) ?></strong>
                        <span class="item-detalhe"><?= e($item['marca']) ?></span>
                        <span class="linha-preco">
                            <span class="preco"><?= formatarPreco($item['preco']) ?></span>
                            <?= seloEstoque((int) $item['estoque']) ?>
                        </span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
