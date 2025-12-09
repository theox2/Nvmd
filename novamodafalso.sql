-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 05/12/2025 às 00:22
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `novamoda`
--
CREATE DATABASE IF NOT EXISTS `novamoda` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `novamoda`;

DELIMITER $$
--
-- Procedimentos
--
DROP PROCEDURE IF EXISTS `sp_atualizar_estoque`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_atualizar_estoque` (IN `p_pedido_id` INT)   BEGIN
    UPDATE produtos p
    JOIN pedido_itens pi ON p.id = pi.produto_id
    SET p.estoque = p.estoque - pi.quantidade
    WHERE pi.pedido_id = p_pedido_id;
END$$

DROP PROCEDURE IF EXISTS `sp_calcular_total_carrinho`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_calcular_total_carrinho` (IN `p_carrinho_id` INT)   BEGIN
    SELECT 
        SUM(ci.quantidade * ci.preco_unitario) as subtotal,
        COUNT(*) as total_itens
    FROM carrinho_itens ci
    WHERE ci.carrinho_id = p_carrinho_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `avaliacao_imagens`
--

DROP TABLE IF EXISTS `avaliacao_imagens`;
CREATE TABLE IF NOT EXISTS `avaliacao_imagens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `avaliacao_id` int(11) NOT NULL,
  `url` varchar(500) NOT NULL,
  `data_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_avaliacao` (`avaliacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `avaliacao_util`
--

DROP TABLE IF EXISTS `avaliacao_util`;
CREATE TABLE IF NOT EXISTS `avaliacao_util` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `avaliacao_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data_voto` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_usuario_avaliacao` (`usuario_id`,`avaliacao_id`),
  KEY `idx_avaliacao` (`avaliacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `avaliacoes`
--

DROP TABLE IF EXISTS `avaliacoes`;
CREATE TABLE IF NOT EXISTS `avaliacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produto_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nota` int(11) NOT NULL CHECK (`nota` >= 1 and `nota` <= 5),
  `titulo` varchar(200) DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `verificada` tinyint(1) DEFAULT 0,
  `aprovada` tinyint(1) DEFAULT 1,
  `data_avaliacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_usuario_produto` (`usuario_id`,`produto_id`),
  KEY `idx_produto` (`produto_id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_nota` (`nota`),
  KEY `idx_avaliacoes_produto_aprovada` (`produto_id`,`aprovada`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `avaliacoes`
--

INSERT INTO `avaliacoes` (`id`, `produto_id`, `usuario_id`, `nota`, `titulo`, `comentario`, `verificada`, `aprovada`, `data_avaliacao`) VALUES
(1, 1, 2, 5, 'Produto excelente!', 'A qualidade superou minhas expectativas. Muito confortável e o tecido é ótimo.', 1, 1, '2025-12-04 23:15:20'),
(2, 1, 3, 4, 'Muito bom', 'Gostei bastante, só achei um pouco grande. Recomendo pedir um tamanho menor.', 1, 1, '2025-12-04 23:15:20'),
(3, 2, 2, 5, 'Melhor moletom que já comprei', 'Super quente e confortável. Vale cada centavo!', 1, 1, '2025-12-04 23:15:20');

-- --------------------------------------------------------

--
-- Estrutura para tabela `carrinhos`
--

DROP TABLE IF EXISTS `carrinhos`;
CREATE TABLE IF NOT EXISTS `carrinhos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `sessao_id` varchar(100) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_sessao` (`sessao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `carrinho_itens`
--

DROP TABLE IF EXISTS `carrinho_itens`;
CREATE TABLE IF NOT EXISTS `carrinho_itens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `carrinho_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) DEFAULT 1,
  `tamanho` varchar(10) DEFAULT NULL,
  `cor` varchar(50) DEFAULT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `data_adicao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_carrinho` (`carrinho_id`),
  KEY `idx_produto` (`produto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `imagem_url` varchar(500) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `ordem` int(11) DEFAULT 0,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`, `slug`, `descricao`, `imagem_url`, `ativo`, `ordem`, `data_cadastro`) VALUES
(1, 'Masculino', 'masculino', 'Moda masculina urbana e streetwear', NULL, 1, 1, '2025-12-04 23:15:20'),
(2, 'Feminino', 'feminino', 'Estilo e elegância feminina', NULL, 1, 2, '2025-12-04 23:15:20'),
(3, 'Infantil', 'infantil', 'Conforto e diversão para os pequenos', NULL, 1, 3, '2025-12-04 23:15:20'),
(4, 'Acessórios', 'acessorios', 'Complete seu look com estilo', NULL, 1, 4, '2025-12-04 23:15:20'),
(5, 'Calçados', 'calcados', 'Tênis e sapatos para todos os estilos', NULL, 1, 5, '2025-12-04 23:15:20');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cupons`
--

DROP TABLE IF EXISTS `cupons`;
CREATE TABLE IF NOT EXISTS `cupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `descricao` varchar(200) DEFAULT NULL,
  `tipo` enum('percentual','fixo') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `valor_minimo` decimal(10,2) DEFAULT 0.00,
  `limite_uso` int(11) DEFAULT NULL,
  `vezes_usado` int(11) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `data_inicio` date DEFAULT NULL,
  `data_expiracao` date DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_codigo` (`codigo`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cupons`
--

INSERT INTO `cupons` (`id`, `codigo`, `descricao`, `tipo`, `valor`, `valor_minimo`, `limite_uso`, `vezes_usado`, `ativo`, `data_inicio`, `data_expiracao`, `data_cadastro`) VALUES
(1, 'NOVA10', 'Desconto de 10% para novos clientes', 'percentual', 10.00, 100.00, 100, 0, 1, NULL, '2025-12-31', '2025-12-04 23:15:20'),
(2, 'PRIMEIRA', 'Desconto de 15% na primeira compra', 'percentual', 15.00, 150.00, 50, 0, 1, NULL, '2025-12-31', '2025-12-04 23:15:20'),
(3, 'FRETE50', 'R$ 50 de desconto no frete', 'fixo', 50.00, 200.00, NULL, 0, 1, NULL, '2025-12-31', '2025-12-04 23:15:20');

-- --------------------------------------------------------

--
-- Estrutura para tabela `enderecos`
--

DROP TABLE IF EXISTS `enderecos`;
CREATE TABLE IF NOT EXISTS `enderecos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `nome_endereco` varchar(50) DEFAULT 'Principal',
  `cep` varchar(10) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `cidade` varchar(100) NOT NULL,
  `bairro` varchar(100) NOT NULL,
  `endereco` varchar(200) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `padrao` tinyint(1) DEFAULT 0,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `favoritos`
--

DROP TABLE IF EXISTS `favoritos`;
CREATE TABLE IF NOT EXISTS `favoritos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `data_adicao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_usuario_produto` (`usuario_id`,`produto_id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_produto` (`produto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_sistema`
--

DROP TABLE IF EXISTS `logs_sistema`;
CREATE TABLE IF NOT EXISTS `logs_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `acao` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `data_log` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_acao` (`acao`),
  KEY `idx_data` (`data_log`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `newsletter`
--

DROP TABLE IF EXISTS `newsletter`;
CREATE TABLE IF NOT EXISTS `newsletter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_inscricao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
CREATE TABLE IF NOT EXISTS `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_pedido` varchar(20) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `endereco_id` int(11) DEFAULT NULL,
  `status` enum('pendente','processando','enviado','entregue','cancelado') DEFAULT 'pendente',
  `forma_pagamento` enum('pix','credito','boleto') NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `desconto` decimal(10,2) DEFAULT 0.00,
  `frete` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `cupom_codigo` varchar(50) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `data_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_pedido` (`numero_pedido`),
  KEY `endereco_id` (`endereco_id`),
  KEY `idx_numero` (`numero_pedido`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_status` (`status`),
  KEY `idx_data` (`data_pedido`),
  KEY `idx_pedidos_usuario_status` (`usuario_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Acionadores `pedidos`
--
DROP TRIGGER IF EXISTS `trg_gerar_numero_pedido`;
DELIMITER $$
CREATE TRIGGER `trg_gerar_numero_pedido` BEFORE INSERT ON `pedidos` FOR EACH ROW BEGIN
    IF NEW.numero_pedido IS NULL OR NEW.numero_pedido = '' THEN
        SET NEW.numero_pedido = CONCAT('#', LPAD(FLOOR(RAND() * 99999) + 10000, 5, '0'));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedido_itens`
--

DROP TABLE IF EXISTS `pedido_itens`;
CREATE TABLE IF NOT EXISTS `pedido_itens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `nome_produto` varchar(200) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `tamanho` varchar(10) DEFAULT NULL,
  `cor` varchar(50) DEFAULT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pedido` (`pedido_id`),
  KEY `idx_produto` (`produto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

DROP TABLE IF EXISTS `produtos`;
CREATE TABLE IF NOT EXISTS `produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `descricao` text DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `preco_antigo` decimal(10,2) DEFAULT NULL,
  `estoque` int(11) DEFAULT 0,
  `imagem_principal` varchar(500) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `destaque` tinyint(1) DEFAULT 0,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultima_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_categoria` (`categoria_id`),
  KEY `idx_ativo` (`ativo`),
  KEY `idx_destaque` (`destaque`),
  KEY `idx_preco` (`preco`),
  KEY `idx_produtos_categoria_ativo` (`categoria_id`,`ativo`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `slug`, `descricao`, `categoria_id`, `preco`, `preco_antigo`, `estoque`, `imagem_principal`, `ativo`, `destaque`, `data_cadastro`, `ultima_atualizacao`) VALUES
(1, 'Camiseta Oversized Street', 'camiseta-oversized-street', 'Camiseta oversized premium com tecido 100% algodão. Design moderno e confortável.', 1, 129.90, 179.90, 45, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800', 1, 1, '2025-12-04 23:15:20', '2025-12-04 23:15:20'),
(2, 'Moletom Premium Black', 'moletom-premium-black', 'Moletom com capuz e bolso canguru. Tecido macio e quente.', 1, 249.90, NULL, 23, 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=800', 1, 1, '2025-12-04 23:15:20', '2025-12-04 23:15:20'),
(3, 'Calça Cargo Utility', 'calca-cargo-utility', 'Calça cargo com múltiplos bolsos funcionais. Design utilitário moderno.', 1, 189.90, NULL, 8, 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=800', 1, 0, '2025-12-04 23:15:20', '2025-12-04 23:15:20'),
(4, 'Jaqueta Bomber Vintage', 'jaqueta-bomber-vintage', 'Jaqueta bomber estilo vintage com acabamento premium.', 1, 299.90, 399.90, 0, 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=800', 1, 0, '2025-12-04 23:15:20', '2025-12-04 23:15:20'),
(5, 'Tênis Air Classic', 'tenis-air-classic', 'Tênis esportivo clássico com tecnologia de amortecimento.', 5, 599.90, NULL, 67, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800', 1, 1, '2025-12-04 23:15:20', '2025-12-04 23:15:20'),
(6, 'Boné Snapback Logo', 'bone-snapback-logo', 'Boné snapback com logo bordado. Ajuste regulável.', 4, 79.90, NULL, 120, 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=800', 1, 0, '2025-12-04 23:15:20', '2025-12-04 23:15:20');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto_cores`
--

DROP TABLE IF EXISTS `produto_cores`;
CREATE TABLE IF NOT EXISTS `produto_cores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produto_id` int(11) NOT NULL,
  `cor` varchar(50) NOT NULL,
  `codigo_hex` varchar(7) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_produto_cor` (`produto_id`,`cor`),
  KEY `idx_produto` (`produto_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produto_cores`
--

INSERT INTO `produto_cores` (`id`, `produto_id`, `cor`, `codigo_hex`) VALUES
(1, 1, 'Preto', '#000000'),
(2, 1, 'Branco', '#FFFFFF'),
(3, 1, 'Cinza', '#808080'),
(4, 2, 'Preto', '#000000'),
(5, 2, 'Cinza', '#808080'),
(6, 2, 'Azul Marinho', '#000080'),
(7, 3, 'Verde Militar', '#4B5320'),
(8, 3, 'Preto', '#000000'),
(9, 3, 'Bege', '#F5F5DC'),
(10, 5, 'Branco', '#FFFFFF'),
(11, 5, 'Preto', '#000000'),
(12, 6, 'Preto', '#000000'),
(13, 6, 'Branco', '#FFFFFF'),
(14, 6, 'Azul', '#0000FF');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto_imagens`
--

DROP TABLE IF EXISTS `produto_imagens`;
CREATE TABLE IF NOT EXISTS `produto_imagens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produto_id` int(11) NOT NULL,
  `url` varchar(500) NOT NULL,
  `ordem` int(11) DEFAULT 0,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_produto` (`produto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto_tamanhos`
--

DROP TABLE IF EXISTS `produto_tamanhos`;
CREATE TABLE IF NOT EXISTS `produto_tamanhos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produto_id` int(11) NOT NULL,
  `tamanho` varchar(10) NOT NULL,
  `estoque` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_produto_tamanho` (`produto_id`,`tamanho`),
  KEY `idx_produto` (`produto_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produto_tamanhos`
--

INSERT INTO `produto_tamanhos` (`id`, `produto_id`, `tamanho`, `estoque`) VALUES
(1, 1, 'P', 10),
(2, 1, 'M', 15),
(3, 1, 'G', 12),
(4, 1, 'GG', 8),
(5, 2, 'P', 5),
(6, 2, 'M', 8),
(7, 2, 'G', 7),
(8, 2, 'GG', 3),
(9, 3, '38', 2),
(10, 3, '40', 2),
(11, 3, '42', 2),
(12, 3, '44', 2),
(13, 5, '38', 10),
(14, 5, '39', 12),
(15, 5, '40', 15),
(16, 5, '41', 15),
(17, 5, '42', 10),
(18, 5, '43', 5),
(19, 6, 'Único', 120);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `tipo` enum('cliente','admin') DEFAULT 'cliente',
  `ativo` tinyint(1) DEFAULT 1,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultima_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cpf` (`cpf`),
  KEY `idx_email` (`email`),
  KEY `idx_tipo` (`tipo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `telefone`, `cpf`, `tipo`, `ativo`, `data_cadastro`, `ultima_atualizacao`) VALUES
(1, 'Administrador', 'admin@novamoda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'admin', 1, '2025-12-04 23:15:20', '2025-12-04 23:15:20'),
(2, 'João Silva', 'joao@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'cliente', 1, '2025-12-04 23:15:20', '2025-12-04 23:15:20'),
(3, 'Maria Santos', 'maria@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'cliente', 1, '2025-12-04 23:15:20', '2025-12-04 23:15:20'),
(4, 'Carlos Oliveira', 'carlos@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'cliente', 1, '2025-12-04 23:15:20', '2025-12-04 23:15:20');

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_pedidos_detalhes`
-- (Veja abaixo para a visão atual)
--
DROP VIEW IF EXISTS `vw_pedidos_detalhes`;
CREATE TABLE IF NOT EXISTS `vw_pedidos_detalhes` (
`id` int(11)
,`numero_pedido` varchar(20)
,`data_pedido` timestamp
,`status` enum('pendente','processando','enviado','entregue','cancelado')
,`total` decimal(10,2)
,`cliente_nome` varchar(100)
,`cliente_email` varchar(150)
,`total_itens` bigint(21)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_produtos_avaliacoes`
-- (Veja abaixo para a visão atual)
--
DROP VIEW IF EXISTS `vw_produtos_avaliacoes`;
CREATE TABLE IF NOT EXISTS `vw_produtos_avaliacoes` (
`id` int(11)
,`nome` varchar(200)
,`slug` varchar(200)
,`descricao` text
,`categoria_id` int(11)
,`preco` decimal(10,2)
,`preco_antigo` decimal(10,2)
,`estoque` int(11)
,`imagem_principal` varchar(500)
,`ativo` tinyint(1)
,`destaque` tinyint(1)
,`data_cadastro` timestamp
,`ultima_atualizacao` timestamp
,`nota_media` decimal(14,4)
,`total_avaliacoes` bigint(21)
);

-- --------------------------------------------------------

--
-- Estrutura para view `vw_pedidos_detalhes`
--
DROP TABLE IF EXISTS `vw_pedidos_detalhes`;

DROP VIEW IF EXISTS `vw_pedidos_detalhes`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_pedidos_detalhes`  AS SELECT `pd`.`id` AS `id`, `pd`.`numero_pedido` AS `numero_pedido`, `pd`.`data_pedido` AS `data_pedido`, `pd`.`status` AS `status`, `pd`.`total` AS `total`, `u`.`nome` AS `cliente_nome`, `u`.`email` AS `cliente_email`, count(`pi`.`id`) AS `total_itens` FROM ((`pedidos` `pd` join `usuarios` `u` on(`pd`.`usuario_id` = `u`.`id`)) left join `pedido_itens` `pi` on(`pd`.`id` = `pi`.`pedido_id`)) GROUP BY `pd`.`id` ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_produtos_avaliacoes`
--
DROP TABLE IF EXISTS `vw_produtos_avaliacoes`;

DROP VIEW IF EXISTS `vw_produtos_avaliacoes`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_produtos_avaliacoes`  AS SELECT `p`.`id` AS `id`, `p`.`nome` AS `nome`, `p`.`slug` AS `slug`, `p`.`descricao` AS `descricao`, `p`.`categoria_id` AS `categoria_id`, `p`.`preco` AS `preco`, `p`.`preco_antigo` AS `preco_antigo`, `p`.`estoque` AS `estoque`, `p`.`imagem_principal` AS `imagem_principal`, `p`.`ativo` AS `ativo`, `p`.`destaque` AS `destaque`, `p`.`data_cadastro` AS `data_cadastro`, `p`.`ultima_atualizacao` AS `ultima_atualizacao`, coalesce(avg(`a`.`nota`),0) AS `nota_media`, count(`a`.`id`) AS `total_avaliacoes` FROM (`produtos` `p` left join `avaliacoes` `a` on(`p`.`id` = `a`.`produto_id` and `a`.`aprovada` = 1)) GROUP BY `p`.`id` ;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `avaliacao_imagens`
--
ALTER TABLE `avaliacao_imagens`
  ADD CONSTRAINT `avaliacao_imagens_ibfk_1` FOREIGN KEY (`avaliacao_id`) REFERENCES `avaliacoes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `avaliacao_util`
--
ALTER TABLE `avaliacao_util`
  ADD CONSTRAINT `avaliacao_util_ibfk_1` FOREIGN KEY (`avaliacao_id`) REFERENCES `avaliacoes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avaliacao_util_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD CONSTRAINT `avaliacoes_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avaliacoes_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `carrinhos`
--
ALTER TABLE `carrinhos`
  ADD CONSTRAINT `carrinhos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `carrinho_itens`
--
ALTER TABLE `carrinho_itens`
  ADD CONSTRAINT `carrinho_itens_ibfk_1` FOREIGN KEY (`carrinho_id`) REFERENCES `carrinhos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carrinho_itens_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `enderecos`
--
ALTER TABLE `enderecos`
  ADD CONSTRAINT `enderecos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `favoritos`
--
ALTER TABLE `favoritos`
  ADD CONSTRAINT `favoritos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favoritos_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `logs_sistema`
--
ALTER TABLE `logs_sistema`
  ADD CONSTRAINT `logs_sistema_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`endereco_id`) REFERENCES `enderecos` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `pedido_itens`
--
ALTER TABLE `pedido_itens`
  ADD CONSTRAINT `pedido_itens_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedido_itens_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);

--
-- Restrições para tabelas `produtos`
--
ALTER TABLE `produtos`
  ADD CONSTRAINT `produtos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `produto_cores`
--
ALTER TABLE `produto_cores`
  ADD CONSTRAINT `produto_cores_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `produto_imagens`
--
ALTER TABLE `produto_imagens`
  ADD CONSTRAINT `produto_imagens_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `produto_tamanhos`
--
ALTER TABLE `produto_tamanhos`
  ADD CONSTRAINT `produto_tamanhos_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
