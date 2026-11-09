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
Descritivo: Rodapé comum a todas as páginas: créditos, menu inferior do
            celular, aviso flutuante, janela de confirmação de exclusão e o
            JavaScript do sistema.
*******************************************************************************/
?>
                <footer class="rodape">
                    &copy; <?= date('Y') ?> <?= e(NOME_LOJA) ?> · Trabalho Prático 2 · Linguagem e Técnicas de Programação
                </footer>
            </main>
        </div>
    </div>

    <!-- Menu inferior: só aparece no celular, no lugar do menu lateral -->
    <nav class="nav-inferior" aria-label="Menu principal no celular">
        <?php foreach ($menu as $paginaMenu => $item): ?>
            <a href="<?= url($paginaMenu) ?>"
               class="<?= $paginaAtiva === $paginaMenu ? 'ativo' : '' ?>"
               <?= $paginaAtiva === $paginaMenu ? 'aria-current="page"' : '' ?>>
                <?= icone($item['icone'], 22) ?>
                <?= e($item['rotulo']) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <?php if ($mensagem): ?>
        <!-- Aviso flutuante com o resultado da última ação (some sozinho quando é sucesso) -->
        <div class="toast toast-<?= e($mensagem['tipo']) ?>"
             role="<?= $mensagem['tipo'] === 'erro' ? 'alert' : 'status' ?>"
             data-toast="<?= e($mensagem['tipo']) ?>">
            <span class="toast-icone"><?= icone($mensagem['tipo'] === 'erro' ? 'erro' : 'check', 16) ?></span>
            <span class="toast-texto"><?= e($mensagem['texto']) ?></span>
            <button type="button" class="toast-fechar" aria-label="Fechar aviso"><?= icone('fechar', 18) ?></button>
        </div>
    <?php endif; ?>

    <!-- Janela de confirmação usada por todos os botões "Excluir" (o texto é preenchido pelo js/app.js) -->
    <dialog class="dialogo" id="dialogo-exclusao" aria-labelledby="dialogo-titulo" aria-describedby="dialogo-texto">
        <span class="dialogo-icone"><?= icone('lixeira', 24) ?></span>
        <h2 id="dialogo-titulo">Confirmar exclusão</h2>
        <p id="dialogo-texto"></p>
        <div class="dialogo-item">
            <strong id="dialogo-item"></strong>
            <span id="dialogo-detalhe"></span>
        </div>
        <div class="dialogo-acoes">
            <button type="button" class="botao botao-secundario" data-fechar-dialogo>Cancelar</button>
            <button type="button" class="botao botao-perigo" id="dialogo-confirmar">Excluir</button>
        </div>
    </dialog>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
