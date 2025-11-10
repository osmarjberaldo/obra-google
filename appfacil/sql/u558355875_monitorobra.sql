-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 09/11/2025 às 22:41
-- Versão do servidor: 11.8.3-MariaDB-log
-- Versão do PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `u558355875_monitorobra`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `assinaturas`
--

CREATE TABLE `assinaturas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `plano_id` int(11) DEFAULT NULL COMMENT 'NULL para período trial',
  `data_inicio` datetime NOT NULL,
  `data_fim` datetime NOT NULL,
  `tipo_assinatura` enum('trial','basic','premium','profissional') NOT NULL DEFAULT 'trial',
  `status` enum('ativo','expirada','cancelada') NOT NULL DEFAULT 'ativo',
  `data_cancelamento` datetime DEFAULT NULL,
  `motivo_cancelamento` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `assinaturas`
--

INSERT INTO `assinaturas` (`id`, `usuario_id`, `plano_id`, `data_inicio`, `data_fim`, `tipo_assinatura`, `status`, `data_cancelamento`, `motivo_cancelamento`) VALUES
(12, 12, NULL, '2025-07-22 00:00:00', '2026-03-20 18:00:00', 'profissional', 'ativo', NULL, NULL),
(15, 15, NULL, '2025-09-04 10:53:42', '2025-12-12 11:04:38', 'profissional', '', NULL, NULL),
(17, 16, NULL, '2025-09-04 00:00:00', '2025-11-07 00:00:00', 'trial', 'ativo', NULL, NULL),
(25, 23, NULL, '2025-09-23 00:00:00', '2025-10-08 00:00:00', 'trial', 'expirada', NULL, NULL),
(26, 24, NULL, '2025-09-25 00:00:00', '2025-10-10 00:00:00', 'trial', 'expirada', NULL, NULL),
(27, 25, NULL, '2025-09-27 00:00:00', '2025-10-12 00:00:00', 'trial', 'expirada', NULL, NULL),
(28, 26, NULL, '2025-09-27 00:00:00', '2025-10-13 00:00:00', 'trial', 'expirada', NULL, NULL),
(29, 27, NULL, '2025-09-28 00:00:00', '2025-10-13 00:00:00', 'trial', 'expirada', NULL, NULL),
(30, 28, NULL, '2025-09-30 00:00:00', '2025-11-20 00:00:00', 'basic', 'ativo', NULL, NULL),
(31, 29, NULL, '2025-10-01 00:00:00', '2025-10-16 00:00:00', 'trial', 'expirada', NULL, NULL),
(32, 30, NULL, '2025-10-01 00:00:00', '2025-10-16 00:00:00', 'trial', 'expirada', NULL, NULL),
(33, 31, NULL, '2025-10-01 00:00:00', '2025-10-16 00:00:00', 'trial', 'expirada', NULL, NULL),
(34, 32, NULL, '2025-10-01 00:00:00', '2025-10-16 00:00:00', 'trial', 'expirada', NULL, NULL),
(35, 33, NULL, '2025-10-02 00:00:00', '2025-10-17 00:00:00', 'trial', 'expirada', NULL, NULL),
(36, 34, NULL, '2025-10-07 00:00:00', '2025-10-22 00:00:00', 'trial', 'expirada', NULL, NULL),
(37, 35, NULL, '2025-10-07 00:00:00', '2025-10-22 00:00:00', 'trial', 'expirada', NULL, NULL),
(38, 36, NULL, '2025-10-10 00:00:00', '2025-11-30 00:00:00', 'basic', 'ativo', NULL, NULL),
(39, 37, NULL, '2025-10-13 00:00:00', '2025-10-28 00:00:00', 'trial', 'expirada', NULL, NULL),
(40, 38, NULL, '2025-10-14 00:00:00', '2025-10-29 00:00:00', 'trial', 'expirada', NULL, NULL),
(41, 39, NULL, '2025-10-14 00:00:00', '2025-10-29 00:00:00', 'trial', 'expirada', NULL, NULL),
(42, 40, NULL, '2025-10-15 00:00:00', '2025-10-30 00:00:00', 'trial', 'expirada', NULL, NULL),
(43, 41, NULL, '2025-10-15 00:00:00', '2025-10-30 00:00:00', 'trial', 'expirada', NULL, NULL),
(44, 42, NULL, '2025-10-16 00:00:00', '2025-10-31 00:00:00', 'trial', 'expirada', NULL, NULL),
(45, 43, NULL, '2025-10-17 20:42:59', '2025-11-01 20:42:59', 'trial', 'ativo', NULL, NULL),
(47, 44, NULL, '2025-10-18 00:00:00', '2025-11-02 00:00:00', 'trial', 'ativo', NULL, NULL),
(49, 46, NULL, '2025-10-19 00:00:00', '2025-11-03 00:00:00', 'trial', 'expirada', NULL, NULL),
(50, 47, NULL, '2025-10-21 12:41:10', '2025-11-05 12:41:10', 'trial', 'ativo', NULL, NULL),
(51, 48, NULL, '2025-10-22 00:00:00', '2025-11-06 00:00:00', 'trial', 'ativo', NULL, NULL),
(52, 49, NULL, '2025-10-22 00:00:00', '2025-11-06 00:00:00', 'trial', 'ativo', NULL, NULL),
(53, 50, NULL, '2025-10-25 00:00:00', '2025-11-24 00:00:00', 'trial', 'ativo', NULL, NULL),
(54, 51, NULL, '2025-10-25 00:00:00', '2025-11-24 00:00:00', 'trial', 'ativo', NULL, NULL),
(55, 52, NULL, '2025-10-28 00:00:00', '2025-11-27 00:00:00', 'trial', 'ativo', NULL, NULL),
(56, 53, NULL, '2025-10-28 00:00:00', '2025-11-27 00:00:00', 'trial', 'ativo', NULL, NULL),
(57, 54, NULL, '2025-10-29 00:00:00', '2025-11-28 00:00:00', 'trial', 'ativo', NULL, NULL),
(58, 55, NULL, '2025-10-29 00:00:00', '2025-11-28 00:00:00', 'trial', 'ativo', NULL, NULL),
(59, 56, NULL, '2025-10-30 00:00:00', '2025-11-29 00:00:00', 'trial', 'ativo', NULL, NULL),
(60, 57, NULL, '2025-10-31 00:00:00', '2025-11-30 00:00:00', 'trial', 'ativo', NULL, NULL),
(61, 58, NULL, '2025-10-31 00:00:00', '2025-11-30 00:00:00', 'trial', 'ativo', NULL, NULL),
(62, 59, NULL, '2025-10-31 00:00:00', '2025-11-30 00:00:00', 'trial', 'ativo', NULL, NULL),
(63, 60, NULL, '2025-11-01 00:00:00', '2025-12-01 00:00:00', 'trial', 'ativo', NULL, NULL),
(65, 62, NULL, '2025-11-03 00:00:00', '2025-12-03 00:00:00', 'trial', 'ativo', NULL, NULL),
(66, 63, NULL, '2025-11-04 00:00:00', '2025-12-04 00:00:00', 'trial', 'ativo', NULL, NULL),
(68, 65, NULL, '2025-11-04 00:00:00', '2025-12-04 00:00:00', 'trial', 'ativo', NULL, NULL),
(69, 66, NULL, '2025-11-04 00:00:00', '2025-12-04 00:00:00', 'trial', 'ativo', NULL, NULL),
(70, 67, NULL, '2025-11-05 00:00:00', '2025-12-05 00:00:00', 'trial', 'ativo', NULL, NULL),
(72, 69, NULL, '2025-11-08 14:26:28', '2025-12-08 14:26:28', 'trial', 'ativo', NULL, NULL),
(73, 70, NULL, '2025-11-09 00:00:00', '2025-12-09 00:00:00', 'trial', 'ativo', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `cdAdm`
--

CREATE TABLE `cdAdm` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `token` varchar(64) DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `cdAdm`
--

INSERT INTO `cdAdm` (`id`, `username`, `email`, `password_hash`, `token`, `token_expires`, `created_at`, `updated_at`, `active`) VALUES
(1, 'admin', 'osmar@devosmar.com.br', '$2y$10$3mCkJgw8LbJQv1N6MHe94uz6w1h8kQrQ6oeWt6ignHhdnhaqbtM5O', '75c366eb285799e917cc37cb3b654c83d0f497ceaff11ce8d2d5cfd63319aeae', '2025-11-10 22:32:06', '2025-10-16 16:18:39', '2025-11-09 22:32:06', 1),
(2, 'matheus', 'matheusvianacosta1@outlook.com', '$2y$10$s5iE8d/oBltJzV7VDTkrQu9n3Sni5QIV9oqXwplsrErl9Sl7qevCa', NULL, NULL, '2025-10-16 19:38:48', '2025-11-06 10:50:29', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `checklist_categorias`
--

CREATE TABLE `checklist_categorias` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `data_criacao` datetime DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `checklist_categorias`
--

INSERT INTO `checklist_categorias` (`id`, `usuario_id`, `nome`, `descricao`, `status`, `data_criacao`, `data_atualizacao`) VALUES
(1, 15, 'Muro', NULL, 'ativo', '2025-11-06 00:45:27', '2025-11-06 00:45:27'),
(2, 15, 'Documentação e licenças', NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(3, 15, 'Segurança do trabalho (EHS / SST)', NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(4, 15, 'Canteiro de obras / infraestrutura', NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(5, 15, 'Materiais e suprimentos', NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(6, 15, 'Planejamento e cronograma', NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(7, 15, 'Terraplenagem e fundações', NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(8, 15, 'Estrutura (concreto & armadura)', NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(9, 15, 'Alvenaria, revestimentos e pisos', NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(10, 15, 'Impermeabilização e cobertura', NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(11, 15, 'Instalações hidráulicas e saneamento', NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(12, 15, 'Instalações elétricas e SPDA', NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(13, 15, 'Esquadrias, portas e janelas', NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(14, 15, 'Acabamentos (pintura, revestimento, louças)', NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(15, 15, 'Testes, comissionamento e inspeções finais', NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(16, 15, 'Entrega da obra / documentação final', NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(17, 15, 'Meio ambiente e gestão de resíduos', NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(18, 15, 'Máquinas, equipamentos e manutenção', NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(19, 15, 'Controle financeiro e medições', NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06');

-- --------------------------------------------------------

--
-- Estrutura para tabela `checklist_itens`
--

CREATE TABLE `checklist_itens` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `nome` text NOT NULL,
  `tipo` enum('texto','check') NOT NULL DEFAULT 'texto',
  `valor_texto` text DEFAULT NULL,
  `feito` tinyint(1) DEFAULT 0,
  `ordem` int(11) DEFAULT NULL,
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `data_criacao` datetime DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `checklist_itens`
--

INSERT INTO `checklist_itens` (`id`, `usuario_id`, `categoria_id`, `nome`, `tipo`, `valor_texto`, `feito`, `ordem`, `status`, `data_criacao`, `data_atualizacao`) VALUES
(5, 15, 1, 'test', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 14:28:19', '2025-11-06 14:28:19'),
(6, 15, 2, 'Projeto executivo disponível (plantas, cortes, detalhes)', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(7, 15, 2, 'ART/RRT do responsável técnico', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(8, 15, 2, 'Alvará de construção e licenças ambientais', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(9, 15, 2, 'Contratos, cronograma e ordens de serviço', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(10, 15, 2, 'CADASTRO de fornecedores e notas fiscais', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(11, 15, 3, 'CIPA/PCMSO/PPRA/PGR atualizados', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(12, 15, 3, 'EPCs e EPIs disponíveis e usados (capacetes, botas, luvas, óculos)', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(13, 15, 3, 'Sinalização e rotas de evacuação', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(14, 15, 3, 'Treinamento de segurança e registros de ASO', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(15, 15, 3, 'Extintores/brigada/inspeção de inflamáveis', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(16, 15, 4, 'Portaria, controle de acesso e cadastro de visitantes', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(17, 15, 4, 'Vestiário, refeitório e sanitários adequados', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(18, 15, 4, 'Armazenagem correta de materiais (sacas, tubulações)', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(19, 15, 4, 'Iluminação e circulação seguras', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(20, 15, 4, 'Limpeza/5S e sinalização de áreas perigosas', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(21, 15, 5, 'Conferência de notas vs. entrada física (quantidade/qualidade)', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:03', '2025-11-06 23:47:03'),
(22, 15, 5, 'Certificados de qualidade e ensaios (cimento, aço, argamassa)', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(23, 15, 5, 'Condição de armazenamento (coberto, paletizado)', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(24, 15, 5, 'Controle de perdas e desperdício', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(25, 15, 5, 'Materiais críticos com aprovação técnica antes do uso', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(26, 15, 6, 'Cronograma atualizado e responsáveis por cada etapa', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(27, 15, 6, 'Caminhos críticos identificados e ações mitigadoras', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(28, 15, 6, 'Relatório de progresso semanal', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(29, 15, 6, 'Controle de verba/medições e aprovação de medição', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(30, 15, 6, 'Reunião de obra com ATA registrada', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(31, 15, 7, 'Marcação/estacas conferidas com projeto', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(32, 15, 7, 'Verificação de sondagem/compactação do solo', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(33, 15, 7, 'Locais de fundação executados conforme projeto (cotas)', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(34, 15, 7, 'Drenagem provisória funcionando', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(35, 15, 7, 'Proteção de escavações e taludes', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(36, 15, 8, 'Armaduras conferidas antes do concretagem', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(37, 15, 8, 'Fôrmas, escoramentos e prumo verificados', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(38, 15, 8, 'Dosagem e traço do concreto controlados', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(39, 15, 8, 'Cura do concreto executada', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(40, 15, 8, 'Ensaios de corpo de prova e registro', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:04', '2025-11-06 23:47:04'),
(41, 15, 9, 'Alinhamento e prumo da alvenaria conferidos', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(42, 15, 9, 'Argamassas e juntas com espessura correta', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(43, 15, 9, 'Rejuntes e impermeabilizações em áreas molhadas', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(44, 15, 9, 'Nivelamento e piso com regularidade', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(45, 15, 9, 'Proteção de acabamentos já executados', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(46, 15, 10, 'Membranas aplicadas conforme especificação', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(47, 15, 10, 'Teste de estanqueidade em áreas molhadas (banheiros, lajes)', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(48, 15, 10, 'Cobertura (telhado) com fixação e calhas corretas', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(49, 15, 10, 'Verificação de pendentes para escoamento', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(50, 15, 11, 'Projeto hidráulico disponível e conferido', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(51, 15, 11, 'Tubulações sem vazamentos, fixação e cintagem', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(52, 15, 11, 'Caixa d’água limpa e com tampa adequada', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(53, 15, 11, 'Teste de pressão e estanqueidade', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(54, 15, 11, 'Ponto de água/descargas conformes ao projeto', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(55, 15, 12, 'Projeto elétrico e aterramento executados', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(56, 15, 12, 'Disjuntores, quadros e proteções instalados e identificados', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(57, 15, 12, 'EPI e sinalização nas áreas eletricamente ativas', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(58, 15, 12, 'Teste de continuidade, isolamento e aterramento', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(59, 15, 12, 'Verificação de SPDA (para-raios) quando aplicável', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(60, 15, 13, 'Dimensões e prumo conferidos', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(61, 15, 13, 'Fechaduras, dobradiças e vedação testadas', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(62, 15, 13, 'Proteção contra água e infiltração', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:05', '2025-11-06 23:47:05'),
(63, 15, 13, 'Acabamento e pintura/verificação de ferragens', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(64, 15, 14, 'Aplicação conforme especificação (demãos, cura)', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(65, 15, 14, 'Peças (louças, metais) conferidas e instaladas', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(66, 15, 14, 'Teste de funcionamento (torneiras, registros)', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(67, 15, 14, 'Limpeza final e proteção dos ambientes', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(68, 15, 15, 'Testes hidrostáticos, elétricos e de ventilação realizados', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(69, 15, 15, 'Lista de não conformidades e ações corretivas registradas', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(70, 15, 15, 'Inspeção final pelo responsável técnico', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(71, 15, 15, 'Entrega de manuais e garantias', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(72, 15, 16, 'As-built/plantas finais atualizadas', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(73, 15, 16, 'Certidões, laudos e AVCB (quando aplicável)', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(74, 15, 16, 'Termo de entrega e aceitação assinado', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(75, 15, 16, 'Registro de pendências e prazos de garantia', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(76, 15, 17, 'Pátio de resíduos, segregação e destinação correta', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(77, 15, 17, 'Controle de efluentes e proteção de corpos d’água', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(78, 15, 17, 'Plano de controle de emissões/poeira e ruído', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(79, 15, 18, 'Checklist diário de máquinas (lubrificação, freios, cabos)', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(80, 15, 18, 'Inspeção de guindastes/muncks e certificação', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(81, 15, 18, 'Plano de manutenção preventiva', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(82, 15, 19, 'Medições mensais conferidas e aprovadas', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:06', '2025-11-06 23:47:06'),
(83, 15, 19, 'Retenção, garantias e provisões registradas', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:07', '2025-11-06 23:47:07'),
(84, 15, 19, 'Fluxo de caixa atualizado e notas fiscais organizadas', 'check', NULL, 0, NULL, 'ativo', '2025-11-06 23:47:07', '2025-11-06 23:47:07');

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL COMMENT 'ID do usuário que cadastrou o cliente',
  `nome` varchar(255) NOT NULL COMMENT 'Nome completo do cliente',
  `tipo_pessoa` enum('fisica','juridica') NOT NULL DEFAULT 'fisica' COMMENT 'Tipo de pessoa física ou jurídica',
  `cpf_cnpj` varchar(20) DEFAULT NULL COMMENT 'CPF ou CNPJ do cliente',
  `telefone` varchar(20) DEFAULT NULL COMMENT 'Telefone de contato',
  `observacoes` text DEFAULT NULL COMMENT 'Observações adicionais',
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `token` varchar(255) DEFAULT NULL COMMENT 'Token para autenticação',
  `token_expiracao` datetime DEFAULT NULL COMMENT 'Data de expiração do token',
  `data_cadastro` datetime DEFAULT current_timestamp(),
  `ultimo_acesso` datetime DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de clientes do sistema';

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `usuario_id`, `nome`, `tipo_pessoa`, `cpf_cnpj`, `telefone`, `observacoes`, `email`, `senha`, `token`, `token_expiracao`, `data_cadastro`, `ultimo_acesso`, `ativo`) VALUES
(17, 25, 'Zilor USB', 'fisica', '', '1835519630', '', 'paula.favaretto@zilor.com.br', '$2y$10$v7SFGNsymHfe9UmddAOaXuPiNFOOWaMb0HPsxoPyt5eYxJYPYNbcW', NULL, NULL, '2025-09-27 00:58:28', NULL, 1),
(18, 28, 'Claudio', 'fisica', '', '', '', '', '', NULL, NULL, '2025-09-30 15:32:18', NULL, 1),
(19, 32, 'Claudio e Michele', 'fisica', '', '', '', '', '', NULL, NULL, '2025-10-01 23:32:37', NULL, 1),
(20, 28, 'Wanderson Caldas', 'fisica', '', '', '', '', '', NULL, NULL, '2025-10-04 03:12:37', NULL, 1),
(24, 15, 'jorge', 'fisica', '', '(11) 11111-1111', '', 'jorge.eduardo.pir@gmail.com', '$2y$10$9Z3Nxzvujmnlg2qEmdNAZuB9y09I8L8rn2rMLksRWP8YdelB4.EVa', '38933e994bb5ec17e4eca8966f3e7a4b07ba97eaa880a5058e20888e9e93a685', '2025-11-29 13:49:40', '2025-10-09 03:37:33', '2025-10-30 13:49:40', 1),
(25, 52, 'Marcos Moreira Lira', 'fisica', '60903084392', '99982192844', '', 'marcos55call@gmail.com', '$2y$10$L4HMLHA1zZkoUModFY3zteJcJ/17E5piZdWcrW2q3T5StYzvOzM7u', NULL, NULL, '2025-10-28 00:51:23', NULL, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `contratos`
--

CREATE TABLE `contratos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL COMMENT 'ID do usuário que cadastrou o contrato',
  `cliente_id` int(11) DEFAULT NULL COMMENT 'ID do cliente vinculado ao contrato (opcional)',
  `obra_id` int(11) DEFAULT NULL COMMENT 'ID da obra vinculada ao contrato (opcional)',
  `numero_contrato` varchar(100) DEFAULT NULL COMMENT 'Número único do contrato',
  `titulo` varchar(255) DEFAULT NULL COMMENT 'Título do contrato',
  `descricao` text DEFAULT NULL COMMENT 'Descrição detalhada do contrato',
  `valor_total` decimal(15,2) DEFAULT 0.00 COMMENT 'Valor total do contrato',
  `data_inicio` date NOT NULL COMMENT 'Data de início do contrato',
  `data_fim` date DEFAULT NULL COMMENT 'Data de fim do contrato',
  `status` enum('ativo','concluido','cancelado','suspenso') NOT NULL DEFAULT 'ativo' COMMENT 'Status do contrato',
  `anexo` varchar(500) DEFAULT NULL COMMENT 'Caminho do arquivo anexado (PDF ou imagem)',
  `nome_documento` varchar(255) DEFAULT NULL COMMENT 'Nome original do documento anexado',
  `data_cadastro` datetime DEFAULT current_timestamp() COMMENT 'Data de cadastro do contrato',
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Data da última atualização'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de contratos do sistema';

--
-- Despejando dados para a tabela `contratos`
--

INSERT INTO `contratos` (`id`, `usuario_id`, `cliente_id`, `obra_id`, `numero_contrato`, `titulo`, `descricao`, `valor_total`, `data_inicio`, `data_fim`, `status`, `anexo`, `nome_documento`, `data_cadastro`, `data_atualizacao`) VALUES
(4, 1, NULL, NULL, NULL, 'Contrato de Teste', NULL, 0.00, '2025-09-19', NULL, 'ativo', 'uploads/contratos/1/68cd554caf453_1758287180.pdf', 'contrato_teste.pdf', '2025-09-19 13:06:19', '2025-09-19 13:06:19'),
(6, 28, 18, 69, '001', 'Construção de Casa', 'Construção de uma Casa no retiro - São Pedro da Aldeia', 25800.00, '2025-09-29', '2025-12-01', 'ativo', NULL, NULL, '2025-09-30 15:35:32', '2025-10-06 15:37:38'),
(7, 32, 19, 63, '001.2025', 'Acabamento da casa da roça', '', 25800.00, '2025-09-29', '2025-12-05', 'ativo', NULL, NULL, '2025-10-01 23:33:39', '2025-10-01 23:33:39'),
(8, 28, 20, 68, '002.2025', 'Construção Residencial Viverde SPA', 'Conforme projeto', 100000.00, '2025-09-22', '2026-03-02', 'ativo', 'uploads/contratos/8/documentos/Projeto_20completo-1_20wanderson.pdf', 'Projeto%20completo-1%20wanderson.pdf', '2025-10-04 03:14:20', '2025-10-06 15:37:50'),
(12, 12, 21, NULL, '2345', 'Ouxo', 'Xjxjdjj', 1023400.00, '2025-10-17', '2026-02-28', 'ativo', NULL, NULL, '2025-10-04 12:50:22', '2025-10-05 11:18:07'),
(13, 12, NULL, NULL, '', 'uyrtyurtyu', '', 0.00, '2025-10-04', '0000-00-00', 'ativo', NULL, NULL, '2025-10-04 13:38:12', '2025-10-04 13:38:12'),
(14, 15, 24, 41, '25145', 'Test', 'Dkdj', 3500.00, '2025-10-21', '2026-01-25', 'ativo', 'uploads/contratos/14/documentos/Screenshot_20251024-224909.png', 'Screenshot_20251024-224909.png', '2025-10-26 14:57:11', '2025-10-27 13:07:23'),
(17, 67, NULL, 103, '03', 'Reforma casa e muro', 'Reforma geral da casa', 75000.00, '2025-10-28', '2025-11-09', 'ativo', NULL, NULL, '2025-11-05 00:59:17', '2025-11-05 00:59:17');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Cupom`
--

CREATE TABLE `Cupom` (
  `id` int(11) NOT NULL,
  `cupom_nome` varchar(255) NOT NULL COMMENT 'nome do cupom',
  `cupom` varchar(255) NOT NULL COMMENT 'codigo do cupom',
  `validade` date NOT NULL COMMENT 'data de validade',
  `tipo_cupom` enum('Novo','Reativar','Anual','Especial') NOT NULL DEFAULT 'Novo' COMMENT 'tipo de cupom',
  `forma_pagamento` enum('Stripe','InfinitePay','Paypal','Outros') NOT NULL DEFAULT 'Stripe' COMMENT 'selecione forma de pagamento',
  `tema` enum('BlackFray','Natal') DEFAULT NULL COMMENT 'tema especial'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Cupom`
--

INSERT INTO `Cupom` (`id`, `cupom_nome`, `cupom`, `validade`, `tipo_cupom`, `forma_pagamento`, `tema`) VALUES
(1, 'Primeira Assinatura 15%  de Desconto, no Plano Semestral ou Anual.', 'ASSINA15', '2028-01-21', 'Novo', 'Stripe', NULL),
(2, 'Renova 8% de Desconto, na sua Renovacao', 'Renova8', '2030-01-02', 'Reativar', 'Stripe', NULL),
(3, 'Promoção da Black Fraday, aproveite e limitado.', 'ASSINA15', '2025-11-11', 'Especial', 'Stripe', 'BlackFray');

-- --------------------------------------------------------

--
-- Estrutura para tabela `emails_prontos`
--

CREATE TABLE `emails_prontos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `assunto` varchar(255) NOT NULL,
  `mensagem` text NOT NULL,
  `tipo_email` enum('bem_vindo','renovar_assinatura','cadastro','promocao','outro') NOT NULL DEFAULT 'outro',
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `emails_prontos`
--

INSERT INTO `emails_prontos` (`id`, `nome`, `assunto`, `mensagem`, `tipo_email`, `data_criacao`, `data_atualizacao`) VALUES
(1, 'Bem-vindo ao Monitor Obra', 'Bem-vindo ao nosso sistema de gerenciamento de obras!', '<p><strong>Assunto:</strong> Bem-vindo ao Gestão de Obra Fácil 🚀</p><p>Olá, {nome}, tudo certo?</p><p>É um prazer ter você com a gente!</p><p> Seu cadastro no <strong>Gestão de Obra Fácil</strong> foi realizado com sucesso.</p><p>A partir de agora você tem acesso a uma plataforma pensada para facilitar o controle e a documentação das suas obras, com foco em eficiência, organização e tranquilidade no dia a dia.</p><p>O que você pode fazer agora:</p><p> • Acessar seu painel e cadastrar sua obra</p><p> • Registrar atividades com o Relatório Diário de Obra</p><p> • Acompanhar histórico e progresso com clareza</p><p>Se precisar de ajuda, suporte ou tiver sugestões, estamos aqui para te ouvir.</p><p> Desejamos ótimos projetos e ainda mais sucesso para você!</p><p>Abraço,</p><p> <strong>Equipe Gestão de Obra Fácil</strong></p><p><a href=\" www.gestaodeobrafacil.com\" target=\"_blank\"> www.gestaodeobrafacil.com</a></p>', 'bem_vindo', '2025-10-22 13:08:45', '2025-10-31 19:58:40'),
(2, 'Renovação de Assinatura', 'Sua assinatura está prestes a expirar', 'Olá {nome},\r\n\r\nSua assinatura no Monitor Obra expira em {dias_restantes} dias.\r\n\r\nPara continuar aproveitando todos os benefícios do nosso sistema, renove sua assinatura agora mesmo acessando:\r\n{link_renovacao}\r\n\r\nBenefícios da assinatura:\r\n- Relatórios ilimitados\r\n- Armazenamento em nuvem\r\n- Suporte prioritário\r\n- Acesso em múltiplos dispositivos\r\n\r\nNão perca a continuidade dos seus projetos!\r\n\r\nAtenciosamente,\r\nEquipe Monitor Obra', 'renovar_assinatura', '2025-10-22 13:08:45', '2025-10-22 13:08:45'),
(3, 'Confirmação de Cadastro', 'Confirmação de cadastro - Monitor Obra', 'Olá {nome},\r\n\r\nSeu cadastro no Monitor Obra foi realizado com sucesso!\r\n\r\nPara ativar sua conta, clique no link abaixo:\r\n{link_ativacao}\r\n\r\nDados do cadastro:\r\n- Email: {email}\r\n- Data: {data_cadastro}\r\n\r\nApós a ativação, você poderá acessar todas as funcionalidades do sistema.\r\n\r\nAtenciosamente,\r\nEquipe Monitor Obra', 'cadastro', '2025-10-22 13:08:45', '2025-10-22 13:08:45'),
(4, 'Promoção Especial', 'Promoção exclusiva para nossos clientes!', '<!-- Email HTML - Gestão de Obra Fácil | Black Friday -->\r\n<!DOCTYPE html>\r\n<html lang=\"pt-BR\">\r\n<head>\r\n  <meta charset=\"utf-8\" />\r\n  <meta name=\"viewport\" content=\"width=device-width,initial-scale=1\" />\r\n  <title>Black Friday — Gestão de Obra Fácil</title>\r\n  <style>\r\n    /* Reset simples */\r\n    body { margin:0; padding:0; background:#f4f6f8; font-family: Arial, sans-serif; -webkit-font-smoothing:antialiased; }\r\n    .container { max-width:680px; margin:28px auto; background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 6px 24px rgba(16,24,40,0.08); }\r\n    .header { padding:28px; text-align:center; background:linear-gradient(90deg,#ff7a18,#ffb03b); color:#fff; }\r\n    .title { margin:0; font-size:22px; font-weight:700; letter-spacing:0.2px; }\r\n    .subtitle { margin-top:6px; font-size:14px; opacity:0.95; }\r\n    .body { padding:28px; color:#0f1724; line-height:1.5; }\r\n    .lead { font-size:16px; margin-bottom:16px; }\r\n    .promo-box { background:#f8fafc; border:1px solid #e6eef6; padding:18px; border-radius:8px; display:flex; align-items:center; justify-content:space-between; gap:12px; }\r\n    .code { font-weight:700; font-size:18px; color:#0f1724; background:#fff; padding:8px 12px; border-radius:6px; border:1px dashed #d1e3f2; }\r\n    .details { font-size:13px; color:#334155; }\r\n    .cta { display:block; text-align:center; margin:22px 0 8px; }\r\n    .btn { display:inline-block; padding:12px 20px; border-radius:8px; background:#0b74ff; color:#fff; text-decoration:none; font-weight:700; }\r\n    .small { font-size:13px; color:#475569; margin-top:8px; }\r\n    .footer { padding:18px; text-align:center; font-size:12px; color:#94a3b8; background:#fbfdff; }\r\n    @media (max-width:520px) {\r\n      .container { margin:12px; }\r\n      .promo-box { flex-direction:column; align-items:flex-start; }\r\n      .code { width:100%; text-align:center; }\r\n    }\r\n  </style>\r\n</head>\r\n<body>\r\n  <table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\r\n    <tr>\r\n      <td align=\"center\">\r\n        <div class=\"container\" role=\"article\" aria-label=\"Oferta Black Friday Gestão de Obra Fácil\">\r\n          <div class=\"header\">\r\n            <h1 class=\"title\">Black Friday — 12% OFF</h1>\r\n            <div class=\"subtitle\">Desconto aplicado em qualquer plano — pagamento via Stripe</div>\r\n          </div>\r\n\r\n          <div class=\"body\">\r\n            <p class=\"lead\">Olá,</p>\r\n\r\n            <p>Black Friday chegou no <strong>Gestão de Obra Fácil</strong>. Aproveite <strong>12% de desconto</strong> em qualquer plano ao finalizar sua assinatura pela Stripe.</p>\r\n\r\n            <div class=\"promo-box\" role=\"region\" aria-label=\"Código promocional\">\r\n              <div class=\"details\">\r\n                <div style=\"font-size:13px; color:#0f1724; font-weight:700;\">Código de desconto</div>\r\n                <div class=\"small\">Insira no checkout Stripe para aplicar o desconto</div>\r\n              </div>\r\n              <div class=\"code\">ASSINA15</div>\r\n            </div>\r\n\r\n            <div class=\"cta\" role=\"group\" aria-label=\"Chamada para ação\">\r\n              <!-- substitua {{stripe_checkout_url}} pela URL real do checkout Stripe -->\r\n              <a href=\"{{stripe_checkout_url}}\" class=\"btn\" target=\"_blank\" rel=\"noopener noreferrer\">Assinar agora — Aplicar desconto</a>\r\n            </div>\r\n\r\n            <p class=\"small\">Observações: desconto de 12% válido somente para pagamentos realizados via Stripe. Oferta por tempo limitado e sujeita a término sem aviso prévio.</p>\r\n\r\n            <p style=\"margin-top:18px;\">Se precisar de ajuda para completar a assinatura, responde este e-mail ou acesse nossa Central de Ajuda.</p>\r\n\r\n            <p style=\"margin-top:18px\">Um abraço,<br><strong>Equipe Gestão de Obra Fácil</strong></p>\r\n          </div>\r\n\r\n          <div class=\"footer\">\r\n            Gestão de Obra Fácil — Simplificando a gestão do seu canteiro.<br>\r\n            <span style=\"display:block; margin-top:8px;\">Se não quiser receber mais comunicações promocionais, <a href=\"{{unsubscribe_url}}\" style=\"color:#0b74ff; text-decoration:none;\">clique aqui</a>.</span>\r\n          </div>\r\n        </div>\r\n      </td>\r\n    </tr>\r\n  </table>\r\n</body>\r\n</html>\r\n', 'promocao', '2025-10-22 13:08:45', '2025-10-31 20:23:54');

-- --------------------------------------------------------

--
-- Estrutura para tabela `equipamentos`
--

CREATE TABLE `equipamentos` (
  `id` int(11) NOT NULL,
  `obra_id` int(11) DEFAULT NULL COMMENT 'ID da obra vinculada',
  `usuario_id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `tipo` varchar(255) NOT NULL,
  `marca` varchar(255) DEFAULT NULL,
  `modelo` varchar(255) DEFAULT NULL,
  `numero_serie` varchar(255) DEFAULT NULL,
  `ano_fabricacao` int(11) DEFAULT NULL,
  `status` enum('disponivel','em_uso','manutencao','inativo') DEFAULT 'disponivel',
  `observacoes` text DEFAULT NULL,
  `data_cadastro` datetime DEFAULT current_timestamp() COMMENT 'Data de cadastro de cadastro',
  `data_atualizacao` datetime DEFAULT current_timestamp() COMMENT 'Data de atualizacao'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `equipamentos`
--

INSERT INTO `equipamentos` (`id`, `obra_id`, `usuario_id`, `nome`, `tipo`, `marca`, `modelo`, `numero_serie`, `ano_fabricacao`, `status`, `observacoes`, `data_cadastro`, `data_atualizacao`) VALUES
(4, 34, 16, 'Pincel', 'Ferramenta', 'Amanco', 'FG 256', 'SN4567', 2014, 'disponivel', 'Trincha pequena', '2025-09-15 09:38:15', '2025-09-15 09:38:15'),
(13, 40, 12, 'Jeiddii', 'Oyzogz', 'Gkxo', 'Up', 'Yizi', NULL, 'disponivel', '', '2025-10-04 12:24:29', '2025-10-04 12:24:29');

-- --------------------------------------------------------

--
-- Estrutura para tabela `equipe`
--

CREATE TABLE `equipe` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `cargo` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` enum('ativo','inativo') DEFAULT 'ativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `equipe`
--

INSERT INTO `equipe` (`id`, `usuario_id`, `nome`, `cargo`, `telefone`, `email`, `status`) VALUES
(7, 11, 'marcos', 'pedreiro', '(11) 1111-1111', NULL, 'ativo'),
(9, 11, 'carlos', 'pedreiro', '(33) 33333-3333', 'asdasdasd@sdad.com', 'ativo'),
(15, 16, 'João Victor', 'Servente de pedreiro', '889952114365', 'jvictor@gmail.com', 'ativo'),
(18, 16, 'Alisson', 'Cadista', '8498542113', 'alisson@gmail.com', 'ativo'),
(23, 27, 'Diego henrique santos', 'Encarregado de obra', '+55 31 99477-2124', NULL, 'ativo'),
(24, 28, 'Marcio Santos', 'Supervisor Geral', '22998328637', 'iprvmarcio@gmail.com', 'ativo'),
(26, 28, 'Ezequiel Braga', 'Pedreiro', NULL, NULL, 'ativo'),
(27, 28, 'Marcone', 'Meio Oficial', NULL, NULL, 'ativo'),
(28, 28, 'Jeferson', 'Ajudante', NULL, NULL, 'ativo'),
(29, 28, 'Lazaro', 'Ajudante', NULL, NULL, 'ativo'),
(30, 32, 'Marcone', 'Meio oficial', NULL, NULL, 'ativo'),
(31, 32, 'Francisco Daniel', 'Ajudante', NULL, NULL, 'ativo'),
(32, 32, 'Jeferson', 'Ajudante', NULL, NULL, 'ativo'),
(34, 15, 'Jorge', 'Eng', NULL, 'test@test.com', 'ativo'),
(35, 48, 'Alan Carlos B. Da Silva', 'Diretor', '62984768938', 'alancarlossilva503@gmail.com', 'ativo'),
(36, 48, 'Wesley da Costa Santos', 'Diretor', '62986428926', 'wesleycosta326@gmail.com', 'ativo'),
(37, 66, 'Lucas delgado', 'Apontador', '95974003385', NULL, 'ativo'),
(38, 66, 'Matheus', 'Ajudante', NULL, NULL, 'ativo'),
(39, 67, 'Oderlan', 'Pintor', '95981212962', NULL, 'ativo'),
(40, 67, 'Vitor', 'Pintor', NULL, NULL, 'ativo'),
(41, 67, 'Abraão', 'Pedreiro', NULL, NULL, 'ativo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `fornecedores`
--

CREATE TABLE `fornecedores` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL COMMENT 'Nome da empresa ou pessoa física',
  `tipo_pessoa` enum('fisica','juridica') NOT NULL DEFAULT 'juridica',
  `cpf_cnpj` varchar(20) DEFAULT NULL COMMENT 'CPF ou CNPJ do fornecedor',
  `telefone` varchar(20) DEFAULT NULL COMMENT 'Telefone principal',
  `email` varchar(255) DEFAULT NULL COMMENT 'E-mail de contato',
  `endereco` varchar(255) DEFAULT NULL COMMENT 'Endereço completo',
  `cidade` varchar(100) DEFAULT NULL COMMENT 'Cidade',
  `estado` varchar(2) DEFAULT NULL COMMENT 'Estado (UF)',
  `cep` varchar(10) DEFAULT NULL COMMENT 'CEP',
  `categoria` varchar(100) DEFAULT NULL COMMENT 'Categoria/Tipo de serviço',
  `observacoes` text DEFAULT NULL COMMENT 'Observações adicionais',
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `data_cadastro` timestamp NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `fornecedores`
--

INSERT INTO `fornecedores` (`id`, `usuario_id`, `nome`, `tipo_pessoa`, `cpf_cnpj`, `telefone`, `email`, `endereco`, `cidade`, `estado`, `cep`, `categoria`, `observacoes`, `status`, `data_cadastro`, `data_atualizacao`) VALUES
(8, 11, 'contrucasa', 'juridica', '00.000.000/0000-00', '(11) 11111-1111', NULL, NULL, NULL, NULL, NULL, 'material de construcao', NULL, 'ativo', '2025-07-27 00:33:34', '2025-07-27 00:33:34'),
(9, 11, 'Materiais São José', 'juridica', '12.345.678/0001-90', '(11) 99999-8888', 'vendas@saojose.com.br', 'Rua das Palmeiras, 123', 'São Paulo', 'SP', '01234-567', 'Material de Construção', 'Fornecedor de cimento, areia e brita', 'ativo', '2025-09-02 10:00:00', '2025-09-02 10:00:00'),
(10, 11, 'Elétrica Central', 'juridica', '98.765.432/0001-11', '(11) 88888-7777', 'contato@eletricacentral.com', 'Av. Paulista, 456', 'São Paulo', 'SP', '04567-890', 'Elétrica', 'Especializada em instalações elétricas', 'ativo', '2025-09-02 10:15:00', '2025-09-02 10:15:00'),
(12, 11, 'Teste Fornecedor', 'juridica', '12345678000195', '11999887766', 'teste@teste.com', '', '', '', '', 'Teste', '', 'ativo', '2025-09-02 17:07:46', '2025-09-02 17:07:46'),
(24, 15, 'Construcasa', 'juridica', '32.132.132/1321-321', '546546546546', 'sadas@sdas.com', NULL, NULL, NULL, NULL, NULL, NULL, 'ativo', '2025-10-30 14:30:20', '2025-10-30 14:30:20'),
(25, 70, 'Ind tintas Primavera ', 'juridica', '32257682000109', '6235766144', 'financeiro tintasprimavera5@gmail.com', 'RUa H 152 ', 'Aparecida de Goiânia ', 'GO', '4937510', 'TIntas', 'Fornecido de tintas e insumos de impermeabilização e outros\n', 'ativo', '2025-11-09 20:52:16', '2025-11-09 20:52:16'),
(26, 70, 'Forrações distribuidora de ferragem serralheiria', 'juridica', '', '62991658930', '', '', '', '', '', 'SERRALHEIRia', '', 'ativo', '2025-11-09 20:55:17', '2025-11-09 20:55:17'),
(27, 70, 'A locadora Pamela', 'juridica', '', '620000000', '', '', 'GOia', 'GO', '', '', '', 'ativo', '2025-11-09 20:56:05', '2025-11-09 20:56:05'),
(28, 70, 'LImpa obra caçamba de entulho', 'juridica', '', '6232423334', '', '', 'GOIÂNIA ', 'GO', '', '', '', 'ativo', '2025-11-09 20:57:14', '2025-11-09 20:57:14'),
(29, 70, 'GAsmac materiais de construção ALANA', 'juridica', '', '62995730481', '', '', 'Aparecida de Goiânia ', 'GO', '', '', '', 'ativo', '2025-11-09 20:58:38', '2025-11-09 20:58:38'),
(30, 70, 'DANIEL pintor', 'fisica', '', '62991349390', '', '', 'GOIÂNIA ', 'GO', '', 'Pintor', '', 'ativo', '2025-11-09 20:59:51', '2025-11-09 20:59:51'),
(31, 70, 'Eletrica Hidráulica == HENRIQUE __ Jhemerson', 'fisica', '', '64981714311', '', '', 'GOIÂNIA ', 'GO', '', 'ELETRICA hidráulica ', 'eletrica hidráulica e pedreiro e outros outros', 'ativo', '2025-11-09 21:02:14', '2025-11-09 21:02:14'),
(32, 70, 'SERRALHEIRO JAIRTON ', 'fisica', '', '62986296125', '', '', 'APARECIDA de Goiânia ', 'GO', '', 'SERRALHEIRO ', '', 'ativo', '2025-11-09 21:03:24', '2025-11-09 21:03:24');

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcionarios`
--

CREATE TABLE `funcionarios` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL COMMENT 'Token para autenticação',
  `token_expiracao` datetime DEFAULT NULL COMMENT 'Data de expiração do token',
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `data_cadastro` timestamp NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `funcionarios`
--

INSERT INTO `funcionarios` (`id`, `usuario_id`, `nome`, `telefone`, `email`, `senha`, `cargo`, `token`, `token_expiracao`, `status`, `data_cadastro`, `data_atualizacao`) VALUES
(10, 12, 'Osmar 2', '(43) 96546-546', 'hosttotal@hotmail.com', '$2y$10$EWN1DB7wg6xBjoEppZLBoeeUsEsZGIQNSnMgeVeEoM6F99pflE.P.', 'sdfsd', 'c0c9384dc7421bda29cc4367d38145454e35e0b33f7e8aebbaf640814ea2f698', NULL, 'ativo', '2025-10-05 12:04:24', '2025-10-05 12:04:24'),
(12, 15, 'jorge', '(44) 44444-4444', 'jorge.eduardo.pir@gmail.com', '$2y$10$dYdQnHlT4wGPlvLtTr8u2exl5oehJwb72K1NXn3.9xD39.KPHSk22', 'engenheiro', NULL, NULL, 'ativo', '2025-10-09 02:03:10', '2025-10-27 11:31:48'),
(13, 52, 'Antônio Nunes ', '', 'jaco.pedagogo@gmail.com', '$2y$10$POggACfjWPzt96FR9rU7RulTcKv0vzrDfIxG4b14znpkbWuLHNifu', 'Pedreiro ', 'f1ef26c626807cfd727d238b781982fe9fe5604bb3a41461af4037d2e04aff70', NULL, 'ativo', '2025-10-28 00:28:40', '2025-10-28 00:28:40'),
(14, 67, 'Oderlan', '', 'oderlan@gmail.com', '$2y$10$nED8.N0fdwOs2ZWivrz9eevByg0GAzvkEApi2an5PPmfuAH1FZu16', 'Pintor', 'e1c32065accec9f74509499c29f639a5b389c28e0ef3632dbf3102652215ce72', NULL, 'ativo', '2025-11-05 00:55:32', '2025-11-05 00:55:32');

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_emails`
--

CREATE TABLE `historico_emails` (
  `id` int(11) NOT NULL,
  `email_pronto_id` int(11) NOT NULL,
  `nome_email` varchar(255) NOT NULL,
  `assunto` varchar(255) NOT NULL,
  `total_destinatarios` int(11) NOT NULL,
  `enviados` int(11) NOT NULL,
  `falhas` int(11) NOT NULL,
  `data_envio` timestamp NULL DEFAULT current_timestamp(),
  `administrador_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `lancamentos_financeiros`
--

CREATE TABLE `lancamentos_financeiros` (
  `id` int(11) NOT NULL,
  `obra_id` int(11) NOT NULL COMMENT 'ID da obra vinculada',
  `usuario_id` int(11) NOT NULL COMMENT 'ID do usuário que criou o lançamento',
  `fornecedor_id` int(11) DEFAULT NULL COMMENT 'id do fornecedor',
  `tipo` enum('receita','despesa') NOT NULL DEFAULT 'despesa' COMMENT 'Tipo do lançamento',
  `categoria` varchar(100) DEFAULT NULL COMMENT 'Categoria do lançamento',
  `descricao` text NOT NULL COMMENT 'Descrição detalhada do lançamento',
  `valor` decimal(65,2) NOT NULL COMMENT 'Valor do lançamento',
  `data_lancamento` date NOT NULL COMMENT 'Data do lançamento',
  `data_vencimento` date DEFAULT NULL COMMENT 'Data de vencimento (opcional)',
  `status` enum('pendente','pago','vencido','cancelado') NOT NULL DEFAULT 'pendente',
  `forma_pagamento` varchar(50) DEFAULT NULL COMMENT 'Forma de pagamento utilizada',
  `observacoes` text DEFAULT NULL COMMENT 'Observações adicionais',
  `anexo` varchar(255) DEFAULT NULL COMMENT 'Caminho do arquivo anexado',
  `nome_documento` varchar(255) DEFAULT NULL COMMENT 'Nome original do documento anexado',
  `data_cadastro` timestamp NULL DEFAULT current_timestamp() COMMENT 'Data de criação do registro',
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Data da última atualização'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de lançamentos financeiros das obras';

--
-- Despejando dados para a tabela `lancamentos_financeiros`
--

INSERT INTO `lancamentos_financeiros` (`id`, `obra_id`, `usuario_id`, `fornecedor_id`, `tipo`, `categoria`, `descricao`, `valor`, `data_lancamento`, `data_vencimento`, `status`, `forma_pagamento`, `observacoes`, `anexo`, `nome_documento`, `data_cadastro`, `data_atualizacao`) VALUES
(34, 35, 15, NULL, 'despesa', 'Material', 'Compra cimento', 350.00, '2025-09-22', '2025-10-22', 'pendente', 'boleto', 'null', NULL, NULL, '2025-09-22 19:52:07', '2025-09-22 19:52:07'),
(35, 35, 15, NULL, 'despesa', 'Material', 'Compra ferragem', 5000.00, '2025-09-24', '2025-10-22', 'pendente', 'boleto', 'null', NULL, NULL, '2025-09-22 19:52:38', '2025-09-22 19:52:38'),
(36, 35, 15, NULL, 'despesa', 'compra madeira', 'Compra de tabua', 500.00, '2025-09-24', '2025-10-23', 'pendente', 'boleto', 'null', NULL, NULL, '2025-09-24 01:48:50', '2025-09-24 01:48:50'),
(37, 55, 26, NULL, 'despesa', 'mao_de_obra', 'Pagamento total ja recebido', 15.00, '2025-09-27', '2025-09-26', 'pago', 'pix', NULL, NULL, NULL, '2025-09-27 10:49:00', '2025-09-27 10:49:00'),
(38, 55, 26, NULL, 'receita', 'mao_de_obra', 'Pagamento ja recebido ', 15000.00, '2025-09-27', '2025-09-26', 'pago', 'pix', NULL, NULL, NULL, '2025-09-27 10:50:23', '2025-09-27 10:50:23'),
(39, 55, 26, NULL, 'despesa', 'mao_de_obra', 'Mão de obra paga', 12985.00, '2025-09-27', '2025-09-26', 'pago', 'pix', NULL, NULL, NULL, '2025-09-27 10:51:21', '2025-09-27 10:51:21'),
(40, 55, 26, NULL, 'despesa', 'mao_de_obra', 'Mão de obra paga', 1000.00, '2025-09-27', '2025-09-26', 'pago', 'pix', NULL, NULL, NULL, '2025-09-27 10:52:35', '2025-09-27 10:52:35'),
(44, 61, 30, NULL, 'despesa', 'servico', 'Proj', 1000.00, '2025-10-01', '2025-10-10', 'pago', 'cartao_debito', NULL, NULL, NULL, '2025-10-01 16:48:01', '2025-10-01 16:48:01'),
(46, 64, 32, NULL, 'receita', 'mao_de_obra', 'Contrato 001.2025', 25800.00, '2025-10-01', '2025-12-05', 'pendente', 'pix', NULL, NULL, NULL, '2025-10-01 23:49:23', '2025-10-01 23:49:23'),
(47, 64, 32, NULL, 'despesa', 'mao_de_obra', 'Adiantamento', 3110.00, '2025-10-01', '2025-09-29', 'pago', 'pix', NULL, NULL, NULL, '2025-10-01 23:50:06', '2025-10-01 23:50:06'),
(49, 66, 32, NULL, 'receita', 'mao_de_obra', 'Contrato 002.2025', 100000.00, '2025-10-01', '2026-03-30', 'pendente', 'pix', NULL, NULL, NULL, '2025-10-01 23:56:22', '2025-10-01 23:56:22'),
(50, 66, 32, NULL, 'despesa', 'mao_de_obra', 'Pagamento 1° semana', 2000.00, '2025-10-01', '2025-09-26', 'pago', 'pix', NULL, NULL, NULL, '2025-10-01 23:57:21', '2025-10-01 23:57:21'),
(51, 64, 32, NULL, 'despesa', 'mao_de_obra', '1° semana ', 3000.00, '2025-10-04', '2025-10-04', 'pago', 'pix', NULL, 'uploads/financeiro/51/documentos/IMG-20251003-WA0068.jpg', 'IMG-20251003-WA0068.jpg', '2025-10-04 00:22:38', '2025-10-04 00:22:38'),
(52, 66, 32, NULL, 'despesa', 'mao_de_obra', '2° semana', 2850.00, '2025-10-04', '2025-10-04', 'pago', 'pix', NULL, 'uploads/financeiro/52/documentos/IMG-20251003-WA0001.jpg', 'IMG-20251003-WA0001.jpg', '2025-10-04 00:24:16', '2025-10-04 00:24:16'),
(55, 69, 28, NULL, 'receita', 'mao_de_obra', 'Valor do Contrato', 25800.00, '2025-10-04', '2025-09-29', 'pago', 'pix', '', NULL, NULL, '2025-10-04 09:41:40', '2025-10-04 09:43:00'),
(57, 69, 28, NULL, 'despesa', 'mao_de_obra', 'Adiantamento', 3110.00, '2025-10-04', '2025-09-22', 'pago', 'pix', '', NULL, NULL, '2025-10-04 09:43:28', '2025-10-04 09:43:49'),
(58, 69, 28, NULL, 'despesa', 'mao_de_obra', '1° semana', 3000.00, '2025-10-04', '2025-10-03', 'pago', 'pix', '', 'uploads/financeiro/58/documentos/IMG-20251003-WA0068.jpg', 'IMG-20251003-WA0068.jpg', '2025-10-04 09:45:41', '2025-10-04 09:45:52'),
(59, 68, 28, NULL, 'despesa', 'mao_de_obra', '1° semana ', 2000.00, '2025-10-04', '2025-09-22', 'pago', 'pix', '', 'uploads/financeiro/59/documentos/IMG-20251003-WA0001.jpg', 'IMG-20251003-WA0001.jpg', '2025-10-04 09:47:01', '2025-10-04 09:47:14'),
(60, 68, 28, NULL, 'receita', 'mao_de_obra', 'Valor do contrato ', 100000.00, '2025-10-04', '2025-09-22', 'pago', 'pix', '', NULL, NULL, '2025-10-04 09:47:46', '2025-10-04 09:47:55'),
(61, 68, 28, NULL, 'despesa', 'mao_de_obra', '2° semana ', 2850.00, '2025-10-04', '2025-10-03', 'pago', 'pix', NULL, NULL, NULL, '2025-10-04 09:48:24', '2025-10-04 09:48:24'),
(62, 56, 27, NULL, 'receita', 'mao_de_obra', 'Entrada movimentação operacional', 5000.00, '2025-10-06', '2025-10-05', 'pago', 'pix', NULL, NULL, NULL, '2025-10-06 07:44:26', '2025-10-06 07:44:26'),
(64, 56, 27, NULL, 'despesa', 'aluguel', 'Aliguel apartamento ', 500.00, '2025-10-06', '2025-10-06', 'pago', 'pix', NULL, NULL, NULL, '2025-10-06 07:46:02', '2025-10-06 07:46:02'),
(66, 56, 27, NULL, 'despesa', 'mao_de_obra', 'Combustível alimentação ', 440.00, '2025-10-06', '2025-10-06', 'pago', 'pix', NULL, NULL, NULL, '2025-10-06 07:49:56', '2025-10-06 07:49:56'),
(67, 56, 27, NULL, 'despesa', 'materiais', 'Benedouro ', 747.00, '2025-10-06', '2025-10-06', 'pago', 'pix', NULL, NULL, NULL, '2025-10-06 07:51:23', '2025-10-06 07:51:23'),
(68, 56, 27, NULL, 'despesa', 'outros', 'Parafusadeira ', 288.00, '2025-10-06', '2025-10-06', 'pago', 'pix', NULL, NULL, NULL, '2025-10-06 07:52:31', '2025-10-06 07:52:31'),
(69, 56, 27, NULL, 'despesa', 'outros', 'Papelaria mimos ', 100.00, '2025-10-06', '2025-10-05', 'pago', 'pix', NULL, NULL, NULL, '2025-10-06 07:53:04', '2025-10-06 07:53:04'),
(70, 56, 27, NULL, 'despesa', 'mao_de_obra', 'Washington represa ', 140.00, '2025-10-06', '2025-10-05', 'pago', 'pix', NULL, NULL, NULL, '2025-10-06 07:54:46', '2025-10-06 07:54:46'),
(71, 56, 27, NULL, 'despesa', 'outros', 'Inter conta ', 20.00, '2025-10-06', '2025-10-06', 'pago', 'pix', NULL, NULL, NULL, '2025-10-06 07:57:30', '2025-10-06 07:57:30'),
(72, 56, 27, NULL, 'despesa', 'outros', 'Gasolina ', 50.00, '2025-10-06', '2025-10-06', 'pago', 'pix', NULL, NULL, NULL, '2025-10-06 14:25:01', '2025-10-06 14:25:01'),
(73, 56, 27, NULL, 'despesa', 'outros', 'Primeira compra super bh', 515.00, '2025-10-07', '2025-10-07', 'pago', 'pix', NULL, NULL, NULL, '2025-10-07 09:32:07', '2025-10-07 09:32:07'),
(74, 56, 27, NULL, 'despesa', 'outros', 'Combustível primeira ida victor ', 150.00, '2025-10-07', '2025-10-06', 'pago', 'pix', NULL, NULL, NULL, '2025-10-07 09:32:47', '2025-10-07 09:32:47'),
(75, 56, 27, NULL, 'despesa', 'outros', 'Carne ', 41.00, '2025-10-07', '2025-10-06', 'pago', 'cartao_debito', NULL, NULL, NULL, '2025-10-07 09:35:21', '2025-10-07 09:35:21'),
(80, 56, 27, NULL, 'receita', 'materiais', 'Parte do valor da obra ', 50000.00, '2025-10-08', '2025-10-08', 'pago', 'pix', NULL, NULL, NULL, '2025-10-08 00:44:35', '2025-10-08 00:44:35'),
(81, 56, 27, NULL, 'despesa', 'mao_de_obra', 'Rogerio última semana de setembro', 1200.00, '2025-10-08', '2025-10-08', 'pago', 'pix', NULL, NULL, NULL, '2025-10-08 00:46:17', '2025-10-08 00:46:17'),
(82, 56, 27, NULL, 'despesa', 'materiais', 'Forro PVC ', 1148.00, '2025-10-08', '2025-10-08', 'pago', 'pix', NULL, NULL, NULL, '2025-10-08 00:47:20', '2025-10-08 00:47:20'),
(83, 56, 27, NULL, 'despesa', 'materiais', 'Material drywall patos ', 7000.00, '2025-10-08', '2025-10-08', 'pago', 'pix', NULL, NULL, NULL, '2025-10-08 00:48:01', '2025-10-08 00:48:01'),
(84, 56, 27, NULL, 'despesa', 'materiais', 'Carrinho e epis', 290.00, '2025-10-08', '2025-10-08', 'pago', 'pix', NULL, NULL, NULL, '2025-10-08 00:49:03', '2025-10-08 00:49:03'),
(85, 56, 27, NULL, 'despesa', 'materiais', 'Spray e fita ', 42.00, '2025-10-08', '2025-10-08', 'pago', 'pix', NULL, NULL, NULL, '2025-10-08 00:49:42', '2025-10-08 00:49:42'),
(86, 56, 27, NULL, 'despesa', 'materiais', 'Projeto ', 20.00, '2025-10-08', '2025-10-08', 'pago', 'pix', NULL, NULL, NULL, '2025-10-08 00:50:05', '2025-10-08 00:50:05'),
(87, 56, 27, NULL, 'despesa', 'aluguel', 'Restante aluguel ap ', 2300.00, '2025-10-08', '2025-10-08', 'pago', 'pix', NULL, NULL, NULL, '2025-10-08 09:56:12', '2025-10-08 09:56:12'),
(88, 70, 15, NULL, 'despesa', 'compraa', 'compra', 100.00, '2025-10-09', '2025-10-09', 'pago', 'pix', 'ssfsfsfsf', 'uploads/financeiro/70/documentos/68e71520a02f1.jpeg', NULL, '2025-10-09 01:51:28', '2025-10-09 01:51:28'),
(89, 70, 15, NULL, 'despesa', 'rytrgrgrg', 'rgrrgr', 2222.00, '2025-10-09', '2025-10-24', 'pago', 'dinheiro', 'null', NULL, NULL, '2025-10-09 03:57:54', '2025-10-09 03:57:54'),
(93, 69, 28, NULL, 'despesa', 'mao_de_obra', '2 semana', 2600.00, '2025-10-10', '2025-10-10', 'pago', 'pix', NULL, NULL, NULL, '2025-10-10 21:55:49', '2025-10-10 21:55:49'),
(94, 69, 28, NULL, 'despesa', 'mao_de_obra', 'Pagamento sábado ', 450.00, '2025-10-11', '2025-10-11', 'pago', 'pix', NULL, NULL, NULL, '2025-10-11 21:10:52', '2025-10-11 21:10:52'),
(105, 79, 38, NULL, 'despesa', 'mao_de_obra', 'Valor da obra', 880000.00, '2025-10-14', '2025-12-30', 'pendente', 'pix', NULL, NULL, NULL, '2025-10-14 14:41:27', '2025-10-15 11:32:34'),
(117, 68, 28, NULL, 'despesa', 'mao_de_obra', '3 semana ', 2500.00, '2025-10-14', '2025-10-10', 'pago', 'pix', NULL, NULL, NULL, '2025-10-14 21:45:24', '2025-10-14 21:45:24'),
(121, 84, 41, NULL, 'receita', 'mao_de_obra', 'VALOR TOTAL DA OBRA', 780000.00, '2025-09-30', '2025-12-30', 'pendente', 'pix', '', NULL, NULL, '2025-10-15 10:04:15', '2025-10-15 11:42:29'),
(122, 84, 41, NULL, 'despesa', 'outros', 'Seguro obra / Paulo Garcia', 385.00, '2025-10-15', '2025-09-30', 'pago', 'pix', '', NULL, NULL, '2025-10-15 10:07:23', '2025-10-15 10:10:54'),
(123, 84, 41, NULL, 'despesa', 'materiais', 'CARRINHO DE MAO E TAMBOR/ PAUlO', 1000.00, '2025-10-15', '2025-10-01', 'pendente', 'pix', '', NULL, NULL, '2025-10-15 10:09:03', '2025-10-15 10:11:24'),
(124, 84, 41, NULL, 'despesa', 'materiais', 'Rubitadeira / Ivanir', 165.00, '2025-10-15', '2025-10-09', 'pago', 'pix', NULL, NULL, NULL, '2025-10-15 10:24:15', '2025-10-15 10:24:15'),
(125, 84, 41, NULL, 'despesa', 'materiais', 'LUVA E DISCO/ ivanir', 216.00, '2025-10-15', '2025-10-13', 'pendente', 'pix', NULL, NULL, NULL, '2025-10-15 10:26:05', '2025-10-15 10:26:05'),
(126, 84, 41, NULL, 'despesa', 'materiais', 'Uniformes_ AGF', 3298.00, '2025-10-15', '2025-10-14', 'pago', 'pix', NULL, NULL, NULL, '2025-10-15 10:28:06', '2025-10-15 10:28:06'),
(132, 84, 41, NULL, 'receita', 'mao_de_obra', 'Mobilização ', 100000.00, '2025-10-15', '2025-10-10', 'pago', 'pix', NULL, NULL, NULL, '2025-10-15 11:04:50', '2025-10-15 11:42:31'),
(140, 85, 42, NULL, 'despesa', 'mao_de_obra', 'Pedreiro', 500.00, '2025-10-16', '2025-10-16', 'pago', 'pix', NULL, NULL, NULL, '2025-10-16 16:17:01', '2025-10-16 16:17:01'),
(141, 40, 12, NULL, 'despesa', 'aluguel', 'Ughh', 380.08, '2025-10-17', '2025-10-17', 'pendente', 'cartao_debito', NULL, NULL, NULL, '2025-10-17 00:44:02', '2025-10-17 00:44:02'),
(142, 40, 12, NULL, 'receita', 'mao_de_obra', 'Huh', 322.55, '2025-10-17', '2025-10-18', 'pendente', 'cartao_credito', NULL, NULL, NULL, '2025-10-17 00:46:00', '2025-10-17 00:46:00'),
(143, 84, 41, NULL, 'despesa', 'mao_de_obra', 'Folha pagamento semana 07/10 a 10/10', 9104.00, '2025-10-17', '2025-10-07', 'pago', 'pix', NULL, NULL, NULL, '2025-10-17 12:37:04', '2025-10-17 12:37:04'),
(144, 84, 41, NULL, 'despesa', 'aluguel', 'Maquina retro/ Agf', 1000.00, '2025-10-17', '2025-10-16', 'pago', 'pix', '', NULL, NULL, '2025-10-17 12:38:21', '2025-10-17 13:03:31'),
(145, 84, 41, NULL, 'despesa', 'outros', 'Combustível semana  07 a 17/10 Ary', 267.00, '2025-10-17', '2025-10-17', 'pendente', 'pix', '', NULL, NULL, '2025-10-17 12:56:34', '2025-10-17 13:02:38'),
(146, 84, 41, NULL, 'despesa', 'materiais', 'Ribitadadeira semana 07 a 17 out Ary', 102.00, '2025-10-17', '2025-10-16', 'pendente', 'pix', '', NULL, NULL, '2025-10-17 12:58:50', '2025-10-17 13:02:27'),
(147, 84, 41, NULL, 'despesa', 'outros', 'Cafe ary', 3075.00, '2025-10-17', '2025-10-16', 'pendente', 'pix', '', NULL, NULL, '2025-10-17 13:00:01', '2025-10-17 13:01:56'),
(148, 84, 41, NULL, 'despesa', 'materiais', 'Ferramentas cachote / telha ary', 651.00, '2025-10-17', '2025-10-15', 'pendente', 'pix', '', NULL, NULL, '2025-10-17 13:01:01', '2025-10-17 13:02:07'),
(149, 84, 41, NULL, 'despesa', 'outros', 'Alimentação ivanir', 45.00, '2025-10-17', '2025-10-15', 'pendente', 'pix', NULL, NULL, NULL, '2025-10-17 13:06:03', '2025-10-17 13:06:03'),
(150, 84, 41, NULL, 'despesa', 'materiais', 'Material ivanir telha', 231.00, '2025-10-17', '2025-10-15', 'pendente', 'pix', '', NULL, NULL, '2025-10-17 13:07:22', '2025-10-17 13:08:01'),
(151, 84, 41, NULL, 'despesa', 'materiais', 'Transformado discos desmepenadeira ivanir', 244.00, '2025-10-17', '2025-10-16', 'pendente', 'pix', NULL, NULL, NULL, '2025-10-17 13:13:28', '2025-10-17 13:13:28'),
(152, 84, 41, NULL, 'despesa', 'mao_de_obra', 'Mao de obra indireta Ivani', 2500.00, '2025-10-17', '2025-10-17', 'pendente', 'pix', NULL, NULL, NULL, '2025-10-17 13:16:00', '2025-10-17 13:16:00'),
(153, 84, 41, NULL, 'despesa', 'mao_de_obra', 'Paulo', 2000.00, '2025-10-17', '2025-10-17', 'pendente', 'pix', NULL, NULL, NULL, '2025-10-17 13:16:51', '2025-10-17 13:16:51'),
(154, 84, 41, NULL, 'despesa', 'mao_de_obra', 'Ary ', 2500.00, '2025-10-17', '2025-10-17', 'pendente', 'pix', NULL, NULL, NULL, '2025-10-17 13:17:36', '2025-10-17 13:17:36'),
(157, 86, 44, NULL, 'despesa', 'materiais', 'Cerâmica da casa ', 5200.00, '2025-10-18', '2025-10-18', 'pago', 'cartao_credito', NULL, NULL, NULL, '2025-10-18 03:11:17', '2025-10-18 03:11:17'),
(158, 86, 44, NULL, 'despesa', 'materiais', 'Tijolos ', 6400.00, '2025-10-18', '2025-10-18', 'pago', 'dinheiro', NULL, NULL, NULL, '2025-10-18 03:11:55', '2025-10-18 03:11:55'),
(159, 86, 44, NULL, 'receita', 'outros', 'Sinal de compra ', 15000.00, '2025-10-18', '2025-10-18', 'pago', 'dinheiro', NULL, NULL, NULL, '2025-10-18 03:12:42', '2025-10-18 03:12:42'),
(160, 69, 28, NULL, 'despesa', 'mao_de_obra', '3 semana', 2600.00, '2025-10-18', '2025-10-17', 'pago', 'pix', NULL, NULL, NULL, '2025-10-18 09:01:55', '2025-10-18 09:01:55'),
(161, 68, 28, NULL, 'despesa', 'mao_de_obra', '4 semana ', 2850.00, '2025-10-18', '2025-10-17', 'pago', 'pix', NULL, NULL, NULL, '2025-10-18 23:01:31', '2025-10-18 23:01:31'),
(162, 41, 15, NULL, 'despesa', ' Preparação do terreno', ' Terraplanagem', 5000.00, '2025-10-23', '2025-10-24', 'pago', 'pix', 'null', NULL, NULL, '2025-10-22 20:52:37', '2025-10-23 13:16:44'),
(163, 41, 15, 24, 'despesa', 'conta_agua', 'projetos', 12000.00, '2025-10-23', '2025-10-31', 'pago', 'dinheiro', 'null', NULL, NULL, '2025-10-22 21:00:14', '2025-10-30 17:10:51'),
(165, 92, 50, NULL, 'despesa', 'mao_de_obra', 'Pintor', 1100.00, '2025-10-25', '2025-10-25', 'pago', 'pix', NULL, NULL, NULL, '2025-10-25 02:56:26', '2025-10-25 02:56:26'),
(166, 92, 50, NULL, 'despesa', 'mao_de_obra', 'Bombeiro hidráulico ', 350.00, '2025-10-25', '2025-10-21', 'pago', 'pix', NULL, NULL, NULL, '2025-10-25 02:57:46', '2025-10-25 02:57:46'),
(167, 92, 50, NULL, 'despesa', 'servico', 'Vidraceiro', 365.00, '2025-10-25', '2025-10-24', 'pago', 'pix', NULL, NULL, NULL, '2025-10-25 02:59:04', '2025-10-25 02:59:04'),
(168, 69, 28, NULL, 'despesa', 'mao_de_obra', '2 etapa', 2750.00, '2025-10-26', '2025-10-24', 'pago', 'pix', NULL, NULL, NULL, '2025-10-26 09:39:04', '2025-10-26 09:39:04'),
(169, 68, 28, NULL, 'despesa', 'mao_de_obra', '5 semana', 2500.00, '2025-10-26', '2025-10-24', 'pago', 'pix', NULL, NULL, NULL, '2025-10-26 09:40:20', '2025-10-26 09:40:20'),
(170, 68, 28, NULL, 'despesa', 'mao_de_obra', 'Sabado', 560.00, '2025-10-26', '2025-10-19', 'pago', 'pix', NULL, NULL, NULL, '2025-10-26 09:40:53', '2025-10-26 09:40:53'),
(171, 94, 52, NULL, 'receita', 'outros', 'Valor do investimento para construção do muro ', 21905.99, '2025-10-28', '2025-10-28', 'pago', 'cartao_credito', NULL, NULL, NULL, '2025-10-28 00:53:48', '2025-10-28 00:53:48'),
(172, 97, 56, NULL, 'despesa', 'mao_de_obra', 'Pedreiro ', 500.00, '2025-10-30', '2025-10-30', 'pago', 'pix', NULL, NULL, NULL, '2025-10-30 12:47:38', '2025-10-30 12:47:38'),
(175, 41, 15, 24, 'receita', 'gdsfgdfg', 'wetwert', 53453.45, '2025-10-03', '0000-00-00', 'pendente', 'pix', 'wetwetwer', NULL, NULL, '2025-10-30 16:22:06', '2025-10-30 16:22:06'),
(176, 99, 59, NULL, 'despesa', 'mao_de_obra', 'Pagamento Regis ', 10000.00, '2025-10-31', '2025-10-30', 'pago', 'pix', '', 'uploads/financeiro/176/documentos/CA7455AF-4717-4913-BB8C-5A2FB47A5D0A.pdf', 'CA7455AF-4717-4913-BB8C-5A2FB47A5D0A.pdf', '2025-10-31 19:15:58', '2025-11-03 16:46:42'),
(177, 99, 59, NULL, 'despesa', 'mao_de_obra', 'Pagamento Regis ', 400.00, '2025-10-31', '2025-10-31', 'pago', 'outro', '', 'uploads/financeiro/177/documentos/Screenshot_2025-10-31-16-15-33-047_com_whatsapp.jpg', 'Screenshot_2025-10-31-16-15-33-047_com.whatsapp.jpg', '2025-10-31 19:16:38', '2025-11-03 16:46:53'),
(178, 69, 28, NULL, 'despesa', 'mao_de_obra', '5 semana', 2200.00, '2025-10-31', '2025-10-31', 'pago', 'pix', NULL, NULL, NULL, '2025-10-31 23:24:56', '2025-10-31 23:24:56'),
(179, 68, 28, NULL, 'despesa', 'mao_de_obra', '6 semana', 2500.00, '2025-10-31', '2025-10-31', 'pago', 'pix', NULL, NULL, NULL, '2025-10-31 23:25:49', '2025-10-31 23:25:49'),
(180, 100, 60, NULL, 'receita', 'servico', 'Pago inicial para inicio de obra ', 9500000.00, '2025-11-01', '2025-11-01', 'pago', 'transferencia', NULL, NULL, NULL, '2025-11-01 13:28:47', '2025-11-01 13:28:47'),
(181, 99, 59, NULL, 'despesa', 'material', 'Eletro Móveis ', 21000.00, '2025-12-31', '2025-10-31', 'pago', 'pix', '', 'uploads/financeiro/181/documentos/IMG-20251103-WA0039.jpg', 'IMG-20251103-WA0039.jpg', '2025-11-03 16:45:08', '2025-11-03 17:15:37'),
(182, 99, 59, NULL, 'despesa', 'servico', 'Copel ', 1853.47, '2025-11-03', '2025-11-03', 'pago', 'cartao_credito', NULL, 'uploads/financeiro/182/documentos/IMG-20251103-WA0016.jpg', 'IMG-20251103-WA0016.jpg', '2025-11-03 16:46:12', '2025-11-03 16:46:12'),
(183, 101, 63, NULL, 'despesa', 'mao_de_obra', 'Mãos de obra (2 servente por 4 diárias)', 960.00, '2025-11-07', '2025-11-07', 'pendente', 'pix', '', NULL, NULL, '2025-11-04 03:01:45', '2025-11-04 03:08:21'),
(185, 101, 63, NULL, 'receita', 'outros', 'Capital de Investimento', 47000.00, '2025-11-04', '2025-12-20', 'pago', 'transferencia', NULL, NULL, NULL, '2025-11-04 03:04:36', '2025-11-04 03:04:36'),
(186, 103, 67, NULL, 'receita', 'mao_de_obra', 'Mão de obra', 7500.00, '2025-11-05', '2025-10-28', 'pendente', 'pix', NULL, NULL, NULL, '2025-11-05 01:02:11', '2025-11-05 01:02:11'),
(187, 103, 67, NULL, 'despesa', 'servico', 'Adiantamento', 2450.00, '2025-11-05', '2025-11-02', 'pago', 'pix', NULL, NULL, NULL, '2025-11-05 01:04:50', '2025-11-05 01:04:50'),
(188, 104, 67, NULL, 'receita', 'mao_de_obra', 'Mão de obra ', 3200.00, '2025-10-30', '2025-11-07', 'pendente', 'pix', '', NULL, NULL, '2025-11-05 01:22:45', '2025-11-06 22:56:58'),
(189, 104, 67, NULL, 'despesa', 'mao_de_obra', 'Adiantamento ', 1000.00, '2025-10-31', '2025-11-02', 'pago', 'pix', '', NULL, NULL, '2025-11-05 01:23:17', '2025-11-06 22:50:40'),
(190, 92, 50, NULL, 'despesa', 'materiais', 'Material', 164.00, '2025-11-06', '2025-10-22', 'pago', 'cartao_credito', NULL, NULL, NULL, '2025-11-06 16:43:43', '2025-11-06 16:43:43'),
(191, 92, 50, NULL, 'despesa', 'materiais', 'Mat', 75.00, '2025-11-06', '2025-10-22', 'pago', 'cartao_credito', NULL, NULL, NULL, '2025-11-06 16:44:14', '2025-11-06 16:44:14'),
(192, 92, 50, NULL, 'despesa', 'materiais', 'Material', 54.00, '2025-11-06', '2025-10-22', 'pago', 'cartao_credito', NULL, NULL, NULL, '2025-11-06 16:44:58', '2025-11-06 16:44:58'),
(193, 92, 50, NULL, 'despesa', 'materiais', 'Material ', 9.90, '2025-11-06', '2025-10-17', 'pago', 'cartao_credito', NULL, NULL, NULL, '2025-11-06 16:45:55', '2025-11-06 16:45:55'),
(194, 92, 50, NULL, 'despesa', 'materiais', 'Material ', 42.30, '2025-11-06', '2025-10-17', 'pago', 'cartao_credito', NULL, NULL, NULL, '2025-11-06 16:46:42', '2025-11-06 16:46:42'),
(195, 92, 50, NULL, 'despesa', 'materiais', 'Material ', 33.00, '2025-11-06', '2025-10-15', 'pago', 'cartao_credito', NULL, NULL, NULL, '2025-11-06 16:47:16', '2025-11-06 16:47:16'),
(196, 92, 50, NULL, 'despesa', 'materiais', 'Material ', 17.00, '2025-11-06', '2025-10-15', 'pago', 'cartao_credito', NULL, NULL, NULL, '2025-11-06 16:47:50', '2025-11-06 16:47:50'),
(197, 92, 50, NULL, 'despesa', 'materiais', 'Material ', 110.30, '2025-11-06', '2025-10-15', 'pago', 'cartao_credito', NULL, NULL, NULL, '2025-11-06 16:48:19', '2025-11-06 16:48:19'),
(198, 92, 50, NULL, 'despesa', 'materiais', 'Material ', 194.00, '2025-11-06', '2025-10-15', 'pago', 'cartao_credito', NULL, NULL, NULL, '2025-11-06 16:48:54', '2025-11-06 16:48:54'),
(199, 92, 50, NULL, 'despesa', 'materiais', 'Tinta', 740.04, '2025-11-06', '2025-10-15', 'pago', 'cartao_credito', NULL, NULL, NULL, '2025-11-06 16:51:06', '2025-11-06 16:51:06'),
(200, 92, 50, NULL, 'despesa', 'materiais', 'Borrifador', 48.00, '2025-11-06', '2025-10-15', 'pago', 'cartao_credito', NULL, NULL, NULL, '2025-11-06 16:51:55', '2025-11-06 16:51:55'),
(201, 92, 50, NULL, 'despesa', 'materiais', 'Aluguel carro', 194.34, '2025-11-06', '2025-10-15', 'pago', 'cartao_credito', NULL, NULL, NULL, '2025-11-06 16:53:47', '2025-11-06 16:53:47'),
(202, 92, 50, NULL, 'despesa', 'mao_de_obra', 'Taxa carro', 25.18, '2025-11-06', '2025-10-15', 'pago', 'pix', NULL, NULL, NULL, '2025-11-06 16:55:48', '2025-11-06 16:55:48'),
(203, 92, 50, NULL, 'despesa', 'materiais', 'Gasolina', 59.90, '2025-11-06', '2025-10-16', 'pago', 'pix', NULL, NULL, NULL, '2025-11-06 16:57:59', '2025-11-06 16:57:59'),
(204, 92, 50, NULL, 'despesa', 'materiais', 'Pedagio', 13.70, '2025-11-06', '2025-10-15', 'pago', 'pix', NULL, NULL, NULL, '2025-11-06 16:58:57', '2025-11-06 16:58:57'),
(205, 92, 50, NULL, 'despesa', 'materiais', 'Agua', 1630.00, '2025-11-06', '2025-11-06', 'pendente', 'pix', NULL, NULL, NULL, '2025-11-06 17:09:20', '2025-11-06 17:09:20'),
(206, 92, 50, NULL, 'despesa', 'materiais', 'Luz', 100.00, '2025-11-06', '2025-11-06', 'pago', 'pix', NULL, NULL, NULL, '2025-11-06 17:10:07', '2025-11-06 17:10:07'),
(207, 92, 50, NULL, 'despesa', 'materiais', 'Anuncio olx', 50.00, '2025-11-06', '2025-09-10', 'pago', 'pix', NULL, NULL, NULL, '2025-11-06 17:11:12', '2025-11-06 17:11:12'),
(208, 92, 50, NULL, 'despesa', 'materiais', 'Uber', 375.74, '2025-11-06', '2025-10-15', 'pago', 'pix', NULL, NULL, NULL, '2025-11-06 17:17:27', '2025-11-06 17:17:27'),
(209, 104, 67, NULL, 'despesa', 'mao_de_obra', 'Adiantamento ', 400.00, '2025-11-06', '2025-11-06', 'pago', 'pix', NULL, NULL, NULL, '2025-11-06 22:49:26', '2025-11-06 22:49:26'),
(210, 104, 67, NULL, 'despesa', 'mao_de_obra', 'Adiantamento ', 200.00, '2025-10-31', '2025-10-03', 'pago', 'pix', '', NULL, NULL, '2025-11-06 22:51:00', '2025-11-06 22:55:43'),
(211, 104, 67, NULL, 'despesa', 'mao_de_obra', 'Adiantamento ', 600.00, '2025-11-01', '2025-11-01', 'pago', 'pix', '', NULL, NULL, '2025-11-06 22:51:28', '2025-11-06 22:55:51'),
(212, 104, 67, NULL, 'despesa', 'mao_de_obra', 'Adiantamento ', 800.00, '2025-11-01', '2025-11-01', 'pago', 'pix', '', NULL, NULL, '2025-11-06 22:52:10', '2025-11-06 22:56:11'),
(213, 104, 67, NULL, 'despesa', 'mao_de_obra', 'Adiantamento ', 150.00, '2025-11-01', '2025-11-01', 'pago', 'pix', '', NULL, NULL, '2025-11-06 22:53:27', '2025-11-06 22:56:23'),
(214, 69, 28, NULL, 'despesa', 'mao_de_obra', 'Semanal', 3000.00, '2025-11-07', '2025-11-07', 'pago', 'pix', NULL, NULL, NULL, '2025-11-07 23:38:55', '2025-11-07 23:38:55'),
(215, 68, 28, NULL, 'despesa', 'mao_de_obra', '7 semana', 2100.00, '2025-11-07', '2025-11-07', 'pago', 'pix', NULL, NULL, NULL, '2025-11-07 23:41:43', '2025-11-07 23:41:43'),
(223, 107, 70, NULL, 'receita', 'outros', 'Contato', 83000.00, '2025-11-09', '2026-01-04', 'pendente', 'pix', '', NULL, NULL, '2025-11-09 20:46:53', '2025-11-09 21:12:10'),
(224, 107, 70, 26, 'despesa', 'material', 'Serralheiro mezanino', 5000.00, '2025-11-09', '2025-11-07', 'pago', 'pix', '', NULL, NULL, '2025-11-09 20:47:48', '2025-11-09 21:04:46'),
(225, 107, 70, NULL, 'despesa', 'mao_de_obra', 'Pedreiro Iris 5 diaria', 1250.00, '2025-11-09', '2025-11-07', 'pago', 'pix', '', NULL, NULL, '2025-11-09 20:49:34', '2025-11-09 21:05:07'),
(226, 107, 70, 30, 'despesa', 'mao_de_obra', 'Pintura muro 6 diária ', 1500.00, '2025-11-09', '2025-11-07', 'pago', 'pix', NULL, NULL, NULL, '2025-11-09 21:06:03', '2025-11-09 21:06:03'),
(227, 107, 70, 25, 'despesa', 'materiais', 'Material pintura muro e forra piso', 1000.00, '2025-11-09', '2025-11-07', 'pago', 'pix', NULL, NULL, NULL, '2025-11-09 21:06:44', '2025-11-09 21:06:44'),
(228, 107, 70, 28, 'despesa', 'materiais', 'Caçamba entulho', 500.00, '2025-11-09', '2025-11-07', 'pendente', 'pix', NULL, NULL, NULL, '2025-11-09 21:07:23', '2025-11-09 21:07:23'),
(229, 107, 70, 27, 'despesa', 'aluguel', 'A locadora ,andaimes 20mts , piso,trava, betoneira, martelete', 1220.00, '2025-11-09', '2025-11-07', 'pendente', 'boleto', NULL, NULL, NULL, '2025-11-09 21:09:27', '2025-11-09 21:09:27'),
(230, 107, 70, 29, 'despesa', 'materiais', 'Gasmac, Tijolo,areia ,Britânia, cimento,ferro,Taboão,vedalite', 3560.00, '2025-11-09', '2025-11-07', 'pendente', 'boleto', NULL, NULL, NULL, '2025-11-09 21:10:54', '2025-11-09 21:10:54');

-- --------------------------------------------------------

--
-- Estrutura para tabela `layout_relatorio_cliente`
--

CREATE TABLE `layout_relatorio_cliente` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `logo_path` varchar(255) DEFAULT NULL COMMENT 'Caminho do arquivo de logo',
  `logo_nome` varchar(255) DEFAULT NULL COMMENT 'Nome original do arquivo de logo',
  `cor_primaria` varchar(7) NOT NULL COMMENT 'Cor primária em hexadecimal',
  `mostrar_data` tinyint(1) DEFAULT 1 COMMENT 'Se deve mostrar a data',
  `formato_data` varchar(20) DEFAULT 'd/m/Y' COMMENT 'Formato da data',
  `ativo` tinyint(1) DEFAULT 1 COMMENT 'Se o layout está ativo',
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `assinatura_imagem_path` varchar(255) DEFAULT NULL,
  `assinatura_imagem_nome` varchar(255) DEFAULT NULL,
  `assinatura_desenhada_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `layout_relatorio_cliente`
--

INSERT INTO `layout_relatorio_cliente` (`id`, `usuario_id`, `logo_path`, `logo_nome`, `cor_primaria`, `mostrar_data`, `formato_data`, `ativo`, `data_criacao`, `data_atualizacao`, `assinatura_imagem_path`, `assinatura_imagem_nome`, `assinatura_desenhada_path`) VALUES
(4, 15, '/uploads/logos/logo_15_1761739251.jpg', 'logo.jpg', '#000', 1, 'd/m/Y', 1, '2025-09-05 17:04:33', '2025-10-30 02:42:17', NULL, NULL, '/uploads/signatures/assinatura_desenhada_15_1761707007.png'),
(5, 41, '/uploads/logos/logo_41_1760551878.jpg', 'logo.jpg', '#0000FF', 1, 'd/m/Y', 1, '2025-10-15 11:17:44', '2025-10-15 18:15:05', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `lembretes`
--

CREATE TABLE `lembretes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL COMMENT 'ID do usuário que criou o lembrete',
  `obra_id` int(11) DEFAULT NULL COMMENT 'ID da obra vinculada (opcional)',
  `titulo` varchar(200) NOT NULL COMMENT 'Título do lembrete',
  `descricao` text DEFAULT NULL COMMENT 'Descrição detalhada do lembrete',
  `data_lembrete` datetime NOT NULL COMMENT 'Data e hora do lembrete',
  `prioridade` enum('baixa','media','alta') NOT NULL DEFAULT 'media' COMMENT 'Prioridade do lembrete',
  `status` enum('pendente','concluido','cancelado') NOT NULL DEFAULT 'pendente' COMMENT 'Status do lembrete',
  `tipo` enum('geral','obra','financeiro','reuniao','prazo') NOT NULL DEFAULT 'geral' COMMENT 'Tipo/categoria do lembrete',
  `notificar_email` tinyint(1) DEFAULT 0 COMMENT 'Enviar notificação por email',
  `notificar_sistema` tinyint(1) DEFAULT 1 COMMENT 'Mostrar notificação no sistema',
  `data_notificacao` datetime DEFAULT NULL COMMENT 'Data para envio da notificação',
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Data e hora do cadastro',
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Data e hora da última atualização',
  `data_conclusao` datetime DEFAULT NULL COMMENT 'Data e hora da conclusão do lembrete',
  `data_criacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de lembretes do sistema';

--
-- Despejando dados para a tabela `lembretes`
--

INSERT INTO `lembretes` (`id`, `usuario_id`, `obra_id`, `titulo`, `descricao`, `data_lembrete`, `prioridade`, `status`, `tipo`, `notificar_email`, `notificar_sistema`, `data_notificacao`, `data_cadastro`, `data_atualizacao`, `data_conclusao`, `data_criacao`) VALUES
(16, 11, NULL, 'Test', 'Fjdk', '2025-08-18 19:14:00', 'media', 'pendente', 'geral', 0, 1, NULL, '2025-08-18 19:14:25', '2025-08-18 19:14:25', NULL, '2025-08-18 19:14:25'),
(24, 16, NULL, 'Compra de material', 'Comprar material para pintar a parede', '2025-10-30 10:03:00', 'media', 'pendente', 'geral', 0, 1, NULL, '2025-09-12 22:04:27', '2025-09-12 22:04:27', NULL, '2025-09-12 22:04:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `lembretes_confirmacoes`
--

CREATE TABLE `lembretes_confirmacoes` (
  `id` int(11) NOT NULL,
  `lembrete_id` int(11) NOT NULL COMMENT 'ID do lembrete confirmado',
  `usuario_id` int(11) NOT NULL COMMENT 'ID do usuário que confirmou',
  `data_confirmacao` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Data e hora da confirmação'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Confirmações de lembretes por usuário';

--
-- Despejando dados para a tabela `lembretes_confirmacoes`
--

INSERT INTO `lembretes_confirmacoes` (`id`, `lembrete_id`, `usuario_id`, `data_confirmacao`) VALUES
(1, 33, 15, '2025-10-18 18:43:02'),
(2, 34, 15, '2025-10-29 17:50:22');

-- --------------------------------------------------------

--
-- Estrutura para tabela `obras`
--

CREATE TABLE `obras` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL COMMENT 'ID do usuário que cadastrou a obra',
  `tipo_pessoa` enum('fisica','juridica') DEFAULT 'fisica' COMMENT 'Tipo de pessoa do cliente',
  `cnpj_cpf` varchar(20) DEFAULT NULL COMMENT 'CNPJ ou CPF do cliente',
  `razao_social` varchar(200) DEFAULT NULL COMMENT 'Razão social ou nome do cliente',
  `telefone_cliente` varchar(20) DEFAULT NULL COMMENT 'Telefone do cliente',
  `nome_obra` varchar(200) NOT NULL COMMENT 'Nome da obra',
  `responsavel` varchar(100) DEFAULT NULL COMMENT 'Nome do responsável pela obra',
  `status` enum('planejamento','em_andamento','pausado','concluido','cancelado') NOT NULL DEFAULT 'planejamento' COMMENT 'Status da obra',
  `data_inicio` date DEFAULT NULL COMMENT 'Data de início da obra',
  `previsao_termino` date DEFAULT NULL COMMENT 'Previsão de término da obra',
  `cep` varchar(10) DEFAULT NULL COMMENT 'CEP da obra',
  `endereco` varchar(200) DEFAULT NULL COMMENT 'Endereço da obra',
  `numero` varchar(20) DEFAULT NULL COMMENT 'Número do endereço',
  `complemento` varchar(100) DEFAULT NULL COMMENT 'Complemento do endereço',
  `bairro` varchar(100) DEFAULT NULL COMMENT 'Bairro da obra',
  `cidade` varchar(100) DEFAULT NULL COMMENT 'Cidade da obra',
  `estado` varchar(2) DEFAULT NULL COMMENT 'Estado da obra (UF)',
  `escopo` text DEFAULT NULL COMMENT 'Escopo do projeto',
  `capa` varchar(255) DEFAULT NULL COMMENT 'Caminho da imagem de capa',
  `data_cadastro` timestamp NULL DEFAULT current_timestamp() COMMENT 'Data e hora do cadastro',
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Data e hora da última atualização'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de obras do sistema';

--
-- Despejando dados para a tabela `obras`
--

INSERT INTO `obras` (`id`, `usuario_id`, `tipo_pessoa`, `cnpj_cpf`, `razao_social`, `telefone_cliente`, `nome_obra`, `responsavel`, `status`, `data_inicio`, `previsao_termino`, `cep`, `endereco`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `escopo`, `capa`, `data_cadastro`, `data_atualizacao`) VALUES
(21, 16, NULL, NULL, 'Restaurar paredes', NULL, 'Restaurar paredes', 'Restaurar paredes', 'planejamento', '2025-10-08', '2025-10-12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-05 14:41:48', '2025-09-07 15:14:53'),
(34, 16, NULL, NULL, 'Pintar cerâmica.', NULL, 'Pintar cerâmica.', 'Pintar cerâmica.', '', '2025-11-05', '2025-12-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-10 09:31:07', '2025-09-10 09:31:07'),
(35, 15, 'fisica', '112.122.222-22', 'Construção piscina', '(22) 22222-2222', 'Construção piscina', 'Construção piscina', 'em_andamento', '2025-09-07', '2025-12-31', '88600-300', 'rua das flores', '123', '', 'santana', 'Londrina', 'PR', 'Construção da piscina.', 'uploads/obras/obra_1758569710_68d1a4ee521f2.jpg', '2025-09-11 11:58:37', '2025-09-22 19:58:19'),
(40, 12, NULL, NULL, 'Reforma', NULL, 'Reforma', 'Reforma', '', '2025-09-15', '2025-09-15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/obras/obra_1757955461_68c84585d2f5e.jpg', '2025-09-15 16:57:41', '2025-09-15 16:57:41'),
(41, 15, 'fisica', '111.111.111-11', 'Casa do Jorge', '(43) 11111-1111', 'Casa do Jorge', 'Eng Gustavo', 'planejamento', '2025-09-17', '2026-03-27', '86499-999', 'Rua das flores', '111', '', 'floral', 'Jacarezinho', 'PR', 'Projeto de construção de uma casa térrea de 150 m².', 'uploads/obras/68cb50b7ad423_mao-de-obra.jpg', '2025-09-18 00:22:13', '2025-10-22 20:44:40'),
(50, 15, 'fisica', '', 'Casa Gilmar', '', 'Casa Gilmar', 'Casa Gilmar', 'planejamento', '2026-01-23', '2027-06-25', '', '', '', '', '', '', '', '', 'uploads/obras/68d1aee055e02_images (1).jpg', '2025-09-22 20:17:36', '2025-09-22 20:17:36'),
(53, 24, NULL, NULL, 'Ampliación de Vivienda Multifamiliar - Wilson', NULL, 'Ampliación de Vivienda Multifamiliar - Wilson', 'Ampliación de Vivienda Multifamiliar - Wilson', 'planejamento', '2025-09-24', '2025-09-24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-25 01:18:11', '2025-09-25 01:18:56'),
(54, 25, NULL, NULL, 'Construção Banheiro Fertirrigação', NULL, 'Construção Banheiro Fertirrigação', 'Construção Banheiro Fertirrigação', '', '2025-09-06', '2025-10-25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/obras/obra_1758933956_68d733c4a82f4.jpg', '2025-09-27 00:45:56', '2025-09-27 00:45:56'),
(55, 26, NULL, NULL, 'Reforma ap sr. Pereira', NULL, 'Reforma ap sr. Pereira', 'Reforma ap sr. Pereira', '', '2025-08-04', '2025-10-10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-27 10:29:19', '2025-09-27 10:29:19'),
(56, 27, NULL, NULL, 'Obra clinica patos de minas', NULL, 'Obra clinica patos de minas', 'Obra clinica patos de minas', '', '2025-10-01', '2025-12-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-28 18:28:09', '2025-09-28 18:28:09'),
(57, 27, NULL, NULL, 'Obra represa', NULL, 'Obra represa', 'Obra represa', '', '2025-09-28', '2025-10-04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-28 20:10:42', '2025-09-28 20:10:42'),
(60, 29, NULL, NULL, 'Escola gracinda', NULL, 'Escola gracinda', 'Escola gracinda', '', '2025-07-02', '2025-12-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-01 02:18:04', '2025-10-01 02:18:04'),
(61, 30, NULL, NULL, 'Obra 1', NULL, 'Obra 1', 'Obra 1', '', '2025-10-01', '2026-07-31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-01 16:46:44', '2025-10-01 16:46:44'),
(62, 31, NULL, NULL, 'Madero (New York) Rio de janeiro - Barra - Área administrativa', NULL, 'Madero (New York) Rio de janeiro - Barra - Área administrativa', 'Madero (New York) Rio de janeiro - Barra - Área administrativa', 'planejamento', '2025-10-01', '2025-10-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-01 21:36:05', '2025-10-01 21:36:49'),
(64, 32, NULL, NULL, 'Retiro - Cláudio', NULL, 'Retiro - Cláudio', 'Retiro - Cláudio', 'em_andamento', '2025-09-28', '2025-12-04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/obras/obra_1759362414_68ddbd6ee8eb4.jpg', '2025-10-01 23:46:54', '2025-10-01 23:47:17'),
(66, 32, NULL, NULL, 'Wanderson - Viverde', NULL, 'Wanderson - Viverde', 'Wanderson - Viverde', 'em_andamento', '2025-09-21', '2026-03-29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/obras/obra_1759362913_68ddbf611ad97.jpg', '2025-10-01 23:55:13', '2025-10-01 23:55:22'),
(67, 33, NULL, NULL, 'Obra Eduardo', NULL, 'Obra Eduardo', 'Obra Eduardo', 'planejamento', '2025-07-31', '2026-12-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-02 08:48:13', '2025-10-02 08:49:44'),
(68, 28, NULL, NULL, 'Wanderson Viverde', NULL, 'Wanderson Viverde', 'Wanderson Viverde', 'em_andamento', '2025-09-20', '2026-03-31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/obras/obra_1759547987_68e09253e40e0.jpg', '2025-10-04 03:19:47', '2025-10-06 15:37:16'),
(69, 28, NULL, NULL, 'Claudio - Retiro', NULL, 'Claudio - Retiro', 'Claudio - Retiro', 'em_andamento', '2025-09-28', '2025-12-04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/obras/obra_1759548045_68e0928d634d6.jpg', '2025-10-04 03:20:45', '2025-10-04 03:20:52'),
(72, 36, NULL, NULL, 'Casa', NULL, 'Casa', 'Casa', '', '2025-10-01', '2027-04-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/obras/obra_1760112485_68e92f65ef72a.jpg', '2025-10-10 16:08:05', '2025-10-10 16:08:05'),
(79, 38, NULL, NULL, 'Vale dos cristais', NULL, 'Vale dos cristais', 'Vale dos cristais', '', '2025-09-30', '2025-12-29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-14 14:40:19', '2025-10-14 14:40:19'),
(80, 40, NULL, NULL, 'Casa ms', NULL, 'Casa ms', 'Casa ms', '', '2025-10-15', '2025-10-15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-15 01:12:55', '2025-10-15 01:12:55'),
(84, 41, NULL, NULL, 'Vale dos Cristais', NULL, 'Vale dos Cristais', 'Vale dos Cristais', '', '2025-09-30', '2025-12-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-15 10:03:18', '2025-10-15 10:03:18'),
(85, 42, NULL, NULL, 'Casa clebson', NULL, 'Casa clebson', 'Casa clebson', '', '2025-10-16', '2025-11-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-16 16:15:48', '2025-10-16 16:15:48'),
(86, 44, NULL, NULL, 'Casa Alonso', NULL, 'Casa Alonso', 'Casa Alonso', 'concluido', '2025-10-17', '2025-11-29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/obras/obra_1760756999_68f30507ede33.jpg', '2025-10-18 03:09:59', '2025-10-18 03:13:41'),
(87, 44, NULL, NULL, 'Casa Rogério', NULL, 'Casa Rogério', 'Casa Rogério', '', '2025-10-18', '2025-12-31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-18 03:13:16', '2025-10-18 03:13:16'),
(88, 46, NULL, NULL, 'Vitta Home Resort', NULL, 'Vitta Home Resort', 'Vitta Home Resort', '', '2022-08-28', '2025-10-19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-19 18:22:46', '2025-10-19 18:22:46'),
(89, 46, NULL, NULL, 'Smart Way Camboriú', NULL, 'Smart Way Camboriú', 'Smart Way Camboriú', '', '2025-10-19', '2025-10-19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-19 18:23:16', '2025-10-19 18:23:16'),
(90, 47, 'fisica', '', 'Serraria', '', 'Serraria', 'Serraria', 'planejamento', '2025-06-24', '2026-09-25', '', '', '', '', '', '', '', '', NULL, '2025-10-21 12:42:53', '2025-10-21 12:42:53'),
(92, 50, NULL, NULL, 'ROCHA PITA 44', NULL, 'ROCHA PITA 44', 'ROCHA PITA 44', '', '2025-10-14', '2025-10-25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 02:54:54', '2025-10-25 02:54:54'),
(93, 48, NULL, NULL, 'Nest23', NULL, 'Nest23', 'Nest23', '', '2025-10-27', '2025-11-21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/obras/obra_1761530922_68fed42a05f2d.jpg', '2025-10-27 02:08:42', '2025-10-27 02:08:42'),
(94, 52, NULL, NULL, 'Muro Casa 46 quadra 17', NULL, 'Marcos Moreira de Lira', 'Muro Casa 46 quadra 17', 'planejamento', '2025-10-27', '2025-10-27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-28 00:27:07', '2025-10-28 00:56:51'),
(95, 53, NULL, NULL, 'Mônica', NULL, 'Mônica', 'Mônica', '', '2025-10-27', '2025-11-14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-28 13:25:37', '2025-10-28 13:25:37'),
(96, 54, NULL, NULL, 'Usina Buriti', NULL, 'Usina Buriti', 'Usina Buriti', '', '2025-10-27', '2026-03-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-29 14:29:45', '2025-10-29 14:29:45'),
(97, 56, NULL, NULL, 'Casa', NULL, 'Casa', 'Casa', '', '2025-10-30', '2025-11-12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-30 12:46:08', '2025-10-30 12:46:08'),
(98, 55, NULL, NULL, 'Iolanda', NULL, 'Iolanda', 'Iolanda', 'concluido', '2025-10-26', '2025-10-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-30 13:20:54', '2025-11-01 22:44:11'),
(99, 59, NULL, NULL, 'Cada', NULL, 'Casa', 'Cada', 'planejamento', '2025-10-30', '2026-07-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/obras/obra_1761938020_69050a64d8ec1.jpg', '2025-10-31 19:13:40', '2025-11-02 20:01:31'),
(100, 60, NULL, NULL, 'ZOME TALLER DE CERÁMICAS', NULL, 'ZOME TALLER DE CERÁMICAS', 'ZOME TALLER DE CERÁMICAS', '', '2025-10-14', '2026-01-15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-01 13:25:00', '2025-11-01 13:25:00'),
(101, 63, NULL, NULL, 'Casa dos Pais', NULL, 'Casa dos Pais', 'Casa dos Pais', '', '2025-11-05', '2025-12-21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-04 02:58:50', '2025-11-04 02:58:50'),
(102, 66, NULL, NULL, 'Ngi diarias', NULL, 'Ngi diarias', 'Ngi diarias', 'em_andamento', '2025-11-02', '2025-12-02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-04 18:06:05', '2025-11-04 18:06:56'),
(103, 67, NULL, NULL, 'Reforma casa Jeane', NULL, 'Reforma casa Jeane', 'Reforma casa Jeane', 'em_andamento', '2025-10-27', '2025-11-08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/obras/obra_1762304191_690aa0bfb8fa9.jpg', '2025-11-05 00:56:31', '2025-11-05 00:56:49'),
(104, 67, NULL, NULL, 'Reforma muro Kayrom', NULL, 'Reforma muro Kayrom', 'Reforma muro Kayrom', 'em_andamento', '2025-10-31', '2025-11-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/obras/obra_1762305664_690aa68004d58.jpg', '2025-11-05 01:21:04', '2025-11-05 01:21:18'),
(106, 69, 'fisica', '', 'Teste', '', 'Teste', 'Teste', 'planejamento', '2025-11-08', '2025-11-29', '', '', '', '', '', '', '', '', NULL, '2025-11-08 14:29:32', '2025-11-08 14:29:32'),
(107, 70, NULL, NULL, 'Obra Macedo e Andrade Advogados', NULL, 'Obra Macedo e Andrade Advogados', 'Obra Macedo e Andrade Advogados', '', '2025-10-27', '2026-01-04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-09 20:45:47', '2025-11-09 20:45:47'),
(108, 70, NULL, NULL, 'Wilson apartamento', NULL, 'Wilson apartamento', 'Wilson apartamento', '', '2025-10-07', '2025-12-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-09 21:12:55', '2025-11-09 21:12:55');

-- --------------------------------------------------------

--
-- Estrutura para tabela `orcamentos`
--

CREATE TABLE `orcamentos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL COMMENT 'ID do usuário que cadastrou o orçamento',
  `obra_id` int(11) DEFAULT NULL COMMENT 'ID da obra vinculada (opcional)',
  `cliente` varchar(255) NOT NULL COMMENT 'Nome do cliente',
  `cpf_cnpj` varchar(255) DEFAULT NULL COMMENT 'documento orcamento',
  `telefone` varchar(255) DEFAULT NULL COMMENT 'telefone contato',
  `valor` decimal(60,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor total do orçamento',
  `titulo` varchar(255) DEFAULT NULL COMMENT 'titulo',
  `escopo` text DEFAULT NULL COMMENT 'escopo',
  `data` date NOT NULL COMMENT 'Data do orçamento',
  `validade` date DEFAULT NULL COMMENT 'Data de validade do orçamento',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending' COMMENT 'Status do orçamento',
  `observacoes` text DEFAULT NULL COMMENT 'Observações adicionais',
  `data_cadastro` datetime DEFAULT current_timestamp() COMMENT 'Data de cadastro do orçamento',
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Data da última atualização'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de orçamentos do sistema';

--
-- Despejando dados para a tabela `orcamentos`
--

INSERT INTO `orcamentos` (`id`, `usuario_id`, `obra_id`, `cliente`, `cpf_cnpj`, `telefone`, `valor`, `titulo`, `escopo`, `data`, `validade`, `status`, `observacoes`, `data_cadastro`, `data_atualizacao`) VALUES
(4, 67, NULL, 'Caçari', NULL, NULL, 2500.00, NULL, NULL, '2025-11-05', '2025-12-07', 'pending', NULL, '2025-11-05 01:42:48', '2025-11-05 01:42:48');

-- --------------------------------------------------------

--
-- Estrutura para tabela `orcamento_itens`
--

CREATE TABLE `orcamento_itens` (
  `id` int(11) NOT NULL,
  `orcamento_id` int(11) NOT NULL COMMENT 'ID do orçamento vinculado',
  `nome` varchar(255) NOT NULL COMMENT 'Nome do item',
  `descricao` text DEFAULT NULL COMMENT 'Descrição do item',
  `quantidade` int(11) NOT NULL DEFAULT 1 COMMENT 'Quantidade do item',
  `valor_unitario` decimal(65,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor unitário do item',
  `ordem_exibicao` int(11) DEFAULT 0 COMMENT 'Ordem de exibição dos itens',
  `data_cadastro` datetime DEFAULT current_timestamp() COMMENT 'Data de cadastro do item',
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Data da última atualização'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de itens dos orçamentos';

--
-- Despejando dados para a tabela `orcamento_itens`
--

INSERT INTO `orcamento_itens` (`id`, `orcamento_id`, `nome`, `descricao`, `quantidade`, `valor_unitario`, `ordem_exibicao`, `data_cadastro`, `data_atualizacao`) VALUES
(4, 4, 'Instalação centrais de ar', 'Mudar de lugar o motor das centrais', 4, 150.00, 0, '2025-11-05 01:42:48', '2025-11-05 01:42:48'),
(5, 4, 'Reforma muro', 'Reforma muro\nCom telhas', 1, 500.00, 1, '2025-11-05 01:42:48', '2025-11-05 01:42:48'),
(6, 4, 'Instalação de cameras', 'Troca de cameras', 6, 100.00, 2, '2025-11-05 01:42:48', '2025-11-05 01:42:48'),
(7, 4, 'Reforma forro', 'Restauração e pintura do forro da área ', 4, 200.00, 3, '2025-11-05 01:42:48', '2025-11-05 01:42:48'),
(8, 4, 'Reforma telhado ', NULL, 4, 0.00, 4, '2025-11-05 01:42:48', '2025-11-05 01:42:48');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamentos_infinitepay`
--

CREATE TABLE `pagamentos_infinitepay` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `order_nsu` varchar(100) NOT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `customer_email` varchar(255) DEFAULT NULL,
  `plano_tipo` varchar(50) DEFAULT NULL COMMENT 'Tipo do plano: basic, premium, profissional',
  `periodo` varchar(20) DEFAULT NULL COMMENT 'Período: mensal, semestral, anual',
  `webhook_data` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `recuperacao_senha`
--

CREATE TABLE `recuperacao_senha` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `email` varchar(255) NOT NULL,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `data_expiracao` timestamp NOT NULL,
  `usado` tinyint(1) DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tokens para recuperação de senha';

-- --------------------------------------------------------

--
-- Estrutura para tabela `relatorios_diarios`
--

CREATE TABLE `relatorios_diarios` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL COMMENT 'ID do usuário que criou o relatório',
  `obra_id` int(11) NOT NULL COMMENT 'ID da obra vinculada',
  `nome_relatorio` varchar(200) DEFAULT NULL COMMENT 'Nome personalizado do relatório (opcional)',
  `data_relatorio` date NOT NULL COMMENT 'Data do relatório',
  `data_final` date DEFAULT NULL COMMENT 'Data do relatório final',
  `autor` varchar(100) NOT NULL COMMENT 'Nome do autor/responsável pelo relatório',
  `status` enum('rascunho','pendente','finalizado','aprovado','rejeitado','revisao') NOT NULL DEFAULT 'rascunho' COMMENT 'Status do relatório',
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Data e hora do cadastro',
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Data e hora da última atualização',
  `id_cliente` int(11) DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de relatórios diários das obras';

--
-- Despejando dados para a tabela `relatorios_diarios`
--

INSERT INTO `relatorios_diarios` (`id`, `usuario_id`, `obra_id`, `nome_relatorio`, `data_relatorio`, `data_final`, `autor`, `status`, `data_cadastro`, `data_atualizacao`, `id_cliente`, `descricao`) VALUES
(15, 11, 13, 'diario', '2025-07-26', NULL, 'Carlos Silva', 'finalizado', '2025-07-27 00:34:17', '2025-07-27 00:39:55', 25, NULL),
(16, 11, 13, 'primeiro dia', '2025-07-29', NULL, 'Carlos Silva', 'finalizado', '2025-07-29 18:56:15', '2025-07-29 19:00:37', 25, NULL),
(24, 16, 34, 'Pinturas setembro ', '2025-09-17', '2025-09-20', '16', 'pendente', '2025-09-13 09:05:51', '2025-09-13 09:05:51', NULL, NULL),
(27, 15, 35, 'Compra ferragem ', '2025-09-16', NULL, '15', 'finalizado', '2025-09-22 19:39:41', '2025-10-05 11:51:19', 22, NULL),
(28, 25, 54, 'RDO Banheiro Fertirrigação 26.09', '2025-09-27', '2025-10-25', '25', 'finalizado', '2025-09-27 00:47:35', '2025-09-27 01:00:35', 17, NULL),
(31, 29, 60, 'Muro', '2025-10-01', '2025-10-31', '29', 'pendente', '2025-10-01 02:20:47', '2025-10-01 02:20:47', NULL, NULL),
(32, 30, 61, 'Diário obra teste', '2025-10-01', '2025-10-01', '30', 'pendente', '2025-10-01 16:48:51', '2025-10-01 16:48:51', NULL, NULL),
(34, 32, 66, 'Fundação (1° semana)', '2025-09-22', '2025-09-26', '32', 'pendente', '2025-10-01 23:58:47', '2025-10-01 23:58:47', NULL, NULL),
(35, 32, 64, 'RDO', '2025-09-22', '2025-10-03', '32', 'pendente', '2025-10-02 09:17:35', '2025-10-02 09:17:35', NULL, NULL),
(37, 28, 69, 'Relatório Semana de Obra', '2025-08-29', '2025-10-04', '28', 'finalizado', '2025-10-04 09:49:43', '2025-10-04 09:53:01', 18, NULL),
(38, 12, 40, 'Eididi', '2025-10-04', '2025-10-04', '12', 'pendente', '2025-10-04 12:22:56', '2025-10-04 12:22:56', NULL, NULL),
(39, 27, 56, 'Inicio obra', '2025-10-06', '2025-10-06', '27', 'pendente', '2025-10-06 07:58:43', '2025-10-06 07:58:43', NULL, NULL),
(40, 28, 69, 'Relatório diário de Obra', '2025-10-06', '2025-10-06', '28', 'finalizado', '2025-10-06 15:38:28', '2025-10-06 15:45:30', 18, NULL),
(41, 15, 41, 'Terraplanagem', '2025-10-07', '2025-10-07', 'Gestao de obra facil', 'finalizado', '2025-10-07 15:28:55', '2025-10-22 21:17:32', 24, NULL),
(47, 41, 84, 'Rdo', '2025-09-30', '2025-10-14', '41', 'rascunho', '2025-10-15 11:07:30', '2025-10-15 11:41:38', NULL, NULL),
(51, 48, 93, 'Mezanino ', '2025-10-23', NULL, '48', 'pendente', '2025-10-27 02:14:56', '2025-10-27 02:14:56', NULL, NULL),
(52, 48, 93, 'Mezanino ', '2025-10-25', '2025-10-26', '48', 'pendente', '2025-10-27 02:51:29', '2025-10-27 02:51:29', NULL, NULL),
(53, 15, 41, '', '2025-10-28', NULL, 'Gestao de obra facil', 'rascunho', '2025-10-28 13:06:08', '2025-10-28 13:06:28', NULL, NULL),
(54, 53, 95, '2 ajudante trabalhando ', '2025-10-28', '2025-10-28', '53', 'pendente', '2025-10-28 13:27:23', '2025-10-28 13:27:23', NULL, NULL),
(55, 69, 106, '', '2025-11-08', NULL, 'Ana Laura', 'rascunho', '2025-11-08 14:47:46', '2025-11-08 14:52:27', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `relatorio_arquivos`
--

CREATE TABLE `relatorio_arquivos` (
  `id` int(11) NOT NULL,
  `relatorio_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nome_arquivo` varchar(255) NOT NULL,
  `nome_original` varchar(255) NOT NULL,
  `tipo_arquivo` enum('imagem','video') NOT NULL,
  `tamanho_arquivo` int(11) NOT NULL,
  `caminho_arquivo` varchar(500) NOT NULL,
  `descricao` text DEFAULT NULL,
  `categoria` enum('progresso','problema','material','equipe','antes_depois','outro') DEFAULT 'progresso',
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `relatorio_arquivos`
--

INSERT INTO `relatorio_arquivos` (`id`, `relatorio_id`, `usuario_id`, `nome_arquivo`, `nome_original`, `tipo_arquivo`, `tamanho_arquivo`, `caminho_arquivo`, `descricao`, `categoria`, `data_criacao`, `data_atualizacao`) VALUES
(16, 15, 11, '6885751b56327_1753576731.jpeg', 'images.jpeg', 'imagem', 9019, 'uploads/relatorios/15/6885751b56327_1753576731.jpeg', 'frente da casa pronta', 'progresso', '2025-07-27 00:38:51', '2025-07-27 00:38:51'),
(21, 26, 15, '68cf37ee20f69_1758410734.jpg', 'IMG-20250920-WA0067.jpg', 'imagem', 109595, 'uploads/relatorios/26/68cf37ee20f69_1758410734.jpg', 'Andamento 15/10 as 15: 35', 'material', '2025-09-20 23:25:34', '2025-10-24 12:45:12'),
(22, 26, 15, '68cf3800be2e8_1758410752.mp4', 'VID-20250920-WA0056.mp4', 'video', 2511979, 'uploads/relatorios/26/68cf3800be2e8_1758410752.mp4', 'Andamento 15/10 as 15: 35', 'progresso', '2025-09-20 23:25:52', '2025-10-24 12:45:09'),
(23, 27, 15, '68d1a7f58d6f8_1758570485.png', 'Captura de tela 2025-09-22 164908.png', 'imagem', 306478, 'uploads/relatorios/27/68d1a7f58d6f8_1758570485.png', 'Perfuração das estacas', 'progresso', '2025-09-22 19:48:05', '2025-09-22 19:48:05'),
(24, 28, 25, '68d7357e8eff9_1758934398.jpg', 'IMG-20250926-WA0010.jpg', 'imagem', 144890, 'uploads/relatorios/28/68d7357e8eff9_1758934398.jpg', 'Andamento 15/10 as 15: 35', 'progresso', '2025-09-27 00:53:18', '2025-10-24 12:45:08'),
(25, 28, 25, '68d7359118b70_1758934417.jpg', 'IMG-20250926-WA0006.jpg', 'imagem', 95691, 'uploads/relatorios/28/68d7359118b70_1758934417.jpg', 'Andamento 15/10 as 15: 35', 'progresso', '2025-09-27 00:53:37', '2025-10-24 12:45:08'),
(26, 28, 25, '68d73597e01d5_1758934423.jpg', 'IMG-20250926-WA0007.jpg', 'imagem', 97129, 'uploads/relatorios/28/68d73597e01d5_1758934423.jpg', 'Andamento 15/10 as 15: 35', 'progresso', '2025-09-27 00:53:43', '2025-10-24 12:45:07'),
(27, 28, 25, '68d735a570a16_1758934437.jpg', 'IMG-20250926-WA0009.jpg', 'imagem', 135266, 'uploads/relatorios/28/68d735a570a16_1758934437.jpg', 'Andamento 15/10 as 15: 35', 'progresso', '2025-09-27 00:53:57', '2025-10-24 12:45:06'),
(29, 34, 32, '68ddc17720e01_1759363447.jpg', '20251001_162817.jpg', 'imagem', 3528663, 'uploads/relatorios/34/68ddc17720e01_1759363447.jpg', 'Andamento 15/10 as 15: 35', 'progresso', '2025-10-02 00:04:07', '2025-10-24 12:45:05'),
(30, 34, 32, '68ddc18c7c558_1759363468.jpg', '20251001_162803.jpg', 'imagem', 2888183, 'uploads/relatorios/34/68ddc18c7c558_1759363468.jpg', 'Assentamento de bloco', 'progresso', '2025-10-02 00:04:28', '2025-10-02 00:04:28'),
(31, 34, 32, '68ddc1aa4aa0c_1759363498.jpg', '20251001_162704.jpg', 'imagem', 3898233, 'uploads/relatorios/34/68ddc1aa4aa0c_1759363498.jpg', 'Andamento 15/10 as 15: 35', 'progresso', '2025-10-02 00:04:58', '2025-10-24 12:45:05'),
(32, 34, 32, '68ddc1b4abed1_1759363508.jpg', '20251001_162643.jpg', 'imagem', 3631432, 'uploads/relatorios/34/68ddc1b4abed1_1759363508.jpg', 'Andamento 15/10 as 15: 35', 'progresso', '2025-10-02 00:05:08', '2025-10-24 12:45:04'),
(33, 34, 32, '68ddc1c2b7f52_1759363522.jpg', '20251001_162655.jpg', 'imagem', 3898194, 'uploads/relatorios/34/68ddc1c2b7f52_1759363522.jpg', 'Andamento 15/10 as 15: 35', 'progresso', '2025-10-02 00:05:22', '2025-10-24 12:45:03'),
(34, 34, 32, '68ddc1d82d4fc_1759363544.jpg', '20250926_152958.jpg', 'imagem', 5096085, 'uploads/relatorios/34/68ddc1d82d4fc_1759363544.jpg', 'Andamento 15/10 as 15: 35', 'progresso', '2025-10-02 00:05:44', '2025-10-24 12:45:02'),
(37, 37, 28, '68e0ee5c22f82_1759571548.jpg', '20250911_122301.jpg', 'imagem', 3204896, 'uploads/relatorios/37/68e0ee5c22f82_1759571548.jpg', 'Andamento 15/10 as 15: 35', 'progresso', '2025-10-04 09:52:28', '2025-10-24 12:45:02'),
(38, 37, 28, '68e0ee69487c6_1759571561.jpg', '20250911_122331.jpg', 'imagem', 2764477, 'uploads/relatorios/37/68e0ee69487c6_1759571561.jpg', 'Andamento 15/10 as 15: 35', 'progresso', '2025-10-04 09:52:41', '2025-10-24 12:45:01'),
(39, 37, 28, '68e0ee761ae4a_1759571574.jpg', '20250911_122249.jpg', 'imagem', 2574507, 'uploads/relatorios/37/68e0ee761ae4a_1759571574.jpg', 'Andamento 15/10 as 15: 35', 'progresso', '2025-10-04 09:52:54', '2025-10-24 12:45:00'),
(40, 40, 28, '68e3e36d14fb6_1759765357.jpg', '20251006_124151.jpg', 'imagem', 4284256, 'uploads/relatorios/40/68e3e36d14fb6_1759765357.jpg', 'Baldrame do muro', 'progresso', '2025-10-06 15:42:37', '2025-10-06 15:42:37'),
(41, 40, 28, '68e3e3bf24e28_1759765439.jpg', '20251006_124128.jpg', 'imagem', 4816908, 'uploads/relatorios/40/68e3e3bf24e28_1759765439.jpg', 'Baldrame do muro', 'progresso', '2025-10-06 15:43:59', '2025-10-06 15:43:59'),
(42, 40, 28, '68e3e3e3cecd9_1759765475.jpg', '20251006_124105.jpg', 'imagem', 4544391, 'uploads/relatorios/40/68e3e3e3cecd9_1759765475.jpg', 'Andamento 15/10 as 15: 35', 'progresso', '2025-10-06 15:44:35', '2025-10-24 12:44:59'),
(43, 41, 15, '68e532c3472a3_1759851203.jpeg', 'WhatsApp Image 2025-10-01 at 2.57.46 PM.jpeg', 'imagem', 191773, 'uploads/relatorios/41/68e532c3472a3_1759851203.jpeg', 'Escoras para segurar a laje e escora.', 'progresso', '2025-10-07 15:33:23', '2025-10-26 16:47:12'),
(46, 45, 15, '68e71e709c238_1759977072.jpeg', 'WhatsApp Image 2025-10-08 at 9.56.04 AM.jpeg', 'imagem', 339870, 'uploads/relatorios/45/68e71e709c238_1759977072.jpeg', 'obra jose 2', 'progresso', '2025-10-09 02:31:12', '2025-10-23 17:58:02'),
(47, 45, 15, '68eee9065df1e_1760487686.mp4', 'VideoApp.mp4', 'video', 18897723, 'uploads/relatorios/45/68eee9065df1e_1760487686.mp4', 'wertwertwer', 'progresso', '2025-10-15 00:21:26', '2025-10-15 00:21:26'),
(49, 47, 41, '68ef8a57ba74a_1760528983.jpg', 'IMG-20251015-WA0040.jpg', 'imagem', 366749, 'uploads/relatorios/47/68ef8a57ba74a_1760528983.jpg', 'Rdo de 30/09 a 15/10', 'progresso', '2025-10-15 11:49:43', '2025-10-15 11:49:43'),
(51, 47, 41, '68ef8d4d8bed7_1760529741.jpg', 'IMG-20251015-WA0034.jpg', 'imagem', 256810, 'uploads/relatorios/47/68ef8d4d8bed7_1760529741.jpg', 'Montagem do baldrame', 'progresso', '2025-10-15 12:02:21', '2025-10-15 12:02:21'),
(52, 47, 41, '68ef8d631643d_1760529763.jpg', 'IMG-20251015-WA0028.jpg', 'imagem', 243243, 'uploads/relatorios/47/68ef8d631643d_1760529763.jpg', 'Montagem do baldrame', 'progresso', '2025-10-15 12:02:43', '2025-10-15 12:02:43'),
(53, 47, 41, '68efe31848db1_1760551704.jpg', 'IMG-20251015-WA0098.jpg', 'imagem', 375946, 'uploads/relatorios/47/68efe31848db1_1760551704.jpg', 'Construção dia 15/10 15:00', 'progresso', '2025-10-15 18:08:24', '2025-10-15 18:08:24'),
(54, 47, 41, '68efe3599aa17_1760551769.jpg', 'IMG-20251015-WA0099.jpg', 'imagem', 411925, 'uploads/relatorios/47/68efe3599aa17_1760551769.jpg', 'Construção 15/10  as 15:09', 'progresso', '2025-10-15 18:09:29', '2025-10-15 18:09:29'),
(55, 47, 41, '68efe8f508abc_1760553205.jpg', 'IMG-20251015-WA0132.jpg', 'imagem', 243405, 'uploads/relatorios/47/68efe8f508abc_1760553205.jpg', 'Andamento dia 15/10 as 15:33', 'progresso', '2025-10-15 18:33:25', '2025-10-15 18:33:25'),
(56, 47, 41, '68efe91c5a8e4_1760553244.jpg', 'IMG-20251015-WA0131.jpg', 'imagem', 247694, 'uploads/relatorios/47/68efe91c5a8e4_1760553244.jpg', 'Andamento 15/10 as 15: 35', 'progresso', '2025-10-15 18:34:04', '2025-10-15 18:34:04'),
(57, 38, 12, '68effeca8c36a_1760558794.jpg', 'IMG-20251015-WA0023.jpg', 'imagem', 577013, 'uploads/relatorios/38/68effeca8c36a_1760558794.jpg', 'Test', 'progresso', '2025-10-15 20:06:34', '2025-10-15 20:06:34'),
(59, 41, 15, '68f537f3b6af9_1760901107.mp4', 'VID-20251016-WA0053.mp4', 'video', 1063690, 'uploads/relatorios/41/68f537f3b6af9_1760901107.mp4', 'Andamento 15/10 as 15: 35 sdfasdfasdf', 'progresso', '2025-10-19 19:11:47', '2025-10-26 23:40:21'),
(60, 51, 48, '68fedcdbdb6d7_1761533147.jpg', 'IMG-20251022-WA0210.jpg', 'imagem', 67757, 'uploads/relatorios/51/68fedcdbdb6d7_1761533147.jpg', 'Corte no mezanino', 'progresso', '2025-10-27 02:45:47', '2025-10-27 02:45:47'),
(61, 52, 48, '68fee0fccacc1_1761534204.mp4', 'VID-20251022-WA0191.mp4', 'video', 1749693, 'uploads/relatorios/52/68fee0fccacc1_1761534204.mp4', 'Furo', 'progresso', '2025-10-27 03:03:24', '2025-10-27 03:03:24'),
(62, 55, 69, '690f5900e3965_1762613504.jpg', 'image.jpg', 'imagem', 1274606, 'uploads/relatorios/55/690f5900e3965_1762613504.jpg', '', 'progresso', '2025-11-08 14:51:44', '2025-11-08 14:51:44');

-- --------------------------------------------------------

--
-- Estrutura para tabela `relatorio_checklist`
--

CREATE TABLE `relatorio_checklist` (
  `id` int(11) NOT NULL,
  `relatorio_id` int(11) NOT NULL,
  `checklist_item_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `status` enum('pendente','concluido','em_andamento') NOT NULL DEFAULT 'pendente',
  `observacoes` text DEFAULT NULL,
  `data_criacao` datetime DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `relatorio_checklist`
--

INSERT INTO `relatorio_checklist` (`id`, `relatorio_id`, `checklist_item_id`, `usuario_id`, `status`, `observacoes`, `data_criacao`, `data_atualizacao`) VALUES
(2, 53, 20, 15, 'em_andamento', NULL, '2025-11-07 00:10:01', '2025-11-07 00:10:01');

-- --------------------------------------------------------

--
-- Estrutura para tabela `relatorio_clima`
--

CREATE TABLE `relatorio_clima` (
  `id` int(11) NOT NULL,
  `relatorio_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo_clima` enum('ensolarado','nublado','chuvoso','tempestade','neblina','vento_forte') NOT NULL DEFAULT 'ensolarado',
  `temperatura` int(11) DEFAULT NULL,
  `umidade` int(11) DEFAULT NULL,
  `velocidade_vento` int(11) DEFAULT NULL,
  `condicao_trabalho` enum('ideal','praticavel','dificil','impraticavel') DEFAULT 'praticavel',
  `observacoes` text DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `relatorio_clima`
--

INSERT INTO `relatorio_clima` (`id`, `relatorio_id`, `usuario_id`, `tipo_clima`, `temperatura`, `umidade`, `velocidade_vento`, `condicao_trabalho`, `observacoes`, `data_criacao`, `data_atualizacao`) VALUES
(9, 15, 11, 'nublado', 10, 10, 10, 'dificil', '', '2025-07-27 00:37:47', '2025-07-27 00:37:47'),
(10, 16, 11, 'nublado', 10, 20, 10, 'dificil', '', '2025-07-29 18:59:05', '2025-07-29 18:59:05'),
(17, 27, 15, 'ensolarado', 24, 0, 0, 'praticavel', '', '2025-09-22 19:45:32', '2025-09-22 19:45:40'),
(18, 28, 25, 'ensolarado', 29, 50, 0, 'ideal', '', '2025-09-27 00:48:34', '2025-09-27 00:48:34'),
(20, 32, 30, 'ensolarado', 20, 50, 30, 'ideal', 'Ok', '2025-10-01 16:49:30', '2025-10-01 16:49:51'),
(21, 34, 32, '', 20, 50, 0, 'praticavel', '', '2025-10-01 23:59:34', '2025-10-01 23:59:34'),
(22, 35, 32, 'ensolarado', 20, 50, 0, 'ideal', '', '2025-10-02 09:17:53', '2025-10-02 09:17:53'),
(24, 37, 28, 'ensolarado', 20, 50, 0, 'ideal', '', '2025-10-04 09:49:54', '2025-10-04 09:49:54'),
(25, 38, 12, 'tempestade', 20, 50, 0, 'ideal', '', '2025-10-04 12:23:27', '2025-10-04 12:23:27'),
(26, 40, 28, 'ensolarado', 20, 50, 0, 'ideal', '', '2025-10-06 15:38:41', '2025-10-06 15:38:41'),
(27, 41, 15, 'nublado', 10, 0, 0, 'praticavel', '', '2025-10-07 15:31:47', '2025-10-07 15:31:47'),
(29, 45, 15, 'nublado', 10, 0, 0, 'praticavel', '', '2025-10-15 00:12:12', '2025-10-15 00:12:12'),
(30, 47, 41, '', 20, 50, 0, 'ideal', '', '2025-10-15 11:08:27', '2025-10-15 11:08:27'),
(31, 55, 69, 'nublado', 10, 0, 0, 'praticavel', '', '2025-11-08 14:50:13', '2025-11-08 14:50:13');

-- --------------------------------------------------------

--
-- Estrutura para tabela `relatorio_documentos`
--

CREATE TABLE `relatorio_documentos` (
  `id` int(11) NOT NULL,
  `relatorio_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nome_arquivo` varchar(255) NOT NULL,
  `nome_original` varchar(255) NOT NULL,
  `tamanho_arquivo` int(11) NOT NULL,
  `caminho_arquivo` varchar(500) NOT NULL,
  `descricao` text DEFAULT NULL,
  `categoria` enum('contrato','orcamento','projeto','licenca','relatorio','outro') DEFAULT 'outro',
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `id_financeiro` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `relatorio_documentos`
--

INSERT INTO `relatorio_documentos` (`id`, `relatorio_id`, `usuario_id`, `nome_arquivo`, `nome_original`, `tamanho_arquivo`, `caminho_arquivo`, `descricao`, `categoria`, `data_criacao`, `data_atualizacao`, `id_financeiro`) VALUES
(9, 45, 15, '68eee8fba42af_1760487675.pdf', 'stsw-stm32102.pdf', 119552, 'uploads/relatorios/45/documentos/68eee8fba42af_1760487675.pdf', 'wertwertwert', 'contrato', '2025-10-15 00:21:15', '2025-10-15 00:21:15', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `relatorio_equipamentos`
--

CREATE TABLE `relatorio_equipamentos` (
  `id` int(11) NOT NULL,
  `relatorio_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nome_equipamento` varchar(255) NOT NULL,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ordem_exibicao` int(11) DEFAULT 0,
  `tipo_equipamento` varchar(50) NOT NULL,
  `quantidade` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `relatorio_equipamentos`
--

INSERT INTO `relatorio_equipamentos` (`id`, `relatorio_id`, `usuario_id`, `nome_equipamento`, `data_criacao`, `data_atualizacao`, `ordem_exibicao`, `tipo_equipamento`, `quantidade`) VALUES
(13, 15, 11, 'martelos', '2025-07-27 00:36:38', '2025-07-27 00:37:05', 1, 'outro', 4),
(14, 15, 11, 'enchada', '2025-07-27 00:36:58', '2025-07-27 00:37:05', 2, 'outro', 3),
(15, 16, 11, 'pa', '2025-07-29 18:58:14', '2025-07-29 18:58:30', 1, 'outro', 3),
(16, 16, 11, 'bitorneira', '2025-07-29 18:58:26', '2025-07-29 18:58:30', 2, 'betoneira', 2),
(22, 26, 15, 'matelo', '2025-09-17 01:57:09', '2025-09-17 01:57:09', 1, 'Outro', 6),
(23, 27, 15, 'betoneira', '2025-09-22 19:45:03', '2025-09-22 19:45:03', 1, 'betoneira', 1),
(24, 28, 25, 'Pá, Marreta, Betoneira, Carriola, Andaime -2M de altura.', '2025-09-27 00:51:32', '2025-09-27 00:51:32', 1, 'Ferramenta', 1),
(25, 34, 32, 'Retro escavadeira', '2025-10-02 00:02:11', '2025-10-02 00:02:11', 1, 'Máquina', 1),
(26, 34, 32, 'Bitoneira', '2025-10-02 00:02:27', '2025-10-02 00:02:27', 2, 'Equipamento Elétrico', 1),
(27, 35, 32, 'Cavadeira - pa - enxada', '2025-10-02 09:20:29', '2025-10-02 09:20:29', 1, 'Ferramenta', 1),
(28, 39, 27, 'Caçamba', '2025-10-06 07:59:45', '2025-10-06 07:59:45', 1, 'Máquina', 1),
(29, 40, 28, 'Picareta, pá, enxada, cavadeira', '2025-10-06 15:40:02', '2025-10-06 15:40:02', 1, 'Ferramenta', 1),
(30, 41, 15, 'retroescavadeira', '2025-10-07 15:31:05', '2025-10-22 21:02:17', 1, 'caminhao', 1),
(32, 45, 15, 'cat320', '2025-10-15 00:11:53', '2025-10-15 00:11:53', 1, 'escavadeira', 4),
(33, 47, 41, 'Betoneira , cavadeira, pa, enxada, chibanca, marreta.', '2025-10-15 11:09:45', '2025-10-15 11:59:25', 1, 'Material', 10),
(34, 41, 15, 'Caminhao', '2025-10-22 21:02:30', '2025-10-22 21:02:30', 2, 'caminhao', 1),
(35, 55, 69, 'Trator', '2025-11-08 14:49:40', '2025-11-08 14:49:40', 1, 'trator', 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `relatorio_mao_obra`
--

CREATE TABLE `relatorio_mao_obra` (
  `id` int(11) NOT NULL,
  `relatorio_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo_mao_obra` varchar(50) NOT NULL,
  `quantidade` int(11) DEFAULT 1,
  `ordem_exibicao` int(11) DEFAULT 0,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `relatorio_mao_obra`
--

INSERT INTO `relatorio_mao_obra` (`id`, `relatorio_id`, `usuario_id`, `tipo_mao_obra`, `quantidade`, `ordem_exibicao`, `data_criacao`, `data_atualizacao`) VALUES
(17, 15, 11, 'pedreiro', 3, 2, '2025-07-27 00:37:15', '2025-07-27 00:37:33'),
(18, 15, 11, 'servente', 10, 1, '2025-07-27 00:37:21', '2025-07-27 00:37:33'),
(19, 15, 11, 'pintor', 2, 3, '2025-07-27 00:37:27', '2025-07-27 00:37:27'),
(20, 16, 11, 'pedreiro', 3, 1, '2025-07-29 18:58:37', '2025-07-29 18:58:37'),
(21, 16, 11, 'servente', 10, 2, '2025-07-29 18:58:48', '2025-07-29 18:58:48'),
(26, 26, 15, 'Carpinteiro', 3, 1, '2025-09-16 11:54:14', '2025-09-16 11:54:14'),
(27, 27, 15, 'pedreiro', 1, 1, '2025-09-22 19:45:10', '2025-09-22 19:45:21'),
(28, 27, 15, 'servente', 2, 2, '2025-09-22 19:45:15', '2025-09-22 19:45:15'),
(29, 28, 25, 'Pedreiro', 1, 1, '2025-09-27 00:51:44', '2025-09-27 00:51:44'),
(30, 28, 25, 'Servente', 3, 2, '2025-09-27 00:51:53', '2025-09-27 00:51:53'),
(32, 34, 32, 'Pedreiro', 1, 1, '2025-10-02 00:02:36', '2025-10-02 00:02:36'),
(33, 34, 32, 'Servente', 2, 2, '2025-10-02 00:02:42', '2025-10-02 00:02:42'),
(34, 34, 32, 'Técnico', 1, 3, '2025-10-02 00:02:56', '2025-10-02 00:02:56'),
(35, 35, 32, 'Pedreiro', 1, 1, '2025-10-02 09:20:38', '2025-10-02 09:20:38'),
(36, 35, 32, 'Servente', 2, 2, '2025-10-02 09:20:44', '2025-10-02 09:21:00'),
(37, 35, 32, 'Engenheiro', 1, 3, '2025-10-02 09:20:55', '2025-10-02 09:20:55'),
(38, 37, 28, 'Pedreiro', 2, 1, '2025-10-04 09:51:08', '2025-10-04 09:51:08'),
(39, 37, 28, 'Servente', 2, 2, '2025-10-04 09:51:14', '2025-10-04 09:51:14'),
(40, 39, 27, 'Outro', 3, 1, '2025-10-06 08:00:43', '2025-10-06 08:01:02'),
(41, 40, 28, 'Pedreiro', 2, 1, '2025-10-06 15:40:13', '2025-10-06 15:40:13'),
(42, 40, 28, 'Servente', 2, 2, '2025-10-06 15:40:19', '2025-10-06 15:40:19'),
(43, 41, 15, 'operador', 2, 1, '2025-10-07 15:31:26', '2025-10-22 21:02:47'),
(44, 41, 15, 'engenheiro', 1, 2, '2025-10-07 15:31:30', '2025-10-07 15:31:30'),
(48, 45, 15, 'servente', 4, 1, '2025-10-15 00:12:00', '2025-10-15 00:12:00'),
(49, 45, 15, 'pedreiro', 3, 2, '2025-10-15 00:12:06', '2025-10-15 00:12:06'),
(50, 47, 41, 'Pedreiro', 6, 1, '2025-10-15 11:09:54', '2025-10-15 11:45:54'),
(51, 52, 48, 'Técnico', 2, 1, '2025-10-27 03:04:19', '2025-10-27 03:04:19'),
(52, 55, 69, 'pedreiro', 5, 1, '2025-11-08 14:49:51', '2025-11-08 14:49:51');

-- --------------------------------------------------------

--
-- Estrutura para tabela `relatorio_observacoes`
--

CREATE TABLE `relatorio_observacoes` (
  `id` int(11) NOT NULL,
  `relatorio_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `titulo_observacao` varchar(255) DEFAULT 'Observação',
  `descricao_observacao` text DEFAULT NULL,
  `ordem_exibicao` int(11) DEFAULT 0,
  `texto_observacao` text NOT NULL,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `relatorio_observacoes`
--

INSERT INTO `relatorio_observacoes` (`id`, `relatorio_id`, `usuario_id`, `titulo_observacao`, `descricao_observacao`, `ordem_exibicao`, `texto_observacao`, `data_criacao`, `data_atualizacao`) VALUES
(23, 15, 11, 'nao terminou piso', 'piso nao terminou por motivo do mal tempo.', 1, '', '2025-07-27 00:38:19', '2025-07-27 00:38:19'),
(24, 16, 11, 'o que quiser', 'qualquer coisa', 1, '', '2025-07-29 18:59:26', '2025-07-29 18:59:26'),
(28, 27, 15, 'Perfuração das estacas', 'No dia 20 foi realizada a perfuração das estacas', 1, '', '2025-09-22 19:48:45', '2025-09-22 19:48:45'),
(29, 28, 25, 'Liberação da PTR', 'PTR liberada às 08:30, foi realizado um diálogo de segurança antes do início das atividades.', 1, '', '2025-09-27 00:52:59', '2025-09-27 00:52:59'),
(32, 45, 15, 'twretwer twert wertwert wert wer', 'wert wertwertwer', 1, '', '2025-10-15 00:21:07', '2025-10-15 00:21:07'),
(33, 41, 15, 'No período de 17 outubro a 21 outubro, foi realizado o acompanhamento dos serviços executados no canteiro de obras:', 'Durante esse período, foram realizadas atividades iniciais essenciais para o andamento da obra, com destaque para a terraplanagem do terreno. Esse processo envolveu a regularização da área, a retirada do excesso de terra e a preparação do solo para as próximas etapas da construção. A remoção da terra foi feita com o auxílio de caminhões basculantes, garantindo o transporte adequado do material excedente. Essas ações visaram nivelar o terreno de acordo com o projeto topográfico, assegurando uma base sólida e estável para a fundação da casa térrea de 150 m²', 1, '', '2025-10-22 21:05:59', '2025-10-22 21:05:59'),
(34, 41, 15, 'Planejamento', 'Os próximos passos consistem na marcação dos gabaritos, etapa fundamental para o alinhamento e posicionamento correto da fundação. Essa atividade antecede a perfuração das estacas, que serão executadas conforme o projeto estrutural. A marcação será realizada com base nos pontos de referência definidos na planta, garantindo precisão na localização dos eixos e dimensões da obra. Após a conclusão dos gabaritos, dará-se início à perfuração das estacas, etapa essencial para a sustentação da estrutura da residência.', 2, '', '2025-10-22 21:07:32', '2025-10-22 21:07:32'),
(35, 55, 69, 'Obs', 'deu errado', 1, '', '2025-11-08 14:50:32', '2025-11-08 14:50:32');

-- --------------------------------------------------------

--
-- Estrutura para tabela `relatorio_ocorrencias`
--

CREATE TABLE `relatorio_ocorrencias` (
  `id` int(11) NOT NULL,
  `relatorio_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `descricao` text NOT NULL,
  `gravidade` varchar(20) DEFAULT 'media',
  `hora_ocorrencia` time DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ordem_exibicao` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `relatorio_ocorrencias`
--

INSERT INTO `relatorio_ocorrencias` (`id`, `relatorio_id`, `usuario_id`, `tipo`, `descricao`, `gravidade`, `hora_ocorrencia`, `data_criacao`, `data_atualizacao`, `ordem_exibicao`) VALUES
(12, 15, 11, 'acidente', 'marcos cortou o dedo na serra', 'alta', '10:00:00', '2025-07-27 00:35:55', '2025-07-27 00:36:20', 1),
(13, 15, 11, 'clima', 'muita chuva', 'media', '10:00:00', '2025-07-27 00:36:11', '2025-07-27 00:36:20', 2),
(14, 16, 11, 'clima', 'muitas chuva', 'alta', '16:00:00', '2025-07-29 18:57:34', '2025-07-29 18:58:01', 1),
(15, 16, 11, 'atraso', 'entrega de tijolos', 'baixa', '15:57:00', '2025-07-29 18:57:58', '2025-07-29 18:58:01', 2),
(21, 32, 30, 'Equipamento', 'Falta combustível', 'Baixa', '13:00:00', '2025-10-01 16:50:50', '2025-10-01 16:50:50', 1),
(22, 39, 27, 'Problema Técnico', '2 colaboradores nao estavam disponiveis', 'Baixa', '00:00:00', '2025-10-06 08:00:22', '2025-10-06 08:00:22', 1),
(24, 45, 15, 'atraso', 'uytuytuty', 'alta', '10:00:00', '2025-10-15 00:11:43', '2025-10-15 00:11:43', 1),
(25, 47, 41, 'Atraso', 'Material', 'Média', '08:08:00', '2025-10-15 11:09:24', '2025-10-15 11:09:24', 1),
(26, 47, 41, 'atraso', 'Atraso na entrega da betobeira', 'baixa', '09:42:00', '2025-10-15 11:43:10', '2025-10-15 12:00:21', 2),
(29, 55, 69, 'acidente', 'Problema na obra', 'media', '11:49:00', '2025-11-08 14:49:20', '2025-11-08 14:49:20', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `relatorio_tarefas`
--

CREATE TABLE `relatorio_tarefas` (
  `id` int(11) NOT NULL,
  `relatorio_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `descricao` text NOT NULL,
  `responsavel` varchar(255) DEFAULT NULL,
  `status` enum('pendente','em_andamento','concluida') DEFAULT 'pendente',
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ordem_exibicao` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `relatorio_tarefas`
--

INSERT INTO `relatorio_tarefas` (`id`, `relatorio_id`, `usuario_id`, `descricao`, `responsavel`, `status`, `data_criacao`, `data_atualizacao`, `ordem_exibicao`) VALUES
(13, 15, 11, 'piso', 'marcos', 'concluida', '2025-07-27 00:34:54', '2025-07-27 00:35:26', 1),
(14, 15, 11, 'caragem', 'pedro', 'concluida', '2025-07-27 00:35:18', '2025-07-27 00:35:26', 2),
(15, 16, 11, 'piso', 'tadeu', 'pendente', '2025-07-29 18:56:36', '2025-08-27 19:47:15', 3),
(16, 16, 11, 'terra planajem', 'marcos', 'pendente', '2025-07-29 18:57:00', '2025-08-27 19:47:15', 1),
(27, 27, 15, 'Compra ferragem', '', 'em_andamento', '2025-09-22 19:43:21', '2025-09-22 19:43:21', 1),
(28, 27, 15, 'Compra concreto', '', 'pendente', '2025-09-22 19:44:05', '2025-09-22 19:44:05', 2),
(29, 27, 15, 'Perfuração das estacas', '', 'pendente', '2025-09-22 19:44:31', '2025-09-22 19:44:31', 3),
(30, 28, 25, 'Levantamento de alvenaria 100%, aplicação de reboco nas paredes 80%, instalação da tubulação de esgoto 100% e instalação das portas dos banheiros 100%.', NULL, 'pendente', '2025-09-27 00:50:02', '2025-09-27 00:50:02', 1),
(32, 32, 30, 'Concreto', NULL, 'pendente', '2025-10-01 16:50:00', '2025-10-01 16:50:00', 1),
(33, 32, 30, 'Baldrame', NULL, 'pendente', '2025-10-01 16:50:10', '2025-10-01 16:50:10', 2),
(34, 34, 32, 'Marcação - Gabarito - Escavação da sapata - reguadro dos buracos - amarração dos radier - concretagem da sapatas - enchimento dos arranques', NULL, 'pendente', '2025-10-02 00:01:37', '2025-10-02 00:01:37', 1),
(35, 35, 32, 'Arremate telhado - retirada de tela para construção do muro - escavação da fossa - instalação da soleira - fechamento de pontos de água na laje - fechamento de esgoto - concretagem da varanda', NULL, 'pendente', '2025-10-02 09:19:53', '2025-10-02 09:19:53', 1),
(36, 37, 28, 'Fechamento de esgoto, caixa de inspeção, arremate do telhado, escavação sapata do muro, concretagem, conserto de cinta', NULL, 'pendente', '2025-10-04 09:50:56', '2025-10-04 09:50:56', 1),
(37, 39, 27, 'Iniciamos a chegada e demolicao na obra', NULL, 'pendente', '2025-10-06 07:59:15', '2025-10-06 07:59:15', 1),
(38, 40, 28, 'Baldrame do Muro. Escavação, dobrar e armação de ferragem, montagem de tábua e concretagem', NULL, 'pendente', '2025-10-06 15:39:34', '2025-10-06 15:39:34', 1),
(39, 41, 15, 'Terraplagem do terreno', 'Matheus', 'concluida', '2025-10-07 15:29:41', '2025-10-22 21:01:40', 1),
(42, 45, 15, 'hdfghdfgh', 'ytryt', 'em_andamento', '2025-10-15 00:11:28', '2025-10-15 00:11:28', 1),
(43, 45, 15, 'rtyrtyrtytr', 'yyyy', 'em_andamento', '2025-10-15 00:11:34', '2025-10-15 00:11:34', 2),
(44, 47, 41, 'Montagem do baldrame', 'IVANIR GARCIA', 'em_andamento', '2025-10-15 11:09:04', '2025-10-15 11:42:33', 1),
(45, 47, 41, 'Concretagem do baldrame', 'IVANIR GARCIA', 'em_andamento', '2025-10-15 11:42:08', '2025-10-15 11:42:08', 2),
(46, 52, 48, 'Passagem de cabos', NULL, 'pendente', '2025-10-27 03:03:49', '2025-10-27 03:03:49', 1),
(47, 55, 69, 'Teste', 'Maria', 'concluida', '2025-11-08 14:48:56', '2025-11-08 14:48:56', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tentativas_login`
--

CREATE TABLE `tentativas_login` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `data_tentativa` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tutoriais`
--

CREATE TABLE `tutoriais` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL COMMENT 'Título do tutorial',
  `descricao` text NOT NULL COMMENT 'Descrição do tutorial',
  `link_youtube` varchar(500) NOT NULL COMMENT 'Link do vídeo no YouTube',
  `duracao` varchar(20) DEFAULT NULL COMMENT 'Duração do vídeo (ex: 5 min)',
  `nivel` enum('basico','intermediario','avancado') NOT NULL DEFAULT 'basico' COMMENT 'Nível de dificuldade',
  `categoria` varchar(100) NOT NULL COMMENT 'Categoria do tutorial',
  `icone` varchar(50) DEFAULT 'fas fa-play-circle' COMMENT 'Ícone FontAwesome para exibição',
  `ordem_exibicao` int(11) DEFAULT 0 COMMENT 'Ordem de exibição na página',
  `ativo` tinyint(1) DEFAULT 1 COMMENT 'Se o tutorial está ativo',
  `visualizacoes` int(11) DEFAULT 0 COMMENT 'Contador de visualizações',
  `data_criacao` timestamp NULL DEFAULT current_timestamp() COMMENT 'Data de criação',
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Data da última atualização',
  `tipo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de tutoriais em vídeo';

--
-- Despejando dados para a tabela `tutoriais`
--

INSERT INTO `tutoriais` (`id`, `titulo`, `descricao`, `link_youtube`, `duracao`, `nivel`, `categoria`, `icone`, `ordem_exibicao`, `ativo`, `visualizacoes`, `data_criacao`, `data_atualizacao`, `tipo`) VALUES
(1, 'Como Criar uma Nova Obra', 'Aprenda o passo a passo para cadastrar uma nova obra no sistema, incluindo dados do cliente e informações da obra.', 'https://youtu.be/IjzVIM2jp1w', '1 min', 'basico', 'Obras', 'fas fa-plus-circle', 1, 1, 10, '2025-06-19 14:43:54', '2025-10-30 12:43:41', 'Web'),
(2, 'Criando Relatórios Diários', 'Como criar e gerenciar relatórios diários de suas obras, incluindo tarefas, equipamentos e observações.', 'https://youtu.be/J69Fqgq7F0Y', '2 min', 'basico', 'Relatórios', 'fas fa-file-alt', 2, 1, 3, '2025-06-19 14:43:54', '2025-10-30 12:43:41', 'Web'),
(3, 'Gestão Financeira', 'Controle receitas e despesas de suas obras de forma eficiente, com categorização e relatórios.', 'https://youtu.be/v4F90tOH_hQ', '2 min', 'intermediario', 'Financeiro', 'fas fa-dollar-sign', 3, 1, 1, '2025-06-19 14:43:54', '2025-10-30 12:43:42', 'Web'),
(4, 'Gerenciamento de Equipe', 'Como cadastrar e gerenciar sua equipe de trabalho, incluindo cargos e informações de contato.', 'https://youtu.be/_et3ZrynsQs', '30 Seg', 'basico', 'Equipe', 'fas fa-users', 4, 1, 2, '2025-06-19 14:43:54', '2025-10-30 12:43:43', 'Web'),
(5, 'Gerando Relatórios PDF', 'Como gerar e personalizar relatórios em PDF para seus clientes, incluindo layout e informações.', 'https://www.youtube.com/watch?v=nfs4iJzmE78', '1 min', 'avancado', 'Relatórios', 'fas fa-file-pdf', 5, 1, 1, '2025-06-19 14:43:54', '2025-10-30 12:43:43', 'Web'),
(6, 'Cadastro de Equipamentos', 'Tenha seus equipamentos cadastros e organizados.', 'https://www.youtube.com/watch?v=2Kfeg0gJB1k', '1 min', 'avancado', 'Equipamento', 'fas fa-cog', 6, 1, 0, '2025-06-19 14:43:54', '2025-10-30 12:43:44', 'Web'),
(7, 'Cadastro de Clientes', 'Como cadastrar e gerenciar clientes no sistema, incluindo dados de contato e histórico.', 'https://youtu.be/A_WA-llDhBs', '30 Seg', 'basico', 'Clientes', 'fas fa-user-plus', 7, 1, 0, '2025-06-19 14:43:54', '2025-10-30 12:43:45', 'Web'),
(9, 'Lembretes e Notificações', 'Como criar e gerenciar lembretes para não perder prazos importantes das suas obras.', 'https://youtu.be/d_CxzluBp8s', '30 Seg', 'basico', 'Lembretes', 'fas fa-bell', 9, 1, 0, '2025-06-19 14:43:54', '2025-10-30 12:43:45', 'Web'),
(11, 'Onde Acessar', 'Onde o app esta disponivel: https://gestaodeobrafacil.com/\r\nhttps://play.google.com/store/apps/details?id=com.zimiro.gestordeobrafacil', 'https://youtu.be/A19zGBJDHsI', '8 Seg', 'basico', 'Tudo', 'fas fa-play-circle', 1, 1, 0, '2025-10-30 22:40:23', '2025-11-01 03:16:53', 'app');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `tipo_conta` enum('fisica','juridica') NOT NULL,
  `nome` varchar(255) NOT NULL,
  `documento` varchar(20) DEFAULT NULL COMMENT 'CPF ou CNPJ',
  `telefone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `token` varchar(255) DEFAULT NULL COMMENT 'Token para autenticação',
  `token_expiracao` datetime DEFAULT NULL COMMENT 'Data de expiração do token',
  `data_cadastro` datetime DEFAULT current_timestamp(),
  `ultimo_acesso` datetime DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `tipo_plat` enum('Android','IOS','Web') DEFAULT NULL COMMENT 'tipo de plataforma usado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `tipo_conta`, `nome`, `documento`, `telefone`, `email`, `senha`, `token`, `token_expiracao`, `data_cadastro`, `ultimo_acesso`, `ativo`, `tipo_plat`) VALUES
(12, 'fisica', 'osmar', '04578810976', '65465465464', 'osmar@ppiscinas.com.br', '$2y$10$6JVronzGtRw60JwajDc0F.97rBUK6Pbz/6./Y19uH5eJcE2V1hPYO', 'da4cbe0c7fa7c5f9af2b889ac26e0a0c4c157e3b114fae9bcd7d187fe7b38ac0', '2025-12-07 03:37:41', '2025-07-25 19:08:27', '2025-11-07 03:37:41', 1, 'Android'),
(15, 'fisica', 'Gestao de obra facil', '60937563005', '14333333333', 'test@test.com.br', '$2y$10$KgZ9s8gN8FnRkpv7kgeANeck.2HkrgcYBxNfPexPnQT4KjEJv9xzm', '6790de61bfccbe47d80a894719749a9ad6569a3e9125e12f234376a4714b38bb', '2025-12-09 22:18:01', '2025-09-04 10:53:42', '2025-11-09 22:23:01', 1, 'Android'),
(16, 'fisica', 'Carlos Pessoa', '12345678900', '84996751984', 'neuro121@gmail.com', '$2y$10$yxCiwUC8MIojik8dtsPQ8OMfMTZ5qkdMPiGegSQ1dwCq.9BP.L63O', NULL, NULL, '2025-09-04 14:21:19', NULL, 1, 'Android'),
(23, 'fisica', 'Claudio Marcio Gonçalves da Silva', '01280835494', '84988382205', 'claudio.marcio@outlook.com.br', '$2y$10$buggH0QECkR.osGG7d3JAerdqdzPj7qhaugIJtE2zlQHThrHjF/Vm', NULL, NULL, '2025-09-23 23:09:50', NULL, 1, 'Android'),
(24, 'juridica', 'Dennis Alberto Huaman Poemape', '00000000000', '92928665', 'dennis.grconstructora@gmail.com', '$2y$10$plMR6RR6e8q3cj8QCJ/Ftuoseq3y.VbpT.c3HtkIwTiOBbw.fhfcO', NULL, NULL, '2025-09-25 01:17:13', NULL, 1, 'Android'),
(25, 'fisica', 'Paula Favaretto', '24102876030', '18997692274', 'paula1997favaretto@gmail.com', '$2y$10$S0ITyKrCTf3sqq/zQMhlTOvdGd.TfZkhUByMJbX9u0zs.6T7lPTze', NULL, NULL, '2025-09-27 00:42:16', NULL, 1, 'Android'),
(26, 'fisica', 'Renato Correia', '02065625007', '62982857537', 'oppflow.emp@gmail.com', '$2y$10$MAAY1wnWVH94KBdJQr1bBuz/KI2/kFBtDCXzBl9S7lC9xUDZXXXPm', NULL, NULL, '2025-09-27 10:17:36', NULL, 1, 'Android'),
(27, 'juridica', 'Victor lv empreendimentos', '16886705000198', '37998574573', 'martinelevictor7@gmail.com', '$2y$10$J1s5am8wccCCPtEk4Pgx5u4HAsXFRYPJdbQVa9DNWKxUaspDzVyWW', NULL, NULL, '2025-09-28 18:18:49', NULL, 1, 'Android'),
(28, 'fisica', 'Márcio Santos', '12269165705', '22998328637', '2sconstrutora2024@gmail.com', '$2y$10$gw50m4CoKsYLo.Syn8cCzOe/MhsHb631DeW.LEEKckkh3hDSbhWJ6', NULL, NULL, '2025-09-30 08:59:09', NULL, 1, 'Android'),
(29, 'juridica', 'Marco Aurélio do Nascimento', '48569944000100', '13991364534', 'mnasc69@gmail.com', '$2y$10$kabzzH22eH2Hm9DUmF6C8OGpK03igZVmGI8xU1kGZZuDsnSmK5r8C', NULL, NULL, '2025-10-01 02:16:07', NULL, 1, 'Android'),
(30, 'fisica', 'Angélica', '31127256890', '19974052646', 'angel.teclas@gmail.com', '$2y$10$IQLj37vDBUVrvmptEHaGtepxBQtBRpA/J75l926b/2lTh5A0yO5Si', NULL, NULL, '2025-10-01 16:45:53', NULL, 1, 'Android'),
(31, 'fisica', 'Rogério Nunes', '01943806292', '62984823976', 'rogeriobarrorz@gmail.com', '$2y$10$4BKBI1wdiI0e1Zh72stvB.gTBC/YaCKRXyV7pqT3b.VS.smiDY4oq', NULL, NULL, '2025-10-01 18:31:02', NULL, 1, 'Android'),
(32, 'juridica', 'MS ENGENHARIA', '56039458008109', '22998328637', 'eng.adm.engenharia@gmail.com', '$2y$10$2Zw7CJMmTPJsWk44.xUybO2SEw.RvbOtWqt10WvKH71qktfvjUegm', NULL, NULL, '2025-10-01 23:29:57', NULL, 1, 'Android'),
(33, 'fisica', 'Samuel Hungria', '11282236601', '32988952731', 'samuelhungria0898@gmail.com', '$2y$10$fLZNcFn2aXsuAPU2EZNhqu4EsDo04EaifBgirLmqcS1XJ.pyM3CfW', NULL, NULL, '2025-10-02 08:46:32', NULL, 1, 'Android'),
(34, 'fisica', 'JOSIMAR CÉSAR GUIMARÃES', '08033330643', '31993767555', 'jgescritoriodearte@gmail.com', '$2y$10$NDEkxZ/IuaxczVBvcRR.y.FNQCBOKcR.my9NE8v6DnJergz4iO/2e', NULL, NULL, '2025-10-07 00:24:56', NULL, 1, 'Android'),
(35, 'fisica', 'Marcos Antônio Xavier reis', '05690179558', '71991312069', 'reism4008@gmail.com', '$2y$10$Yl30iyB5ebXYcCoO.sV9BO7Dg26KLdFME8PHJrExQkv.OJCUqcNVW', NULL, NULL, '2025-10-07 22:05:29', NULL, 1, 'Android'),
(36, 'fisica', 'Rubens', '03022624905', '41991120321', 'rubens.kliewer@gmail.com', '$2y$10$4KPdPoNn6cG9eQ..P2G98uUOg7Bt42ou4gfC5Xx4A9so9Q6Zvfqxu', NULL, NULL, '2025-10-10 16:05:11', NULL, 1, 'Android'),
(37, 'fisica', 'Charles queiroz', '06127986664', '48933802583', 'Obrasclprestacaodeservicos@gmail.com', '$2y$10$l7f/Q5nDWgWiCf4VdVGIwOS/gkETfajnBaWofzQEkrrMM4SPQDycy', NULL, NULL, '2025-10-13 04:53:15', NULL, 1, 'Android'),
(38, 'fisica', 'Paulo Rogerio Garcia', '30494220', '94992430601', 'prgtelecom@gmail.com', '$2y$10$G1uIwFgyzNY/X37fqqM/duKUXMRfprUjF/AP9oQNDu9GwEZzPpj5O', NULL, NULL, '2025-10-14 14:03:27', NULL, 1, 'Android'),
(39, 'fisica', 'Sergio gabriel', '08742016401', '82998296044', 'unognd1865@gmail.com', '$2y$10$Tf/CmgVVpXnYCgjkNxMq/.OwaOYaif90BIalkjIsCyU8TZe0kW.WS', NULL, NULL, '2025-10-14 20:48:58', NULL, 1, 'Android'),
(40, 'fisica', 'Esteban', '875', '996241414', 'estebanscc125@gmail.com', '$2y$10$ZnyngM7SBW7l0QXrQ5cQX.WL2JESjYHDHeEMG3PQGwubJysIxrrCy', NULL, NULL, '2025-10-15 01:12:15', NULL, 1, 'Android'),
(41, 'fisica', 'Paulo Garcia', '05086300614', '94992430601', 'paulo@prgnet.com.br', '$2y$10$iS35kuQO3aI7YT0qz/h2kOipjuzpfCsRcsFulL.Pk.tBNOHMCU81C', 'cb0be6ad811c7b59829e97285eba46846d0d5d7893b159405bfb50447cf6049f', '2025-11-14 11:31:10', '2025-10-15 09:55:17', '2025-10-15 11:31:10', 1, 'Web'),
(42, 'fisica', 'Clebson xavier fernandes', '84888571287', '93991665600', 'like.xc83@gmail.com', '$2y$10$olK5gSccMpyFxFYFzzjORuljRtOCigi9g08XEqPqWR.S0Sgpo5TfO', NULL, NULL, '2025-10-16 16:14:26', NULL, 1, 'Android'),
(43, 'fisica', 'WILLIAN DIAS', '09358381981', '49999601625', 'contato@w10engenharia.com.br', '$2y$10$I4BO96VGWO/H6SSIWFydxehiBqmlplg7ykkaLJ3ZRxaXS4K3Ruawa', NULL, NULL, '2025-10-17 20:42:59', '2025-10-17 20:43:07', 1, 'Web'),
(44, 'juridica', 'Construtora malibu', '37980358000138', '81996725700', 'josenildoalvessantos505@gmail.com', '$2y$10$PSTyQq3Vd95c9gqYjtwUS.DCCVNHONEDPfbjlpfjFWPwg3kQ/HhTq', NULL, NULL, '2025-10-18 03:06:42', NULL, 1, 'Android'),
(46, 'fisica', 'Telmo', '80250495902', '53991914684', 'Telmo.freitas.porto5@gmail.com', '$2y$10$XzxEiyoMBjEUVxmb27A/VeMBgbTRAGFDRrjnerAdermyi.dcH3grS', 'a2b350024b2625f34d27d88d5394bc4186281295c99e1001f931858b84eada43', '2025-11-18 18:22:12', '2025-10-19 18:21:50', '2025-10-19 18:22:12', 1, 'Android'),
(47, 'fisica', 'Mariane hawerroth', '10826429912', '49999814571', 'mari.hawerroth@gmail.com', '$2y$10$UYOQBLd5pASrQDjcj4VYoedR8IkqxRO1J9rS3jsTMYU9tIdguqJiS', NULL, NULL, '2025-10-21 12:41:10', '2025-10-21 12:41:38', 1, 'Web'),
(48, 'juridica', 'Wesley da Costa Santos', '3705677800122', '62986428926', 'wesleycosta326@gmail.com', '$2y$10$kbRAJZ2u9F8Og9CNp1VhFel3/TqnzFZdeQ3njlKnSofvkykaysT7y', '0380ceaee61841c6cdedaea449f83367b9a0e1881f2ba54ea8cc09b307790856', '2025-11-21 03:21:40', '2025-10-22 03:17:00', '2025-10-22 03:21:40', 1, 'Android'),
(49, 'fisica', 'Arkkos', '05578348731', '24926231303', 'emersoncarloscarvalho@gmail.com', '$2y$10$pnUMD9O7NnP9Ty9Tw64MWenMH3fly9csbCeEr3L3WD67Vi0r/rC1a', 'ecf824b0c24dcf26849c83322e150e31c23f39ddbe2691de708d63b7fa27d10c', '2025-11-21 22:12:24', '2025-10-22 22:11:20', '2025-10-22 22:12:24', 1, 'Android'),
(50, 'fisica', 'Rony Ferreira Silva', '09431963701', '21998393916', 'ronyfs@gmail.com', '$2y$10$Rwt.2CsDYfw6l3PR28gz9eP9jSCkH80k.hWOHA1uMgV8E2VeJeYH.', 'bdb0157ff0601cca2bc95404593e502495492b9267b41c225240f061fb7f7fb1', '2025-11-24 02:53:40', '2025-10-25 02:52:55', '2025-10-25 02:53:40', 1, 'Android'),
(51, 'juridica', 'Rafael Oliveira Santos', '58383229000132', '11993536739', 'rafaelrank22@gmail.com', '$2y$10$Va5v3dDwGhAKKUk91tUzPeDGKQUsFf.zUF7bcyqDRsMFaP/DvZykW', '1cee667680d835497190ad976ff54da104e4bd0176275e07742e7a071933a775', '2025-11-24 21:35:12', '2025-10-25 21:34:25', '2025-10-25 21:35:12', 1, 'Android'),
(52, 'fisica', 'Jacó Brito de Oliveira', '92428479334', '99991276241', 'jaco.pedagogo@gmail.com', '$2y$10$LoRyfD/xq2RgpWlBcxrtPursLKlmgQorfZsXk1SkqFtu9TUw48WOS', '08986c40caf2c25df3f0246739aa6fa91203106e5229db0cb7dfd88aa8d5d81d', '2025-11-27 00:25:24', '2025-10-28 00:24:59', '2025-10-28 00:25:24', 1, 'Android'),
(53, 'fisica', 'Antônio Carlos Lima da Silva', '98275470544', '11971876414', 'nholima@hotmail.com', '$2y$10$7dUCdgd5r5.jkIOzvoXdjerWjUTW5wnXcznrIBMV5v6tbWxjOuqGm', 'bb6debd953a6b6d3be4635d226659d782473c092f7528a11afe1d919b7c845af', '2025-11-27 13:24:10', '2025-10-28 13:23:17', '2025-10-28 13:24:10', 1, 'Android'),
(54, 'fisica', 'Rafael Almeida Silva', '06352389775', '22999984286', 'vrauwh@gmail.com', '$2y$10$CrXmIsIEWOGShQYUZwJur.9h2wkSL.jZD3baLe2XLfheJv42BHzk.', '9a068bf4b1bd16262dd061f44691f3748b2b84197f9e2125ae1e1eac12abdce0', '2025-11-28 14:29:04', '2025-10-29 14:28:46', '2025-10-29 14:29:04', 1, 'Android'),
(55, 'fisica', 'Marcos Diogo da Silva', '08745453900', '41988272225', 'marcossilva43783@gmail.com', '$2y$10$FiaIHMG4fYYN8ZWZW1.VDOvXy.NDCIux.DXf1PSDHih4ZTxCN6Xma', '7bd27962c04b3a75a389e011f2a3609a0eef6b09d81e8ef2068667140a57d299', '2025-11-28 16:33:22', '2025-10-29 16:32:57', '2025-10-29 16:33:22', 1, 'Android'),
(56, 'fisica', 'Adrian Alexandre Pires', '12691512908', '43988631926', 'adrianpires043@gmail.com', '$2y$10$lNCXl2l72scWsAfycfZ90etqTRkDR2bRqXTWl7NuUxNKKXOVgTWby', '61f9bce9668cfc80d104b0ed7c80abc1b905c6fd57b7ffde72b67c7b1599277a', '2025-11-29 12:45:39', '2025-10-30 12:45:13', '2025-10-30 12:45:39', 1, 'Android'),
(57, 'juridica', 'Pedro Henrique Silva Alves barros', '55434348000189', '62984583502', 'pjtj2019@gmail.com', '$2y$10$B0SpgqzeZqmpV336Pmfcw.u6pNgUsRlIrP42tUxS8BJjZm3jobhCO', '6a35d09f2830b44fbbc69bf7a280390325ae2139423cce8c3e072bdff20dce4f', '2025-11-30 01:32:38', '2025-10-31 01:32:21', '2025-10-31 01:32:38', 1, 'Android'),
(58, 'fisica', 'China Jiangsu intencional Moçambique LDA.', '100000000', '25871808919', 'ChinaJiangsu@gmail.com.', '$2y$10$Q8l.fNbNDI9Hunaca9l/FuifZXrI7E9iabixRlzjSEI4mcLyptcXu', NULL, NULL, '2025-10-31 13:20:03', NULL, 1, 'Android'),
(59, 'fisica', 'Lucas Gdak', '09950215978', '42998100694', 'lucas.gdak20@gmail.com', '$2y$10$J/.2ZHmwZhF2jrQrYG7OKenj1SbYEhyc/bVMTXpfnGOKJJXeptj16', 'b0b6e3e3b1da3f686d22acbea53e2861ee04a2ec2cf3674cd34d5e853feebd14', '2025-11-30 19:11:28', '2025-10-31 19:10:54', '2025-10-31 19:11:28', 1, 'Android'),
(60, 'juridica', 'Juan Pablo Parraguez Zúñiga', '777950460', '979781979', 'bonanza.jppz@gmail.com', '$2y$10$.BMkBx935eqjHDDaCii/neHn0MEyanAWAmo7Czp2p08iw3ZAtcHZC', '3be439a127f6424fdb07005f143508459f9d806ae10ae0a5f3375fc193f989c5', '2025-12-01 13:22:47', '2025-11-01 13:15:15', '2025-11-01 13:22:47', 1, 'Android'),
(62, 'fisica', 'Fernandes pinturas', '35749388883', '12981718729', 'andeson.pezao1988@gmail.com', '$2y$10$fdKEaAMQvhQzcK1Iv8O5Neiref./sGIrRtL9HdjS5hPn36ms.rLKS', '016f66802a030ed2c4cffe87ecf8ac20c48ae60b8b50d4641005e6e85ff967c6', '2025-12-03 02:04:02', '2025-11-03 02:03:35', '2025-11-03 02:04:02', 1, 'Android'),
(63, 'fisica', 'Cleber Couto da Costa', '02192564708', '21996868126', 'cleber_costa72@yahoo.com', '$2y$10$/LQReAekW5NxJOPzJ95VZuJz1XIh04V7KBIY342y3vl1o1tOheFky', '21535f190d6fac9e846f4dd47206eb8a996a84e16844af40a9581b6ae922991e', '2025-12-04 02:58:09', '2025-11-04 02:57:52', '2025-11-04 02:58:09', 1, 'Android'),
(65, 'fisica', 'James', '62285891300', '88996818580', 'jamescoelho2904@gmail.com', '$2y$10$ejumwBP74tAWmAErXaNK0uBYPa3DEkHkHKQ9QcuKrjnFMGJP/tWRC', NULL, NULL, '2025-11-04 17:30:58', NULL, 1, 'Android'),
(66, 'fisica', 'Lucas delgado bezerra', '02909241203', '95974003385', 'lucasdelgado465@gmail.com', '$2y$10$ygS1c/hDC3TSUE78xSFAL.elFgxzLi.KJ1MB4uDCq8kr4nB5dDGfu', '500dd7b65e607d9ca2ea83b6191ed37cc43b4ac1daf5969fbc286d186937f718', '2025-12-04 18:05:19', '2025-11-04 18:04:58', '2025-11-04 18:05:19', 1, 'Android'),
(67, 'fisica', 'Rodrigo Araújo costa', '03336165296', '95991183657', 'rodrigo1997acostaa@gmail.com', '$2y$10$TW5dCRkn9rh/oTTDQ92dmOxKkit4YECgrpgfNrBiXz9QgHQi/044i', 'b09c5786d186a2e950fbe23370ae28cc669bf0192df01ef7dfbb7729306b1668', '2025-12-05 01:01:17', '2025-11-05 00:52:07', '2025-11-05 01:01:17', 1, 'Android'),
(69, 'fisica', 'Ana Laura', '51942077009', '11836625355', 'ana.laura@gmail.com', '$2y$10$a1Kk6he.JwRfm2coMTRMfO1WGpm./Ueav3oCL5rhx/HjtbsFA1Pxa', NULL, NULL, '2025-11-08 14:26:28', '2025-11-08 14:26:56', 1, 'Web'),
(70, 'fisica', 'Ailto Lima', '96304502168', '6298619924', 'lc-construcao@hotmail.coml', '$2y$10$0WBy0P0.kSm4v2RVmQy06uSxPJh1XaABoAPaNJN/ZLnMpy3SEJieC', 'd57a7ed6e220c5341b62f5743ce386f4c6dc6cc5309c05173cfef5d5b8d3031a', '2025-12-09 20:44:49', '2025-11-09 20:44:30', '2025-11-09 20:44:49', 1, 'Android');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `assinaturas`
--
ALTER TABLE `assinaturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `plano_id` (`plano_id`);

--
-- Índices de tabela `cdAdm`
--
ALTER TABLE `cdAdm`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`);

--
-- Índices de tabela `checklist_categorias`
--
ALTER TABLE `checklist_categorias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`);

--
-- Índices de tabela `checklist_itens`
--
ALTER TABLE `checklist_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_categoria` (`categoria_id`),
  ADD KEY `idx_tipo_status` (`tipo`,`status`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `contratos`
--
ALTER TABLE `contratos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `Cupom`
--
ALTER TABLE `Cupom`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `emails_prontos`
--
ALTER TABLE `emails_prontos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `equipamentos`
--
ALTER TABLE `equipamentos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `equipe`
--
ALTER TABLE `equipe`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Índices de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_funcionarios_usuario` (`usuario_id`);

--
-- Índices de tabela `historico_emails`
--
ALTER TABLE `historico_emails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_pronto_id` (`email_pronto_id`);

--
-- Índices de tabela `lancamentos_financeiros`
--
ALTER TABLE `lancamentos_financeiros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lancamentos_fornecedor` (`fornecedor_id`);

--
-- Índices de tabela `layout_relatorio_cliente`
--
ALTER TABLE `layout_relatorio_cliente`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `lembretes`
--
ALTER TABLE `lembretes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `lembretes_confirmacoes`
--
ALTER TABLE `lembretes_confirmacoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_confirmacao` (`lembrete_id`,`usuario_id`),
  ADD KEY `fk_confirmacao_lembrete` (`lembrete_id`),
  ADD KEY `fk_confirmacao_usuario` (`usuario_id`);

--
-- Índices de tabela `obras`
--
ALTER TABLE `obras`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `orcamentos`
--
ALTER TABLE `orcamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `obra_id` (`obra_id`);

--
-- Índices de tabela `orcamento_itens`
--
ALTER TABLE `orcamento_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orcamento_id` (`orcamento_id`);

--
-- Índices de tabela `pagamentos_infinitepay`
--
ALTER TABLE `pagamentos_infinitepay`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_nsu` (`order_nsu`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `customer_email` (`customer_email`),
  ADD KEY `status` (`status`),
  ADD KEY `plano_tipo` (`plano_tipo`),
  ADD KEY `periodo` (`periodo`);

--
-- Índices de tabela `recuperacao_senha`
--
ALTER TABLE `recuperacao_senha`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `relatorios_diarios`
--
ALTER TABLE `relatorios_diarios`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `relatorio_arquivos`
--
ALTER TABLE `relatorio_arquivos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `relatorio_checklist`
--
ALTER TABLE `relatorio_checklist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_relatorio` (`relatorio_id`),
  ADD KEY `idx_checklist_item` (`checklist_item_id`),
  ADD KEY `idx_usuario` (`usuario_id`);

--
-- Índices de tabela `relatorio_clima`
--
ALTER TABLE `relatorio_clima`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `relatorio_documentos`
--
ALTER TABLE `relatorio_documentos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `relatorio_equipamentos`
--
ALTER TABLE `relatorio_equipamentos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `relatorio_mao_obra`
--
ALTER TABLE `relatorio_mao_obra`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `relatorio_observacoes`
--
ALTER TABLE `relatorio_observacoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `relatorio_ocorrencias`
--
ALTER TABLE `relatorio_ocorrencias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `relatorio_tarefas`
--
ALTER TABLE `relatorio_tarefas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tentativas_login`
--
ALTER TABLE `tentativas_login`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tutoriais`
--
ALTER TABLE `tutoriais`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `assinaturas`
--
ALTER TABLE `assinaturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT de tabela `cdAdm`
--
ALTER TABLE `cdAdm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `checklist_categorias`
--
ALTER TABLE `checklist_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `checklist_itens`
--
ALTER TABLE `checklist_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de tabela `contratos`
--
ALTER TABLE `contratos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `Cupom`
--
ALTER TABLE `Cupom`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `emails_prontos`
--
ALTER TABLE `emails_prontos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `equipamentos`
--
ALTER TABLE `equipamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `equipe`
--
ALTER TABLE `equipe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `historico_emails`
--
ALTER TABLE `historico_emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `lancamentos_financeiros`
--
ALTER TABLE `lancamentos_financeiros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=231;

--
-- AUTO_INCREMENT de tabela `layout_relatorio_cliente`
--
ALTER TABLE `layout_relatorio_cliente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `lembretes`
--
ALTER TABLE `lembretes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de tabela `lembretes_confirmacoes`
--
ALTER TABLE `lembretes_confirmacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `obras`
--
ALTER TABLE `obras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT de tabela `orcamentos`
--
ALTER TABLE `orcamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `orcamento_itens`
--
ALTER TABLE `orcamento_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `pagamentos_infinitepay`
--
ALTER TABLE `pagamentos_infinitepay`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `recuperacao_senha`
--
ALTER TABLE `recuperacao_senha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `relatorios_diarios`
--
ALTER TABLE `relatorios_diarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de tabela `relatorio_arquivos`
--
ALTER TABLE `relatorio_arquivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de tabela `relatorio_checklist`
--
ALTER TABLE `relatorio_checklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `relatorio_clima`
--
ALTER TABLE `relatorio_clima`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de tabela `relatorio_documentos`
--
ALTER TABLE `relatorio_documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `relatorio_equipamentos`
--
ALTER TABLE `relatorio_equipamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de tabela `relatorio_mao_obra`
--
ALTER TABLE `relatorio_mao_obra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de tabela `relatorio_observacoes`
--
ALTER TABLE `relatorio_observacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de tabela `relatorio_ocorrencias`
--
ALTER TABLE `relatorio_ocorrencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de tabela `relatorio_tarefas`
--
ALTER TABLE `relatorio_tarefas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de tabela `tentativas_login`
--
ALTER TABLE `tentativas_login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT de tabela `tutoriais`
--
ALTER TABLE `tutoriais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `checklist_categorias`
--
ALTER TABLE `checklist_categorias`
  ADD CONSTRAINT `fk_checklist_categorias_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `checklist_itens`
--
ALTER TABLE `checklist_itens`
  ADD CONSTRAINT `fk_checklist_itens_categorias` FOREIGN KEY (`categoria_id`) REFERENCES `checklist_categorias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_checklist_itens_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `historico_emails`
--
ALTER TABLE `historico_emails`
  ADD CONSTRAINT `historico_emails_ibfk_1` FOREIGN KEY (`email_pronto_id`) REFERENCES `emails_prontos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `lancamentos_financeiros`
--
ALTER TABLE `lancamentos_financeiros`
  ADD CONSTRAINT `fk_lancamentos_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `orcamentos`
--
ALTER TABLE `orcamentos`
  ADD CONSTRAINT `orcamentos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orcamentos_ibfk_2` FOREIGN KEY (`obra_id`) REFERENCES `obras` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `orcamento_itens`
--
ALTER TABLE `orcamento_itens`
  ADD CONSTRAINT `orcamento_itens_ibfk_1` FOREIGN KEY (`orcamento_id`) REFERENCES `orcamentos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `relatorio_checklist`
--
ALTER TABLE `relatorio_checklist`
  ADD CONSTRAINT `fk_relatorio_checklist_item` FOREIGN KEY (`checklist_item_id`) REFERENCES `checklist_itens` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_relatorio_checklist_relatorio` FOREIGN KEY (`relatorio_id`) REFERENCES `relatorios_diarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_relatorio_checklist_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
