-- =====================================================================
-- ACADEMIA DE IDIOMAS - CONSULTAS SQL DE PRÁCTICA
-- Archivo: database/consultas.sql
-- Motor: MySQL / MariaDB (XAMPP)
-- Base de datos: academia_idiomas_db
--
-- Este archivo reúne consultas comentadas sobre las tablas reales del
-- proyecto: roles, usuarios, cursos, matriculas y pagos.
--
-- Palabras clave utilizadas (todas de forma explícita):
--   SELECT   -> secciones 1 a 6
--   INSERT   -> sección 7
--   UPDATE   -> sección 7
--   DELETE   -> sección 7
--   WHERE    -> secciones 1 a 7
--   ORDER BY -> secciones 1 a 6
--   JOIN     -> secciones 1 a 6
--   COUNT    -> secciones 3 y 5
--   SUM      -> secciones 4 y 5
--   GROUP BY -> secciones 3, 4 y 5
--   HAVING   -> secciones 3 y 5
--
-- Las consultas de lectura (secciones 1 a 6) no modifican información.
-- El bloque de escritura (sección 7) está dentro de una transacción que
-- termina con ROLLBACK para poder demostrar INSERT, UPDATE y DELETE sin
-- alterar los datos reales de la academia.
-- =====================================================================

USE academia_idiomas_db;

-- =====================================================================
-- 1. SELECT + WHERE + ORDER BY
-- Objetivo: listar únicamente a los estudiantes (rol 3), ordenados
-- alfabéticamente por nombre.
-- =====================================================================
SELECT
    u.id_usuario,
    u.nombre,
    u.correo,
    r.nombre_rol
FROM usuarios AS u
INNER JOIN roles AS r ON r.id_rol = u.id_rol
WHERE r.nombre_rol = 'estudiante'
ORDER BY u.nombre ASC;

-- =====================================================================
-- 2. JOIN + WHERE + ORDER BY
-- Objetivo: mostrar las matrículas de los niveles A1 y A2 junto con
-- el curso, su precio y el profesor asignado.
-- =====================================================================
SELECT
    u.nombre AS estudiante,
    u.correo,
    c.nombre_curso,
    c.nivel,
    c.precio,
    COALESCE(prof.nombre, 'Sin profesor asignado') AS profesor,
    m.fecha_matricula
FROM matriculas AS m
INNER JOIN usuarios AS u ON u.id_usuario = m.id_usuario
INNER JOIN cursos AS c ON c.id_curso = m.id_curso
LEFT JOIN usuarios AS prof ON prof.id_usuario = c.id_profesor
WHERE c.nivel IN ('A1', 'A2')
ORDER BY c.nombre_curso ASC, u.nombre ASC;

-- =====================================================================
-- 3. COUNT + GROUP BY + HAVING + ORDER BY
-- Objetivo: contar los estudiantes matriculados por curso y mostrar
-- solo los cursos que tienen dos o más estudiantes.
-- =====================================================================
SELECT
    c.nombre_curso,
    c.nivel,
    COUNT(m.id_matricula) AS total_estudiantes
FROM cursos AS c
LEFT JOIN matriculas AS m ON m.id_curso = c.id_curso
GROUP BY c.id_curso, c.nombre_curso, c.nivel
HAVING COUNT(m.id_matricula) >= 2
ORDER BY total_estudiantes DESC, c.nombre_curso ASC;

-- =====================================================================
-- 4. SUM + GROUP BY + JOIN + WHERE
-- Objetivo: calcular el total recaudado por curso, teniendo en cuenta
-- únicamente los pagos con estado 'Pagado'.
-- =====================================================================
SELECT
    c.nombre_curso,
    SUM(pg.monto) AS total_recaudado
FROM pagos AS pg
INNER JOIN matriculas AS m ON m.id_matricula = pg.id_matricula
INNER JOIN cursos AS c ON c.id_curso = m.id_curso
WHERE pg.estado_pago = 'Pagado'
GROUP BY c.id_curso, c.nombre_curso
ORDER BY total_recaudado DESC;

-- =====================================================================
-- 5. JOIN + COUNT + SUM + GROUP BY + HAVING
-- Objetivo: resumen por profesor: cantidad de cursos, estudiantes
-- únicos y dinero recaudado. Solo se muestran profesores con al menos
-- un estudiante matriculado.
-- =====================================================================
SELECT
    prof.nombre AS profesor,
    COUNT(DISTINCT c.id_curso) AS total_cursos,
    COUNT(DISTINCT m.id_usuario) AS total_estudiantes,
    COALESCE(
        SUM(CASE WHEN pg.estado_pago = 'Pagado' THEN pg.monto ELSE 0 END),
        0
    ) AS total_recaudado
FROM usuarios AS prof
INNER JOIN cursos AS c ON c.id_profesor = prof.id_usuario
LEFT JOIN matriculas AS m ON m.id_curso = c.id_curso
LEFT JOIN pagos AS pg ON pg.id_matricula = m.id_matricula
WHERE prof.id_rol = 2
GROUP BY prof.id_usuario, prof.nombre
HAVING COUNT(DISTINCT m.id_usuario) >= 1
ORDER BY total_recaudado DESC, prof.nombre ASC;

-- =====================================================================
-- 6. SELECT + JOIN + WHERE + ORDER BY
-- Objetivo: listar los pagos pendientes o vencidos, con el estudiante,
-- el curso y la fecha, para apoyar la gestión de cartera.
-- =====================================================================
SELECT
    u.nombre AS estudiante,
    c.nombre_curso,
    pg.monto,
    pg.fecha_pago,
    pg.estado_pago
FROM pagos AS pg
INNER JOIN matriculas AS m ON m.id_matricula = pg.id_matricula
INNER JOIN usuarios AS u ON u.id_usuario = m.id_usuario
INNER JOIN cursos AS c ON c.id_curso = m.id_curso
WHERE pg.estado_pago IN ('Pendiente', 'Vencido')
ORDER BY pg.fecha_pago ASC, u.nombre ASC;

-- =====================================================================
-- 7. INSERT + UPDATE + DELETE (ejemplo seguro con ROLLBACK)
--
-- Este bloque crea un usuario de prueba con un correo del dominio
-- reservado .invalid (no existe en el negocio), lo actualiza y lo
-- elimina. Al final se ejecuta ROLLBACK, por lo que la base de datos
-- queda exactamente igual que antes de ejecutar el archivo.
--
-- Si se quisiera conservar el cambio, bastaría con reemplazar
-- ROLLBACK por COMMIT.
-- =====================================================================
START TRANSACTION;

INSERT INTO usuarios (nombre, correo, contrasena, id_rol)
SELECT
    'Estudiante de Prueba',
    'prueba.transaccion@academia.invalid',
    '1234',
    r.id_rol
FROM roles AS r
WHERE r.nombre_rol = 'estudiante'
  AND NOT EXISTS (
      SELECT 1
      FROM usuarios AS u
      WHERE u.correo = 'prueba.transaccion@academia.invalid'
  );

SET @id_prueba := (
    SELECT id_usuario
    FROM usuarios
    WHERE correo = 'prueba.transaccion@academia.invalid'
    LIMIT 1
);

UPDATE usuarios
SET nombre = 'Estudiante de Prueba Actualizado'
WHERE id_usuario = @id_prueba;

DELETE FROM usuarios
WHERE id_usuario = @id_prueba;

ROLLBACK;

-- =====================================================================
-- FIN DEL ARCHIVO
-- =====================================================================
