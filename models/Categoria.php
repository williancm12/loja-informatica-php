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
Descritivo: Model da entidade Categoria. Contém todas as consultas SQL da
            tabela categorias (listar, buscar, inserir, atualizar e excluir).
*******************************************************************************/

class Categoria
{
    private $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    // Lista as categorias em ordem alfabética, com a quantidade de produtos de cada uma.
    public function listar(): array
    {
        $sql = "SELECT c.id, c.nome, c.descricao, c.icone, COUNT(p.id) AS total_produtos
                  FROM categorias c
                  LEFT JOIN produtos p ON p.categoria_id = c.id
                 GROUP BY c.id, c.nome, c.descricao, c.icone
                 ORDER BY c.nome";

        return $this->conn->query($sql)->fetchAll();
    }

    // Busca uma categoria pelo id. Retorna null se ela não existir.
    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT id, nome, descricao, icone FROM categorias WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $categoria = $stmt->fetch();

        return $categoria ?: null;
    }

    // Verifica se já existe outra categoria com o mesmo nome.
    // $ignorarId serve para a edição: a própria categoria não conta como repetida.
    public function nomeEmUso(string $nome, int $ignorarId = 0): bool
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM categorias WHERE nome = :nome AND id <> :id");
        $stmt->execute([':nome' => $nome, ':id' => $ignorarId]);

        return $stmt->fetchColumn() > 0;
    }

    // Quantos produtos estão ligados à categoria.
    public function contarProdutos(int $id): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM produtos WHERE categoria_id = :id");
        $stmt->execute([':id' => $id]);

        return (int) $stmt->fetchColumn();
    }

    // Cadastra uma nova categoria e devolve o id gerado.
    public function inserir(array $dados): int
    {
        $stmt = $this->conn->prepare("INSERT INTO categorias (nome, descricao, icone) VALUES (:nome, :descricao, :icone)");
        $stmt->execute([
            ':nome'      => $dados['nome'],
            ':descricao' => $dados['descricao'] !== '' ? $dados['descricao'] : null,
            ':icone'     => $dados['icone'],
        ]);

        return (int) $this->conn->lastInsertId();
    }

    // Altera o nome, a descrição e o ícone de uma categoria.
    public function atualizar(int $id, array $dados): bool
    {
        $stmt = $this->conn->prepare("UPDATE categorias SET nome = :nome, descricao = :descricao, icone = :icone WHERE id = :id");

        return $stmt->execute([
            ':nome'      => $dados['nome'],
            ':descricao' => $dados['descricao'] !== '' ? $dados['descricao'] : null,
            ':icone'     => $dados['icone'],
            ':id'        => $id,
        ]);
    }

    // Exclui a categoria. O banco recusa se ainda houver produtos nela.
    public function excluir(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM categorias WHERE id = :id");

        return $stmt->execute([':id' => $id]);
    }
}
