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
Descritivo: Pedaço de view com os campos da categoria (nome, descrição e
            ícone). É usado no formulário completo e no cadastro rápido da
            listagem de categorias. Espera as variáveis $dados e $erros.
*******************************************************************************/
?>
<div class="campo <?= isset($erros['nome']) ? 'com-erro' : '' ?>">
    <label for="nome">Nome da categoria *</label>
    <input type="text" id="nome" name="nome" class="entrada" maxlength="100" required
           value="<?= e($dados['nome']) ?>" placeholder="Ex.: Cabos e adaptadores" <?= atributosErro($erros, 'nome') ?>>
    <?= erroCampo($erros, 'nome') ?>
</div>

<div class="campo <?= isset($erros['descricao']) ? 'com-erro' : '' ?>">
    <label for="descricao">Descrição</label>
    <textarea id="descricao" name="descricao" class="entrada" rows="3" maxlength="255"
              placeholder="Que tipo de produto entra nesta categoria" <?= atributosErro($erros, 'descricao') ?>><?= e($dados['descricao']) ?></textarea>
    <?= erroCampo($erros, 'descricao') ?>
</div>

<fieldset class="seletor-icones">
    <legend>Ícone (aparece nos produtos sem foto)</legend>
    <?php foreach (ICONES_CATEGORIA as $valor => $rotulo): ?>
        <label class="opcao-icone">
            <input type="radio" name="icone" value="<?= $valor ?>" <?= $dados['icone'] === $valor ? 'checked' : '' ?>>
            <span class="opcao-icone-caixa">
                <span class="miniatura miniatura-p tom-<?= $valor ?>">
                    <img src="<?= e(urlIconeCategoria($valor)) ?>" alt="" class="miniatura-icone">
                </span>
                <?= e($rotulo) ?>
            </span>
        </label>
    <?php endforeach; ?>
</fieldset>
<?= erroCampo($erros, 'icone') ?>
