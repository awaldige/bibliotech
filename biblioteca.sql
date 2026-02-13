-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Tempo de geração: 13/02/2026 às 21:05
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
-- Banco de dados: `biblioteca`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `autores`
--

CREATE TABLE `autores` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `nacionalidade` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `autores`
--

INSERT INTO `autores` (`id`, `nome`, `nacionalidade`) VALUES
(1, 'Machado de Assis', 'Brasileiro'),
(2, 'Maria Dueñas', 'Espanhola'),
(3, 'franz kafka', NULL),
(4, 'Clarice Lispector', 'brasileira'),
(5, 'Danielle Steel', NULL),
(6, 'Agatha Christie', NULL),
(7, 'Sidney Sheldon', NULL),
(8, 'Nora Roberts	', 'Estados Unidense'),
(9, 'Stephen King', NULL),
(10, 'John Grisham', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `editoras`
--

CREATE TABLE `editoras` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `localizacao` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `editoras`
--

INSERT INTO `editoras` (`id`, `nome`, `localizacao`) VALUES
(1, 'Companhia das Letras', 'São Paulo,Brasil'),
(2, 'Editora Planeta do Brasil', ' São Paulo, Brasil. '),
(3, 'Editora Arqueiro', 'São Paulo, Brasil'),
(4, ' Editora Suma', 'Rio de Janeiro,Brasil'),
(5, 'Editorial Record', 'Rio de Janeiro,Brasil'),
(6, 'Editora Rocco', 'Rio de Janeiro,Brasil'),
(7, 'Editora Sextante', 'Rio de Janeiro; Brasil');

-- --------------------------------------------------------

--
-- Estrutura para tabela `emprestimos`
--

CREATE TABLE `emprestimos` (
  `id` int(11) NOT NULL,
  `id_livro` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `data_emprestimo` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_devolucao_prevista` date DEFAULT NULL,
  `data_devolucao_real` datetime DEFAULT NULL,
  `status` enum('ativo','devolvido','atrasado') DEFAULT 'ativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `emprestimos`
--

INSERT INTO `emprestimos` (`id`, `id_livro`, `id_usuario`, `data_emprestimo`, `data_devolucao_prevista`, `data_devolucao_real`, `status`) VALUES
(4, 7, 2, '2026-02-11 15:36:51', '2026-02-18', '2026-02-11 12:39:11', 'devolvido'),
(5, 7, 2, '2026-02-11 15:39:27', '2026-02-20', NULL, ''),
(6, 6, 2, '2026-02-11 15:39:45', '2026-02-20', NULL, ''),
(11, 4, 2, '2026-02-11 18:27:59', '2026-02-18', NULL, ''),
(12, 4, 2, '2026-02-11 18:28:21', '2026-02-18', NULL, ''),
(13, 9, 3, '2026-02-11 18:51:57', '2026-02-18', '2026-02-11 16:02:50', 'devolvido'),
(14, 4, 2, '2026-02-11 19:02:33', '2026-02-18', '2026-02-11 16:17:47', 'devolvido'),
(15, 6, 3, '2026-02-11 19:10:54', '2026-02-18', '2026-02-11 16:17:44', 'devolvido'),
(16, 1, 2, '2026-02-11 19:17:21', '2026-02-18', '2026-02-11 16:17:41', 'devolvido'),
(17, 3, 3, '2026-02-11 19:43:46', '2026-02-18', NULL, ''),
(18, 2, 2, '2026-02-11 19:50:08', '2026-02-18', NULL, ''),
(19, 7, 2, '2026-02-13 14:10:05', '2026-02-20', '2026-02-13 11:11:08', 'devolvido'),
(20, 2, 4, '2026-02-13 14:15:50', '2026-02-20', NULL, ''),
(21, 6, 3, '2026-02-13 18:21:49', '2026-02-20', NULL, ''),
(22, 9, 3, '2026-02-13 18:43:00', '2026-02-20', NULL, ''),
(23, 10, 2, '2026-02-13 18:57:19', '2026-02-20', NULL, ''),
(24, 9, 2, '2026-02-13 19:04:18', '2026-02-20', NULL, ''),
(25, 4, 2, '2026-02-13 19:04:53', '2026-02-20', NULL, ''),
(26, 7, 3, '2026-02-13 22:08:24', '2026-02-20', '2026-02-13 15:16:44', 'devolvido'),
(27, 5, 6, '2026-02-13 22:18:26', '2026-02-20', NULL, '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `exemplares`
--
-- Erro ao ler a estrutura para a tabela biblioteca.exemplares: #1932 - Table &#039;biblioteca.exemplares&#039; doesn&#039;t exist in engine
-- Erro ao ler dados para tabela biblioteca.exemplares: #1064 - Você tem um erro de sintaxe no seu SQL próximo a &#039;FROM `biblioteca`.`exemplares`&#039; na linha 1

-- --------------------------------------------------------

--
-- Estrutura para tabela `generos`
--
-- Erro ao ler a estrutura para a tabela biblioteca.generos: #1932 - Table &#039;biblioteca.generos&#039; doesn&#039;t exist in engine
-- Erro ao ler dados para tabela biblioteca.generos: #1064 - Você tem um erro de sintaxe no seu SQL próximo a &#039;FROM `biblioteca`.`generos`&#039; na linha 1

-- --------------------------------------------------------

--
-- Estrutura para tabela `idiomas`
--
-- Erro ao ler a estrutura para a tabela biblioteca.idiomas: #1932 - Table &#039;biblioteca.idiomas&#039; doesn&#039;t exist in engine
-- Erro ao ler dados para tabela biblioteca.idiomas: #1064 - Você tem um erro de sintaxe no seu SQL próximo a &#039;FROM `biblioteca`.`idiomas`&#039; na linha 1

-- --------------------------------------------------------

--
-- Estrutura para tabela `livros`
--

CREATE TABLE `livros` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `id_autor` int(11) DEFAULT NULL,
  `id_editora` int(11) DEFAULT NULL,
  `ano_publicacao` int(11) DEFAULT NULL,
  `quantidade` int(11) DEFAULT 0,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `livros`
--

INSERT INTO `livros` (`id`, `titulo`, `isbn`, `id_autor`, `id_editora`, `ano_publicacao`, `quantidade`, `data_cadastro`) VALUES
(1, 'A melhor história está por vir ', '01', 2, 2, 2012, 1, '2026-02-09 18:36:53'),
(2, 'Dom casmurro', '02', 1, 1, 1900, 1, '2026-02-09 18:36:53'),
(3, 'Destino. La Templanza', '', 2, 2, 2022, 1, '2026-02-09 18:36:53'),
(4, 'O Tempo Entre Costuras', '04', 2, 2, 2022, 1, '2026-02-09 18:36:53'),
(5, 'A firma', '05', 10, 3, 2022, 1, '2026-02-09 18:36:53'),
(6, 'A lista do juiz ', '06', 10, 3, 2022, 1, '2026-02-09 18:36:53'),
(7, 'It: A coisa', '07', 9, 4, 2014, 1, '2026-02-09 18:36:53'),
(8, 'Louca obsessão', '08', 9, 4, 2015, 1, '2026-02-09 18:36:53'),
(9, 'A amante', '09', 5, 5, 2019, 1, '2026-02-09 18:36:53'),
(10, 'O apelo do amor', '10', 5, 5, 1986, 2, '2026-02-09 18:36:53');

-- --------------------------------------------------------

--
-- Estrutura para tabela `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_livro` int(11) NOT NULL,
  `data_reserva` datetime DEFAULT current_timestamp(),
  `status` enum('pendente','concluida','cancelada') DEFAULT 'pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `reservas`
--

INSERT INTO `reservas` (`id`, `id_usuario`, `id_livro`, `data_reserva`, `status`) VALUES
(3, 2, 4, '2026-02-11 15:05:15', 'concluida'),
(4, 2, 4, '2026-02-11 15:27:49', 'concluida'),
(5, 2, 4, '2026-02-11 15:29:03', 'concluida'),
(6, 2, 1, '2026-02-11 16:11:20', 'concluida'),
(7, 2, 2, '2026-02-11 16:22:53', 'concluida'),
(8, 3, 3, '2026-02-11 16:40:47', 'concluida'),
(9, 2, 7, '2026-02-11 16:44:44', 'concluida'),
(11, 4, 2, '2026-02-11 16:56:12', 'concluida'),
(12, 5, 5, '2026-02-11 16:56:22', 'pendente'),
(13, 5, 5, '2026-02-13 12:21:41', 'pendente'),
(14, 4, 2, '2026-02-13 14:54:12', 'pendente');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `telefone`, `data_cadastro`) VALUES
(2, 'André Waldige', 'awaldige@email.com', '11978587468', '2026-02-11 14:24:32'),
(3, 'Ana Claudia Rosa', 'aclaurosa@email.com', '11982365478', '2026-02-11 18:38:36'),
(4, 'Ida Cori Wal ', 'icwal@email.com', '11974578345', '2026-02-11 19:53:45'),
(5, 'Vlad Vamp Wal', 'vladvamp@email.com', '11987452143', '2026-02-11 19:55:26'),
(6, 'Luis Carlos Santos', 'lcarlossantos@email.com', '11874521365', '2026-02-13 18:16:16');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `autores`
--
ALTER TABLE `autores`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `editoras`
--
ALTER TABLE `editoras`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `emprestimos`
--
ALTER TABLE `emprestimos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_livro` (`id_livro`),
  ADD KEY `id_leitor` (`id_usuario`);

--
-- Índices de tabela `livros`
--
ALTER TABLE `livros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_autor_livro` (`id_autor`),
  ADD KEY `fk_editora_livro` (`id_editora`);

--
-- Índices de tabela `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_livro` (`id_livro`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `autores`
--
ALTER TABLE `autores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `editoras`
--
ALTER TABLE `editoras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `emprestimos`
--
ALTER TABLE `emprestimos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `livros`
--
ALTER TABLE `livros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `emprestimos`
--
ALTER TABLE `emprestimos`
  ADD CONSTRAINT `emprestimos_ibfk_1` FOREIGN KEY (`id_livro`) REFERENCES `livros` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `emprestimos_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `livros`
--
ALTER TABLE `livros`
  ADD CONSTRAINT `fk_autor_livro` FOREIGN KEY (`id_autor`) REFERENCES `autores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_editora_livro` FOREIGN KEY (`id_editora`) REFERENCES `editoras` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`id_livro`) REFERENCES `livros` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
