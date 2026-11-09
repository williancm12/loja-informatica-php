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
Descritivo: Controller de Categorias. Recebe as requisições, valida os dados
            do formulário, chama o model Categoria e escolhe a view exibida.
*******************************************************************************/

class CategoriaController
{
    private $categoria;

    public function __construct(PDO $conn)
    {
        $this->categoria = new Categoria($conn);
    }

    // Lista todas as categorias.
    public function index(): void
    {
        renderizar('categorias/index', [
            'titulo'     => 'Categorias',
            'categorias' => $this->categoria->listar(),
        ]);
    }

    // Exibe o formulário vazio e, no envio (POST), cadastra a categoria.
    public function create(): void
    {
        $dados = ['nome' => '', 'descricao' => '', 'icone' => 'caixa'];
        $erros = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = $this->lerFormulario();
            $erros = $this->validar($dados);

            if (!$erros) {
                $this->categoria->inserir($dados);
                definirMensagem('sucesso', 'Categoria "' . $dados['nome'] . '" cadastrada com sucesso.');
                redirecionar(url('categorias'));
            }
        }

        renderizar('categorias/form', [
            'titulo'   => 'Nova categoria',
            'editando' => false,
            'dados'    => $dados,
            'erros'  => $erros,
            'acao'   => url('categorias', 'create'),
        ]);
    }

    // Exibe o formulário preenchido e, no envio (POST), salva as alterações.
    public function edit(int $id): void
    {
        $dados = $this->buscarOuFalhar($id);
        $erros = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = $this->lerFormulario();
            $erros = $this->validar($dados, $id);

            if (!$erros) {
                $this->categoria->atualizar($id, $dados);
                definirMensagem('sucesso', 'Categoria "' . $dados['nome'] . '" atualizada com sucesso.');
                redirecionar(url('categorias'));
            }
        }

        renderizar('categorias/form', [
            'titulo'   => 'Editar categoria',
            'editando' => true,
            'dados'    => $dados,
            'erros'  => $erros,
            'acao'   => url('categorias', 'edit', ['id' => $id]),
        ]);
    }

    // Exclui a categoria, desde que ela não tenha produtos.
    public function delete(int $id): void
    {
        // A exclusão só acontece por formulário (POST), nunca por um simples link.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirecionar(url('categorias'));
        }

        $categoria = $this->buscarOuFalhar($id);
        $totalProdutos = $this->categoria->contarProdutos($id);

        if ($totalProdutos > 0) {
            definirMensagem('erro', 'A categoria "' . $categoria['nome'] . '" tem ' . $totalProdutos
                . ' produto(s). Mova ou exclua os produtos antes de excluir a categoria.');
        } else {
            $this->categoria->excluir($id);
            definirMensagem('sucesso', 'Categoria "' . $categoria['nome'] . '" excluída com sucesso.');
        }

        redirecionar(url('categorias'));
    }

    // Lê os campos enviados pelo formulário, já sem espaços nas pontas.
    private function lerFormulario(): array
    {
        return [
            'nome'      => trim($_POST['nome'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'icone'     => $_POST['icone'] ?? 'caixa',
        ];
    }

    // Regras de validação. Retorna um array campo => mensagem de erro.
    private function validar(array $dados, int $id = 0): array
    {
        $erros = [];

        if ($dados['nome'] === '') {
            $erros['nome'] = 'Informe o nome da categoria.';
        } elseif (mb_strlen($dados['nome']) > 100) {
            $erros['nome'] = 'O nome pode ter no máximo 100 caracteres.';
        } elseif ($this->categoria->nomeEmUso($dados['nome'], $id)) {
            $erros['nome'] = 'Já existe uma categoria com esse nome.';
        }

        if (mb_strlen($dados['descricao']) > 255) {
            $erros['descricao'] = 'A descrição pode ter no máximo 255 caracteres.';
        }

        // O ícone precisa ser um dos que existem em public/img/categorias.
        if (!isset(ICONES_CATEGORIA[$dados['icone']])) {
            $erros['icone'] = 'Escolha um dos ícones da lista.';
        }

        return $erros;
    }

    // Busca a categoria ou mostra a página 404 se o id não existir.
    private function buscarOuFalhar(int $id): array
    {
        $categoria = $this->categoria->buscarPorId($id);
        if (!$categoria) {
            paginaNaoEncontrada('A categoria procurada não existe ou já foi excluída.');
        }
        return $categoria;
    }
}
