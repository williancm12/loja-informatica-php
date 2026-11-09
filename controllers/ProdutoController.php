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
Descritivo: Controller de Produtos. Trata a listagem com busca e paginação,
            os detalhes, o cadastro, a edição (com upload da foto) e a exclusão.
*******************************************************************************/

class ProdutoController
{
    private $produto;
    private $categoria;

    public function __construct(PDO $conn)
    {
        $this->produto   = new Produto($conn);
        $this->categoria = new Categoria($conn);
    }

    // Lista os produtos aplicando busca, filtros, ordenação e paginação.
    public function index(): void
    {
        $filtros = [
            'busca'     => trim($_GET['busca'] ?? ''),
            'categoria' => (int) ($_GET['categoria'] ?? 0),
            'situacao'  => $_GET['situacao'] ?? '',
            'ordem'     => $_GET['ordem'] ?? 'nome',
        ];
        if (!isset(Produto::ORDENACOES[$filtros['ordem']])) {
            $filtros['ordem'] = 'nome';
        }
        if (!isset(Produto::SITUACOES[$filtros['situacao']])) {
            $filtros['situacao'] = '';
        }

        // Forma de exibição: tabela (padrão) ou grade de cartões.
        $visao = ($_GET['visao'] ?? '') === 'grade' ? 'grade' : 'tabela';

        // Paginação: descobre quantas páginas existem e qual está sendo vista.
        $total        = $this->produto->contar($filtros);
        $totalPaginas = max(1, (int) ceil($total / ITENS_POR_PAGINA));
        $paginaAtual  = min(max(1, (int) ($_GET['pagina'] ?? 1)), $totalPaginas);
        $deslocamento = ($paginaAtual - 1) * ITENS_POR_PAGINA;

        renderizar('produtos/index', [
            'titulo'       => 'Produtos',
            'produtos'     => $this->produto->listar($filtros, ITENS_POR_PAGINA, $deslocamento),
            'categorias'   => $this->categoria->listar(),
            'contagem'     => $this->produto->contarPorSituacao($filtros),
            'filtros'      => $filtros,
            'visao'        => $visao,
            'total'        => $total,
            'paginaAtual'  => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'deslocamento' => $deslocamento,
        ]);
    }

    // Página de detalhes de um produto, com outros produtos da mesma categoria.
    public function show(int $id): void
    {
        $produto = $this->buscarOuFalhar($id);

        renderizar('produtos/show', [
            'titulo'       => $produto['nome'],
            'produto'      => $produto,
            'relacionados' => $this->produto->relacionados((int) $produto['categoria_id'], $id, 3),
        ]);
    }

    // Exibe o formulário vazio e, no envio (POST), cadastra o produto.
    public function create(): void
    {
        $categorias = $this->categoria->listar();

        // Sem categorias não há como cadastrar produto: avisa e leva para o cadastro delas.
        if (!$categorias) {
            definirMensagem('erro', 'Cadastre pelo menos uma categoria antes de cadastrar produtos.');
            redirecionar(url('categorias', 'create'));
        }

        $dados = [
            'nome' => '', 'marca' => '', 'categoria_id' => (int) ($_GET['categoria'] ?? 0),
            'preco' => '', 'estoque' => '0', 'descricao' => '', 'imagem' => null,
        ];
        $erros = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = $this->lerFormulario();
            $erros = $this->validar($dados);

            if (!$erros) {
                $dados['imagem'] = $this->salvarImagem();
                $id = $this->produto->inserir($dados);

                definirMensagem('sucesso', 'Produto "' . $dados['nome'] . '" cadastrado com sucesso.');

                // Botão "Salvar e cadastrar outro": volta para um formulário vazio na mesma categoria.
                if (($_POST['depois'] ?? '') === 'novo') {
                    redirecionar(url('produtos', 'create', ['categoria' => $dados['categoria_id']]));
                }
                redirecionar(url('produtos', 'show', ['id' => $id]));
            }
        }

        renderizar('produtos/form', [
            'titulo'     => 'Novo produto',
            'editando'   => false,
            'dados'      => $dados,
            'erros'      => $erros,
            'categorias' => $categorias,
            'acao'       => url('produtos', 'create'),
            'cancelar'   => url('produtos'),
        ]);
    }

    // Exibe o formulário preenchido e, no envio (POST), salva as alterações.
    public function edit(int $id): void
    {
        $produto = $this->buscarOuFalhar($id);
        $dados   = $produto;
        $erros   = [];

        // O preço vem do banco como 1299.90 e é exibido no padrão brasileiro: 1.299,90.
        $dados['preco'] = number_format((float) $produto['preco'], 2, ',', '.');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = $this->lerFormulario();
            $dados['imagem'] = $produto['imagem'];
            $erros = $this->validar($dados);

            if (!$erros) {
                $novaImagem = $this->salvarImagem();

                // Troca a foto (nova enviada) ou remove a atual (caixa marcada).
                if ($novaImagem !== null || isset($_POST['remover_imagem'])) {
                    $this->apagarImagem($produto['imagem']);
                    $dados['imagem'] = $novaImagem;
                }

                $this->produto->atualizar($id, $dados);
                definirMensagem('sucesso', 'Produto "' . $dados['nome'] . '" atualizado com sucesso.');
                redirecionar(url('produtos', 'show', ['id' => $id]));
            }
        }

        renderizar('produtos/form', [
            'titulo'     => 'Editar produto',
            'editando'   => true,
            'dados'      => $dados,
            'erros'      => $erros,
            'categorias' => $this->categoria->listar(),
            'acao'       => url('produtos', 'edit', ['id' => $id]),
            'cancelar'   => url('produtos', 'show', ['id' => $id]),
        ]);
    }

    // Exclui o produto e a foto dele.
    public function delete(int $id): void
    {
        // A exclusão só acontece por formulário (POST), nunca por um simples link.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirecionar(url('produtos'));
        }

        $produto = $this->buscarOuFalhar($id);
        $this->produto->excluir($id);
        $this->apagarImagem($produto['imagem']);

        definirMensagem('sucesso', 'Produto "' . $produto['nome'] . '" excluído com sucesso.');
        redirecionar(url('produtos'));
    }

    // Lê os campos enviados pelo formulário, já sem espaços nas pontas.
    private function lerFormulario(): array
    {
        return [
            'nome'         => trim($_POST['nome'] ?? ''),
            'marca'        => trim($_POST['marca'] ?? ''),
            'categoria_id' => (int) ($_POST['categoria_id'] ?? 0),
            'preco'        => trim($_POST['preco'] ?? ''),
            'estoque'      => trim($_POST['estoque'] ?? ''),
            'descricao'    => trim($_POST['descricao'] ?? ''),
            'imagem'       => null,
        ];
    }

    // Regras de validação. Retorna um array campo => mensagem de erro.
    // Também converte preço e estoque para número quando estão corretos.
    private function validar(array &$dados): array
    {
        $erros = [];

        if ($dados['nome'] === '') {
            $erros['nome'] = 'Informe o nome do produto.';
        } elseif (mb_strlen($dados['nome']) > 150) {
            $erros['nome'] = 'O nome pode ter no máximo 150 caracteres.';
        }

        if ($dados['marca'] === '') {
            $erros['marca'] = 'Informe a marca do produto.';
        } elseif (mb_strlen($dados['marca']) > 80) {
            $erros['marca'] = 'A marca pode ter no máximo 80 caracteres.';
        }

        if (!$this->categoria->buscarPorId($dados['categoria_id'])) {
            $erros['categoria_id'] = 'Escolha uma categoria.';
        }

        $preco = converterPreco($dados['preco']);
        if ($preco === null || $preco <= 0) {
            $erros['preco'] = 'Informe um preço maior que zero, por exemplo 1299,90.';
        } elseif ($preco > 99999999.99) {
            $erros['preco'] = 'O preço informado é alto demais.';
        } else {
            $dados['preco'] = round($preco, 2);
        }

        $estoque = filter_var($dados['estoque'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($estoque === false) {
            $erros['estoque'] = 'O estoque deve ser um número inteiro igual ou maior que zero.';
        } else {
            $dados['estoque'] = $estoque;
        }

        if (mb_strlen($dados['descricao']) > 2000) {
            $erros['descricao'] = 'A descrição pode ter no máximo 2000 caracteres.';
        }

        $erroImagem = $this->validarImagem();
        if ($erroImagem !== null) {
            $erros['imagem'] = $erroImagem;
        }

        return $erros;
    }

    // Confere a foto enviada (se houver). Retorna a mensagem de erro ou null se estiver tudo certo.
    private function validarImagem(): ?string
    {
        $arquivo = $_FILES['imagem'] ?? null;

        if (!$arquivo || $arquivo['error'] === UPLOAD_ERR_NO_FILE) {
            return null; // a foto é opcional
        }
        if ($arquivo['error'] !== UPLOAD_ERR_OK || $arquivo['size'] > TAMANHO_MAX_IMAGEM) {
            return 'A imagem deve ter no máximo 2 MB.';
        }

        // getimagesize lê o conteúdo do arquivo, então um arquivo renomeado para .jpg não passa.
        $info = @getimagesize($arquivo['tmp_name']);
        if ($info === false || !isset(TIPOS_IMAGEM[$info['mime']])) {
            return 'Envie uma imagem nos formatos JPG, PNG, WEBP ou GIF.';
        }

        return null;
    }

    // Grava a foto enviada na pasta de uploads com um nome único.
    // Retorna o nome do arquivo salvo ou null se nenhuma foto foi enviada.
    private function salvarImagem(): ?string
    {
        $arquivo = $_FILES['imagem'] ?? null;
        if (!$arquivo || $arquivo['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $extensao = TIPOS_IMAGEM[getimagesize($arquivo['tmp_name'])['mime']];
        $nomeArquivo = 'produto_' . bin2hex(random_bytes(8)) . '.' . $extensao;

        if (!move_uploaded_file($arquivo['tmp_name'], PASTA_UPLOADS . $nomeArquivo)) {
            return null;
        }
        return $nomeArquivo;
    }

    // Apaga uma foto da pasta de uploads, se ela existir.
    private function apagarImagem(?string $nomeArquivo): void
    {
        if ($nomeArquivo && is_file(PASTA_UPLOADS . basename($nomeArquivo))) {
            unlink(PASTA_UPLOADS . basename($nomeArquivo));
        }
    }

    // Busca o produto ou mostra a página 404 se o id não existir.
    private function buscarOuFalhar(int $id): array
    {
        $produto = $this->produto->buscarPorId($id);
        if (!$produto) {
            paginaNaoEncontrada('O produto procurado não existe ou já foi excluído.');
        }
        return $produto;
    }
}
