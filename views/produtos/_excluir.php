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
Descritivo: Pedaço de view com o botão de excluir um produto ($produto), usado
            na tabela e na grade. Os atributos data-confirmar-* são os textos
            da janela de confirmação aberta pelo js/app.js.
*******************************************************************************/
?>
<form method="post" action="<?= url('produtos', 'delete', ['id' => $produto['id']]) ?>"
      data-confirmar="O produto sai do estoque e a foto dele é apagada. Essa ação não pode ser desfeita."
      data-confirmar-titulo="Excluir este produto?"
      data-confirmar-item="<?= e($produto['nome']) ?>"
      data-confirmar-detalhe="<?= e($produto['marca'] . ' · ' . $produto['categoria_nome'] . ' · ' . formatarPreco($produto['preco'])) ?>"
      data-confirmar-botao="Excluir produto">
    <?= campoCsrf() ?>
    <button type="submit" class="botao botao-icone perigo" aria-label="<?= e('Excluir ' . $produto['nome']) ?>" title="Excluir">
        <?= icone('lixeira', 18) ?>
    </button>
</form>
