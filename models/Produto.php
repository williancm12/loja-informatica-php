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
Descritivo: Model da entidade Produto. Contém as consultas SQL da tabela
            produtos, incluindo busca, filtro por categoria, ordenação,
            paginação e os números do painel.
*******************************************************************************/

class Produto
{
    // Opções de ordenação da listagem. Só os trechos de SQL desta lista podem
    // entrar no ORDER BY, o que impede SQL injection por esse parâmetro.
    const ORDENACOES = [
        'nome'       => ['rotulo' => 'Nome (A-Z)',    'sql' => 'p.nome ASC'],
        'preco_asc'  => ['rotulo' => 'Menor preço',   'sql' => 'p.preco ASC, p.nome ASC'],
        'preco_desc' => ['rotulo' => 'Maior preço',   'sql' => 'p.preco DESC, p.nome ASC'],
        'estoque'    => ['rotulo' => 'Menor estoque', 'sql' => 'p.estoque ASC, p.nome ASC'],
        'recentes'   => ['rotulo' => 'Mais recentes', 'sql' => 'p.criado_em DESC, p.id DESC'],
    ];

    // Filtros rápidos pela situação do estoque (os botões "Estoque baixo" e "Esgotados").
    const SITUACOES = [
        'baixo'    => 'Estoque baixo',
        'esgotado' => 'Esgotados',
    ];

    private $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    // Monta o WHERE a partir dos filtros da tela e preenche $parametros com os valores.
    // $usarSituacao = false ignora o filtro de situação (usado para contar cada situação).
    private function montarFiltros(array $filtros, array &$parametros, bool $usarSituacao = true): string
    {
        $condicoes = [];

        if ($usarSituacao && ($filtros['situacao'] ?? '') === 'baixo') {
            $condicoes[] = 'p.estoque BETWEEN 1 AND :limite_baixo';
            $parametros[':limite_baixo'] = LIMITE_ESTOQUE_BAIXO;
        } elseif ($usarSituacao && ($filtros['situacao'] ?? '') === 'esgotado') {
            $condicoes[] = 'p.estoque = 0';
        }

        if (!empty($filtros['busca'])) {
            $condicoes[] = '(p.nome LIKE :busca_nome OR p.marca LIKE :busca_marca)';
            $parametros[':busca_nome']  = '%' . $filtros['busca'] . '%';
            $parametros[':busca_marca'] = '%' . $filtros['busca'] . '%';
        }

        if (!empty($filtros['categoria'])) {
            $condicoes[] = 'p.categoria_id = :categoria';
            $parametros[':categoria'] = (int) $filtros['categoria'];
        }

        return $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';
    }

    // Lista os produtos de uma página, já com o nome da categoria (JOIN).
    public function listar(array $filtros, int $limite, int $deslocamento): array
    {
        $parametros = [];
        $where = $this->montarFiltros($filtros, $parametros);
        $ordem = self::ORDENACOES[$filtros['ordem'] ?? 'nome']['sql'] ?? self::ORDENACOES['nome']['sql'];

        $sql = "SELECT p.id, p.categoria_id, p.nome, p.marca, p.preco, p.estoque, p.imagem, p.criado_em,
                       c.nome AS categoria_nome, c.icone AS categoria_icone
                  FROM produtos p
                 INNER JOIN categorias c ON c.id = p.categoria_id
                 $where
                 ORDER BY $ordem
                 LIMIT :limite OFFSET :deslocamento";

        $stmt = $this->conn->prepare($sql);
        foreach ($parametros as $nome => $valor) {
            $stmt->bindValue($nome, $valor, is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':deslocamento', $deslocamento, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Total de produtos que atendem aos filtros (usado na paginação).
    public function contar(array $filtros): int
    {
        $parametros = [];
        $where = $this->montarFiltros($filtros, $parametros);

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM produtos p $where");
        $stmt->execute($parametros);

        return (int) $stmt->fetchColumn();
    }

    // Quantos produtos há em cada situação de estoque, respeitando a busca e a
    // categoria escolhidas. Usado nos números dos botões de filtro rápido.
    public function contarPorSituacao(array $filtros): array
    {
        $parametros = [':limite' => LIMITE_ESTOQUE_BAIXO];
        $where = $this->montarFiltros($filtros, $parametros, false);

        $sql = "SELECT COUNT(*)                                            AS todos,
                       COALESCE(SUM(p.estoque BETWEEN 1 AND :limite), 0) AS baixo,
                       COALESCE(SUM(p.estoque = 0), 0)                    AS esgotado
                  FROM produtos p
                 $where";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($parametros);

        return array_map('intval', $stmt->fetch());
    }

    // Busca um produto pelo id, com o nome e o ícone da categoria. Retorna null se não existir.
    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT p.*, c.nome AS categoria_nome, c.icone AS categoria_icone
                  FROM produtos p
                 INNER JOIN categorias c ON c.id = p.categoria_id
                 WHERE p.id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $produto = $stmt->fetch();

        return $produto ?: null;
    }

    // Cadastra um novo produto e devolve o id gerado.
    public function inserir(array $dados): int
    {
        $sql = "INSERT INTO produtos (categoria_id, nome, marca, descricao, preco, estoque, imagem)
                VALUES (:categoria_id, :nome, :marca, :descricao, :preco, :estoque, :imagem)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($this->parametros($dados));

        return (int) $this->conn->lastInsertId();
    }

    // Altera todos os dados de um produto.
    public function atualizar(int $id, array $dados): bool
    {
        $sql = "UPDATE produtos
                   SET categoria_id = :categoria_id,
                       nome         = :nome,
                       marca        = :marca,
                       descricao    = :descricao,
                       preco        = :preco,
                       estoque      = :estoque,
                       imagem       = :imagem
                 WHERE id = :id";

        $parametros = $this->parametros($dados);
        $parametros[':id'] = $id;

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($parametros);
    }

    // Exclui um produto.
    public function excluir(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM produtos WHERE id = :id");

        return $stmt->execute([':id' => $id]);
    }

    // Números gerais do painel: produtos, unidades, valor do estoque e esgotados.
    public function resumo(): array
    {
        $sql = "SELECT COUNT(*)                         AS total_produtos,
                       COALESCE(SUM(estoque), 0)         AS total_unidades,
                       COALESCE(SUM(preco * estoque), 0) AS valor_estoque,
                       COALESCE(SUM(estoque = 0), 0)     AS esgotados
                  FROM produtos";

        return $this->conn->query($sql)->fetch();
    }

    // Produtos com estoque menor ou igual ao limite, do menor para o maior.
    public function estoqueBaixo(int $limite): array
    {
        $sql = "SELECT p.id, p.nome, p.marca, p.estoque, p.imagem,
                       c.nome AS categoria_nome, c.icone AS categoria_icone
                  FROM produtos p
                 INNER JOIN categorias c ON c.id = p.categoria_id
                 WHERE p.estoque <= :limite
                 ORDER BY p.estoque ASC, p.nome ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Outros produtos da mesma categoria, para a página de detalhes.
    public function relacionados(int $categoriaId, int $excetoId, int $limite): array
    {
        $sql = "SELECT p.id, p.nome, p.marca, p.preco, p.estoque, p.imagem, c.icone AS categoria_icone
                  FROM produtos p
                 INNER JOIN categorias c ON c.id = p.categoria_id
                 WHERE p.categoria_id = :categoria AND p.id <> :id
                 ORDER BY p.nome
                 LIMIT :limite";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':categoria', $categoriaId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $excetoId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Converte o array de dados do formulário nos parâmetros do SQL.
    private function parametros(array $dados): array
    {
        return [
            ':categoria_id' => $dados['categoria_id'],
            ':nome'         => $dados['nome'],
            ':marca'        => $dados['marca'],
            ':descricao'    => $dados['descricao'] !== '' ? $dados['descricao'] : null,
            ':preco'        => $dados['preco'],
            ':estoque'      => $dados['estoque'],
            ':imagem'       => $dados['imagem'],
        ];
    }
}
