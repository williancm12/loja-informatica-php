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
Descritivo: View do formulário de produto, usada no cadastro e na edição.
            Mostra os erros ao lado de cada campo e, à direita, uma prévia de
            como o produto vai aparecer na loja (atualizada pelo js/app.js).
*******************************************************************************/

// Categoria escolhida no momento (define o ícone e o nome na prévia).
$categoriaEscolhida = null;
foreach ($categorias as $categoria) {
    if ((int) $categoria['id'] === (int) $dados['categoria_id']) {
        $categoriaEscolhida = $categoria;
    }
}
$iconePrevia   = $categoriaEscolhida['icone'] ?? 'caixa';
$fotoAtual     = urlFotoProduto($dados['imagem'] ?? null);
$precoPrevia   = converterPreco((string) $dados['preco']);
$estoquePrevia = max(0, (int) $dados['estoque']);
?>
<section class="cabecalho-pagina">
    <div>
        <nav class="trilha" aria-label="Você está em">
            <a href="<?= url('home') ?>">Painel</a> /
            <a href="<?= url('produtos') ?>">Produtos</a> /
            <span><?= e($titulo) ?></span>
        </nav>
        <h1><?= e($titulo) ?></h1>
        <p class="subtitulo">Campos com * são obrigatórios. A prévia ao lado mostra como o produto vai aparecer na loja.</p>
    </div>
</section>

<?php if ($erros): ?>
    <div class="alerta-caixa alerta-erro" role="alert">
        <?= icone('erro', 20) ?>
        <?= count($erros) === 1 ? 'Falta corrigir 1 campo antes de salvar.' : 'Falta corrigir ' . count($erros) . ' campos antes de salvar.' ?>
    </div>
<?php endif; ?>

<div class="colunas">
    <!-- enctype multipart/form-data é obrigatório para enviar arquivos (a foto) -->
    <form method="post" action="<?= e($acao) ?>" enctype="multipart/form-data" novalidate
          class="coluna-larga formulario-principal"
          data-formulario-produto
          data-limite-baixo="<?= LIMITE_ESTOQUE_BAIXO ?>"
          data-pasta-icones="<?= e(asset('img/categorias/')) ?>">
        <?= campoCsrf() ?>

        <section class="cartao secao">
            <h2>Informações básicas</h2>

            <div class="campo <?= isset($erros['nome']) ? 'com-erro' : '' ?>">
                <label for="nome">Nome do produto *</label>
                <input type="text" id="nome" name="nome" class="entrada" maxlength="150" required
                       value="<?= e($dados['nome']) ?>" placeholder="Ex.: Notebook Inspiron 15" <?= atributosErro($erros, 'nome') ?>>
                <?= erroCampo($erros, 'nome') ?>
            </div>

            <div class="grade-campos">
                <div class="campo <?= isset($erros['marca']) ? 'com-erro' : '' ?>">
                    <label for="marca">Marca *</label>
                    <input type="text" id="marca" name="marca" class="entrada" maxlength="80" required
                           value="<?= e($dados['marca']) ?>" placeholder="Ex.: Dell" <?= atributosErro($erros, 'marca') ?>>
                    <?= erroCampo($erros, 'marca') ?>
                </div>

                <div class="campo <?= isset($erros['categoria_id']) ? 'com-erro' : '' ?>">
                    <label for="categoria_id">Categoria *</label>
                    <select id="categoria_id" name="categoria_id" class="entrada" required <?= atributosErro($erros, 'categoria_id') ?>>
                        <option value="">Selecione…</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= $categoria['id'] ?>" data-icone="<?= e($categoria['icone']) ?>"
                                <?= (int) $dados['categoria_id'] === (int) $categoria['id'] ? 'selected' : '' ?>>
                                <?= e($categoria['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?= erroCampo($erros, 'categoria_id') ?>
                </div>
            </div>
        </section>

        <section class="cartao secao">
            <h2>Preço e estoque</h2>

            <div class="grade-campos">
                <div class="campo <?= isset($erros['preco']) ? 'com-erro' : '' ?>">
                    <label for="preco">Preço de venda *</label>
                    <div class="entrada-grupo">
                        <span class="entrada-prefixo">R$</span>
                        <input type="text" id="preco" name="preco" class="entrada" inputmode="decimal" required
                               value="<?= e($dados['preco']) ?>" placeholder="1.299,90" <?= atributosErro($erros, 'preco') ?>>
                    </div>
                    <?= erroCampo($erros, 'preco') ?>
                    <span class="ajuda">Pode digitar com vírgula: 1.299,90</span>
                </div>

                <div class="campo <?= isset($erros['estoque']) ? 'com-erro' : '' ?>">
                    <label for="estoque">Quantidade em estoque *</label>
                    <div class="passo">
                        <button type="button" class="botao botao-icone" data-passo="-1" aria-label="Diminuir quantidade"><?= icone('menos', 18) ?></button>
                        <input type="number" id="estoque" name="estoque" class="entrada" min="0" step="1" required
                               value="<?= e($dados['estoque']) ?>" <?= atributosErro($erros, 'estoque') ?>>
                        <button type="button" class="botao botao-icone" data-passo="1" aria-label="Aumentar quantidade"><?= icone('mais', 18) ?></button>
                    </div>
                    <?= erroCampo($erros, 'estoque') ?>
                    <span class="ajuda">Com <?= LIMITE_ESTOQUE_BAIXO ?> unidades ou menos o produto entra na lista de atenção.</span>
                </div>
            </div>
        </section>

        <section class="cartao secao">
            <div class="titulo-com-contador">
                <h2><label for="descricao">Descrição</label></h2>
                <span class="contador" data-contador><?= mb_strlen((string) $dados['descricao']) ?> / 2000</span>
            </div>
            <div class="campo <?= isset($erros['descricao']) ? 'com-erro' : '' ?>">
                <textarea id="descricao" name="descricao" class="entrada" rows="4" maxlength="2000"
                          placeholder="Principais características do produto" <?= atributosErro($erros, 'descricao') ?>><?= e($dados['descricao']) ?></textarea>
                <?= erroCampo($erros, 'descricao') ?>
            </div>
        </section>

        <section class="cartao secao">
            <h2>Foto do produto</h2>

            <?php if ($fotoAtual): ?>
                <div class="foto-atual">
                    <?= miniaturaProduto(['imagem' => $dados['imagem'], 'categoria_icone' => $iconePrevia], 'm') ?>
                    <label class="opcao">
                        <input type="checkbox" id="remover_imagem" name="remover_imagem" value="1"> Remover a foto atual
                    </label>
                </div>
            <?php endif; ?>

            <label class="area-upload" for="imagem" data-area-upload>
                <span class="area-upload-icone"><?= icone('upload', 24) ?></span>
                <strong>Arraste a foto para cá ou <span class="destaque-texto">escolha um arquivo</span></strong>
                <span class="ajuda">JPG, PNG, WEBP ou GIF · até 2 MB · sem foto, aparece o ícone da categoria</span>
                <span class="arquivo-escolhido" data-arquivo-escolhido aria-live="polite"></span>
                <input type="file" id="imagem" name="imagem" class="input-arquivo"
                       accept="image/jpeg,image/png,image/webp,image/gif" <?= atributosErro($erros, 'imagem') ?>>
            </label>
            <?= erroCampo($erros, 'imagem') ?>
        </section>

        <!-- O primeiro botão é o que o Enter aciona; o CSS mostra os botões na ordem inversa -->
        <div class="formulario-acoes">
            <button type="submit" name="depois" value="ver" class="botao botao-primario"><?= icone('check', 18) ?> Salvar produto</button>
            <?php if (!$editando): ?>
                <button type="submit" name="depois" value="novo" class="botao botao-secundario">Salvar e cadastrar outro</button>
            <?php endif; ?>
            <a href="<?= e($cancelar) ?>" class="botao botao-texto">Cancelar</a>
        </div>
    </form>

    <aside class="coluna-estreita formulario-lateral" aria-label="Prévia do produto">
        <section class="cartao secao">
            <h2 class="titulo-lateral">Prévia na loja</h2>
            <div class="cartao-produto">
                <span class="miniatura miniatura-g tom-<?= e($iconePrevia) ?>" data-previa="miniatura" data-foto-atual="<?= e($fotoAtual ?? '') ?>">
                    <img src="<?= e($fotoAtual ?: urlIconeCategoria($iconePrevia)) ?>" alt="" class="<?= $fotoAtual ? 'miniatura-foto' : 'miniatura-icone' ?>">
                </span>
                <span class="rotulo-categoria" data-previa="categoria"><?= e($categoriaEscolhida['nome'] ?? 'Categoria') ?></span>
                <strong class="item-nome" data-previa="nome"><?= e($dados['nome'] !== '' ? $dados['nome'] : 'Nome do produto') ?></strong>
                <span class="item-detalhe" data-previa="marca"><?= e($dados['marca'] !== '' ? $dados['marca'] : 'Marca') ?></span>
                <span class="linha-preco">
                    <span class="preco-destaque" data-previa="preco"><?= $precoPrevia !== null && $precoPrevia > 0 ? formatarPreco($precoPrevia) : 'R$ —' ?></span>
                    <span data-previa="selo"><?= seloEstoque($estoquePrevia) ?></span>
                </span>
            </div>
        </section>

        <section class="cartao secao">
            <h2 class="titulo-lateral">Selos de estoque</h2>
            <ul class="legenda-selos">
                <li>Mais de <?= LIMITE_ESTOQUE_BAIXO ?> unidades <?= seloEstoque(12) ?></li>
                <li>De 1 a <?= LIMITE_ESTOQUE_BAIXO ?> unidades <?= seloEstoque(3) ?></li>
                <li>Sem unidades <?= seloEstoque(0) ?></li>
            </ul>
        </section>
    </aside>
</div>
