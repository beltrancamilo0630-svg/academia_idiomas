-- =====================================================================
-- ACADEMIA DE IDIOMAS - DATOS DE PRUEBA
-- Archivo: database/datos_prueba.sql
-- Motor: MySQL / MariaDB (XAMPP)
-- Base de datos: academia_idiomas_db
--
-- Pobla las tablas roles, usuarios, cursos, matriculas y pagos con
-- información realista para probar el sistema.
--
-- El script es idempotente: utiliza INSERT ... SELECT ... WHERE NOT
-- EXISTS y LEFT JOIN para no duplicar registros si se ejecuta varias
-- veces sobre la misma base de datos.
--
-- Todas las cuentas demo usan la contraseña: 1234
-- En un entorno real las contraseñas deben almacenarse con
-- password_hash() desde PHP, nunca en texto plano.
-- =====================================================================

USE academia_idiomas_db;

-- =====================================================================
-- 1. ROLES
-- Se conservan los identificadores que usa la aplicación:
--   1 = admin, 2 = profesor, 3 = estudiante
-- ON DUPLICATE KEY UPDATE permite volver a ejecutar el script sin
-- errores de llave primaria.
-- =====================================================================
INSERT INTO roles (id_rol, nombre_rol) VALUES
    (1, 'admin'),
    (2, 'profesor'),
    (3, 'estudiante')
ON DUPLICATE KEY UPDATE nombre_rol = VALUES(nombre_rol);

-- =====================================================================
-- 2. USUARIOS
-- Un administrador, cuatro profesores y quince estudiantes.
-- La columna rol del bloque temporal se une con roles.nombre_rol para
-- obtener el id_rol correcto sin escribir números fijos.
-- =====================================================================
INSERT INTO usuarios (nombre, correo, contrasena, id_rol)
SELECT
    d.nombre,
    d.correo,
    d.contrasena,
    r.id_rol
FROM (
    SELECT 'Laura Gómez' AS nombre, 'laura.gomez@academia.edu' AS correo, '1234' AS contrasena, 'admin' AS rol
    UNION ALL SELECT 'Carlos Mendoza', 'carlos.mendoza@academia.edu', '1234', 'profesor'
    UNION ALL SELECT 'Sophie Martin', 'sophie.martin@academia.edu', '1234', 'profesor'
    UNION ALL SELECT 'Ana Torres', 'ana.torres@academia.edu', '1234', 'profesor'
    UNION ALL SELECT 'Marco Rossi', 'marco.rossi@academia.edu', '1234', 'profesor'
    UNION ALL SELECT 'Valentina Ríos', 'valentina.rios@academia.edu', '1234', 'estudiante'
    UNION ALL SELECT 'Julián Morales', 'julian.morales@academia.edu', '1234', 'estudiante'
    UNION ALL SELECT 'Mariana Cárdenas', 'mariana.cardenas@academia.edu', '1234', 'estudiante'
    UNION ALL SELECT 'Diego Álvarez', 'diego.alvarez@academia.edu', '1234', 'estudiante'
    UNION ALL SELECT 'Sara Castaño', 'sara.castano@academia.edu', '1234', 'estudiante'
    UNION ALL SELECT 'Daniela Sánchez', 'daniela.sanchez@academia.edu', '1234', 'estudiante'
    UNION ALL SELECT 'Juan Pablo Ruiz', 'juanpablo.ruiz@academia.edu', '1234', 'estudiante'
    UNION ALL SELECT 'Laura Medina', 'laura.medina@academia.edu', '1234', 'estudiante'
    UNION ALL SELECT 'Andrés Castillo', 'andres.castillo@academia.edu', '1234', 'estudiante'
    UNION ALL SELECT 'María Paula Gómez', 'mariapaula.gomez@academia.edu', '1234', 'estudiante'
    UNION ALL SELECT 'David Restrepo', 'david.restrepo@academia.edu', '1234', 'estudiante'
    UNION ALL SELECT 'Valeria Núñez', 'valeria.nunez@academia.edu', '1234', 'estudiante'
    UNION ALL SELECT 'Lucía Navarro', 'lucia.navarro@academia.edu', '1234', 'estudiante'
    UNION ALL SELECT 'Camila Herrera', 'camila.herrera@academia.edu', '1234', 'estudiante'
    UNION ALL SELECT 'Felipe Ramírez', 'felipe.ramirez@academia.edu', '1234', 'estudiante'
) AS d
INNER JOIN roles AS r ON r.nombre_rol = d.rol
LEFT JOIN usuarios AS u ON u.correo = d.correo
WHERE u.id_usuario IS NULL;

-- =====================================================================
-- 3. CURSOS
-- Oferta académica de inglés, francés, portugués e italiano.
-- Cada curso se asigna a un profesor mediante su correo.
-- =====================================================================
INSERT INTO cursos (nombre_curso, nivel, precio, id_profesor)
SELECT
    d.nombre_curso,
    d.nivel,
    d.precio,
    prof.id_usuario
FROM (
    SELECT 'Inglés A1' AS nombre_curso, 'A1' AS nivel, 320000.00 AS precio, 'carlos.mendoza@academia.edu' AS correo_profesor
    UNION ALL SELECT 'Inglés A2', 'A2', 320000.00, 'carlos.mendoza@academia.edu'
    UNION ALL SELECT 'Inglés B1', 'B1', 320000.00, 'carlos.mendoza@academia.edu'
    UNION ALL SELECT 'Inglés B2', 'B2', 340000.00, 'carlos.mendoza@academia.edu'
    UNION ALL SELECT 'Taller de conversación', 'B1', 240000.00, 'carlos.mendoza@academia.edu'
    UNION ALL SELECT 'Francés A1', 'A1', 300000.00, 'sophie.martin@academia.edu'
    UNION ALL SELECT 'Francés A2', 'A2', 300000.00, 'sophie.martin@academia.edu'
    UNION ALL SELECT 'Portugués A1', 'A1', 280000.00, 'ana.torres@academia.edu'
    UNION ALL SELECT 'Italiano A1', 'A1', 300000.00, 'marco.rossi@academia.edu'
) AS d
INNER JOIN usuarios AS prof ON prof.correo = d.correo_profesor
LEFT JOIN cursos AS c ON c.nombre_curso = d.nombre_curso
WHERE c.id_curso IS NULL;

-- =====================================================================
-- 4. MATRÍCULAS
-- Cada estudiante se matricula en uno o varios cursos.
-- Se evita duplicar la combinación usuario + curso.
-- =====================================================================
INSERT INTO matriculas (id_usuario, id_curso, fecha_matricula)
SELECT
    u.id_usuario,
    c.id_curso,
    d.fecha_matricula
FROM (
    SELECT 'valentina.rios@academia.edu' AS correo, 'Inglés B1' AS curso, '2026-08-12' AS fecha_matricula
    UNION ALL SELECT 'valentina.rios@academia.edu', 'Taller de conversación', '2026-10-05'
    UNION ALL SELECT 'julian.morales@academia.edu', 'Inglés B1', '2026-08-12'
    UNION ALL SELECT 'julian.morales@academia.edu', 'Inglés B2', '2026-08-12'
    UNION ALL SELECT 'mariana.cardenas@academia.edu', 'Inglés B1', '2026-08-14'
    UNION ALL SELECT 'mariana.cardenas@academia.edu', 'Taller de conversación', '2026-10-05'
    UNION ALL SELECT 'diego.alvarez@academia.edu', 'Inglés B1', '2026-08-15'
    UNION ALL SELECT 'sara.castano@academia.edu', 'Inglés B1', '2026-08-12'
    UNION ALL SELECT 'sara.castano@academia.edu', 'Taller de conversación', '2026-10-05'
    UNION ALL SELECT 'daniela.sanchez@academia.edu', 'Inglés A1', '2026-09-14'
    UNION ALL SELECT 'juanpablo.ruiz@academia.edu', 'Inglés A1', '2026-09-14'
    UNION ALL SELECT 'laura.medina@academia.edu', 'Inglés A1', '2026-09-16'
    UNION ALL SELECT 'andres.castillo@academia.edu', 'Inglés A1', '2026-09-18'
    UNION ALL SELECT 'mariapaula.gomez@academia.edu', 'Francés A1', '2026-08-20'
    UNION ALL SELECT 'david.restrepo@academia.edu', 'Francés A1', '2026-08-22'
    UNION ALL SELECT 'lucia.navarro@academia.edu', 'Francés A2', '2026-08-25'
    UNION ALL SELECT 'valeria.nunez@academia.edu', 'Portugués A1', '2026-09-01'
    UNION ALL SELECT 'camila.herrera@academia.edu', 'Italiano A1', '2026-09-08'
    UNION ALL SELECT 'felipe.ramirez@academia.edu', 'Inglés A2', '2026-09-18'
) AS d
INNER JOIN usuarios AS u ON u.correo = d.correo
INNER JOIN cursos AS c ON c.nombre_curso = d.curso
LEFT JOIN matriculas AS m
    ON m.id_usuario = u.id_usuario
   AND m.id_curso = c.id_curso
WHERE m.id_matricula IS NULL;

-- =====================================================================
-- 5. PAGOS
-- Se registra un pago por cada matrícula que todavía no tenga uno.
-- Los estados permiten probar cartera: Pagado, Pendiente y Vencido.
-- Diego Álvarez tiene un abono parcial de $160.000 sobre Inglés B1.
-- =====================================================================
INSERT INTO pagos (id_matricula, monto, fecha_pago, estado_pago)
SELECT
    m.id_matricula,
    d.monto,
    d.fecha_pago,
    d.estado_pago
FROM (
    SELECT 'valentina.rios@academia.edu' AS correo, 'Inglés B1' AS curso, 320000.00 AS monto, '2026-08-15' AS fecha_pago, 'Pagado' AS estado_pago
    UNION ALL SELECT 'valentina.rios@academia.edu', 'Taller de conversación', 240000.00, '2026-10-06', 'Pagado'
    UNION ALL SELECT 'julian.morales@academia.edu', 'Inglés B1', 320000.00, '2026-08-15', 'Pagado'
    UNION ALL SELECT 'julian.morales@academia.edu', 'Inglés B2', 340000.00, '2026-08-15', 'Pagado'
    UNION ALL SELECT 'mariana.cardenas@academia.edu', 'Inglés B1', 320000.00, '2026-08-16', 'Pagado'
    UNION ALL SELECT 'mariana.cardenas@academia.edu', 'Taller de conversación', 240000.00, '2026-10-06', 'Pendiente'
    UNION ALL SELECT 'diego.alvarez@academia.edu', 'Inglés B1', 160000.00, '2026-08-18', 'Pendiente'
    UNION ALL SELECT 'sara.castano@academia.edu', 'Inglés B1', 320000.00, '2026-08-15', 'Pagado'
    UNION ALL SELECT 'sara.castano@academia.edu', 'Taller de conversación', 240000.00, '2026-10-06', 'Pagado'
    UNION ALL SELECT 'daniela.sanchez@academia.edu', 'Inglés A1', 320000.00, '2026-09-15', 'Pagado'
    UNION ALL SELECT 'juanpablo.ruiz@academia.edu', 'Inglés A1', 320000.00, '2026-09-15', 'Pendiente'
    UNION ALL SELECT 'laura.medina@academia.edu', 'Inglés A1', 320000.00, '2026-09-17', 'Pagado'
    UNION ALL SELECT 'andres.castillo@academia.edu', 'Inglés A1', 320000.00, '2026-09-19', 'Vencido'
    UNION ALL SELECT 'mariapaula.gomez@academia.edu', 'Francés A1', 300000.00, '2026-08-22', 'Pagado'
    UNION ALL SELECT 'david.restrepo@academia.edu', 'Francés A1', 300000.00, '2026-08-24', 'Pendiente'
    UNION ALL SELECT 'lucia.navarro@academia.edu', 'Francés A2', 300000.00, '2026-08-26', 'Pagado'
    UNION ALL SELECT 'valeria.nunez@academia.edu', 'Portugués A1', 280000.00, '2026-09-02', 'Pagado'
    UNION ALL SELECT 'camila.herrera@academia.edu', 'Italiano A1', 300000.00, '2026-09-10', 'Pendiente'
    UNION ALL SELECT 'felipe.ramirez@academia.edu', 'Inglés A2', 320000.00, '2026-09-20', 'Vencido'
) AS d
INNER JOIN usuarios AS u ON u.correo = d.correo
INNER JOIN cursos AS c ON c.nombre_curso = d.curso
INNER JOIN matriculas AS m
    ON m.id_usuario = u.id_usuario
   AND m.id_curso = c.id_curso
LEFT JOIN pagos AS pg ON pg.id_matricula = m.id_matricula
WHERE pg.id_pago IS NULL;

-- =====================================================================
-- FIN DEL ARCHIVO
-- =====================================================================
