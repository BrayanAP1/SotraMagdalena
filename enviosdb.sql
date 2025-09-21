-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-09-2025 a las 17:55:22
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
-- Base de datos: `enviosdb`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `enviosxdimensiones`
--

CREATE TABLE `enviosxdimensiones` (
  `id` int(11) NOT NULL,
  `nombre_cliente` varchar(100) NOT NULL,
  `direccion_origen` varchar(255) NOT NULL,
  `direccion_destino` varchar(255) NOT NULL,
  `contenido` varchar(255) DEFAULT NULL,
  `ancho` decimal(10,2) DEFAULT NULL,
  `alto` decimal(10,2) DEFAULT NULL,
  `largo` decimal(10,2) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `rango` varchar(50) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `enviosxdimensiones`
--

INSERT INTO `enviosxdimensiones` (`id`, `nombre_cliente`, `direccion_origen`, `direccion_destino`, `contenido`, `ancho`, `alto`, `largo`, `precio`, `rango`, `fecha_registro`, `usuario_id`) VALUES
(38, 'Brayan Pulido', 'Santa Rosa ', 'Bucaramanga', 'Droga', 10.00, 10.00, 10.00, 1000.00, 'Pequeño', '2025-08-30 20:19:44', 17),
(39, 'Yeison', 'Cartagena', 'Bucaramanga', 'Iphone', 7.00, 10.00, 2.00, 140.00, 'Pequeño', '2025-09-03 20:51:07', 11),
(40, 'Yeison', 'Cartagena', 'Bucaramanga', 'Iphone', 7.00, 10.00, 2.00, 140.00, 'Pequeño', '2025-09-03 20:52:57', 11),
(41, 'Sandra', 'Cucuta', 'Bucaramanga', 'Motor ', 40.00, 40.00, 40.00, 128000.00, 'Mediano', '2025-09-05 16:07:31', 11),
(42, 'Jhon', 'Bucaramanga', 'Bogota', 'Repuestos', 20.00, 20.00, 20.00, 16000.00, 'Mediano', '2025-09-05 16:38:50', 11),
(43, 'Ernesto', 'Santa Marta', 'Cartagena', 'Joyeria', 10.00, 5.00, 10.00, 500.00, 'Pequeño', '2025-09-05 16:43:21', 11),
(44, 'Monica', 'Santa Rosa ', 'Bucaramanga', 'Repuestos', 20.00, 20.00, 20.00, 16000.00, 'Mediano', '2025-09-05 17:15:02', 11),
(45, 'Monica', 'Santa Rosa ', 'Bucaramanga', 'Repuestos', 20.00, 20.00, 20.00, 16000.00, 'Mediano', '2025-09-05 17:21:21', 11),
(46, 'Monica', 'Santa Rosa ', 'Bucaramanga', 'Repuestos', 20.00, 20.00, 20.00, 16000.00, 'Mediano', '2025-09-05 17:21:37', 11),
(47, 'Monica', 'Santa Rosa ', 'Bucaramanga', 'Repuestos', 20.00, 20.00, 20.00, 16000.00, 'Mediano', '2025-09-05 17:22:07', 11),
(48, 'Edwin', 'Santa Rosa ', 'Bucaramanga', 'Mecanica', 10.00, 20.00, 40.00, 16000.00, 'Mediano', '2025-09-09 19:56:07', 11),
(49, 'Pulido', 'Bucaramanga', 'Simiti', 'Repuestos', 10.00, 20.00, 40.00, 16000.00, 'Mediano', '2025-09-10 16:45:47', 11);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `enviosxpeso`
--

CREATE TABLE `enviosxpeso` (
  `id` int(11) NOT NULL,
  `nombre_cliente` varchar(100) NOT NULL,
  `direccion_origen` varchar(255) NOT NULL,
  `direccion_destino` varchar(255) NOT NULL,
  `contenido` varchar(255) NOT NULL,
  `peso` decimal(10,2) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `precio` decimal(10,2) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `enviosxpeso`
--

INSERT INTO `enviosxpeso` (`id`, `nombre_cliente`, `direccion_origen`, `direccion_destino`, `contenido`, `peso`, `fecha_registro`, `precio`, `usuario_id`) VALUES
(29, 'Diego Reyes', 'Bucaramanga', 'Santa Rosa del Sur', 'Medicamentos', 8.00, '2025-08-30 20:21:55', 8000.00, 11),
(30, 'Agustin', 'Bogota', 'Bucaramanga', 'Mesa', 10.00, '2025-09-05 16:06:33', 10000.00, 11),
(31, 'Alex', 'Santa Rosa ', 'Cucuta', 'Portatil', 2.00, '2025-09-05 16:33:42', 2000.00, 11),
(35, 'Juan', 'Choco', 'Cesar', 'Moto', 20.00, '2025-09-05 16:37:32', 20000.00, 11),
(36, 'Diana', 'USA', 'Collmbia', 'Ropa', 10.00, '2025-09-05 16:42:27', 10000.00, 11),
(37, 'Sebas', 'Santa Rosa ', 'Cucuta', 'Barberia', 2.00, '2025-09-05 17:41:06', 2000.00, 11),
(38, 'Sebas', 'Santa Rosa ', 'Cucuta', 'Barberia', 2.00, '2025-09-05 17:41:22', 2000.00, 11),
(46, 'Daniel Roa', 'Bogota', 'Bucaramanga', 'Silla', 4.00, '2025-09-09 15:45:32', 4000.00, 11),
(47, 'Liliana', 'Bucaramanga', 'Santa Rosa del Sur', 'Medicamentos', 20.00, '2025-09-09 20:01:07', 20000.00, 11),
(48, 'Crtistian', 'Cucuta', 'Simiti', 'Delicado', 2.00, '2025-09-10 16:50:35', 2000.00, 11);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(150) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre`, `direccion`, `telefono`, `correo`, `fecha_registro`) VALUES
(1, 'Brayan Pulido R', 'Santa Rosa', '3165508903', 'brayan@gmail.com', '2025-08-22 15:45:44'),
(2, 'Diego Reyes', 'Florida Blanca', '3165433476', 'diego@gmail.com', '2025-08-22 15:46:16'),
(5, 'Diego Reyes Reyes', 'Florida Blanca', '3165508903', 'jhon@gmail.com', '2025-09-09 21:32:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rangoxdimen`
--

CREATE TABLE `rangoxdimen` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `minimo` decimal(10,2) NOT NULL,
  `maximo` decimal(10,2) NOT NULL,
  `precio_por_unidad` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rangoxdimen`
--

INSERT INTO `rangoxdimen` (`id`, `nombre`, `minimo`, `maximo`, `precio_por_unidad`) VALUES
(1, 'Pequeño', 1.00, 1000.00, 1.00),
(2, 'Mediano', 1001.00, 100000.00, 2.00),
(3, 'Grande', 100001.00, 10000000.00, 3.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rangoxpeso`
--

CREATE TABLE `rangoxpeso` (
  `id` int(11) NOT NULL,
  `rango_nombre` varchar(50) NOT NULL,
  `peso_min` decimal(10,2) NOT NULL,
  `peso_max` decimal(10,2) NOT NULL,
  `precio_kg` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rangoxpeso`
--

INSERT INTO `rangoxpeso` (`id`, `rango_nombre`, `peso_min`, `peso_max`, `precio_kg`) VALUES
(1, 'delete', 0.00, 20.00, 1000.00),
(3, 'delete2', 21.00, 40.00, 2000.00),
(5, 'delete3', 41.00, 60.00, 3000.00),
(11, 'delete4', 61.00, 80.00, 4000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('usuario','administrador') NOT NULL DEFAULT 'usuario',
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `username`, `password`, `rol`, `estado`) VALUES
(11, 'Usu1', 'usuario1', '$2y$10$usTVn0EvxcUY3L3cyPsf8OJlW6N6jRRAde7/5/7inRfxU2eNDWoSW', 'usuario', 1),
(16, 'SuperAdmin', 'admin', '$2y$10$pDiak7ecfAxjqfWwiUoxdudAE4.oOkam9.Lnk5fOjDhNUQAQMXaCO', 'administrador', 1),
(17, 'Usu2', 'usuario2', '$2y$10$ORSVJhE.nVuE.11Chbi46uhpS/0ZL7VQhXcFj.wDKEq.94X57H7Qa', 'usuario', 1),
(18, 'Usu3', 'usuario3', '$2y$10$/.GKt21/wJauCdId5f7GduKGBgz/HgpVJkrM5tzcg8YUTlnX1Y8rK', 'usuario', 1),
(20, 'Usu4', 'usuario4', '$2y$10$DASvlRbSZZL0WyaUyzI.Qet4dGrFr6WWN2N18RQnTcEGF/pY0vwr2', 'usuario', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `enviosxdimensiones`
--
ALTER TABLE `enviosxdimensiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_enviosxdimensiones_usuario` (`usuario_id`);

--
-- Indices de la tabla `enviosxpeso`
--
ALTER TABLE `enviosxpeso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_enviosxpeso_usuario` (`usuario_id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `rangoxdimen`
--
ALTER TABLE `rangoxdimen`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `rangoxpeso`
--
ALTER TABLE `rangoxpeso`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `enviosxdimensiones`
--
ALTER TABLE `enviosxdimensiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `enviosxpeso`
--
ALTER TABLE `enviosxpeso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `rangoxdimen`
--
ALTER TABLE `rangoxdimen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `rangoxpeso`
--
ALTER TABLE `rangoxpeso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `enviosxdimensiones`
--
ALTER TABLE `enviosxdimensiones`
  ADD CONSTRAINT `fk_enviosxdimensiones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `enviosxpeso`
--
ALTER TABLE `enviosxpeso`
  ADD CONSTRAINT `fk_enviosxpeso_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
