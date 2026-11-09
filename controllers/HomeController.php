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
Descritivo: Controller da página inicial (painel). Reúne os números do estoque,
            os produtos com estoque baixo e os últimos cadastrados.
*******************************************************************************/

class HomeController
{
    private $produto;
    private $categoria;

    public function __construct(PDO $conn)
    {
        $this->produto   = new Produto($conn);
        $this->categoria = new Categoria($conn);
    }

    public function index(): void
    {
        $categorias = $this->categoria->listar();

        // No gráfico de barras, as categorias com mais produtos aparecem primeiro.
        usort($categorias, function ($a, $b) {
            return $b['total_produtos'] <=> $a['total_produtos'] ?: strcmp($a['nome'], $b['nome']);
        });

        // Maior quantidade de produtos em uma categoria (tamanho da barra cheia)
        // e quantas categorias ainda estão vazias.
        $maiorCategoria   = 0;
        $categoriasVazias = 0;
        foreach ($categorias as $categoria) {
            $maiorCategoria = max($maiorCategoria, (int) $categoria['total_produtos']);
            if ((int) $categoria['total_produtos'] === 0) {
                $categoriasVazias++;
            }
        }

        renderizar('home/index', [
            'titulo'           => 'Painel',
            'resumo'           => $this->produto->resumo(),
            'categorias'       => $categorias,
            'maiorCategoria'   => $maiorCategoria,
            'categoriasVazias' => $categoriasVazias,
            'estoqueBaixo'   => $this->produto->estoqueBaixo(LIMITE_ESTOQUE_BAIXO),
            'recentes'       => $this->produto->listar(['ordem' => 'recentes'], 4, 0),
        ]);
    }
}
