-- ============================================
-- LECCIONARIO DIGITAL - Base de datos
-- MySQL/MariaDB
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================
-- Base de datos
-- ============================================
CREATE DATABASE IF NOT EXISTS `leccionario_digital` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `leccionario_digital`;

-- ============================================
-- Tabla: roles
-- ============================================
CREATE TABLE `roles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(30) NOT NULL,
    `descripcion` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla: niveles_educativos
-- ============================================
CREATE TABLE `niveles_educativos` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(50) NOT NULL,
    `abreviatura` VARCHAR(10) NOT NULL,
    `orden` INT NOT NULL DEFAULT 0,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla: usuarios
-- ============================================
CREATE TABLE `usuarios` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `apellido` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `telefono` VARCHAR(20) DEFAULT NULL,
    `nivel_coordinacion` INT UNSIGNED DEFAULT NULL,
    `firma` LONGBLOB DEFAULT NULL,
    `primer_login` TINYINT(1) NOT NULL DEFAULT 1,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `ultimo_login` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    FOREIGN KEY (`nivel_coordinacion`) REFERENCES `niveles_educativos`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla: usuarios_niveles (docentes que enseñan en varios niveles)
-- ============================================
CREATE TABLE `usuarios_niveles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `usuario_id` INT UNSIGNED NOT NULL,
    `nivel_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_usuario_nivel` (`usuario_id`, `nivel_id`),
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`nivel_id`) REFERENCES `niveles_educativos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla: usuario_roles (pivot)
-- ============================================
CREATE TABLE `usuario_roles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `usuario_id` INT UNSIGNED NOT NULL,
    `rol_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `usuario_id` (`usuario_id`),
    KEY `rol_id` (`rol_id`),
    CONSTRAINT `usuario_roles_usuario_fk` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
    CONSTRAINT `usuario_roles_rol_fk` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla: cursos
-- ============================================
CREATE TABLE `cursos` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `nivel` VARCHAR(50) DEFAULT NULL,
    `seccion` VARCHAR(10) DEFAULT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla: asignaturas
-- ============================================
CREATE TABLE `asignaturas` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `codigo` VARCHAR(20) NOT NULL,
    `nombre` VARCHAR(150) NOT NULL,
    `area` VARCHAR(100) DEFAULT NULL,
    `nivel_id` INT UNSIGNED DEFAULT NULL,
    `horas_semanales` INT DEFAULT 0,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `codigo` (`codigo`),
    FOREIGN KEY (`nivel_id`) REFERENCES `niveles_educativos`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla: asignaturas_docentes
-- ============================================
CREATE TABLE `asignaturas_docentes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `usuario_id` INT UNSIGNED NOT NULL,
    `asignatura_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_docente_asignatura` (`usuario_id`, `asignatura_id`),
    KEY `usuario_id` (`usuario_id`),
    KEY `asignatura_id` (`asignatura_id`),
    CONSTRAINT `asignaturas_docentes_usuario_fk` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
    CONSTRAINT `asignaturas_docentes_asignatura_fk` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla: horarios
-- ============================================
CREATE TABLE `horarios` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `usuario_id` INT UNSIGNED NOT NULL,
    `curso_id` INT UNSIGNED NOT NULL,
    `asignatura_id` INT UNSIGNED NOT NULL,
    `dia_semana` TINYINT NOT NULL COMMENT '1=Lunes, 2=Martes, 3=Miércoles, 4=Jueves, 5=Viernes',
    `hora_inicio` TIME NOT NULL,
    `hora_fin` TIME NOT NULL,
    `aula` VARCHAR(50) DEFAULT NULL,
    `periodo` VARCHAR(20) NOT NULL COMMENT 'Ej: 2026-1',
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `usuario_id` (`usuario_id`),
    KEY `curso_id` (`curso_id`),
    KEY `asignatura_id` (`asignatura_id`),
    CONSTRAINT `horarios_usuario_fk` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
    CONSTRAINT `horarios_curso_fk` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
    CONSTRAINT `horarios_asignatura_fk` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla: leccionarios
-- ============================================
CREATE TABLE `leccionarios` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `usuario_id` INT UNSIGNED NOT NULL,
    `horario_id` INT UNSIGNED NOT NULL,
    `fecha` DATE NOT NULL,
    `contenido` TEXT NOT NULL,
    `observaciones` TEXT DEFAULT NULL,
    `firmado` TINYINT(1) NOT NULL DEFAULT 0,
    `fecha_registro` DATETIME NOT NULL,
    `estado` ENUM('pendiente', 'completado', 'atrasado') NOT NULL DEFAULT 'pendiente',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_leccionario` (`usuario_id`, `horario_id`, `fecha`),
    KEY `usuario_id` (`usuario_id`),
    KEY `horario_id` (`horario_id`),
    KEY `fecha` (`fecha`),
    KEY `estado` (`estado`),
    CONSTRAINT `leccionarios_usuario_fk` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
    CONSTRAINT `leccionarios_horario_fk` FOREIGN KEY (`horario_id`) REFERENCES `horarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla: configuraciones
-- ============================================
CREATE TABLE `configuraciones` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `clave` VARCHAR(100) NOT NULL,
    `valor` TEXT DEFAULT NULL,
    `descripcion` VARCHAR(255) DEFAULT NULL,
    `tipo` ENUM('string', 'int', 'boolean', 'json') DEFAULT 'string',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla: logs_notificaciones
-- ============================================
CREATE TABLE `logs_notificaciones` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `usuario_id` INT UNSIGNED NOT NULL,
    `tipo` ENUM('recordatorio', 'bienvenida', 'alerta', 'sistema') NOT NULL,
    `asunto` VARCHAR(255) NOT NULL,
    `mensaje` TEXT NOT NULL,
    `enviado` TINYINT(1) NOT NULL DEFAULT 0,
    `leido` TINYINT(1) NOT NULL DEFAULT 0,
    `fecha_envio` DATETIME DEFAULT NULL,
    `fecha_lectura` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `usuario_id` (`usuario_id`),
    KEY `tipo` (`tipo`),
    KEY `leido` (`leido`),
    CONSTRAINT `logs_notificaciones_usuario_fk` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla: login_intentos (protección fuerza bruta)
-- ============================================
CREATE TABLE IF NOT EXISTS `login_intentos` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ip` VARCHAR(45) NOT NULL,
    `email` VARCHAR(150) DEFAULT NULL,
    `intentos` INT UNSIGNED NOT NULL DEFAULT 1,
    `ultimo_intento` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ip` (`ip`),
    KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATOS INICIALES (Seeders)
-- ============================================

-- Roles
INSERT INTO `roles` (`nombre`, `slug`, `descripcion`) VALUES
('Docente', 'docente', 'Rol para profesores que imparten clases'),
('Coordinador', 'coordinador', 'Rol para coordinadores académicos'),
('Rector', 'rector', 'Rol para el rector de la institución'),
('Vicerrector', 'vicerrector', 'Rol para el vicerrector de la institución');

-- Niveles Educativos
INSERT INTO `niveles_educativos` (`nombre`, `abreviatura`, `orden`) VALUES
('Educación Inicial / Jardín', 'JARDÍN', 1),
('Educación General Básica Elemental', 'EGB ELEMENTAL', 2),
('Educación General Básica Media', 'EGB MEDIA', 3),
('Educación General Básica Superior', 'EGB SUPERIOR', 4),
('Bachillerato General Unificado', 'BGU', 5),
('Bachillerato Técnico', 'BACH TÉCNICO', 6);

-- Configuraciones por defecto
INSERT INTO `configuraciones` (`clave`, `valor`, `descripcion`, `tipo`) VALUES
('habilitar_edicion_horarios', '0', 'Permite a los docentes editar su horario (0=deshabilitado, 1=habilitado)', 'int'),
('horarios_fecha_expiracion', NULL, 'Fecha de expiración para edición de horarios', 'string'),
('bloqueo_semanas_atras', '1', 'Semanas permitidas hacia atrás para llenar leccionarios (0 = sin bloqueo)', 'int'),
('institution_name', 'Institución Educativa', 'Nombre de la institución', 'string'),
('notification_days_before', '1', 'Días antes para enviar recordatorios', 'int'),
('login_max_intentos', '5', 'Numero maximo de intentos de login fallidos antes de bloquear', 'int'),
('login_bloqueo_minutos', '15', 'Minutos de bloqueo despues de maximo de intentos', 'int'),
('smtp_host', '', 'Host del servidor SMTP', 'string'),
('smtp_port', '587', 'Puerto del servidor SMTP', 'int'),
('smtp_user', '', 'Usuario SMTP', 'string'),
('smtp_password', '', 'Contraseña SMTP (encriptada)', 'string');

-- Usuario administrador por defecto (password: admin123)
INSERT INTO `usuarios` (`nombre`, `apellido`, `email`, `password`, `telefono`, `primer_login`, `activo`) VALUES
('Admin', 'Sistema', 'admin@leccionario.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0000000000', 0, 1);

-- Asignar rol de coordinador al admin
INSERT INTO `usuario_roles` (`usuario_id`, `rol_id`) VALUES
(1, 1), -- Admin es docente
(1, 2); -- Admin es coordinador

-- Usuarios de prueba (password temporal para todos: 12345)
INSERT INTO `usuarios` (`nombre`, `apellido`, `email`, `password`, `telefono`, `primer_login`, `activo`) VALUES
('Christian', 'Rodriguez', 'crodriguez@ecomundo.edu.ec', '$2y$10$iKxQ9kZPBqB8u5zXh6xLqOZ9J5qN1XU7r4H2yM0xQa3K8V5d9D1Ee', '0991234567', 1, 1),
('Ana', 'Burgos', 'aburgos@ecomundo.edu.ec', '$2y$10$iKxQ9kZPBqB8u5zXh6xLqOZ9J5qN1XU7r4H2yM0xQa3K8V5d9D1Ee', '0987654321', 1, 1);

-- Asignar rol de docente a los profesores
INSERT INTO `usuario_roles` (`usuario_id`, `rol_id`) VALUES
(2, 1), -- Christian es docente
(3, 1); -- Ana es docente

-- Asignar niveles a los profesores
INSERT INTO `usuarios_niveles` (`usuario_id`, `nivel_id`) VALUES
(2, 3), -- Christian enseña en EGB Media
(2, 4), -- Christian enseña en EGB Superior
(3, 4), -- Ana enseña en EGB Superior
(3, 5); -- Ana enseña en BGU

-- Cursos de ejemplo
INSERT INTO `cursos` (`nombre`, `nivel`, `seccion`, `activo`) VALUES
('1RO EGB A', '1ro', 'A', 1),
('2DO EGB A', '2do', 'A', 1),
('3RO EGB A', '3ro', 'A', 1),
('4TO EGB A', '4to', 'A', 1),
('5TO EGB A', '5to', 'A', 1),
('6TO EGB A', '6to', 'A', 1),
('7MO EGB A', '7mo', 'A', 1),
('8VO EGB A', '8vo', 'A', 1),
('9NO EGB A', '9no', 'A', 1),
('10MO EGB A', '10mo', 'A', 1),
('1RO BGU A', '1ro', 'A', 1),
('2DO BGU A', '2do', 'A', 1),
('3RO BGU A', '3ro', 'A', 1);

-- Asignaturas de ejemplo (con nivel_id)
INSERT INTO `asignaturas` (`codigo`, `nombre`, `area`, `nivel_id`, `horas_semanales`) VALUES
('MAT1-3', 'Matemáticas (1-3)', 'Ciencias Exactas', 2, 5),
('MAT4-6', 'Matemáticas (4-6)', 'Ciencias Exactas', 3, 5),
('MAT7-10', 'Matemáticas (7-10)', 'Ciencias Exactas', 4, 5),
('MAT BGU', 'Matemáticas BGU', 'Ciencias Exactas', 5, 6),
('LEN1-3', 'Lengua y Literatura (1-3)', 'Humanidades', 2, 5),
('LEN4-6', 'Lengua y Literatura (4-6)', 'Humanidades', 3, 5),
('LEN7-10', 'Lengua y Literatura (7-10)', 'Humanidades', 4, 5),
('LEN BGU', 'Lengua y Literatura BGU', 'Humanidades', 5, 5),
('NAT', 'Ciencias Naturales', 'Ciencias', 4, 4),
('BIO', 'Biología', 'Ciencias', 5, 4),
('FIS', 'Física', 'Ciencias', 5, 4),
('QUI', 'Química', 'Ciencias', 5, 4),
('SOC', 'Estudios Sociales', 'Ciencias Sociales', 3, 3),
('ING', 'Inglés', 'Idiomas', 2, 3),
('ING2', 'Inglés Intermedio', 'Idiomas', 4, 3),
('ING BGU', 'Inglés BGU', 'Idiomas', 5, 4),
('ED.FIS', 'Educación Física', 'Salud', 2, 2),
('ART', 'Arte', 'Artes', 2, 2),
('ART2', 'Artes Visuales', 'Artes', 4, 2),
('INFO', 'Informática', 'Tecnología', 3, 2),
('INFO2', 'TIC', 'Tecnología', 5, 2),
('DIB', 'Dibujo', 'Artes', 4, 2),
('EMPR', 'Emprendimiento', 'Ciencias Sociales', 5, 2);
