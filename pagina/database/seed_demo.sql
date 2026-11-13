-- Datos demo para los portales de profesor y estudiante
-- Ejecutar con: mysql -u root academia_idiomas_db < database/seed_demo.sql

USE academia_idiomas_db;

-- Profesores adicionales
INSERT INTO usuarios (nombre, correo, contrasena, id_rol)
SELECT 'Ana Torres', 'ana.torres@academia.edu', '1234', 2
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE correo = 'ana.torres@academia.edu');

INSERT INTO usuarios (nombre, correo, contrasena, id_rol)
SELECT 'Sophie Martin', 'sophie.martin@academia.edu', '1234', 2
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE correo = 'sophie.martin@academia.edu');

-- Estudiantes adicionales
INSERT INTO usuarios (nombre, correo, contrasena, id_rol)
SELECT d.nombre, d.correo, '1234', 3
FROM (
  SELECT 'Julián Morales' AS nombre, 'julian.morales@academia.edu' AS correo
  UNION ALL SELECT 'Mariana Cárdenas', 'mariana.cardenas@academia.edu'
  UNION ALL SELECT 'Diego Álvarez', 'diego.alvarez@academia.edu'
  UNION ALL SELECT 'Sara Castaño', 'sara.castano@academia.edu'
  UNION ALL SELECT 'Daniela Sánchez', 'daniela.sanchez@academia.edu'
  UNION ALL SELECT 'Juan Pablo Ruiz', 'juanpablo.ruiz@academia.edu'
  UNION ALL SELECT 'Laura Medina', 'laura.medina@academia.edu'
  UNION ALL SELECT 'Andrés Castillo', 'andres.castillo@academia.edu'
  UNION ALL SELECT 'María Paula Gómez', 'mariapaula.gomez@academia.edu'
  UNION ALL SELECT 'David Restrepo', 'david.restrepo@academia.edu'
  UNION ALL SELECT 'Valeria Núñez', 'valeria.nunez@academia.edu'
  UNION ALL SELECT 'Lucía Navarro', 'lucia.navarro@academia.edu'
) d
WHERE NOT EXISTS (SELECT 1 FROM usuarios u WHERE u.correo = d.correo);

-- Cursos con profesor asignado
INSERT INTO cursos (nombre_curso, nivel, precio, id_profesor)
SELECT 'Inglés A1', 'A1', 320000, u.id_usuario
FROM usuarios u
WHERE u.correo = 'carlos.mendoza@academia.edu'
  AND NOT EXISTS (SELECT 1 FROM cursos c WHERE c.nombre_curso = 'Inglés A1');

INSERT INTO cursos (nombre_curso, nivel, precio, id_profesor)
SELECT 'Inglés B1', 'B1', 320000, u.id_usuario
FROM usuarios u
WHERE u.correo = 'carlos.mendoza@academia.edu'
  AND NOT EXISTS (SELECT 1 FROM cursos c WHERE c.nombre_curso = 'Inglés B1');

INSERT INTO cursos (nombre_curso, nivel, precio, id_profesor)
SELECT 'Inglés B2', 'B2', 340000, u.id_usuario
FROM usuarios u
WHERE u.correo = 'carlos.mendoza@academia.edu'
  AND NOT EXISTS (SELECT 1 FROM cursos c WHERE c.nombre_curso = 'Inglés B2');

INSERT INTO cursos (nombre_curso, nivel, precio, id_profesor)
SELECT 'Taller de conversación', 'B1', 240000, u.id_usuario
FROM usuarios u
WHERE u.correo = 'carlos.mendoza@academia.edu'
  AND NOT EXISTS (SELECT 1 FROM cursos c WHERE c.nombre_curso = 'Taller de conversación');

INSERT INTO cursos (nombre_curso, nivel, precio, id_profesor)
SELECT 'Francés A1', 'A1', 300000, u.id_usuario
FROM usuarios u
WHERE u.correo = 'sophie.martin@academia.edu'
  AND NOT EXISTS (SELECT 1 FROM cursos c WHERE c.nombre_curso = 'Francés A1');

INSERT INTO cursos (nombre_curso, nivel, precio, id_profesor)
SELECT 'Francés A2', 'A2', 300000, u.id_usuario
FROM usuarios u
WHERE u.correo = 'sophie.martin@academia.edu'
  AND NOT EXISTS (SELECT 1 FROM cursos c WHERE c.nombre_curso = 'Francés A2');

INSERT INTO cursos (nombre_curso, nivel, precio, id_profesor)
SELECT 'Portugués A1', 'A1', 280000, u.id_usuario
FROM usuarios u
WHERE u.correo = 'ana.torres@academia.edu'
  AND NOT EXISTS (SELECT 1 FROM cursos c WHERE c.nombre_curso = 'Portugués A1');

-- Matrículas demo
INSERT INTO matriculas (id_usuario, id_curso, fecha_matricula)
SELECT u.id_usuario, c.id_curso, d.fecha
FROM (
  SELECT 'valentina.rios@academia.edu' AS correo, 'Inglés B1' AS curso, '2026-08-12' AS fecha
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
) d
INNER JOIN usuarios u ON u.correo = d.correo
INNER JOIN cursos c ON c.nombre_curso = d.curso
WHERE NOT EXISTS (
  SELECT 1
  FROM matriculas m
  WHERE m.id_usuario = u.id_usuario
    AND m.id_curso = c.id_curso
);
