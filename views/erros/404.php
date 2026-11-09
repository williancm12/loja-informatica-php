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
Descritivo: View de erro 404, exibida quando a página, o produto ou a
            categoria procurada não existe.
*******************************************************************************/
?>
<section class="pagina-erro">
    <strong class="pagina-erro-codigo">404</strong>
    <h1>Página não encontrada</h1>
    <p><?= e($mensagem) ?></p>
    <div class="cabecalho-acoes">
        <a href="<?= url('produtos') ?>" class="botao botao-secundario">Ver produtos</a>
        <a href="<?= url('home') ?>" class="botao botao-primario">Voltar para o painel</a>
    </div>
</section>
