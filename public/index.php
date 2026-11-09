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
Descritivo: Ponto de entrada do sistema (front controller). Toda requisição
            passa por aqui: os parâmetros page e action da URL escolhem qual
            controller e qual método vão atender o pedido.
*******************************************************************************/

// A sessão guarda as mensagens de aviso e o token dos formulários.
session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/funcoes.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Produto.php';
require_once __DIR__ . '/../controllers/HomeController.php';
require_once __DIR__ . '/../controllers/CategoriaController.php';
require_once __DIR__ . '/../controllers/ProdutoController.php';

// Rotas do sistema: página => controller responsável e ações permitidas.
$rotas = [
    'home'       => ['controller' => 'HomeController',      'acoes' => ['index']],
    'produtos'   => ['controller' => 'ProdutoController',   'acoes' => ['index', 'show', 'create', 'edit', 'delete']],
    'categorias' => ['controller' => 'CategoriaController', 'acoes' => ['index', 'create', 'edit', 'delete']],
];

// Ações que precisam do id do registro na URL.
$acoesComId = ['show', 'edit', 'delete'];

$pagina = $_GET['page'] ?? 'home';
$acao   = $_GET['action'] ?? 'index';
$id     = (int) ($_GET['id'] ?? 0);

// Página ou ação fora da lista de rotas: mostra o erro 404.
if (!isset($rotas[$pagina]) || !in_array($acao, $rotas[$pagina]['acoes'], true)) {
    paginaNaoEncontrada();
}

// Todo formulário enviado precisa trazer o token da sessão (proteção CSRF).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrfValido()) {
    definirMensagem('erro', 'O formulário expirou. Tente enviar novamente.');
    redirecionar(url($pagina));
}

// Conecta ao banco. Se falhar, mostra uma página explicando o que verificar.
try {
    $conexao = Database::getConexao();
} catch (PDOException $erro) {
    http_response_code(500);
    renderizar('erros/banco', ['titulo' => 'Erro de conexão', 'detalhe' => $erro->getMessage()]);
    exit;
}

// Cria o controller da página e chama o método com o nome da ação.
$nomeController = $rotas[$pagina]['controller'];
$controller = new $nomeController($conexao);

if (in_array($acao, $acoesComId, true)) {
    $controller->$acao($id);
} else {
    $controller->$acao();
}
