-- Migración: relación entre cursos y profesores
-- Ejecutar con: mysql -u root academia_idiomas_db < database/migracion_cursos_profesor.sql

USE academia_idiomas_db;

SET @col_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'cursos'
    AND COLUMN_NAME = 'id_profesor'
);

SET @sql := IF(
  @col_exists = 0,
  'ALTER TABLE cursos ADD COLUMN id_profesor INT(11) NULL AFTER precio',
  'SELECT ''La columna cursos.id_profesor ya existe'' AS mensaje'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'cursos'
    AND CONSTRAINT_NAME = 'fk_cursos_profesor'
);

SET @sql := IF(
  @fk_exists = 0,
  'ALTER TABLE cursos ADD CONSTRAINT fk_cursos_profesor FOREIGN KEY (id_profesor) REFERENCES usuarios(id_usuario) ON DELETE SET NULL ON UPDATE CASCADE',
  'SELECT ''La llave foránea fk_cursos_profesor ya existe'' AS mensaje'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
