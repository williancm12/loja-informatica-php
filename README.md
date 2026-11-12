# TecnoByte Informática — Sistema CRUD em PHP com MySQL

Trabalho Prático 2 (2º bimestre) da disciplina **Linguagem e Técnicas de Programação**
do curso de **Engenharia de Software**, professor José Carlos Domingues Flores.

Sistema web de controle de estoque de uma loja de informática. Ele gerencia duas
entidades relacionadas, **Categorias** e **Produtos** (cada produto pertence a uma
categoria), com cadastro, consulta, edição e exclusão completos.

## Integrantes do grupo

| RA | Nome |
|----|------|
| 26012215-2 | Willian Cezar De Souza Moreira |
| 26012217-2 | Pedro Machado Gebauer |
| 26004820-2 | Thiago Tratch |
| 26011025-2 | Maria Clara Mendes Ramalho |
| 26014987-2 | Maria Eduarda Delbone Cardoso |

Turma: ESOFT2

## Arquivos da entrega

| O quê | Onde |
|-------|------|
| Código-fonte, imagens e fontes | todo este repositório |
| **Script do banco de dados** | [`database.sql`](database.sql): cria o banco `loja_informatica`, as tabelas `categorias` e `produtos` e os dados de exemplo |

Testado no **XAMPP 8.2.12** (Apache 2.4.58, PHP 8.2.12 e MariaDB 10.4.32), com o banco importado pela opção **Importar** do phpMyAdmin.
O passo a passo está em [Como rodar no XAMPP](#como-rodar-no-xampp).

## Funcionalidades

**Painel (página inicial)**
- Busca rápida de produtos direto do topo da página.
- Indicadores com o total de produtos, de categorias, de unidades em estoque e o valor total do estoque.
- Lista "Precisa de atenção" com os produtos esgotados ou com até 5 unidades, cada um com o botão **Repor**.
- Barras com a quantidade de produtos por categoria, da maior para a menor.
- Vitrine com os últimos produtos cadastrados.

**Produtos**
- Listagem com foto, marca, categoria, preço e selo colorido de estoque (em estoque, baixo ou esgotado).
- Busca por nome ou marca, filtro por categoria e cinco opções de ordenação.
- Filtros rápidos **Todos**, **Estoque baixo** e **Esgotados**, com a quantidade de cada um.
- Exibição em **tabela** ou em **grade de cartões**. No celular, cada linha da tabela vira um cartão.
- Paginação (12 produtos por página) que mantém a busca, os filtros e a forma de exibição.
- Página de detalhes com alerta de estoque baixo, valor em estoque, datas e outros produtos da mesma categoria.
- Formulário dividido em seções, com **prévia ao vivo** de como o produto vai aparecer na loja, botões − e + no estoque, contador de caracteres e botão "Salvar e cadastrar outro".
- **Upload de foto** (JPG, PNG, WEBP ou GIF até 2 MB) clicando ou **arrastando o arquivo**, com opção de remover a foto. Produto sem foto mostra o ícone da categoria.
- Preço aceito no formato brasileiro (`1.299,90`).

**Categorias**
- Cartões com ícone, quantidade e proporção de produtos de cada categoria.
- Cadastro rápido na própria listagem, com escolha do ícone da categoria.
- Nome único (não aceita duas categorias com o mesmo nome).
- Uma categoria que ainda tem produtos não pode ser excluída. O botão fica desabilitado, o sistema avisa e o banco também impede (`ON DELETE RESTRICT`).

**Interface**
- Layout responsivo: o conteúdo ocupa toda a largura do monitor e as grades ganham ou perdem colunas conforme o tamanho da tela.
- Menu lateral no computador. No celular, barra no topo, menu na parte de baixo e botão "+" flutuante.
- **Modo escuro**, ligado pelo botão do menu e lembrado pelo navegador.
- Aviso flutuante depois de salvar ou excluir, que some sozinho quando é sucesso.
- Janela de confirmação própria antes de qualquer exclusão.
- Pensada para acessibilidade: navegação por teclado, link "Pular para o conteúdo", textos alternativos nos botões só com ícone e erros ligados aos campos para leitores de tela.

**Qualidade e segurança**
- Arquitetura MVC: models (SQL), controllers (regras) e views (telas) em pastas separadas.
- Todas as consultas usam *prepared statements* do PDO (proteção contra SQL injection).
- Todo texto exibido passa por `htmlspecialchars` (proteção contra XSS).
- Formulários com token CSRF, e a exclusão só é aceita por POST, com confirmação antes.
- Validação no servidor com a mensagem de erro ao lado de cada campo, sem perder o que foi digitado.
- Páginas amigáveis para erro 404 e para falha de conexão com o MySQL.
- No Apache, o navegador só acessa a pasta `public/`. As pastas internas (`config`, `controllers`, `helpers`, `models` e `views`) têm um `.htaccess` que bloqueia o acesso direto.
- O sistema funciona mesmo com o JavaScript desligado. O JavaScript só acrescenta melhorias.

## Tecnologias

- PHP 7.4 ou superior (testado no PHP 8.2 do XAMPP)
- MySQL / MariaDB
- HTML5, CSS3 e JavaScript puro, sem bibliotecas externas
- Fontes Inter e Space Grotesk salvas no projeto (licença SIL Open Font License), então tudo funciona sem internet

## Estrutura do projeto

```
loja_informatica/
├── index.php                   # atalho: redireciona para public/
├── database.sql                # script de criação do banco, tabelas e dados de exemplo
├── config/
│   ├── config.php              # nome da loja, acesso ao banco, paginação, ícones e upload
│   └── database.php            # classe Database (conexão PDO)
├── helpers/
│   └── funcoes.php             # funções auxiliares (links, escape, avisos, CSRF, formatação, ícones)
├── models/
│   ├── Categoria.php           # SQL da tabela categorias
│   └── Produto.php             # SQL da tabela produtos
├── controllers/
│   ├── HomeController.php      # painel inicial
│   ├── CategoriaController.php # CRUD de categorias
│   └── ProdutoController.php   # CRUD de produtos + upload da foto
├── views/
│   ├── includes/               # header.php (menu) e footer.php (aviso e janela de confirmação)
│   ├── home/                   # painel
│   ├── categorias/             # listagem, formulário e _campos.php (campos usados nos dois)
│   ├── produtos/               # listagem, detalhes, formulário e _excluir.php (botão de excluir)
│   └── erros/                  # páginas 404 e de erro de banco
└── public/                     # única pasta acessada pelo navegador
    ├── index.php               # front controller: escolhe o controller pela URL
    ├── css/style.css           # folha de estilos externa (temas claro e escuro)
    ├── js/app.js               # modo escuro, avisos, confirmação e recursos do formulário
    ├── fonts/                  # fontes Inter e Space Grotesk + licenças
    ├── img/                    # logotipo e img/categorias/ com os ícones das categorias
    └── uploads/                # fotos enviadas dos produtos
```

### Como uma requisição funciona

1. O navegador acessa, por exemplo, `public/index.php?page=produtos&action=edit&id=3`.
2. O `public/index.php` confere se a página e a ação existem na lista de rotas e cria o `ProdutoController`.
3. O controller chama o model `Produto`, que faz o `SELECT` no banco.
4. O controller valida os dados (no envio do formulário) e escolhe a view.
5. A função `renderizar()` monta a página: `header.php` + view + `footer.php`.

## Banco de dados

O script `database.sql` cria o banco `loja_informatica` com duas tabelas:

- `categorias` (`id`, `nome` único, `descricao`, `icone`, `criado_em`)
- `produtos` (`id`, `categoria_id` → `categorias.id`, `nome`, `marca`, `descricao`, `preco`, `estoque`, `imagem`, `criado_em`, `atualizado_em`)

Ele também insere 7 categorias e 16 produtos de exemplo. A categoria *Impressoras* começa vazia, para demonstrar a exclusão.

> Atenção: o script começa com `DROP DATABASE IF EXISTS`. Rodar de novo apaga o que foi cadastrado e volta aos dados de exemplo.

## Como rodar no XAMPP

1. Coloque o projeto dentro de `C:\xampp\htdocs\`, de um destes jeitos:
   - **Clonando com o Git:** abra um terminal na pasta `C:\xampp\htdocs` e rode
     `git clone https://github.com/williancm12/loja-informatica-php.git`.
     A pasta criada se chama `loja-informatica-php`.
   - **Baixando o ZIP:** no GitHub, clique em **Code → Download ZIP** e extraia o arquivo em `C:\xampp\htdocs\`.
     A pasta extraída se chama `loja-informatica-php-main`.
2. Abra o **XAMPP Control Panel** e clique em **Start** no **Apache** e no **MySQL**.
3. Acesse `http://localhost/phpmyadmin`, abra a aba **Importar**, escolha o arquivo `database.sql` (na raiz do projeto) e clique em **Importar**.
   (Outra opção é abrir a aba **SQL**, colar o conteúdo do arquivo e executar.)
4. Abra no navegador `http://localhost/` seguido do nome da pasta, por exemplo
   `http://localhost/loja-informatica-php/` (ou `http://localhost/loja-informatica-php-main/` se baixou o ZIP).

O sistema calcula o próprio endereço sozinho, então a pasta pode ter qualquer nome dentro do `htdocs`.
Ele usa a configuração padrão do XAMPP (usuário `root` sem senha). Se o MySQL tiver senha, ou se você quiser outro nome de banco, altere as constantes `DB_*` em `config/config.php`.
