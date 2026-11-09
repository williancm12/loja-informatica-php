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
Descritivo: Funções auxiliares usadas em todo o sistema: links, escape de HTML,
            mensagens de aviso, proteção de formulários e formatação de valores.
*******************************************************************************/

/* ----------------------------------------------------------------------------
 * Links e páginas
 * ------------------------------------------------------------------------- */

// Monta o link para uma página e ação do sistema, por exemplo:
// url('produtos', 'edit', ['id' => 3]) => /loja_informatica/public/index.php?page=produtos&action=edit&id=3
function url(string $pagina = 'home', string $acao = 'index', array $parametros = []): string
{
    $query = ['page' => $pagina];
    if ($acao !== 'index') {
        $query['action'] = $acao;
    }

    // Parâmetros vazios (como uma busca em branco) não entram no link.
    $parametros = array_filter($parametros, function ($valor) {
        return $valor !== '' && $valor !== null;
    });

    return BASE_URL . '/index.php?' . http_build_query(array_merge($query, $parametros));
}

// Caminho de um arquivo dentro da pasta public (CSS, JS, imagens).
function asset(string $caminho): string
{
    return BASE_URL . '/' . ltrim($caminho, '/');
}

// Redireciona o navegador para outro endereço e encerra o script.
function redirecionar(string $destino): void
{
    header('Location: ' . $destino);
    exit;
}

// Exibe uma view entre o cabeçalho e o rodapé padrão.
// As chaves do array $dados viram variáveis dentro da view.
function renderizar(string $view, array $dados = []): void
{
    extract($dados);
    require __DIR__ . '/../views/includes/header.php';
    require __DIR__ . '/../views/' . $view . '.php';
    require __DIR__ . '/../views/includes/footer.php';
}

// Mostra a página de erro 404 e encerra o script.
function paginaNaoEncontrada(string $mensagem = 'A página que você procurou não existe.'): void
{
    http_response_code(404);
    renderizar('erros/404', ['titulo' => 'Página não encontrada', 'mensagem' => $mensagem]);
    exit;
}

/* ----------------------------------------------------------------------------
 * Segurança
 * ------------------------------------------------------------------------- */

// Escapa um texto antes de exibir no HTML, evitando ataques XSS.
function e($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

// Token secreto da sessão que todo formulário POST precisa enviar (proteção CSRF).
function tokenCsrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

// Campo oculto com o token, para colocar dentro dos formulários.
function campoCsrf(): string
{
    return '<input type="hidden" name="csrf" value="' . tokenCsrf() . '">';
}

// Confere se o token enviado pelo formulário é o mesmo da sessão.
function csrfValido(): bool
{
    return isset($_POST['csrf']) && hash_equals(tokenCsrf(), (string) $_POST['csrf']);
}

/* ----------------------------------------------------------------------------
 * Mensagens de aviso (aparecem uma única vez, depois de um redirecionamento)
 * ------------------------------------------------------------------------- */

// $tipo pode ser 'sucesso' ou 'erro'.
function definirMensagem(string $tipo, string $texto): void
{
    $_SESSION['mensagem'] = ['tipo' => $tipo, 'texto' => $texto];
}

// Lê a mensagem guardada e já a remove da sessão.
function obterMensagem(): ?array
{
    $mensagem = $_SESSION['mensagem'] ?? null;
    unset($_SESSION['mensagem']);
    return $mensagem;
}

// HTML com o erro de validação de um campo do formulário (ou vazio se não houver).
function erroCampo(array $erros, string $campo): string
{
    if (empty($erros[$campo])) {
        return '';
    }
    return '<span class="erro-campo" id="erro-' . $campo . '">' . icone('erro', 15) . e($erros[$campo]) . '</span>';
}

// Atributos que ligam o campo à mensagem de erro (leitores de tela leem os dois).
function atributosErro(array $erros, string $campo): string
{
    return isset($erros[$campo]) ? 'aria-invalid="true" aria-describedby="erro-' . $campo . '"' : '';
}

/* ----------------------------------------------------------------------------
 * Formatação de valores
 * ------------------------------------------------------------------------- */

// 1299.9 => "R$ 1.299,90"
function formatarPreco($valor): string
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

// Aceita "1299.90", "1299,90" ou "1.299,90" e devolve 1299.9 (ou null se for inválido).
function converterPreco(string $valor): ?float
{
    $valor = trim(str_replace(['R$', ' '], '', $valor));

    if (strpos($valor, ',') !== false) {
        $valor = str_replace('.', '', $valor);  // remove o separador de milhar
        $valor = str_replace(',', '.', $valor); // vírgula decimal vira ponto
    }

    return is_numeric($valor) ? (float) $valor : null;
}

// "2026-10-21 14:30:00" => "21/10/2026 às 14:30"
function formatarData(string $data): string
{
    $momento = strtotime($data);
    return date('d/m/Y', $momento) . ' às ' . date('H:i', $momento);
}

// Selo colorido com a situação do estoque do produto.
function seloEstoque(int $quantidade): string
{
    if ($quantidade === 0) {
        return '<span class="selo selo-esgotado">Esgotado</span>';
    }
    if ($quantidade <= LIMITE_ESTOQUE_BAIXO) {
        return '<span class="selo selo-baixo">' . $quantidade . ' un. · baixo</span>';
    }
    return '<span class="selo selo-ok">' . $quantidade . ' un.</span>';
}

/* ----------------------------------------------------------------------------
 * Imagens e ícones da interface
 * ------------------------------------------------------------------------- */

// Endereço da foto enviada do produto, ou null se ele não tiver foto.
function urlFotoProduto(?string $arquivo): ?string
{
    if ($arquivo && is_file(PASTA_UPLOADS . $arquivo)) {
        return asset('uploads/' . $arquivo);
    }
    return null;
}

// Endereço do ícone de uma categoria (public/img/categorias/<ícone>.svg).
function urlIconeCategoria(?string $icone): string
{
    $icone = isset(ICONES_CATEGORIA[$icone]) ? $icone : 'caixa';
    return asset('img/categorias/' . $icone . '.svg');
}

// Quadro com a foto do produto. Sem foto, mostra o ícone da categoria num fundo
// colorido. $tamanho é a classe CSS do tamanho: p, m, g ou xg.
function miniaturaProduto(array $produto, string $tamanho = 'p'): string
{
    $icone = isset(ICONES_CATEGORIA[$produto['categoria_icone'] ?? '']) ? $produto['categoria_icone'] : 'caixa';
    $foto  = urlFotoProduto($produto['imagem'] ?? null);

    $imagem = $foto
        ? '<img src="' . e($foto) . '" alt="" class="miniatura-foto">'
        : '<img src="' . e(urlIconeCategoria($icone)) . '" alt="" class="miniatura-icone">';

    return '<span class="miniatura miniatura-' . $tamanho . ' tom-' . $icone . '">' . $imagem . '</span>';
}

// Ícone de linha (SVG) usado em botões e menus. A cor vem do texto ao redor.
function icone(string $nome, int $tamanho = 20): string
{
    $desenhos = [
        'painel'     => '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
        'produtos'   => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.3 7 12 12l8.7-5"/><path d="M12 22V12"/>',
        'categorias' => '<path d="M12.6 2.6A2 2 0 0 0 11.2 2H4a2 2 0 0 0-2 2v7.2a2 2 0 0 0 .6 1.4l8.7 8.7a2.4 2.4 0 0 0 3.4 0l6.6-6.6a2.4 2.4 0 0 0 0-3.4z"/><path d="M7.5 7.5h.01"/>',
        'chip'       => '<rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6" rx="1"/><path d="M15 2v2M15 20v2M2 15h2M2 9h2M20 15h2M20 9h2M9 2v2M9 20v2"/>',
        'camadas'    => '<path d="M12.8 2.2a2 2 0 0 0-1.6 0L2.6 6.1a1 1 0 0 0 0 1.8l8.6 3.9a2 2 0 0 0 1.6 0l8.6-3.9a1 1 0 0 0 0-1.8z"/><path d="m22 17.6-9.2 4.2a2 2 0 0 1-1.6 0L2 17.6"/><path d="m22 12.6-9.2 4.2a2 2 0 0 1-1.6 0L2 12.6"/>',
        'dinheiro'   => '<path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'mais'       => '<path d="M5 12h14M12 5v14"/>',
        'menos'      => '<path d="M5 12h14"/>',
        'busca'      => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'olho'       => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'lapis'      => '<path d="M21.2 6.8a1 1 0 0 0-4-4L3.8 16.2a2 2 0 0 0-.5.8l-1.3 4.4a.5.5 0 0 0 .6.6l4.4-1.3a2 2 0 0 0 .8-.5z"/>',
        'lixeira'    => '<path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>',
        'alerta'     => '<path d="m21.7 18-8-14a2 2 0 0 0-3.5 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3"/><path d="M12 9v4M12 17h.01"/>',
        'erro'       => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>',
        'info'       => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
        'check'      => '<path d="m5 12 5 5L20 7"/>',
        'fechar'     => '<path d="M18 6 6 18M6 6l12 12"/>',
        'voltar'     => '<path d="m12 19-7-7 7-7M19 12H5"/>',
        'anterior'   => '<path d="m15 18-6-6 6-6"/>',
        'proxima'    => '<path d="m9 18 6-6-6-6"/>',
        'lista'      => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
        'grade'      => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
        'upload'     => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/>',
        'lua'        => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
    ];

    return '<svg class="icone" viewBox="0 0 24 24" width="' . $tamanho . '" height="' . $tamanho
        . '" aria-hidden="true" focusable="false">' . ($desenhos[$nome] ?? '') . '</svg>';
}
