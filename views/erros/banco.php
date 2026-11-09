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
Descritivo: View exibida quando não é possível conectar ao MySQL, com a lista
            do que conferir para o sistema voltar a funcionar.
*******************************************************************************/
?>
<section class="pagina-erro">
    <strong class="pagina-erro-codigo">MySQL</strong>
    <h1>Não foi possível conectar ao banco de dados</h1>
    <ol class="lista-verificacao">
        <li>Abra o XAMPP Control Panel e confira se o <strong>MySQL</strong> está ligado (Start).</li>
        <li>Importe o arquivo <code>database.sql</code> no phpMyAdmin para criar o banco <code><?= e(DB_NOME) ?></code>.</li>
        <li>Confira o usuário e a senha do MySQL em <code>config/config.php</code>.</li>
    </ol>
    <p>Detalhe técnico: <?= e($detalhe) ?></p>
</section>
