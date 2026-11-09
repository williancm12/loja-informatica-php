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
Descritivo: View do formulário de categoria, usada no cadastro e na edição.
            Mostra os erros de validação ao lado de cada campo.
*******************************************************************************/
?>
<section class="cabecalho-pagina">
    <div>
        <nav class="trilha" aria-label="Você está em">
            <a href="<?= url('home') ?>">Painel</a> /
            <a href="<?= url('categorias') ?>">Categorias</a> /
            <span><?= e($titulo) ?></span>
        </nav>
        <h1><?= e($titulo) ?></h1>
        <p class="subtitulo">Campos com * são obrigatórios.</p>
    </div>
</section>

<?php if ($erros): ?>
    <div class="alerta-caixa alerta-erro" role="alert">
        <?= icone('erro', 20) ?>
        <?= count($erros) === 1 ? 'Falta corrigir 1 campo antes de salvar.' : 'Falta corrigir ' . count($erros) . ' campos antes de salvar.' ?>
    </div>
<?php endif; ?>

<div class="colunas">
    <form method="post" action="<?= e($acao) ?>" class="cartao secao coluna-larga" novalidate>
        <?= campoCsrf() ?>
        <?php include __DIR__ . '/_campos.php'; ?>

        <div class="formulario-acoes">
            <button type="submit" class="botao botao-primario"><?= icone('check', 18) ?> Salvar categoria</button>
            <a href="<?= url('categorias') ?>" class="botao botao-texto">Cancelar</a>
        </div>
    </form>

    <aside class="coluna-estreita">
        <div class="nota" role="note">
            <?= icone('info', 20) ?>
            <span>O ícone escolhido aparece no lugar da foto dos produtos desta categoria que ainda não têm foto.</span>
        </div>
    </aside>
</div>
