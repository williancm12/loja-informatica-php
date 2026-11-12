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
Descritivo: Configurações gerais do sistema: nome da loja, acesso ao banco de
            dados, paginação, estoque mínimo e regras de upload de imagens.
*******************************************************************************/

// Fuso horário usado nas datas exibidas (cadastro e atualização dos produtos).
date_default_timezone_set('America/Sao_Paulo');

// Nome exibido no topo das páginas e na aba do navegador.
define('NOME_LOJA', 'TecnoByte Informática');

// Dados de acesso ao MySQL. No XAMPP o usuário root não tem senha.
define('DB_HOST', 'localhost');
define('DB_NOME', 'loja_informatica');
define('DB_USUARIO', 'root');
define('DB_SENHA', '');

// Quantidade de produtos mostrados em cada página da listagem.
// 12 divide certinho em 2, 3, 4 e 6 colunas, então a grade fica completa em qualquer tela.
define('ITENS_POR_PAGINA', 12);

// Produtos com estoque menor ou igual a este valor aparecem como "estoque baixo".
define('LIMITE_ESTOQUE_BAIXO', 5);

// Ícones que uma categoria pode ter (arquivo em public/img/categorias/<ícone>.svg).
// O ícone aparece no lugar da foto dos produtos que não têm uma.
define('ICONES_CATEGORIA', [
    'notebook'      => 'Notebook',
    'periferico'    => 'Periférico',
    'hardware'      => 'Hardware',
    'monitor'       => 'Monitor',
    'armazenamento' => 'Armazenamento',
    'rede'          => 'Rede',
    'impressora'    => 'Impressora',
    'caixa'         => 'Outros',
]);

// Pasta onde as fotos dos produtos são gravadas e regras do upload.
define('PASTA_UPLOADS', __DIR__ . '/../public/uploads/');
define('TAMANHO_MAX_IMAGEM', 2 * 1024 * 1024); // 2 MB
define('TIPOS_IMAGEM', [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
]);

// Endereço da pasta public calculado a partir do script em execução.
// Assim o sistema funciona em qualquer pasta do htdocs, sem caminho fixo.
define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));
