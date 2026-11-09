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
Descritivo: View da listagem de produtos: busca, filtros por categoria e por
            situação do estoque, ordenação, exibição em tabela ou em grade e
            paginação.
*******************************************************************************/

// Filtros atuais. Entram em todos os links da página para não se perderem
// ao trocar de página, de filtro rápido ou de forma de exibição.
$parametros = [
    'busca'     => $filtros['busca'],
    'categoria' => $filtros['categoria'] ?: '',
    'situacao'  => $filtros['situacao'],
    'ordem'     => $filtros['ordem'] !== 'nome' ? $filtros['ordem'] : '',
    'visao'     => $visao !== 'tabela' ? $visao : '',
];

$filtrosAtivos = $filtros['busca'] !== '' || $filtros['categoria'] || $filtros['situacao'] !== '';

// Botões de filtro rápido pela situação do estoque.
$chips = ['' => 'Todos'] + Produto::SITUACOES;
$quantidades = ['' => $contagem['todos'], 'baixo' => $contagem['baixo'], 'esgotado' => $contagem['esgotado']];

$primeiro = $total > 0 ? $deslocamento + 1 : 0;
$ultimo   = $deslocamento + count($produtos);
?>
<section class="cabecalho-pagina">
    <div>
        <nav class="trilha" aria-label="Você está em"><a href="<?= url('home') ?>">Painel</a> / <span>Produtos</span></nav>
        <h1>Produtos</h1>
        <p class="subtitulo"><?= $total ?> produto(s) encontrado(s)</p>
    </div>
    <a href="<?= url('produtos', 'create') ?>" class="botao botao-primario esconder-no-celular"><?= icone('mais', 18) ?> Novo produto</a>
</section>

<section class="cartao filtros" aria-label="Filtros">
    <form method="get" action="<?= BASE_URL ?>/index.php" class="filtros-linha">
        <input type="hidden" name="page" value="produtos">
        <?php if ($filtros['situacao'] !== ''): ?>
            <input type="hidden" name="situacao" value="<?= e($filtros['situacao']) ?>">
        <?php endif; ?>
        <?php if ($visao !== 'tabela'): ?>
            <input type="hidden" name="visao" value="<?= e($visao) ?>">
        <?php endif; ?>

        <div class="campo campo-busca">
            <label for="busca">Buscar</label>
            <div class="entrada-grupo">
                <span class="entrada-icone"><?= icone('busca', 18) ?></span>
                <input type="search" id="busca" name="busca" class="entrada" value="<?= e($filtros['busca']) ?>" placeholder="Nome ou marca, ex.: Logitech">
            </div>
        </div>

        <div class="campo">
            <label for="categoria">Categoria</label>
            <select id="categoria" name="categoria" class="entrada">
                <option value="">Todas as categorias</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= $categoria['id'] ?>" <?= $filtros['categoria'] === (int) $categoria['id'] ? 'selected' : '' ?>>
                        <?= e($categoria['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="ordem">Ordenar por</label>
            <select id="ordem" name="ordem" class="entrada">
                <?php foreach (Produto::ORDENACOES as $valor => $opcao): ?>
                    <option value="<?= $valor ?>" <?= $filtros['ordem'] === $valor ? 'selected' : '' ?>><?= e($opcao['rotulo']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="botao botao-secundario">Filtrar</button>

        <nav class="alternador" aria-label="Forma de exibição">
            <a href="<?= url('produtos', 'index', array_merge($parametros, ['visao' => ''])) ?>" aria-current="<?= $visao === 'tabela' ? 'true' : 'false' ?>">
                <?= icone('lista', 16) ?> Tabela
            </a>
            <a href="<?= url('produtos', 'index', array_merge($parametros, ['visao' => 'grade'])) ?>" aria-current="<?= $visao === 'grade' ? 'true' : 'false' ?>">
                <?= icone('grade', 16) ?> Grade
            </a>
        </nav>
    </form>

    <div class="chips">
        <?php foreach ($chips as $valor => $rotulo): ?>
            <a href="<?= url('produtos', 'index', array_merge($parametros, ['situacao' => $valor])) ?>"
               class="chip <?= $filtros['situacao'] === $valor ? 'ativo' : '' ?>"
               aria-current="<?= $filtros['situacao'] === $valor ? 'true' : 'false' ?>">
                <?= e($rotulo) ?>
                <span class="chip-qtd"><?= $quantidades[$valor] ?></span>
            </a>
        <?php endforeach; ?>

        <?php if ($filtrosAtivos): ?>
            <a href="<?= url('produtos', 'index', ['visao' => $parametros['visao']]) ?>" class="limpar-filtros">
                <?= icone('fechar', 16) ?> Limpar filtros
            </a>
        <?php endif; ?>
    </div>
</section>

<?php if (!$produtos): ?>
    <section class="vazio">
        <span class="vazio-icone"><?= icone('busca', 26) ?></span>
        <strong>Nenhum produto encontrado</strong>
        <p><?= $filtrosAtivos ? 'Tente outro termo de busca ou limpe os filtros.' : 'Cadastre o primeiro produto da loja.' ?></p>
        <?php if ($filtrosAtivos): ?>
            <a href="<?= url('produtos') ?>" class="botao botao-secundario">Limpar filtros</a>
        <?php else: ?>
            <a href="<?= url('produtos', 'create') ?>" class="botao botao-primario"><?= icone('mais', 18) ?> Novo produto</a>
        <?php endif; ?>
    </section>

<?php elseif ($visao === 'grade'): ?>
    <div class="grade-produtos">
        <?php foreach ($produtos as $produto): ?>
            <article class="cartao-produto">
                <?= miniaturaProduto($produto, 'g') ?>
                <span class="rotulo-categoria"><?= e($produto['categoria_nome']) ?></span>
                <a href="<?= url('produtos', 'show', ['id' => $produto['id']]) ?>" class="item-nome"><?= e($produto['nome']) ?></a>
                <span class="item-detalhe"><?= e($produto['marca']) ?></span>
                <span class="linha-preco">
                    <span class="preco-destaque"><?= formatarPreco($produto['preco']) ?></span>
                    <?= seloEstoque((int) $produto['estoque']) ?>
                </span>
                <span class="cartao-produto-acoes">
                    <a href="<?= url('produtos', 'edit', ['id' => $produto['id']]) ?>" class="botao botao-secundario botao-pequeno">
                        <?= icone('lapis', 16) ?> Editar
                    </a>
                    <?php include __DIR__ . '/_excluir.php'; ?>
                </span>
            </article>
        <?php endforeach; ?>
    </div>

<?php else: ?>
    <div class="cartao tabela-container">
        <table class="tabela">
            <thead>
                <tr>
                    <th scope="col">Produto</th>
                    <th scope="col">Categoria</th>
                    <th scope="col" class="direita">Preço</th>
                    <th scope="col" class="centro">Estoque</th>
                    <th scope="col" class="direita">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td>
                            <div class="celula-produto">
                                <?= miniaturaProduto($produto, 'p') ?>
                                <span class="item-texto">
                                    <a href="<?= url('produtos', 'show', ['id' => $produto['id']]) ?>" class="item-nome"><?= e($produto['nome']) ?></a>
                                    <span class="item-detalhe"><?= e($produto['marca']) ?></span>
                                </span>
                            </div>
                        </td>
                        <td><?= e($produto['categoria_nome']) ?></td>
                        <td class="direita preco"><?= formatarPreco($produto['preco']) ?></td>
                        <td class="centro"><?= seloEstoque((int) $produto['estoque']) ?></td>
                        <td class="direita">
                            <div class="acoes">
                                <a href="<?= url('produtos', 'show', ['id' => $produto['id']]) ?>" class="botao botao-icone"
                                   aria-label="<?= e('Ver ' . $produto['nome']) ?>" title="Ver detalhes"><?= icone('olho', 18) ?></a>
                                <a href="<?= url('produtos', 'edit', ['id' => $produto['id']]) ?>" class="botao botao-icone"
                                   aria-label="<?= e('Editar ' . $produto['nome']) ?>" title="Editar"><?= icone('lapis', 18) ?></a>
                                <?php include __DIR__ . '/_excluir.php'; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php if ($total > 0): ?>
    <nav class="paginacao" aria-label="Paginação">
        <span class="paginacao-info">Mostrando <?= $primeiro ?>–<?= $ultimo ?> de <?= $total ?> produto(s)</span>

        <?php if ($totalPaginas > 1): ?>
            <span class="paginacao-botoes">
                <a href="<?= url('produtos', 'index', $parametros + ['pagina' => $paginaAtual - 1]) ?>"
                   class="pagina <?= $paginaAtual <= 1 ? 'desabilitada' : '' ?>" aria-label="Página anterior"><?= icone('anterior', 18) ?></a>

                <?php for ($numero = 1; $numero <= $totalPaginas; $numero++): ?>
                    <a href="<?= url('produtos', 'index', $parametros + ['pagina' => $numero]) ?>"
                       class="pagina <?= $numero === $paginaAtual ? 'atual' : '' ?>"
                       <?= $numero === $paginaAtual ? 'aria-current="page"' : '' ?>><?= $numero ?></a>
                <?php endfor; ?>

                <a href="<?= url('produtos', 'index', $parametros + ['pagina' => $paginaAtual + 1]) ?>"
                   class="pagina <?= $paginaAtual >= $totalPaginas ? 'desabilitada' : '' ?>" aria-label="Próxima página"><?= icone('proxima', 18) ?></a>
            </span>
        <?php endif; ?>
    </nav>
<?php endif; ?>

<!-- Botão "+" que só aparece no celular -->
<a href="<?= url('produtos', 'create') ?>" class="botao-flutuante" aria-label="Novo produto"><?= icone('mais', 24) ?></a>
