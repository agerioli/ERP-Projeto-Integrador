-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 04/10/2026 às 02:39
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `projetointegrador_dev`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `baseinformacoes`
--

CREATE TABLE `baseinformacoes` (
  `id` int(11) NOT NULL,
  `autorizacao` varchar(45) NOT NULL,
  `cpf` char(11) NOT NULL,
  `nome_completo` varchar(100) NOT NULL,
  `sexo` varchar(45) NOT NULL,
  `data_de_nascimento` date NOT NULL,
  `cep` char(8) NOT NULL,
  `endereco` varchar(100) NOT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) NOT NULL,
  `cidade` varchar(100) NOT NULL,
  `estado` varchar(100) NOT NULL,
  `e_mail` varchar(100) NOT NULL,
  `celular_whatsapp` char(11) NOT NULL,
  `forma_de_pagamento_preferida` varchar(45) NOT NULL,
  `cor_preferida` varchar(45) NOT NULL,
  `tecido_preferido` varchar(45) NOT NULL,
  `tamanho_de_camiseta` varchar(45) NOT NULL,
  `tamanho_de_calca` varchar(45) NOT NULL,
  `tamanho_de_sapato` varchar(45) NOT NULL,
  `insertdate` datetime NOT NULL DEFAULT current_timestamp(),
  `expiredate` datetime DEFAULT NULL,
  `updatedate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `baseinformacoes`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias_produtos`
--

CREATE TABLE `categorias_produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `status` enum('ATIVO','INATIVO') NOT NULL DEFAULT 'ATIVO',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `numero` varchar(40) DEFAULT NULL,
  `fornecedor_id` int(11) DEFAULT NULL,
  `fornecedor_produto_id` int(11) DEFAULT NULL,
  `data_compra` date NOT NULL,
  `data_prevista_entrega` date DEFAULT NULL,
  `data_recebimento` date DEFAULT NULL,
  `status` enum('RASCUNHO','PEDIDA','PARCIAL','RECEBIDA','CANCELADA') NOT NULL DEFAULT 'RASCUNHO',
  `tipo` enum('PRODUTO','SUPRIMENTO','MISTA') NOT NULL DEFAULT 'PRODUTO',
  `valor_frete` decimal(12,2) NOT NULL DEFAULT 0.00,
  `desconto` decimal(12,2) NOT NULL DEFAULT 0.00,
  `observacoes` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `compras`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `compra_itens`
--

CREATE TABLE `compra_itens` (
  `id` int(11) NOT NULL,
  `compra_id` int(11) NOT NULL,
  `tipo_item` enum('PRODUTO','SUPRIMENTO') NOT NULL,
  `produto_id` int(11) DEFAULT NULL,
  `suprimento_id` int(11) DEFAULT NULL,
  `quantidade` decimal(12,2) NOT NULL,
  `quantidade_recebida` decimal(12,2) NOT NULL DEFAULT 0.00,
  `preco_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `desconto` decimal(12,2) NOT NULL DEFAULT 0.00,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `compra_itens`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `configuracoes_sistema`
--

CREATE TABLE `configuracoes_sistema` (
  `id` int(11) NOT NULL,
  `chave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `configuracoes_sistema`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `consumos_materiais`
--

CREATE TABLE `consumos_materiais` (
  `id` int(10) UNSIGNED NOT NULL,
  `material_id` int(11) NOT NULL,
  `data_uso` date NOT NULL,
  `quantidade` decimal(15,3) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `consumos_materiais`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `demandas_compra`
--

CREATE TABLE `demandas_compra` (
  `id` int(11) NOT NULL,
  `numero` varchar(40) DEFAULT NULL,
  `data_solicitacao` date NOT NULL,
  `solicitante_id` int(11) DEFAULT NULL,
  `prioridade` enum('BAIXA','NORMAL','ALTA','URGENTE') NOT NULL DEFAULT 'NORMAL',
  `status` enum('RASCUNHO','PENDENTE','EM_COTACAO','APROVADA','COMPRADA','CANCELADA') NOT NULL DEFAULT 'RASCUNHO',
  `origem` enum('MANUAL','ESTOQUE_BAIXO','SUGESTAO_SISTEMA') NOT NULL DEFAULT 'MANUAL',
  `observacoes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `demanda_compra_itens`
--

CREATE TABLE `demanda_compra_itens` (
  `id` int(11) NOT NULL,
  `demanda_id` int(11) NOT NULL,
  `tipo_item` enum('PRODUTO','SUPRIMENTO') NOT NULL,
  `produto_id` int(11) DEFAULT NULL,
  `suprimento_id` int(11) DEFAULT NULL,
  `quantidade_sugerida` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quantidade_solicitada` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fornecedor_id` int(11) DEFAULT NULL,
  `prazo_entrega_dias` int(11) DEFAULT NULL,
  `preco_estimado` decimal(12,2) DEFAULT NULL,
  `justificativa` varchar(255) DEFAULT NULL,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `devolucoes`
--

CREATE TABLE `devolucoes` (
  `id` int(11) NOT NULL,
  `numero` varchar(50) NOT NULL,
  `venda_id` int(11) DEFAULT NULL,
  `data_devolucao` datetime NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `status` enum('PENDENTE','RECEBIDA','ANALISADA','FINALIZADA','CANCELADA') NOT NULL DEFAULT 'PENDENTE',
  `observacoes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `devolucoes_produtos`
--

CREATE TABLE `devolucoes_produtos` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `data_devolucao` date NOT NULL,
  `quantidade` decimal(12,2) NOT NULL,
  `origem` varchar(30) NOT NULL DEFAULT 'MERCADO_LIVRE',
  `motivo` varchar(100) NOT NULL,
  `observacoes` varchar(255) DEFAULT NULL,
  `avaria` tinyint(1) NOT NULL DEFAULT 0,
  `destino` enum('ESTOQUE_LOCAL','PERDA') NOT NULL,
  `valor_custo_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `valor_perda` decimal(12,2) NOT NULL DEFAULT 0.00,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `valor_unitario_custo` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `devolucoes_produtos`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `encomendas`
--

CREATE TABLE `encomendas` (
  `id` int(11) NOT NULL,
  `cliente_cpf` char(11) NOT NULL,
  `produto_descricao` varchar(200) NOT NULL,
  `tamanho` varchar(10) DEFAULT NULL,
  `cor` varchar(45) DEFAULT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 1,
  `valor_sinal` decimal(10,2) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `data_pedido` date NOT NULL,
  `data_prevista` date NOT NULL,
  `data_entrega_real` date DEFAULT NULL,
  `status` enum('aguardando','em_producao','pronto','entregue','cancelado') NOT NULL DEFAULT 'aguardando',
  `observacoes` mediumtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `encomendas`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque`
--

CREATE TABLE `estoque` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `local_estoque` enum('FISICO','MERCADO_LIVRE') NOT NULL DEFAULT 'FISICO',
  `quantidade` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quantidade_reservada` decimal(12,2) NOT NULL DEFAULT 0.00,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `estoque`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `fornecedores`
--

CREATE TABLE `fornecedores` (
  `id` int(11) NOT NULL,
  `razao_social` varchar(150) NOT NULL,
  `nome_fantasia` varchar(150) DEFAULT NULL,
  `cnpj` varchar(18) DEFAULT NULL,
  `tipo_fornecimento` enum('PRODUTO','MATERIAL','AMBOS') NOT NULL DEFAULT 'PRODUTO',
  `contato` varchar(100) DEFAULT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `cep` varchar(9) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` char(2) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `status` enum('ATIVO','INATIVO') NOT NULL DEFAULT 'ATIVO',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fornecedores`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `fornecedores_produtos`
--

CREATE TABLE `fornecedores_produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `status` enum('ATIVO','INATIVO') NOT NULL DEFAULT 'ATIVO',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fornecedores_produtos`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `fornecedor_produtos`
--

CREATE TABLE `fornecedor_produtos` (
  `id` int(11) NOT NULL,
  `fornecedor_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `codigo_fornecedor` varchar(80) DEFAULT NULL,
  `preco_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `prazo_entrega_dias` int(11) NOT NULL DEFAULT 0,
  `quantidade_minima` decimal(12,2) NOT NULL DEFAULT 1.00,
  `fornecedor_preferencial` tinyint(1) NOT NULL DEFAULT 0,
  `observacoes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fornecedor_produtos`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `fornecedor_suprimentos`
--

CREATE TABLE `fornecedor_suprimentos` (
  `id` int(11) NOT NULL,
  `fornecedor_id` int(11) NOT NULL,
  `suprimento_id` int(11) NOT NULL,
  `codigo_fornecedor` varchar(80) DEFAULT NULL,
  `preco_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `prazo_entrega_dias` int(11) NOT NULL DEFAULT 0,
  `quantidade_minima` decimal(12,2) NOT NULL DEFAULT 1.00,
  `fornecedor_preferencial` tinyint(1) NOT NULL DEFAULT 0,
  `observacoes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fornecedor_suprimentos`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `movimentacoes_estoque`
--

CREATE TABLE `movimentacoes_estoque` (
  `id` bigint(20) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `local_estoque` enum('FISICO','MERCADO_LIVRE') NOT NULL DEFAULT 'FISICO',
  `tipo` varchar(40) NOT NULL,
  `quantidade` decimal(12,2) NOT NULL,
  `estoque_anterior` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estoque_posterior` decimal(12,2) NOT NULL DEFAULT 0.00,
  `motivo` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `referencia_tipo` varchar(50) DEFAULT NULL,
  `referencia_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `movimentacoes_estoque`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `movimentacoes_suprimentos`
--

CREATE TABLE `movimentacoes_suprimentos` (
  `id` bigint(20) NOT NULL,
  `suprimento_id` int(11) NOT NULL,
  `tipo` varchar(40) NOT NULL,
  `quantidade` decimal(12,2) NOT NULL,
  `estoque_anterior` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estoque_posterior` decimal(12,2) NOT NULL DEFAULT 0.00,
  `motivo` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `movimentacoes_suprimentos`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `sku` varchar(80) DEFAULT NULL,
  `nome` varchar(150) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `unidade_medida` varchar(20) NOT NULL DEFAULT 'UN',
  `preco_custo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `preco_venda` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estoque_minimo_local` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estoque_minimo_mercado_livre` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fornecedor_produto_id` int(11) DEFAULT NULL,
  `fornecedor_id` int(11) DEFAULT NULL,
  `estoque_minimo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estoque_seguranca` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ponto_reposicao` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estoque_maximo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('ATIVO','INATIVO') NOT NULL DEFAULT 'ATIVO',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `recebimentos`
--

CREATE TABLE `recebimentos` (
  `id` int(11) NOT NULL,
  `compra_id` int(11) NOT NULL,
  `data_recebimento` date NOT NULL,
  `numero_nf` varchar(60) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `recebimento_itens`
--

CREATE TABLE `recebimento_itens` (
  `id` int(11) NOT NULL,
  `recebimento_id` int(11) NOT NULL,
  `compra_item_id` int(11) NOT NULL,
  `quantidade_recebida` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quantidade_aceita` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quantidade_avariada` decimal(12,2) NOT NULL DEFAULT 0.00,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `suprimentos`
--

CREATE TABLE `suprimentos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `unidade_medida` varchar(20) NOT NULL DEFAULT 'UN',
  `estoque_minimo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estoque_seguranca` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ponto_reposicao` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estoque_maximo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estoque_atual` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('ATIVO','INATIVO') NOT NULL DEFAULT 'ATIVO',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `suprimentos`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `transferencias_mercado_livre`
--

CREATE TABLE `transferencias_mercado_livre` (
  `id` int(10) UNSIGNED NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` decimal(15,3) NOT NULL,
  `data_transferencia` date NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `insertdate` datetime DEFAULT current_timestamp(),
  `expiredate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas`
--

CREATE TABLE `vendas` (
  `id` int(11) NOT NULL,
  `numero` varchar(60) DEFAULT NULL,
  `canal` enum('MERCADO_LIVRE','LOCAL','OUTRO') NOT NULL DEFAULT 'LOCAL',
  `data_venda` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('PENDENTE','PAGA','ENVIADA','CONCLUIDA','CANCELADA') NOT NULL DEFAULT 'PENDENTE',
  `valor_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `observacoes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `vendas`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `venda_itens`
--

CREATE TABLE `venda_itens` (
  `id` int(11) NOT NULL,
  `venda_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` decimal(12,2) NOT NULL,
  `preco_unitario` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `venda_itens`
--



--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `baseinformacoes`
--
ALTER TABLE `baseinformacoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `CPF_UNIQUE` (`cpf`),
  ADD UNIQUE KEY `cpf` (`cpf`);

--
-- Índices de tabela `categorias_produtos`
--
ALTER TABLE `categorias_produtos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_categoria_nome` (`nome`);

--
-- Índices de tabela `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_compra_fornecedor` (`fornecedor_id`),
  ADD KEY `idx_compra_data` (`data_compra`),
  ADD KEY `idx_compra_status` (`status`),
  ADD KEY `idx_compra_fornecedor_produto` (`fornecedor_produto_id`),
  ADD KEY `idx_compra_fornecedor_v03` (`fornecedor_id`),
  ADD KEY `fk_compra_usuario` (`usuario_id`);

--
-- Índices de tabela `compra_itens`
--
ALTER TABLE `compra_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ci_compra` (`compra_id`),
  ADD KEY `idx_ci_produto` (`produto_id`),
  ADD KEY `idx_ci_suprimento` (`suprimento_id`);

--
-- Índices de tabela `configuracoes_sistema`
--
ALTER TABLE `configuracoes_sistema`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_config_chave` (`chave`);

--
-- Índices de tabela `consumos_materiais`
--
ALTER TABLE `consumos_materiais`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_consumos_material` (`material_id`),
  ADD KEY `idx_consumos_data` (`data_uso`),
  ADD KEY `fk_consumo_usuario` (`usuario_id`);

--
-- Índices de tabela `demandas_compra`
--
ALTER TABLE `demandas_compra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_demanda_status` (`status`),
  ADD KEY `idx_demanda_data` (`data_solicitacao`);

--
-- Índices de tabela `demanda_compra_itens`
--
ALTER TABLE `demanda_compra_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dci_demanda` (`demanda_id`),
  ADD KEY `idx_dci_produto` (`produto_id`),
  ADD KEY `idx_dci_suprimento` (`suprimento_id`),
  ADD KEY `fk_dci_fornecedor` (`fornecedor_id`);

--
-- Índices de tabela `devolucoes`
--
ALTER TABLE `devolucoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_devolucao_numero` (`numero`),
  ADD KEY `idx_devolucao_data` (`data_devolucao`),
  ADD KEY `idx_devolucao_status` (`status`),
  ADD KEY `idx_devolucao_venda` (`venda_id`);

--
-- Índices de tabela `devolucoes_produtos`
--
ALTER TABLE `devolucoes_produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dp_produto` (`produto_id`),
  ADD KEY `idx_dp_data` (`data_devolucao`),
  ADD KEY `idx_dp_destino` (`destino`);

--
-- Índices de tabela `encomendas`
--
ALTER TABLE `encomendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_enc_cpf` (`cliente_cpf`);

--
-- Índices de tabela `estoque`
--
ALTER TABLE `estoque`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_estoque_produto_local` (`produto_id`,`local_estoque`),
  ADD KEY `idx_estoque_local` (`local_estoque`);

--
-- Índices de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fornecedor_cnpj` (`cnpj`),
  ADD KEY `idx_fornecedor_status` (`status`),
  ADD KEY `idx_fornecedor_cep` (`cep`);

--
-- Índices de tabela `fornecedores_produtos`
--
ALTER TABLE `fornecedores_produtos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fornecedor_produto_nome` (`nome`),
  ADD KEY `idx_fornecedor_produto_status` (`status`);

--
-- Índices de tabela `fornecedor_produtos`
--
ALTER TABLE `fornecedor_produtos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fornecedor_produto` (`fornecedor_id`,`produto_id`),
  ADD KEY `idx_fp_produto` (`produto_id`);

--
-- Índices de tabela `fornecedor_suprimentos`
--
ALTER TABLE `fornecedor_suprimentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fornecedor_suprimento` (`fornecedor_id`,`suprimento_id`),
  ADD KEY `idx_fs_suprimento` (`suprimento_id`);

--
-- Índices de tabela `movimentacoes_estoque`
--
ALTER TABLE `movimentacoes_estoque`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mov_produto` (`produto_id`),
  ADD KEY `idx_mov_data` (`created_at`),
  ADD KEY `idx_mov_local` (`local_estoque`);

--
-- Índices de tabela `movimentacoes_suprimentos`
--
ALTER TABLE `movimentacoes_suprimentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ms_sup_data` (`suprimento_id`,`created_at`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_produto_categoria` (`categoria_id`),
  ADD KEY `idx_produto_fornecedor` (`fornecedor_id`),
  ADD KEY `idx_produto_fornecedor_produto` (`fornecedor_produto_id`),
  ADD KEY `idx_produto_codigo` (`codigo`),
  ADD KEY `idx_produto_sku` (`sku`);

--
-- Índices de tabela `recebimentos`
--
ALTER TABLE `recebimentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_receb_compra` (`compra_id`),
  ADD KEY `idx_receb_data` (`data_recebimento`);

--
-- Índices de tabela `recebimento_itens`
--
ALTER TABLE `recebimento_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ri_recebimento` (`recebimento_id`),
  ADD KEY `idx_ri_compra_item` (`compra_item_id`);

--
-- Índices de tabela `suprimentos`
--
ALTER TABLE `suprimentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_suprimento_codigo` (`codigo`),
  ADD KEY `idx_suprimento_status` (`status`);

--
-- Índices de tabela `transferencias_mercado_livre`
--
ALTER TABLE `transferencias_mercado_livre`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tml_produto` (`produto_id`),
  ADD KEY `fk_tml_usuario` (`usuario_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `vendas`
--
ALTER TABLE `vendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_venda_data` (`data_venda`),
  ADD KEY `idx_venda_canal` (`canal`),
  ADD KEY `idx_venda_status` (`status`);

--
-- Índices de tabela `venda_itens`
--
ALTER TABLE `venda_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vi_venda` (`venda_id`),
  ADD KEY `idx_vi_produto` (`produto_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `baseinformacoes`
--
ALTER TABLE `baseinformacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `categorias_produtos`
--
ALTER TABLE `categorias_produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `compra_itens`
--
ALTER TABLE `compra_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `configuracoes_sistema`
--
ALTER TABLE `configuracoes_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `consumos_materiais`
--
ALTER TABLE `consumos_materiais`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `demandas_compra`
--
ALTER TABLE `demandas_compra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `demanda_compra_itens`
--
ALTER TABLE `demanda_compra_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `devolucoes`
--
ALTER TABLE `devolucoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `devolucoes_produtos`
--
ALTER TABLE `devolucoes_produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `encomendas`
--
ALTER TABLE `encomendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `estoque`
--
ALTER TABLE `estoque`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `fornecedores_produtos`
--
ALTER TABLE `fornecedores_produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `fornecedor_produtos`
--
ALTER TABLE `fornecedor_produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `fornecedor_suprimentos`
--
ALTER TABLE `fornecedor_suprimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `movimentacoes_estoque`
--
ALTER TABLE `movimentacoes_estoque`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de tabela `movimentacoes_suprimentos`
--
ALTER TABLE `movimentacoes_suprimentos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `recebimentos`
--
ALTER TABLE `recebimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `recebimento_itens`
--
ALTER TABLE `recebimento_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `suprimentos`
--
ALTER TABLE `suprimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `transferencias_mercado_livre`
--
ALTER TABLE `transferencias_mercado_livre`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `venda_itens`
--
ALTER TABLE `venda_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `fk_compra_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`),
  ADD CONSTRAINT `fk_compra_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `compra_itens`
--
ALTER TABLE `compra_itens`
  ADD CONSTRAINT `fk_ci_compra` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ci_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`),
  ADD CONSTRAINT `fk_ci_suprimento` FOREIGN KEY (`suprimento_id`) REFERENCES `suprimentos` (`id`);

--
-- Restrições para tabelas `consumos_materiais`
--
ALTER TABLE `consumos_materiais`
  ADD CONSTRAINT `fk_consumo_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_consumos_material` FOREIGN KEY (`material_id`) REFERENCES `suprimentos` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `demanda_compra_itens`
--
ALTER TABLE `demanda_compra_itens`
  ADD CONSTRAINT `fk_dci_demanda` FOREIGN KEY (`demanda_id`) REFERENCES `demandas_compra` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dci_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_dci_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`),
  ADD CONSTRAINT `fk_dci_suprimento` FOREIGN KEY (`suprimento_id`) REFERENCES `suprimentos` (`id`);

--
-- Restrições para tabelas `devolucoes`
--
ALTER TABLE `devolucoes`
  ADD CONSTRAINT `fk_devolucao_venda` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `devolucoes_produtos`
--
ALTER TABLE `devolucoes_produtos`
  ADD CONSTRAINT `fk_dp_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);

--
-- Restrições para tabelas `estoque`
--
ALTER TABLE `estoque`
  ADD CONSTRAINT `fk_estoque_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `fornecedor_produtos`
--
ALTER TABLE `fornecedor_produtos`
  ADD CONSTRAINT `fk_fp_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fp_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `fornecedor_suprimentos`
--
ALTER TABLE `fornecedor_suprimentos`
  ADD CONSTRAINT `fk_fs_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fs_suprimento` FOREIGN KEY (`suprimento_id`) REFERENCES `suprimentos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `movimentacoes_estoque`
--
ALTER TABLE `movimentacoes_estoque`
  ADD CONSTRAINT `fk_mov_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);

--
-- Restrições para tabelas `movimentacoes_suprimentos`
--
ALTER TABLE `movimentacoes_suprimentos`
  ADD CONSTRAINT `fk_ms_suprimento` FOREIGN KEY (`suprimento_id`) REFERENCES `suprimentos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `produtos`
--
ALTER TABLE `produtos`
  ADD CONSTRAINT `fk_produto_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_produtos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `recebimentos`
--
ALTER TABLE `recebimentos`
  ADD CONSTRAINT `fk_receb_compra` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `recebimento_itens`
--
ALTER TABLE `recebimento_itens`
  ADD CONSTRAINT `fk_ri_compra_item` FOREIGN KEY (`compra_item_id`) REFERENCES `compra_itens` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ri_recebimento` FOREIGN KEY (`recebimento_id`) REFERENCES `recebimentos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `transferencias_mercado_livre`
--
ALTER TABLE `transferencias_mercado_livre`
  ADD CONSTRAINT `fk_tml_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tml_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `venda_itens`
--
ALTER TABLE `venda_itens`
  ADD CONSTRAINT `fk_vi_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`),
  ADD CONSTRAINT `fk_vi_venda` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

