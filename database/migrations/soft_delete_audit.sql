-- ============================================
-- MIGRACIÓN: Soft Delete para Auditoría
-- Fecha: 2024
-- ============================================

-- Agregar columnas de soft delete a usuarios
ALTER TABLE `usuarios` 
ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL AFTER `ultimo_login`,
ADD COLUMN `deleted_reason` VARCHAR(255) NULL DEFAULT NULL AFTER `deleted_at`,
ADD COLUMN `deleted_by` INT UNSIGNED NULL DEFAULT NULL AFTER `deleted_reason`,
ADD INDEX `idx_deleted_at` (`deleted_at`);

-- Agregar columnas de soft delete a asignaturas
ALTER TABLE `asignaturas` 
ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL AFTER `horas_semanales`,
ADD COLUMN `deleted_reason` VARCHAR(255) NULL DEFAULT NULL AFTER `deleted_at`,
ADD COLUMN `deleted_by` INT UNSIGNED NULL DEFAULT NULL AFTER `deleted_reason`,
ADD INDEX `idx_deleted_at` (`deleted_at`);

-- Agregar columnas de soft delete a cursos
ALTER TABLE `cursos` 
ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL AFTER `seccion`,
ADD COLUMN `deleted_reason` VARCHAR(255) NULL DEFAULT NULL AFTER `deleted_at`,
ADD COLUMN `deleted_by` INT UNSIGNED NULL DEFAULT NULL AFTER `deleted_reason`,
ADD INDEX `idx_deleted_at` (`deleted_at`);
