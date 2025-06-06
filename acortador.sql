-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-06-2025 a las 05:54:11
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
-- Base de datos: `acortador`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clicks`
--

CREATE TABLE `clicks` (
  `id` int(11) NOT NULL,
  `link_id` int(11) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `fecha_click` timestamp NOT NULL DEFAULT current_timestamp(),
  `referer` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clicks`
--

INSERT INTO `clicks` (`id`, `link_id`, `ip`, `user_agent`, `fecha_click`, `referer`) VALUES
(1, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-04 01:59:49', NULL),
(2, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-04 02:42:06', NULL),
(3, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-04 02:42:21', NULL),
(4, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-05 00:56:06', 'Directo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `links`
--

CREATE TABLE `links` (
  `id` int(11) NOT NULL,
  `alias` varchar(100) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `mensaje` text DEFAULT NULL,
  `creado_por` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `links`
--

INSERT INTO `links` (`id`, `alias`, `numero`, `mensaje`, `creado_por`, `fecha_creacion`) VALUES
(1, 'HIDROPONIA', '960495553', 'HOLA COMO ESTÁS', 1, '2025-06-04 01:36:39'),
(2, 'NOSENADA', '987654321', 'AADDDAw', 1, '2025-06-04 01:39:30'),
(3, 'TEST1', '987654321', 'TEST', 1, '2025-06-04 01:44:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `rol` enum('admin','usuario') DEFAULT 'usuario',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nombre`, `rol`, `creado_en`) VALUES
(1, 'admin', '$2a$12$GHJNeFny/ghmvAaCzgCp0.fLztdRcvXCUDFrbnRDdQra.S73S40iW', 'ADMINISTRADOR', 'admin', '2025-06-04 01:23:52'),
(2, 'user', '$2y$10$qjfHSuaHuNH5DkPaR2pXOeuJ4VbDIu8vofD2R6gl0nph00eqGJov6', 'Usuario1', 'usuario', '2025-06-05 01:54:14');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clicks`
--
ALTER TABLE `clicks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `link_id` (`link_id`);

--
-- Indices de la tabla `links`
--
ALTER TABLE `links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `alias` (`alias`),
  ADD KEY `creado_por` (`creado_por`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clicks`
--
ALTER TABLE `clicks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `links`
--
ALTER TABLE `links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `clicks`
--
ALTER TABLE `clicks`
  ADD CONSTRAINT `clicks_ibfk_1` FOREIGN KEY (`link_id`) REFERENCES `links` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `links`
--
ALTER TABLE `links`
  ADD CONSTRAINT `links_ibfk_1` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
