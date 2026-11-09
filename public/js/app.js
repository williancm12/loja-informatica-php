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
Descritivo: Comportamentos da interface: modo escuro, aviso flutuante, janela
            de confirmação de exclusão e os recursos do formulário de produto
            (prévia ao vivo, botões + e −, contador e arrastar a foto).
            O sistema funciona sem JavaScript; aqui só ficam as melhorias.
*******************************************************************************/

(function () {
    'use strict';

    /* ------------------------------------------------------------------------
     * Modo escuro
     * --------------------------------------------------------------------- */
    var raiz = document.documentElement;
    var botoesTema = document.querySelectorAll('.js-alternar-tema');

    function aplicarTema(tema) {
        if (tema === 'escuro') {
            raiz.dataset.tema = 'escuro';
        } else {
            delete raiz.dataset.tema;
        }

        botoesTema.forEach(function (botao) {
            botao.setAttribute('aria-pressed', tema === 'escuro' ? 'true' : 'false');
            var rotulo = botao.querySelector('[data-rotulo-tema]');
            if (rotulo) {
                rotulo.textContent = tema === 'escuro' ? 'Modo claro' : 'Modo escuro';
            }
        });
    }

    aplicarTema(raiz.dataset.tema === 'escuro' ? 'escuro' : 'claro');

    botoesTema.forEach(function (botao) {
        botao.addEventListener('click', function () {
            var novoTema = raiz.dataset.tema === 'escuro' ? 'claro' : 'escuro';
            aplicarTema(novoTema);
            try {
                localStorage.setItem('tema', novoTema); // lembra a escolha na próxima visita
            } catch (erro) {}
        });
    });

    /* ------------------------------------------------------------------------
     * Aviso flutuante (toast): fecha no "x" e, se for sucesso, some em 6 segundos
     * --------------------------------------------------------------------- */
    var toast = document.querySelector('[data-toast]');

    if (toast) {
        var fecharToast = function () {
            toast.classList.add('saindo');
            setTimeout(function () {
                toast.remove();
            }, 200);
        };

        toast.querySelector('.toast-fechar').addEventListener('click', fecharToast);

        if (toast.dataset.toast === 'sucesso') {
            setTimeout(fecharToast, 6000);
        }
    }

    /* ------------------------------------------------------------------------
     * Confirmação antes de excluir
     * Os formulários de exclusão têm atributos data-confirmar-* com os textos.
     * Se o navegador não tiver <dialog>, usa o confirm() padrão.
     * --------------------------------------------------------------------- */
    var dialogo = document.getElementById('dialogo-exclusao');
    var formularioPendente = null;

    function preencher(id, texto) {
        document.getElementById(id).textContent = texto || '';
    }

    document.querySelectorAll('form[data-confirmar]').forEach(function (formulario) {
        formulario.addEventListener('submit', function (evento) {
            if (!dialogo || typeof dialogo.showModal !== 'function') {
                if (!confirm(formulario.dataset.confirmar)) {
                    evento.preventDefault();
                }
                return;
            }

            evento.preventDefault();
            formularioPendente = formulario;

            preencher('dialogo-titulo', formulario.dataset.confirmarTitulo || 'Confirmar exclusão');
            preencher('dialogo-texto', formulario.dataset.confirmar);
            preencher('dialogo-item', formulario.dataset.confirmarItem);
            preencher('dialogo-detalhe', formulario.dataset.confirmarDetalhe);
            preencher('dialogo-confirmar', formulario.dataset.confirmarBotao || 'Excluir');

            dialogo.showModal();
        });
    });

    if (dialogo) {
        dialogo.querySelector('[data-fechar-dialogo]').addEventListener('click', function () {
            dialogo.close();
        });

        // submit() envia o formulário direto, sem passar de novo pelo evento acima.
        document.getElementById('dialogo-confirmar').addEventListener('click', function () {
            if (formularioPendente) {
                formularioPendente.submit();
            }
        });

        // Clicar no fundo escuro, fora da janela, também cancela.
        dialogo.addEventListener('click', function (evento) {
            if (evento.target === dialogo) {
                dialogo.close();
            }
        });
    }

    /* ------------------------------------------------------------------------
     * Formulário de produto
     * --------------------------------------------------------------------- */
    var formProduto = document.querySelector('[data-formulario-produto]');

    if (!formProduto) {
        return;
    }

    var campoNome       = document.getElementById('nome');
    var campoMarca      = document.getElementById('marca');
    var campoCategoria  = document.getElementById('categoria_id');
    var campoPreco      = document.getElementById('preco');
    var campoEstoque    = document.getElementById('estoque');
    var campoDescricao  = document.getElementById('descricao');
    var campoImagem     = document.getElementById('imagem');
    var campoRemover    = document.getElementById('remover_imagem');
    var areaUpload      = document.querySelector('[data-area-upload]');
    var nomeArquivo     = document.querySelector('[data-arquivo-escolhido]');
    var contador        = document.querySelector('[data-contador]');
    var limiteBaixo     = Number(formProduto.dataset.limiteBaixo);
    var pastaIcones     = formProduto.dataset.pastaIcones;

    // Elementos do cartão de prévia (coluna da direita).
    function previa(nome) {
        return document.querySelector('[data-previa="' + nome + '"]');
    }

    var miniaturaPrevia = previa('miniatura');
    var imagemPrevia    = miniaturaPrevia.querySelector('img');
    var fotoAtual       = miniaturaPrevia.dataset.fotoAtual || '';
    var fotoNova        = '';

    // Mesma regra do PHP (converterPreco): aceita 1299.90, 1299,90 ou 1.299,90.
    function converterPreco(texto) {
        var valor = String(texto).replace('R$', '').replace(/\s/g, '');
        if (valor.indexOf(',') !== -1) {
            valor = valor.replace(/\./g, '').replace(',', '.');
        }
        var numero = Number(valor);
        return valor !== '' && isFinite(numero) ? numero : null;
    }

    function formatarReais(valor) {
        return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function iconeDaCategoria() {
        var opcao = campoCategoria.options[campoCategoria.selectedIndex];
        return (opcao && opcao.dataset.icone) || 'caixa';
    }

    // Mostra na prévia a foto nova, a foto atual ou o ícone da categoria.
    function atualizarImagem() {
        var icone = iconeDaCategoria();
        var removerMarcado = campoRemover && campoRemover.checked;
        var foto = fotoNova || (removerMarcado ? '' : fotoAtual);

        miniaturaPrevia.className = 'miniatura miniatura-g tom-' + icone;
        imagemPrevia.src = foto || pastaIcones + icone + '.svg';
        imagemPrevia.className = foto ? 'miniatura-foto' : 'miniatura-icone';
    }

    function atualizarPrevia() {
        var opcao = campoCategoria.options[campoCategoria.selectedIndex];
        var preco = converterPreco(campoPreco.value);
        var quantidade = Math.max(0, parseInt(campoEstoque.value, 10) || 0);
        var selo = previa('selo').querySelector('.selo');

        previa('nome').textContent = campoNome.value.trim() || 'Nome do produto';
        previa('marca').textContent = campoMarca.value.trim() || 'Marca';
        previa('categoria').textContent = opcao && opcao.value ? opcao.text.trim() : 'Categoria';
        previa('preco').textContent = preco !== null && preco > 0 ? formatarReais(preco) : 'R$ —';

        if (quantidade === 0) {
            selo.className = 'selo selo-esgotado';
            selo.textContent = 'Esgotado';
        } else if (quantidade <= limiteBaixo) {
            selo.className = 'selo selo-baixo';
            selo.textContent = quantidade + ' un. · baixo';
        } else {
            selo.className = 'selo selo-ok';
            selo.textContent = quantidade + ' un.';
        }

        atualizarImagem();
    }

    [campoNome, campoMarca, campoPreco, campoEstoque].forEach(function (campo) {
        campo.addEventListener('input', atualizarPrevia);
    });
    campoCategoria.addEventListener('change', atualizarPrevia);
    if (campoRemover) {
        campoRemover.addEventListener('change', atualizarImagem);
    }

    // Botões − e + do estoque.
    formProduto.querySelectorAll('[data-passo]').forEach(function (botao) {
        botao.addEventListener('click', function () {
            var atual = parseInt(campoEstoque.value, 10) || 0;
            campoEstoque.value = Math.max(0, atual + Number(botao.dataset.passo));
            atualizarPrevia();
        });
    });

    // Contador de caracteres da descrição.
    function atualizarContador() {
        contador.textContent = campoDescricao.value.length + ' / ' + campoDescricao.maxLength;
    }
    campoDescricao.addEventListener('input', atualizarContador);
    atualizarContador();

    // Foto escolhida: mostra o nome do arquivo e a imagem na prévia.
    campoImagem.addEventListener('change', function () {
        var arquivo = campoImagem.files[0];
        if (fotoNova) {
            URL.revokeObjectURL(fotoNova);
        }
        fotoNova = arquivo && arquivo.type.indexOf('image/') === 0 ? URL.createObjectURL(arquivo) : '';
        nomeArquivo.textContent = arquivo ? 'Arquivo escolhido: ' + arquivo.name : '';
        atualizarImagem();
    });

    // Arrastar e soltar a foto na área tracejada.
    ['dragenter', 'dragover'].forEach(function (tipo) {
        areaUpload.addEventListener(tipo, function (evento) {
            evento.preventDefault();
            areaUpload.classList.add('arrastando');
        });
    });
    ['dragleave', 'drop'].forEach(function (tipo) {
        areaUpload.addEventListener(tipo, function () {
            areaUpload.classList.remove('arrastando');
        });
    });
    areaUpload.addEventListener('drop', function (evento) {
        evento.preventDefault();
        if (evento.dataTransfer.files.length) {
            campoImagem.files = evento.dataTransfer.files;
            campoImagem.dispatchEvent(new Event('change'));
        }
    });

    atualizarPrevia();
})();
