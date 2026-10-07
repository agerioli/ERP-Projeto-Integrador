-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql208.infinityfree.com
-- Tempo de geração: 16/09/2026 às 20:02
-- Versão do servidor: 11.4.13-MariaDB
-- Versão do PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `if0_41748767_projetointegrador`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Despejando dados para a tabela `baseinformacoes`
--



-- --------------------------------------------------------

--
-- Estrutura para tabela `devolucoes`
--

CREATE TABLE `devolucoes` (
  `id` int(11) NOT NULL,
  `cliente_cpf` char(11) NOT NULL,
  `data_solicitacao` date NOT NULL,
  `produto` varchar(120) NOT NULL,
  `tamanho` varchar(10) DEFAULT NULL,
  `motivo` enum('tamanho_errado','defeito','arrependimento','cor_modelo','outro') NOT NULL,
  `descricao` text DEFAULT NULL,
  `resolucao` enum('troca','reembolso','credito_loja','recusada','pendente') NOT NULL DEFAULT 'pendente',
  `status` enum('aguardando','em_analise','concluida','recusada','cancelada') NOT NULL DEFAULT 'aguardando',
  `data_resolucao` date DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `devolucoes`
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
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `encomendas`
--



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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `usuarios`
--



--
-- Índices de tabelas apagadas
--

--
-- Índices de tabela `baseinformacoes`
--
ALTER TABLE `baseinformacoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `CPF_UNIQUE` (`cpf`),
  ADD UNIQUE KEY `cpf` (`cpf`);

--
-- Índices de tabela `devolucoes`
--
ALTER TABLE `devolucoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dev_cpf` (`cliente_cpf`);

--
-- Índices de tabela `encomendas`
--
ALTER TABLE `encomendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_enc_cpf` (`cliente_cpf`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas apagadas
--

--
-- AUTO_INCREMENT de tabela `baseinformacoes`
--
ALTER TABLE `baseinformacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `devolucoes`
--
ALTER TABLE `devolucoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `encomendas`
--
ALTER TABLE `encomendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

