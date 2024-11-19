-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-11-2024 a las 02:48:39
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `iso-sys`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `exams`
--

CREATE TABLE `exams` (
  `id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `exam_order` int(11) NOT NULL,
  `total_score` int(11) NOT NULL,
  `isDeleted` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `exams`
--

INSERT INTO `exams` (`id`, `unit_id`, `title`, `description`, `exam_order`, `total_score`, `isDeleted`) VALUES
(1, 2, 'variables en c++', 'tipos de variables en c++', 1, 20, 1),
(2, 4, 'titulo 1', 'this is a description', 1, 20, 0),
(3, 3, 'pepesss', 'sdsadsdadasdas', 1, 11, 1),
(4, 7, 'examen prueba', 'examen test', 0, 20, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `exam_scores`
--

CREATE TABLE `exam_scores` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `exam_id` int(11) DEFAULT NULL,
  `score` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `exam_scores`
--

INSERT INTO `exam_scores` (`id`, `user_id`, `exam_id`, `score`) VALUES
(1, 3, 2, 13.00),
(2, 3, 4, 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `guides`
--

CREATE TABLE `guides` (
  `id` int(11) NOT NULL,
  `lesson_id` int(11) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `guides`
--

INSERT INTO `guides` (`id`, `lesson_id`, `file`, `name`) VALUES
(34, 4, 'guides/Sin título-1.png', 'Sin título-1.png'),
(38, 3, 'guides/Sin título-1.png', 'Sin título-1.png'),
(39, 3, 'guides/newWork.txt', 'newWork.txt'),
(40, 4, 'guides/newWork.txt', 'newWork.txt'),
(42, 6, 'guides/Estudio de Mercado.docx', 'Estudio de Mercado.docx'),
(43, 6, 'guides/newWork.txt', 'newWork.txt'),
(44, 6, 'guides/woman-sits-stack-books-reads-book_847439-9232.avif', 'woman-sits-stack-books-reads-book_847439-9232.avif'),
(45, 8, 'guides/Unidades (1).pdf', 'Unidades (1).pdf'),
(46, 8, 'guides/document.pdf', 'document.pdf'),
(47, 16, 'guides/document (1).pdf', 'document (1).pdf'),
(48, 5, 'guides/Unidades.pdf', 'Unidades.pdf'),
(49, 15, 'guides/Captura de pantalla_5-10-2024_193430_copilot.microsoft.com.jpeg', 'Captura de pantalla_5-10-2024_193430_copilot.microsoft.com.jpeg'),
(50, 15, 'guides/Captura de pantalla_4-10-2024_105932_bdvenlinea.banvenez.com.jpeg', 'Captura de pantalla_4-10-2024_105932_bdvenlinea.banvenez.com.jpeg'),
(51, 6, 'guides/Solicitud_titulo_21_11188.pdf', 'Solicitud_titulo_21_11188.pdf'),
(52, 6, 'guides/Captura de pantalla_10-11-2024_91425_bdvenlinea.banvenez.com.jpeg', 'Captura de pantalla_10-11-2024_91425_bdvenlinea.banvenez.com.jpeg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lessons`
--

CREATE TABLE `lessons` (
  `id` int(11) NOT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `title` varchar(1000) NOT NULL,
  `content` text DEFAULT NULL,
  `lesson_order` int(100) NOT NULL,
  `summary` text NOT NULL,
  `url` varchar(1000) NOT NULL,
  `isDeleted` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `lessons`
--

INSERT INTO `lessons` (`id`, `unit_id`, `title`, `content`, `lesson_order`, `summary`, `url`, `isDeleted`) VALUES
(3, 3, 'unidad 13: suma y resta fracciones (diferentes denominadores)', '', 2, 'esto es un summary', 'sadasd', 0),
(4, 3, 'el puntos', NULL, 3, 'esta es una prueba', 'https://youtu.be/leUgu-6bWzY', 0),
(5, 4, 'el enfoque sistematico', NULL, 2, 'asdasd', 'test.mp4', 0),
(6, 4, 'introducción a los sistemas', NULL, 1, 'this is a description', 'https://youtu.be/leUgu-6bWzY', 0),
(7, 3, 'cuarta ', NULL, 4, 'this is a summary', 'asdasdas', 0),
(8, 7, 'titulo leccion 1', NULL, 1, 'esto es un texto', 'https://youtu.be/leUgu-6bWzY', 0),
(9, 8, 'sadasdas', NULL, 1, 'asdasdas', 'dasdasdasd', 0),
(10, 8, 'asdasd', NULL, 2, 'asdasdasd', 'asdasdasd', 0),
(11, 8, 'asdsadas', NULL, 22, 'asdasd', 'asdasdasd', 0),
(12, 8, 'asasd', NULL, 5, 'asdasdasd', 'asdasdas', 0),
(13, 8, 'asdasd', NULL, 6, 'asdasdasda', 'asdasd', 0),
(14, 4, 'sistemas de informacion', NULL, 3, 'esto es una prueba', 'https://youtu.be/leUgu-6bWzY', 0),
(15, 4, 'tipos de sistemas de información', NULL, 4, 'this is a test', 'https://youtu.be/leUgu-6bWzY', 0),
(16, 4, 'ciclo de vida del desarrollo del sistema', NULL, 5, 'this is a testa', 'edit_test.mp4', 0),
(17, 4, 'asdasdas', NULL, 6, 'dasdasdd', 'asdasdas', 0),
(18, 4, 'asdasd', NULL, 15, 'asdasdasd', 'asdasdasd', 1),
(19, 4, 'aaaaaaa', NULL, 10, 'asdasda', 'test.mp4', 1),
(20, 4, 'titulo 111', NULL, 11, 'awanile', 'test.mp4', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `person`
--

CREATE TABLE `person` (
  `id` int(10) NOT NULL,
  `nationality` varchar(10) NOT NULL,
  `cedula` varchar(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `second_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `second_last_name` varchar(50) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `birthday` date DEFAULT NULL,
  `gender` varchar(10) NOT NULL,
  `address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `person`
--

INSERT INTO `person` (`id`, `nationality`, `cedula`, `name`, `second_name`, `last_name`, `second_last_name`, `phone`, `birthday`, `gender`, `address`) VALUES
(17, '', '', 'asd', '', 'das', '', '', '2024-10-16', '', ''),
(18, '', '', 'asd', '', 'das', '', '', '2024-10-18', '', ''),
(19, '', '', 'asd', '', 'asd', '', '', '2024-10-07', '', ''),
(20, '', '', 'asd', '', 'asd', '', '', '2024-10-15', '', ''),
(21, 'V-', '28129644', 'admin', 'queso', 'admin', 'alfonsi', '04128581138', '2024-10-15', 'masculino', 'no se '),
(22, '', '', 'iran', '', 'indriago', '', '', '2024-10-09', '', ''),
(23, 'V-', '28129366', 'iran', 'andres', 'indrigo', 'raul', '04128581138', '2006-01-01', '', 'calle juncal'),
(24, 'V-', '4949895', 'mercedes', '', 'figuera', '', '04128146555', '2006-01-01', '', 'no se'),
(25, 'V-', '28129366', 'asd', '', 'asdasd', '', '04128581138', '2006-01-01', '', 'asd'),
(26, '', '', 'nmkj', '', 'jhjhj', '', '', '2024-10-10', '', ''),
(27, '', '', 'nmkj', '', 'jhjhj', '', '', '2024-10-10', '', ''),
(28, '', '', 'nmkj', '', 'jhjhj', '', '', '2024-10-10', '', ''),
(29, '', '', 'nmkj', '', 'jhjhj', '', '', '2024-10-10', '', ''),
(30, '', '', 'nmkj', '', 'jhjhj', '', '', '2024-10-10', '', ''),
(31, '', '', 'nmkj', '', 'jhjhj', '', '', '2024-10-10', '', ''),
(32, '', '', 'nmkj', '', 'jhjhj', '', '', '2024-10-10', '', ''),
(33, '', '', 'asd', '', 'asd', '', '', '2024-10-21', '', ''),
(34, '', '', 'asd', '', 'asd', '', '', '2024-11-14', '', ''),
(35, 'V-', '4949895', 'meche', '', 'figuera', '', '04128844454', '1992-10-14', '', 'no se'),
(36, 'V-', '4949894', 'pierry', '', 'moya', '', '04148581138', '2005-12-05', '', 'no se '),
(37, 'V-', '28128555', 'queso', '', 'alfonsi', '', '', '2024-11-11', '', ''),
(38, 'V-', '3498555', 'lourdes', '', 'figuera', '', '04128581138', '2006-01-01', '', 'no lo se');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `question_order` int(11) NOT NULL,
  `question_mark` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `questions`
--

INSERT INTO `questions` (`id`, `exam_id`, `text`, `question_order`, `question_mark`) VALUES
(1, 3, 'como se declaran las variables enteras', 1, 1),
(8, 3, 'cual es la sintaxis de un \"if\"', 2, 2),
(38, 2, 'pregunta de prueba 1', 1, 4),
(39, 2, 'pregunta de prueba 2', 2, 2),
(40, 2, 'pregunta de prueba 3', 3, 2),
(41, 2, 'prueba 3', 4, 5),
(42, 4, 'pregunta para terminar\n', 1, 20);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `questions_data`
--

CREATE TABLE `questions_data` (
  `id` int(11) NOT NULL,
  `question_id` int(11) DEFAULT NULL,
  `exam_id` int(11) DEFAULT NULL,
  `answer` varchar(255) DEFAULT NULL,
  `type` varchar(150) NOT NULL DEFAULT 'radius',
  `true_response` varchar(255) NOT NULL DEFAULT 'false'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `questions_data`
--

INSERT INTO `questions_data` (`id`, `question_id`, `exam_id`, `answer`, `type`, `true_response`) VALUES
(82, 9, 1, 'ss', 'radius', 'true'),
(83, 9, 1, 'ssasdasdasdasd sadadadasdadasd as das da asasdasd as as asdaasdasdas', 'radius', 'false'),
(131, 8, 1, 'cxcxcx', 'radius', 'false'),
(132, 8, 1, 'cxcxcx', 'radius', 'false'),
(134, 38, 2, 'es esta pregunta verdadera?', 'radius', 'true'),
(135, 38, 2, 'es esta pregunta falsa?', 'radius', 'false'),
(138, 39, 2, 'opcion multiple 2', 'checkbox', 'false'),
(139, 39, 2, 'opcion multiple 3', 'checkbox', 'false'),
(140, 39, 2, 'opcion multiple 4', 'checkbox', 'false'),
(141, 39, 2, 'opcion multiple 5', 'checkbox', 'true'),
(142, 39, 2, 'opcion multiple 1', 'checkbox', 'true'),
(145, 41, 2, 'checkbox 1', 'checkbox', 'true'),
(146, 41, 2, 'checkbox 2', 'checkbox', 'false'),
(147, 41, 2, 'checkbox 3', 'checkbox', 'false'),
(150, 40, 2, 'las variables en php se declaran con el simbolo:', 'text', '$'),
(151, 42, 4, 'this', 'radius', 'true'),
(152, 42, 4, 'also this', 'radius', 'false');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `description`) VALUES
(1, 'ISO', 'This is the only subject required for this project!!!');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `units`
--

CREATE TABLE `units` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `unit_order` int(100) NOT NULL,
  `isDeleted` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `units`
--

INSERT INTO `units` (`id`, `subject_id`, `name`, `unit_order`, `isDeleted`) VALUES
(2, 1, 'asdasd', 3, 1),
(3, 1, 'nuevo nombre', 3, 0),
(4, 1, 'fundamentos de sistemas', 1, 0),
(5, 1, 'prueba 24/10/24', 7, 1),
(6, 1, 'prueba 2', 2, 1),
(7, 1, 'unidad 2', 2, 0),
(8, 1, 'asdasdasd', 5, 1),
(9, 1, 'prueba 4', 4, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user`
--

CREATE TABLE `user` (
  `user_id` int(10) NOT NULL,
  `person_id` int(10) NOT NULL,
  `password` varchar(200) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `isAdmin` int(10) NOT NULL,
  `email` varchar(100) NOT NULL,
  `isBlocked` int(11) NOT NULL DEFAULT 0,
  `isDeleted` int(11) NOT NULL DEFAULT 0,
  `date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user`
--

INSERT INTO `user` (`user_id`, `person_id`, `password`, `isAdmin`, `email`, `isBlocked`, `isDeleted`, `date`) VALUES
(2, 20, '$2y$10$fkimP72pdZrdjLSdKZiMyefGOlTi3jk/ajsXHGLPJwYR0vgA46MSO', 1, 'daniel.alfonsi2011@gmail.com', 0, 0, '2024-10-28'),
(3, 21, '$2y$10$fbBXh8.wLy0x1IBuVnXCP.pxxzrrd4wkaVunnHfwE5wL7/qYdmCky', 1, 'admin@gmail.com', 0, 0, '2024-11-11'),
(4, 22, '$2y$10$8WYYzQQsIIGhV/xVgKoKA.cvQSAjkD.QDbKPgRpe2lHmZcvis.tCa', 1, 'IranIndriago@gmail.com', 0, 1, '2024-05-15'),
(5, 23, '$2y$10$j/O.U.rH1.r39XLXivJDN.IZA8oABgfwTvz5LxWFl0U0WUNkNNRgC', 0, 'irania@gmail.com', 0, 0, '2024-07-29'),
(6, 24, '$2y$10$fbBXh8.wLy0x1IBuVnXCP.pxxzrrd4wkaVunnHfwE5wL7/qYdmCky', 0, 'mechemeche@gmail.com', 0, 0, '2024-04-08'),
(7, 25, '$2y$10$3pJbTd/jBXayzHX0KxpzNe6y4RE8i2pkJxo28sH6xBV/SBzfq7Zoa', 0, 'asda@gmail.com', 0, 1, '2024-08-18'),
(8, 26, '$2y$10$JpkX3IWW61yAoqH4RjIDj.QFJJFxW0qVQXp9aGuweZsr/NUPFoCbq', 0, 'IranIndriagos@gmail.com', 0, 0, '2024-08-11'),
(9, 33, '$2y$10$DQZ1dUcLk1I13jhtJFtUUeE2dFIB2ixap7Cp91q3lq2QKdokxGAFG', 0, 'IranIndriagoss@gmail.com', 1, 1, '2024-07-15'),
(10, 34, '$2y$10$TPh1KxEF3kVxGFWqM1kYOe0IQmYg0bb8EX.FTVmy5F6S2IrHtWYzG', 0, 'Ray@gmail.com', 0, 0, '2024-07-07'),
(11, 35, '$2y$10$NZB.guHzmqd.UcOPi4AWru.ZX1dI3LOMakfvXUkOahGjMCIL2FEwq', 0, 'meme@gmail.com', 0, 0, '2024-09-09'),
(12, 36, '$2y$10$9XnQVh4d11GWnZbyximyfem82yd8hw3w3WyV5uR1YGPz8S8XMwTxm', 0, 'Pierry@gmail.com', 0, 0, '2024-11-15'),
(13, 37, '$2y$10$MH2R7LM088XFiq6yQr3GTugJfFudPu67nsF7Aea1YxzKFvzZTlv.q', 0, 'queso@gmail.com', 0, 0, '2024-11-15'),
(14, 38, '$2y$10$Nj6ijB8Af27zgWm7ibHrd.ody8q.uXE5r2Jjxbx21cGTA5dDkOfbu', 0, 'lulu@gmail.com', 0, 0, '2024-11-18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_history`
--

CREATE TABLE `user_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user_history`
--

INSERT INTO `user_history` (`id`, `user_id`, `action`, `date`) VALUES
(16, 3, 'admin ha editado una unidad', '2024-11-15 17:52:14'),
(17, 3, 'admin ha añadido una unidad', '2024-11-15 17:57:20'),
(18, 3, 'admin ha desabilitado una unidad', '2024-11-15 17:57:37'),
(19, 3, 'admin ha desabilitado una Lección', '2024-11-18 04:08:52'),
(20, 3, 'admin ha desabilitado una Lección', '2024-11-18 04:12:03'),
(21, 3, 'admin ha creado una Lección', '2024-11-18 04:14:15'),
(22, 3, 'admin ha editado una Lección', '2024-11-18 04:14:23'),
(23, 3, 'admin ha editado una Lección', '2024-11-18 04:24:47'),
(24, 3, 'admin ha editado un usuario', '2024-11-18 04:37:02'),
(25, 3, 'admin ha editado un usuario', '2024-11-18 04:37:29'),
(26, 3, 'admin ha borrado a un usuario', '2024-11-18 04:41:57'),
(27, 3, 'admin ha bloqueado a un usuario', '2024-11-18 04:42:04'),
(28, 3, 'admin ha bloqueado a un usuario', '2024-11-18 04:42:05'),
(29, 3, 'admin ha borrado a un usuario', '2024-11-18 04:42:10'),
(30, 3, 'admin ha borrado a un usuario', '2024-11-18 04:42:16'),
(31, 3, 'admin ha desabilitado un Examen', '2024-11-18 13:34:14'),
(32, 3, 'admin ha editado una Lección', '2024-11-18 18:32:33'),
(33, 3, 'admin ha creado un examen', '2024-11-19 01:41:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `videos`
--

CREATE TABLE `videos` (
  `id` int(11) NOT NULL,
  `lesson_id` int(11) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `videos`
--

INSERT INTO `videos` (`id`, `lesson_id`, `file`, `name`) VALUES
(1, 17, 'videos/test.mp4', ''),
(2, 17, 'videos/test.mp4', ''),
(3, 18, 'videos/test.mp4', ''),
(4, 18, 'videos/test.mp4', ''),
(5, 18, 'videos/test.mp4', ''),
(6, 19, 'videos/test.mp4', 'test.mp4'),
(7, 19, 'videos/edit_test.mp4', 'edit_test.mp4'),
(8, 21, 'videos/test.mp4', 'test.mp4'),
(9, 20, 'videos/test.mp4', 'test.mp4'),
(10, 5, 'videos/edit_test.mp4', 'edit_test.mp4');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `exam_scores`
--
ALTER TABLE `exam_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indices de la tabla `guides`
--
ALTER TABLE `guides`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lesson_id` (`lesson_id`);

--
-- Indices de la tabla `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indices de la tabla `person`
--
ALTER TABLE `person`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `questions_data`
--
ALTER TABLE `questions_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indices de la tabla `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indices de la tabla `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `person_id` (`person_id`);

--
-- Indices de la tabla `user_history`
--
ALTER TABLE `user_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lesson_id` (`lesson_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `exams`
--
ALTER TABLE `exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `exam_scores`
--
ALTER TABLE `exam_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `guides`
--
ALTER TABLE `guides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de la tabla `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `person`
--
ALTER TABLE `person`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `questions_data`
--
ALTER TABLE `questions_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=153;

--
-- AUTO_INCREMENT de la tabla `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `user_history`
--
ALTER TABLE `user_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `exam_scores`
--
ALTER TABLE `exam_scores`
  ADD CONSTRAINT `exam_scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `exam_scores_ibfk_2` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`);

--
-- Filtros para la tabla `guides`
--
ALTER TABLE `guides`
  ADD CONSTRAINT `guides_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`);

--
-- Filtros para la tabla `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Filtros para la tabla `questions_data`
--
ALTER TABLE `questions_data`
  ADD CONSTRAINT `questions_data_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`),
  ADD CONSTRAINT `questions_data_ibfk_2` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`);

--
-- Filtros para la tabla `units`
--
ALTER TABLE `units`
  ADD CONSTRAINT `units_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

--
-- Filtros para la tabla `user_history`
--
ALTER TABLE `user_history`
  ADD CONSTRAINT `user_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
