# Academia de Idiomas — Sistema académico

Sistema de gestión de una academia de idiomas construido con **PHP, MySQL, HTML, CSS y JavaScript vanilla**. La interfaz conserva el diseño original y la autenticación, los CRUD de usuarios y cursos y las consultas de los portales de estudiante y profesor se procesan contra la base de datos mediante **PDO**.

El proyecto incluye el sitio público y los cinco módulos solicitados: inicio de sesión, dashboard administrativo, gestión académica, finanzas y reportes, y portal del estudiante. Como valor agregado se añadió el **portal del profesor**.

---

## 1. Cómo ejecutar el proyecto

Requiere **XAMPP** (Apache + MySQL + PHP) y la base de datos `academia_idiomas_db`.

1. Inicia **Apache** y **MySQL** desde el panel de XAMPP.
2. Copia la carpeta del proyecto en `C:\xampp\htdocs\pagina`.
3. Importa la base de datos `academia_idiomas_db` (por ejemplo, desde phpMyAdmin).
4. Ejecuta la migración y carga los datos de prueba (opcional, recomendado para probar los portales):

   ```bash
   C:\xampp\mysql\bin\mysql.exe -u root academia_idiomas_db < database/migracion_cursos_profesor.sql
   C:\xampp\mysql\bin\mysql.exe -u root academia_idiomas_db < database/seed_demo.sql
   C:\xampp\mysql\bin\mysql.exe -u root academia_idiomas_db < database/datos_prueba.sql
   ```

   La migración agrega `cursos.id_profesor` y su llave foránea. `seed_demo.sql` y `datos_prueba.sql` crean profesores, estudiantes, cursos, matrículas y pagos de ejemplo; ambos son idempotentes. `database/consultas.sql` contiene consultas de práctica con `SELECT`, `INSERT`, `UPDATE`, `DELETE`, `WHERE`, `ORDER BY`, `JOIN`, `COUNT`, `SUM`, `GROUP BY` y `HAVING`.

5. Abre `http://localhost/pagina/index.html`.

> La conexión se configura en `app/config/database.php` (host `localhost`, usuario `root`, sin contraseña por defecto en XAMPP).

---

## 2. Páginas incluidas

| Archivo | Módulo | Descripción |
|---|---|---|
| `index.html` | Sitio público | Landing page con propuesta de valor, programas, niveles, metodología, testimonios y contacto. |
| `login.php` | Inicio de sesión | Procesa el POST con PDO, valida contra `usuarios` y redirige según `id_rol`. |
| `logout.php` | Cierre de sesión | Limpia la sesión, destruye la cookie y redirige a `login.php`. |
| `admin.php` | Dashboard administrativo | KPIs, clases, alertas, pagos y **CRUD real de usuarios** con `roles` JOIN, alta y baja con CSRF. |
| `cursos.php` | Gestión de cursos | **CRUD real de cursos**: listado con profesor asignado, alta, baja y conteo de inscritos. |
| `academico.php` | Gestión académica | Estudiantes, cursos dinámicos desde MySQL, matrículas, horarios, notas y asistencia. |
| `finanzas.php` | Finanzas y reportes | Registro de pagos, cartera pendiente, comprobantes, recibos y reportes. |
| `estudiante.php` | Portal del estudiante | Cursos obtenidos de `matriculas` para el estudiante logueado, horario, notas, pagos y perfil. |
| `profesor.php` | Portal del profesor | Cursos asignados desde `cursos.id_profesor` y estudiantes matriculados en ellos. |

---

## 3. Cuentas de prueba

El inicio de sesión consulta la tabla `usuarios` de `academia_idiomas_db`. La contraseña de las cuentas demo es **1234**. El login acepta contraseñas guardadas con `password_hash()` y, por compatibilidad con los datos iniciales, texto plano; los usuarios creados desde `admin.php` siempre se guardan con `password_hash()`.

| Correo | Contraseña | `id_rol` | Redirección |
|---|---|---|---|
| `laura.gomez@academia.edu` | `1234` | 1 | `admin.php` |
| `carlos.mendoza@academia.edu` | `1234` | 2 | `profesor.php` |
| `valentina.rios@academia.edu` | `1234` | 3 | `estudiante.php` |
| `ana.torres@academia.edu` | `1234` | 2 | `profesor.php` |
| `sophie.martin@academia.edu` | `1234` | 2 | `profesor.php` |
| `marco.rossi@academia.edu` | `1234` | 2 | `profesor.php` |
| `julian.morales@academia.edu` | `1234` | 3 | `estudiante.php` |
| `mariana.cardenas@academia.edu` | `1234` | 3 | `estudiante.php` |
| `diego.alvarez@academia.edu` | `1234` | 3 | `estudiante.php` |
| `camila.herrera@academia.edu` | `1234` | 3 | `estudiante.php` |
| `felipe.ramirez@academia.edu` | `1234` | 3 | `estudiante.php` |

Los scripts de datos incluyen además a Sara Castaño, Daniela Sánchez, Juan Pablo Ruiz, Laura Medina, Andrés Castillo, María Paula Gómez, David Restrepo, Valeria Núñez y Lucía Navarro, todas con contraseña `1234`.

En la parte inferior del formulario, los accesos rápidos autocompletan el correo y la contraseña; el envío siempre se procesa por POST en `login.php`.

---

## 4. Estructura del proyecto

```
.
├── index.html
├── login.php
├── logout.php
├── admin.php
├── cursos.php
├── academico.php
├── finanzas.php
├── estudiante.php
├── profesor.php
├── README.md
├── app/
│   ├── config/
│   │   └── database.php   # Conexión PDO a academia_idiomas_db.
│   ├── helpers.php        # Sesión, roles, CSRF, flash, escape, iniciales, banderas y formatos.
│   ├── Usuario.php        # Modelo de usuario (consulta por correo).
│   └── AuthController.php # Controlador de autenticación de apoyo.
├── database/
│   ├── migracion_cursos_profesor.sql # Agrega cursos.id_profesor y su FK.
│   ├── seed_demo.sql                 # Profesores, estudiantes, cursos y matrículas demo.
│   ├── datos_prueba.sql              # Roles, usuarios, cursos, matrículas y pagos de prueba.
│   └── consultas.sql                 # Consultas de práctica con las palabras clave de la guía.
├── assets/
│   ├── favicon.svg
│   ├── fonts/
│   │   ├── inter.css
│   │   └── inter-*.woff2
│   └── img/
│       ├── hero.jpg
│       ├── classroom.jpg
│       ├── study.jpg
│       ├── campus.jpg
│       ├── login.jpg
│       └── teacher.jpg
├── css/
│   ├── base.css        # Tokens, reset, tipografía, botones, formularios, tablas, tarjetas, badges, modales, toasts.
│   ├── layout.css      # Shell de la aplicación: sidebar, topbar, page, anchor-nav, responsive.
│   ├── pages.css       # Componentes de dashboard, académico, finanzas y portales.
│   └── landing.css     # Sitio público y layout dividido del login.
└── js/
    ├── shell.js        # Iconos SVG, navegación por rol, sidebar/topbar, dropdowns, scrollspy y toasts.
    └── main.js         # Interacciones demo: login, modales, chips, asistencia, reveal, formularios.
```

---

## 5. Decisiones de diseño

### Sistema de diseño
- **Paleta oficial respetada:** `#0284C7` (primario), `#0F172A` (tinta/sidebar), `#F8FAFC` (fondo), `#FFFFFF` (superficies), `#10B981` (éxito), `#F59E0B` (advertencia), `#EF4444` (error), `#64748B` (texto secundario) y `#334155` (texto de cuerpo).
- **Tipografía:** Inter variable, descargada localmente en `assets/fonts/`.
- **Escala tipográfica:** 32 / 24 / 16 / 14 px, con jerarquías auxiliares para tablas y metadatos.
- **Radios, sombras y espaciado:** tokens CSS reutilizables para mantener consistencia entre módulos.

### Navegación y shell
- El sidebar oscuro y la barra superior se inyectan desde `js/shell.js`, evitando duplicar HTML en cada página.
- La navegación cambia según `data-role` (`admin`, `student`, `teacher`).
- En los portales de estudiante y profesor se usa **una sola página larga con navegación por anclas** y scrollspy, lo que simplifica la maqueta y mantiene el contexto.
- Los módulos administrativos usan páginas independientes enlazadas desde el sidebar.

### Contenido y datos
- El inicio de sesión es real: consulta `usuarios` por `correo`, verifica `contrasena` y guarda `id_usuario`, `id_rol`, `usuario` y `rol` en la sesión.
- `admin.php` lista usuarios reales con JOIN a `roles`, crea usuarios con `password_hash()` y elimina usuarios con protección contra la autoeliminación y contra borrar al último administrador.
- `cursos.php` lista, crea y elimina cursos reales; permite asignar un profesor y elimina en cascada matrículas y pagos asociados dentro de una transacción.
- `estudiante.php` consulta `matriculas` + `cursos` para mostrar únicamente los cursos del estudiante logueado.
- `profesor.php` consulta `cursos.id_profesor` y las matrículas de esos cursos para mostrar sus grupos y estudiantes.
- Las tablas `matriculas` y `pagos` ya existen y se usan en las consultas y en la baja en cascada; los módulos de notas, asistencia, horarios y la interfaz de pagos todavía conservan datos de ejemplo.
- `database/consultas.sql` reúne consultas comentadas con todas las palabras clave solicitadas y un bloque de escritura seguro con `ROLLBACK`; `database/datos_prueba.sql` carga roles, usuarios, cursos, matrículas y pagos de forma idempotente.

### Seguridad y sesión
- Las vistas `admin.php`, `academico.php`, `cursos.php` y `finanzas.php` exigen `id_rol = 1`; `profesor.php` exige `id_rol = 2` y `estudiante.php` exige `id_rol = 3`.
- Si no hay sesión activa o el rol no coincide, la petición se redirige de inmediato a `login.php`.
- Los formularios de alta y los enlaces de baja incluyen un token CSRF de sesión.
- Las consultas usan sentencias preparadas de PDO; las salidas se escapan con `htmlspecialchars()` mediante el helper `e()`.
- El cierre de sesión destruye la sesión y la cookie desde `logout.php`.

### Reglas de negocio representadas visualmente
- **RN-01 · Capacidad:** un curso no admite matrículas cuando alcanza su cupo máximo.
- **RN-02 · Conflictos de horario:** el sistema advierte cuando dos clases coinciden en aula o franja horaria.
- **RN-03 · Escala de notas:** 0.0 a 5.0; se aprueba con 3.0 o más.
- **Asistencia mínima:** 80% para presentar el examen final.
- **Pagos:** los comprobantes reportados quedan en revisión hasta ser validados por finanzas.

---

## 6. Interacciones incluidas

- Login real con PDO, sesión PHP y redirección por rol (`admin`, `profesor`, `estudiante`).
- Cierre de sesión desde el menú lateral y el dropdown de usuario (`logout.php`).
- CRUD real de usuarios en `admin.php` y de cursos en `cursos.php`, con mensajes flash y confirmación de borrado.
- Protección de rutas por `id_rol` y token CSRF en formularios de alta y enlaces de baja.
- Consultas reales de cursos y estudiantes en los portales de estudiante y profesor.
- Menú lateral móvil con overlay y cierre por teclado.
- Dropdowns de notificaciones y usuario.
- Modales de registro de pago, estudiante y nota.
- Toasts de confirmación para acciones demo.
- Chips de filtro y controles segmentados de asistencia.
- Scrollspy en las páginas con navegación por anclas.
- Animaciones de aparición al hacer scroll en el sitio público.
- Formulario de contacto con confirmación visual.

---

## 7. Responsive y accesibilidad

- **Desktop:** diseño optimizado para 1440 px.
- **Mobile:** diseño verificado a 390 px; el sidebar se convierte en menú deslizante y las tablas anchas permiten desplazamiento horizontal.
- Se incluyen textos alternativos, etiquetas `aria-label`, `aria-expanded`, foco visible, enlace para saltar al contenido y estructura semántica con `header`, `nav`, `main`, `section` y `article`.
- Los iconos son SVG inline decorativos (`aria-hidden="true"`), sin librerías externas.

---

## 8. Limitaciones

- La autenticación, el CRUD de usuarios, el CRUD de cursos y las consultas de los portales de estudiante y profesor son reales.
- Notas, asistencia, horarios, pagos y reportes financieros todavía usan datos de ejemplo porque el esquema no tiene tablas para esas entidades.
- Los filtros, búsquedas y paginaciones son visuales.
- Las descargas y envíos de formularios de notas, asistencia y pagos simulan el resultado con toasts.
- Las imágenes son fotografías de Unsplash incluidas localmente con fines demostrativos.

---

## 9. Créditos

- Diseño y desarrollo: maqueta académica individual.
- Fotografías: Unsplash.
- Tipografía: Inter (SIL Open Font License).
