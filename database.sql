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
Descritivo: Script de criação do banco loja_informatica, das tabelas categorias
            e produtos e dos dados de exemplo da loja.
*******************************************************************************/

-- ATENÇÃO: o script recria o banco do zero. Rodar de novo apaga os dados
-- cadastrados e volta para os dados de exemplo abaixo.
DROP DATABASE IF EXISTS `loja_informatica`;

CREATE DATABASE `loja_informatica`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `loja_informatica`;

-- ---------------------------------------------------------------------------
-- Tabela de categorias
-- O nome é único: não podem existir duas categorias com o mesmo nome.
-- O ícone aparece no lugar da foto dos produtos que não têm uma
-- (os nomes possíveis estão em ICONES_CATEGORIA, no config/config.php).
-- ---------------------------------------------------------------------------
CREATE TABLE `categorias` (
    `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nome`      VARCHAR(100) NOT NULL,
    `descricao` VARCHAR(255) NULL,
    `icone`     VARCHAR(20) NOT NULL DEFAULT 'caixa',
    `criado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_categorias_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Tabela de produtos
-- Cada produto pertence a uma categoria (relacionamento N:1).
-- ON DELETE RESTRICT impede apagar uma categoria que ainda tem produtos.
-- ---------------------------------------------------------------------------
CREATE TABLE `produtos` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `categoria_id`  INT UNSIGNED NOT NULL,
    `nome`          VARCHAR(150) NOT NULL,
    `marca`         VARCHAR(80) NOT NULL,
    `descricao`     TEXT NULL,
    `preco`         DECIMAL(10, 2) NOT NULL,
    `estoque`       INT UNSIGNED NOT NULL DEFAULT 0,
    `imagem`        VARCHAR(255) NULL,
    `criado_em`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_produtos_nome` (`nome`),
    CONSTRAINT `fk_produtos_categoria`
        FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT `ck_produtos_preco` CHECK (`preco` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Dados de exemplo
-- ---------------------------------------------------------------------------
INSERT INTO `categorias` (`id`, `nome`, `descricao`, `icone`) VALUES
(1, 'Notebooks',     'Notebooks para estudo, trabalho e jogos.',               'notebook'),
(2, 'Periféricos',   'Mouses, teclados, headsets e webcams.',                  'periferico'),
(3, 'Hardware',      'Processadores, placas de vídeo, memórias e placas-mãe.', 'hardware'),
(4, 'Monitores',     'Monitores para escritório e para jogos.',                'monitor'),
(5, 'Armazenamento', 'SSDs, HDs externos e pendrives.',                        'armazenamento'),
(6, 'Redes',         'Roteadores, switches e adaptadores Wi-Fi.',              'rede'),
(7, 'Impressoras',   'Impressoras jato de tinta e laser.',                     'impressora');

INSERT INTO `produtos` (`categoria_id`, `nome`, `marca`, `descricao`, `preco`, `estoque`) VALUES
(1, 'Notebook Inspiron 15',          'Dell',     'Intel Core i5, 8 GB de RAM, SSD de 512 GB e tela Full HD de 15,6".', 3899.00, 7),
(1, 'Notebook IdeaPad Gaming 3',     'Lenovo',   'AMD Ryzen 7, 16 GB de RAM, RTX 3050 e tela de 120 Hz.',              5299.90, 3),
(1, 'MacBook Air 13" M2',            'Apple',    'Chip M2, 8 GB de memória unificada e SSD de 256 GB.',                8999.00, 0),
(2, 'Mouse Gamer G203',              'Logitech', 'Sensor de 8.000 DPI, iluminação RGB e 6 botões programáveis.',        149.90, 25),
(2, 'Teclado Mecânico Kumara',       'Redragon', 'Switch Outemu Blue, layout ABNT2 e retroiluminação vermelha.',        229.90, 12),
(2, 'Headset Cloud Stinger',         'HyperX',   'Drivers de 50 mm, microfone com cancelamento de ruído.',              279.00, 4),
(2, 'Webcam C920 Full HD',           'Logitech', 'Vídeo 1080p a 30 fps com dois microfones estéreo.',                   399.90, 9),
(3, 'Processador Ryzen 5 5600',      'AMD',      '6 núcleos, 12 threads, até 4,4 GHz, soquete AM4.',                    799.00, 6),
(3, 'Placa de Vídeo RTX 4060 8 GB',  'Gigabyte', 'GDDR6, DLSS 3 e três ventoinhas.',                                   2199.00, 2),
(3, 'Memória RAM 16 GB DDR4',        'Kingston', 'Fury Beast 3200 MHz, CL16.',                                          259.90, 18),
(4, 'Monitor UltraGear 24" 144 Hz',  'LG',       'Painel IPS Full HD, 1 ms e AMD FreeSync.',                           1099.00, 5),
(4, 'Monitor 27" Full HD',           'Samsung',  'Painel VA curvo, 75 Hz e entrada HDMI.',                              899.90, 10),
(5, 'SSD NVMe 1 TB NV2',             'Kingston', 'Leitura de até 3.500 MB/s, formato M.2 2280.',                        379.90, 30),
(5, 'HD Externo 2 TB',               'Seagate',  'USB 3.0, compatível com Windows e macOS.',                            459.00, 0),
(5, 'Pendrive 64 GB Ultra',          'SanDisk',  'USB 3.0 com leitura de até 130 MB/s.',                                 39.90, 50),
(6, 'Roteador Wi-Fi 6 Archer AX23',  'TP-Link',  'Dual band AX1800 com quatro antenas.',                                449.90, 8);
