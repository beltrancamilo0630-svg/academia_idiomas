<?php
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/config/database.php';

require_role([1]);

$database = new Database();
$conn = $database->conectar();

$stmt = $conn->prepare('SELECT nombre FROM usuarios WHERE id_usuario = ?');
$stmt->execute([(int) $_SESSION['id_usuario']]);
$usuarioActual = $stmt->fetch() ?: ['nombre' => $_SESSION['usuario']];

$cursos = $conn->query(
    'SELECT c.id_curso, c.nombre_curso, c.nivel, c.precio, c.id_profesor,
            u.nombre AS profesor,
            COUNT(m.id_matricula) AS inscritos
     FROM cursos c
     LEFT JOIN usuarios u ON u.id_usuario = c.id_profesor
     LEFT JOIN matriculas m ON m.id_curso = c.id_curso
     GROUP BY c.id_curso, c.nombre_curso, c.nivel, c.precio, c.id_profesor, u.nombre
     ORDER BY c.nombre_curso'
)->fetchAll();

$conteoNiveles = ['A1' => 0, 'A2' => 0, 'B1' => 0, 'B2' => 0, 'C1' => 0];
foreach ($cursos as $curso) {
    $nivel = strtoupper(trim((string) $curso['nivel']));
    $conteoNiveles[$nivel] = ($conteoNiveles[$nivel] ?? 0) + 1;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Gestión académica de la Academia de Idiomas: estudiantes, cursos, matrículas, horarios, notas y asistencia.">
  <meta name="theme-color" content="#0F172A">
  <title>Gestión académica | Academia de Idiomas</title>
  <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/layout.css">
  <link rel="stylesheet" href="css/pages.css">
</head>
<body data-role="admin" data-page="academico" data-title="Gestión académica" data-subtitle="Estudiantes, cursos, matrículas, horarios, notas y asistencia" data-user-name="<?= e($usuarioActual['nombre']) ?>" data-user-role="Administrador" data-user-initials="<?= e(initials($usuarioActual['nombre'])) ?>">
  <a class="skip-link" href="#main">Saltar al contenido</a>

  <div class="app-shell">
    <aside class="sidebar" id="sidebar"></aside>

    <div class="app-main">
      <header class="topbar" id="topbar"></header>

      <main class="page" id="main">
        <div class="page-head">
          <div>
            <span class="eyebrow">Módulo 03</span>
            <h1>Gestión académica</h1>
            <p>Administra estudiantes, cursos, matrículas, horarios y el seguimiento académico en un solo lugar.</p>
          </div>
          <div class="page-actions">
            <button class="btn btn-outline" type="button" data-toast="Exportación generada (demo).">
              <span data-icon="download"></span>
              Exportar listado
            </button>
            <button class="btn btn-primary" type="button" data-modal-open="studentModal">
              <span data-icon="plus"></span>
              Nuevo estudiante
            </button>
          </div>
        </div>

        <nav class="anchor-nav" aria-label="Secciones de gestión académica">
          <a class="is-active" href="#estudiantes"><span data-icon="users"></span> Estudiantes</a>
          <a href="#cursos"><span data-icon="book"></span> Cursos</a>
          <a href="#matriculas"><span data-icon="clipboard-check"></span> Matrículas</a>
          <a href="#horarios"><span data-icon="calendar"></span> Horarios</a>
          <a href="#notas"><span data-icon="file-text"></span> Notas</a>
          <a href="#asistencia"><span data-icon="user-check"></span> Asistencia</a>
        </nav>

        <!-- ========================= ESTUDIANTES ========================= -->
        <section class="content-section" id="estudiantes">
          <div class="section-head">
            <div>
              <h2>Estudiantes</h2>
              <p>248 estudiantes activos · 12 nuevos este mes</p>
            </div>
            <div class="section-actions">
              <button class="btn btn-outline btn-sm" type="button" data-toast="Filtros avanzados (demo).">
                <span data-icon="filter"></span>
                Filtros
              </button>
              <button class="btn btn-primary btn-sm" type="button" data-modal-open="studentModal">
                <span data-icon="plus"></span>
                Nuevo estudiante
              </button>
            </div>
          </div>

          <div class="split-2-1">
            <div class="card">
              <div class="toolbar">
                <div class="toolbar-search">
                  <span data-icon="search"></span>
                  <input type="search" placeholder="Buscar por nombre, documento o correo…" aria-label="Buscar estudiante">
                </div>
                <select class="select" aria-label="Filtrar por idioma">
                  <option>Todos los idiomas</option>
                  <option>Inglés</option>
                  <option>Francés</option>
                  <option>Portugués</option>
                  <option>Italiano</option>
                </select>
                <select class="select" aria-label="Filtrar por estado">
                  <option>Todos los estados</option>
                  <option>Activo</option>
                  <option>Inactivo</option>
                </select>
              </div>

              <div class="table-wrap">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Estudiante</th>
                      <th>Documento</th>
                      <th>Curso actual</th>
                      <th>Estado</th>
                      <th>Saldo</th>
                      <th class="text-right">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>
                        <div class="cell-main">
                          <span class="avatar avatar-teal">VR</span>
                          <div><strong>Valentina Ríos</strong><span>valentina.rios@academia.edu</span></div>
                        </div>
                      </td>
                      <td class="num">1.032.456.789</td>
                      <td>Inglés B1</td>
                      <td><span class="badge badge-success">Activa</span></td>
                      <td class="num"><strong>$0</strong></td>
                      <td>
                        <div class="actions">
                          <button class="row-action" type="button" aria-label="Ver historial" data-toast="Historial académico de Valentina Ríos (demo)."><span data-icon="eye"></span></button>
                          <button class="row-action" type="button" aria-label="Editar" data-modal-open="studentModal"><span data-icon="edit"></span></button>
                          <button class="row-action" type="button" aria-label="Más opciones" data-toast="Opciones del estudiante (demo)."><span data-icon="more-horizontal"></span></button>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div class="cell-main">
                          <span class="avatar avatar-violet">AF</span>
                          <div><strong>Andrés Felipe Castro</strong><span>andres.castro@academia.edu</span></div>
                        </div>
                      </td>
                      <td class="num">1.018.765.432</td>
                      <td>Francés A2</td>
                      <td><span class="badge badge-success">Activo</span></td>
                      <td class="num"><strong class="text-danger">$300.000</strong></td>
                      <td>
                        <div class="actions">
                          <button class="row-action" type="button" aria-label="Ver historial" data-toast="Historial académico de Andrés Felipe Castro (demo)."><span data-icon="eye"></span></button>
                          <button class="row-action" type="button" aria-label="Editar" data-modal-open="studentModal"><span data-icon="edit"></span></button>
                          <button class="row-action" type="button" aria-label="Más opciones" data-toast="Opciones del estudiante (demo)."><span data-icon="more-horizontal"></span></button>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div class="cell-main">
                          <span class="avatar avatar-warning">MH</span>
                          <div><strong>María José Herrera</strong><span>maria.herrera@academia.edu</span></div>
                        </div>
                      </td>
                      <td class="num">1.045.123.876</td>
                      <td>Francés A1</td>
                      <td><span class="badge badge-success">Activa</span></td>
                      <td class="num"><strong>$0</strong></td>
                      <td>
                        <div class="actions">
                          <button class="row-action" type="button" aria-label="Ver historial" data-toast="Historial académico de María José Herrera (demo)."><span data-icon="eye"></span></button>
                          <button class="row-action" type="button" aria-label="Editar" data-modal-open="studentModal"><span data-icon="edit"></span></button>
                          <button class="row-action" type="button" aria-label="Más opciones" data-toast="Opciones del estudiante (demo)."><span data-icon="more-horizontal"></span></button>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div class="cell-main">
                          <span class="avatar avatar-success">SR</span>
                          <div><strong>Santiago Ramírez</strong><span>santiago.ramirez@academia.edu</span></div>
                        </div>
                      </td>
                      <td class="num">1.020.998.765</td>
                      <td>Inglés B2</td>
                      <td><span class="badge badge-success">Activo</span></td>
                      <td class="num"><strong>$0</strong></td>
                      <td>
                        <div class="actions">
                          <button class="row-action" type="button" aria-label="Ver historial" data-toast="Historial académico de Santiago Ramírez (demo)."><span data-icon="eye"></span></button>
                          <button class="row-action" type="button" aria-label="Editar" data-modal-open="studentModal"><span data-icon="edit"></span></button>
                          <button class="row-action" type="button" aria-label="Más opciones" data-toast="Opciones del estudiante (demo)."><span data-icon="more-horizontal"></span></button>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div class="cell-main">
                          <span class="avatar avatar-danger">LO</span>
                          <div><strong>Luisa Fernanda Ortiz</strong><span>luisa.ortiz@academia.edu</span></div>
                        </div>
                      </td>
                      <td class="num">1.055.234.981</td>
                      <td>Portugués A1</td>
                      <td><span class="badge badge-success">Activa</span></td>
                      <td class="num"><strong class="text-danger">$90.000</strong></td>
                      <td>
                        <div class="actions">
                          <button class="row-action" type="button" aria-label="Ver historial" data-toast="Historial académico de Luisa Fernanda Ortiz (demo)."><span data-icon="eye"></span></button>
                          <button class="row-action" type="button" aria-label="Editar" data-modal-open="studentModal"><span data-icon="edit"></span></button>
                          <button class="row-action" type="button" aria-label="Más opciones" data-toast="Opciones del estudiante (demo)."><span data-icon="more-horizontal"></span></button>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div class="cell-main">
                          <span class="avatar avatar-pink">DS</span>
                          <div><strong>Daniela Sánchez</strong><span>daniela.sanchez@academia.edu</span></div>
                        </div>
                      </td>
                      <td class="num">1.067.345.210</td>
                      <td>Inglés A1</td>
                      <td><span class="badge badge-success">Activa</span></td>
                      <td class="num"><strong>$0</strong></td>
                      <td>
                        <div class="actions">
                          <button class="row-action" type="button" aria-label="Ver historial" data-toast="Historial académico de Daniela Sánchez (demo)."><span data-icon="eye"></span></button>
                          <button class="row-action" type="button" aria-label="Editar" data-modal-open="studentModal"><span data-icon="edit"></span></button>
                          <button class="row-action" type="button" aria-label="Más opciones" data-toast="Opciones del estudiante (demo)."><span data-icon="more-horizontal"></span></button>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div class="cell-main">
                          <span class="avatar avatar-dark">JM</span>
                          <div><strong>Juan Pablo Mejía</strong><span>juan.mejia@academia.edu</span></div>
                        </div>
                      </td>
                      <td class="num">1.014.567.890</td>
                      <td>Francés A2</td>
                      <td><span class="badge badge-neutral">Inactivo</span></td>
                      <td class="num"><strong>$0</strong></td>
                      <td>
                        <div class="actions">
                          <button class="row-action" type="button" aria-label="Ver historial" data-toast="Historial académico de Juan Pablo Mejía (demo)."><span data-icon="eye"></span></button>
                          <button class="row-action" type="button" aria-label="Editar" data-modal-open="studentModal"><span data-icon="edit"></span></button>
                          <button class="row-action" type="button" aria-label="Más opciones" data-toast="Opciones del estudiante (demo)."><span data-icon="more-horizontal"></span></button>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div class="cell-main">
                          <span class="avatar avatar-success">CR</span>
                          <div><strong>Camila Restrepo</strong><span>camila.restrepo@academia.edu</span></div>
                        </div>
                      </td>
                      <td class="num">1.079.123.456</td>
                      <td>Inglés C1</td>
                      <td><span class="badge badge-success">Activa</span></td>
                      <td class="num"><strong>$0</strong></td>
                      <td>
                        <div class="actions">
                          <button class="row-action" type="button" aria-label="Ver historial" data-toast="Historial académico de Camila Restrepo (demo)."><span data-icon="eye"></span></button>
                          <button class="row-action" type="button" aria-label="Editar" data-modal-open="studentModal"><span data-icon="edit"></span></button>
                          <button class="row-action" type="button" aria-label="Más opciones" data-toast="Opciones del estudiante (demo)."><span data-icon="more-horizontal"></span></button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="table-footer">
                <span>Mostrando 1–8 de 248 estudiantes</span>
                <div class="pagination" aria-label="Paginación">
                  <a href="#" aria-label="Página anterior"><span data-icon="chevron-left"></span></a>
                  <span class="is-active">1</span>
                  <a href="#">2</a>
                  <a href="#">3</a>
                  <a href="#">…</a>
                  <a href="#">31</a>
                  <a href="#" aria-label="Página siguiente"><span data-icon="chevron-right"></span></a>
                </div>
              </div>
            </div>

            <!-- Ficha del estudiante -->
            <aside class="card student-panel">
              <div class="student-panel-cover"></div>
              <div class="student-panel-body">
                <div class="student-panel-head">
                  <span class="avatar avatar-teal">VR</span>
                  <span class="badge badge-success">Activa</span>
                </div>
                <h3>Valentina Ríos</h3>
                <div class="student-email">valentina.rios@academia.edu</div>

                <div class="info-list">
                  <div class="info-row"><span>Documento</span><strong>1.032.456.789</strong></div>
                  <div class="info-row"><span>Teléfono</span><strong>300 456 7890</strong></div>
                  <div class="info-row"><span>Sede</span><strong>Sede Norte</strong></div>
                  <div class="info-row"><span>Curso actual</span><strong>Inglés B1 · B1-03</strong></div>
                  <div class="info-row"><span>Profesor</span><strong>Carlos Mendoza</strong></div>
                  <div class="info-row"><span>Inscripción</span><strong>12 ago 2026</strong></div>
                </div>

                <div class="mini-stats">
                  <div class="mini-stat"><strong>4.3</strong><span>Promedio</span></div>
                  <div class="mini-stat"><strong>94%</strong><span>Asistencia</span></div>
                  <div class="mini-stat"><strong>B1</strong><span>Nivel</span></div>
                </div>

                <div class="mt-16">
                  <div class="progress-row"><strong>Progreso del nivel</strong><span>68%</span></div>
                  <div class="progress"><span style="width:68%"></span></div>
                </div>

                <div class="student-actions">
                  <button class="btn btn-outline btn-sm" type="button" data-toast="Historial académico completo (demo).">
                    <span data-icon="file-text"></span>
                    Historial
                  </button>
                  <button class="btn btn-primary btn-sm" type="button" data-modal-open="studentModal">
                    <span data-icon="edit"></span>
                    Editar
                  </button>
                </div>
              </div>
            </aside>
          </div>
        </section>

        <!-- ========================= CURSOS ========================= -->
        <section class="content-section" id="cursos">
          <div class="section-head">
            <div>
              <h2>Cursos y niveles</h2>
              <p><?= count($cursos) ?> cursos registrados · asignación de profesores y precios</p>
            </div>
            <div class="section-actions">
              <a class="btn btn-outline btn-sm" href="#horarios"><span data-icon="calendar"></span> Ver horarios</a>
              <a class="btn btn-primary btn-sm" href="cursos.php">
                <span data-icon="plus"></span>
                Gestionar cursos
              </a>
            </div>
          </div>

          <div class="level-strip" aria-label="Niveles disponibles">
            <?php $primerNivel = true; ?>
            <?php foreach ($conteoNiveles as $nivel => $cantidad): ?>
              <?php if (!$primerNivel): ?>
                <span class="level-arrow"><span data-icon="chevron-right"></span></span>
              <?php endif; ?>
              <span class="level-pill <?= $cantidad > 0 ? 'is-active' : '' ?>">
                <?= e($nivel) ?> <small><?= (int) $cantidad ?> curso<?= $cantidad === 1 ? '' : 's' ?></small>
              </span>
              <?php $primerNivel = false; ?>
            <?php endforeach; ?>
          </div>

          <div class="course-grid">
            <?php if (empty($cursos)): ?>
              <article class="card">
                <div class="card-body">
                  <p>No hay cursos registrados todavía. <a href="cursos.php">Crea el primero</a> para empezar a matricular estudiantes.</p>
                </div>
              </article>
            <?php else: ?>
              <?php foreach ($cursos as $curso): ?>
                <article class="course-card">
                  <div class="course-top">
                    <div class="course-title">
                      <span class="flag flag-<?= e(flag_class($curso['nombre_curso'])) ?>"></span>
                      <div>
                        <h4><?= e($curso['nombre_curso']) ?></h4>
                        <span>Nivel <?= e($curso['nivel']) ?> · <?= (int) $curso['inscritos'] ?> inscritos</span>
                      </div>
                    </div>
                    <span class="badge badge-success">Activo</span>
                  </div>
                  <div class="course-meta">
                    <div><span data-icon="user"></span> <?= e($curso['profesor'] ?: 'Sin profesor asignado') ?></div>
                    <div><span data-icon="book"></span> Nivel <?= e($curso['nivel']) ?></div>
                    <div><span data-icon="users"></span> <?= (int) $curso['inscritos'] ?> estudiantes inscritos</div>
                  </div>
                  <div class="course-foot">
                    <div class="course-price"><?= e(format_cop($curso['precio'])) ?> <span>/ mes</span></div>
                    <a class="link" href="#matriculas">Ver grupo <span data-icon="arrow-right"></span></a>
                  </div>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </section>

        <!-- ========================= MATRÍCULAS ========================= -->
        <section class="content-section" id="matriculas">
          <div class="section-head">
            <div>
              <h2>Matrículas</h2>
              <p>24 matrículas registradas este mes · 3 pendientes de pago</p>
            </div>
            <div class="section-actions">
              <button class="btn btn-outline btn-sm" type="button" data-toast="Reporte de matrículas exportado (demo).">
                <span data-icon="download"></span>
                Exportar
              </button>
            </div>
          </div>

          <div class="notice notice-success mb-16">
            <span data-icon="shield"></span>
            <div><strong>Validación de cupos activa.</strong> El sistema verifica la disponibilidad del curso antes de confirmar cada matrícula (RN-01).</div>
          </div>

          <div class="enroll-layout">
            <div class="card">
              <div class="card-header">
                <div>
                  <h3>Matrículas recientes</h3>
                  <p>Periodo 2026-2 · todas las sedes</p>
                </div>
                <span class="badge badge-primary">24 este mes</span>
              </div>

              <div class="table-wrap">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Estudiante</th>
                      <th>Curso</th>
                      <th>Fecha</th>
                      <th>Valor</th>
                      <th>Cupo</th>
                      <th>Estado</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>
                        <div class="cell-main">
                          <span class="avatar avatar-teal avatar-sm">VR</span>
                          <div><strong>Valentina Ríos</strong><span>Inglés B1</span></div>
                        </div>
                      </td>
                      <td>Inglés B1 · B1-03</td>
                      <td>05 nov 2026</td>
                      <td class="num"><strong>$320.000</strong></td>
                      <td><span class="badge badge-neutral no-dot">14/18</span></td>
                      <td><span class="badge badge-success">Confirmada</span></td>
                    </tr>
                    <tr>
                      <td>
                        <div class="cell-main">
                          <span class="avatar avatar-warning avatar-sm">MH</span>
                          <div><strong>María José Herrera</strong><span>Francés A1</span></div>
                        </div>
                      </td>
                      <td>Francés A1 · A1-02</td>
                      <td>04 nov 2026</td>
                      <td class="num"><strong>$300.000</strong></td>
                      <td><span class="badge badge-neutral no-dot">11/16</span></td>
                      <td><span class="badge badge-success">Confirmada</span></td>
                    </tr>
                    <tr>
                      <td>
                        <div class="cell-main">
                          <span class="avatar avatar-danger avatar-sm">LO</span>
                          <div><strong>Luisa F. Ortiz</strong><span>Portugués A1</span></div>
                        </div>
                      </td>
                      <td>Portugués A1 · A1-01</td>
                      <td>03 nov 2026</td>
                      <td class="num"><strong>$280.000</strong></td>
                      <td><span class="badge badge-neutral no-dot">12/16</span></td>
                      <td><span class="badge badge-warning">Pendiente pago</span></td>
                    </tr>
                    <tr>
                      <td>
                        <div class="cell-main">
                          <span class="avatar avatar-success avatar-sm">SV</span>
                          <div><strong>Sebastián Vargas</strong><span>Italiano A1</span></div>
                        </div>
                      </td>
                      <td>Italiano A1 · A1-01</td>
                      <td>02 nov 2026</td>
                      <td class="num"><strong>$280.000</strong></td>
                      <td><span class="badge badge-neutral no-dot">7/14</span></td>
                      <td><span class="badge badge-danger">Rechazado</span></td>
                    </tr>
                    <tr>
                      <td>
                        <div class="cell-main">
                          <span class="avatar avatar-pink avatar-sm">DS</span>
                          <div><strong>Daniela Sánchez</strong><span>Inglés A1</span></div>
                        </div>
                      </td>
                      <td>Inglés A1 · A1-05</td>
                      <td>01 nov 2026</td>
                      <td class="num"><strong>$320.000</strong></td>
                      <td><span class="badge badge-neutral no-dot">16/18</span></td>
                      <td><span class="badge badge-success">Confirmada</span></td>
                    </tr>
                    <tr>
                      <td>
                        <div class="cell-main">
                          <span class="avatar avatar-success avatar-sm">CR</span>
                          <div><strong>Camila Restrepo</strong><span>Inglés C1</span></div>
                        </div>
                      </td>
                      <td>Inglés C1 · C1-01</td>
                      <td>31 oct 2026</td>
                      <td class="num"><strong>$340.000</strong></td>
                      <td><span class="badge badge-neutral no-dot">8/12</span></td>
                      <td><span class="badge badge-warning">Pendiente pago</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <aside class="card card-pad">
              <div class="panel-title"><span data-icon="clipboard-check"></span> Nueva matrícula</div>
              <p class="text-sm text-muted mt-4">Asigna un cupo validando la disponibilidad del curso.</p>

              <div class="mt-16" style="display:flex;flex-direction:column;gap:12px">
                <div class="field">
                  <label class="label" for="enrollStudent">Estudiante</label>
                  <select class="select" id="enrollStudent">
                    <option>Valentina Ríos · 1.032.456.789</option>
                    <option>Andrés Felipe Castro · 1.018.765.432</option>
                    <option>María José Herrera · 1.045.123.876</option>
                  </select>
                </div>
                <div class="field">
                  <label class="label" for="enrollCourse">Curso</label>
                  <select class="select" id="enrollCourse">
                    <option>Inglés B1 · B1-03</option>
                    <option>Francés A1 · A1-02</option>
                    <option>Portugués A1 · A1-01</option>
                    <option>Italiano A1 · A1-01</option>
                  </select>
                </div>
                <div class="field">
                  <label class="label" for="enrollPeriod">Periodo</label>
                  <select class="select" id="enrollPeriod">
                    <option>2026-2 · Noviembre</option>
                    <option>2026-2 · Diciembre</option>
                  </select>
                </div>
              </div>

              <div class="capacity-callout mt-16">
                <span class="capacity-ring"></span>
                <div>
                  <strong>Cupo disponible: 4 de 18</strong>
                  <span>El curso puede recibir 4 estudiantes más.</span>
                </div>
              </div>

              <div class="enroll-summary">
                <div class="info-row"><span>Matrícula</span><strong>$120.000</strong></div>
                <div class="info-row"><span>Mensualidad</span><strong>$320.000</strong></div>
                <div class="total"><span>Total primer pago</span><strong>$440.000</strong></div>
              </div>

              <button class="btn btn-primary btn-block" type="button" data-toast="Matrícula registrada y cupo actualizado (demo).">
                <span data-icon="check"></span>
                Confirmar matrícula
              </button>
              <p class="hint mt-8">La matrícula se bloquea si el curso alcanza el cupo máximo.</p>
            </aside>
          </div>
        </section>

        <!-- ========================= HORARIOS ========================= -->
        <section class="content-section" id="horarios">
          <div class="section-head">
            <div>
              <h2>Horarios</h2>
              <p>Semana del 3 al 8 de noviembre · 18 clases programadas</p>
            </div>
            <div class="section-actions">
              <select class="select btn-sm" aria-label="Seleccionar semana" style="width:auto;height:36px;font-size:13px">
                <option>Semana del 3 al 8 de noviembre</option>
                <option>Semana del 10 al 15 de noviembre</option>
              </select>
              <button class="btn btn-primary btn-sm" type="button" data-toast="Formulario para programar clase (demo).">
                <span data-icon="plus"></span>
                Programar clase
              </button>
            </div>
          </div>

          <div class="notice notice-success mb-16">
            <span data-icon="check-circle"></span>
            <div><strong>Sin choques de horario.</strong> El sistema valida que el profesor y el salón no estén asignados en la misma franja (RN-02).</div>
          </div>

          <div class="schedule-wrap">
            <table class="schedule">
              <thead>
                <tr>
                  <th>Hora</th>
                  <th>Lunes 3</th>
                  <th>Martes 4</th>
                  <th>Miércoles 5</th>
                  <th>Jueves 6</th>
                  <th>Viernes 7</th>
                  <th>Sábado 8</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="time-cell">6:00 – 8:00 p. m.<span>Franja 1</span></td>
                  <td><div class="sched-block"><strong>Inglés A1</strong><span>Carlos Mendoza</span><small>Salón 204 · 16/18</small></div></td>
                  <td><div class="sched-block"><strong>Inglés B1</strong><span>Carlos Mendoza</span><small>Salón 204 · 14/18</small></div></td>
                  <td><div class="sched-block"><strong>Inglés A1</strong><span>Carlos Mendoza</span><small>Salón 204 · 16/18</small></div></td>
                  <td><div class="sched-block"><strong>Inglés B1</strong><span>Carlos Mendoza</span><small>Salón 204 · 14/18</small></div></td>
                  <td><div class="sched-block warning"><strong>Italiano A1</strong><span>Ana Torres</span><small>Salón 302 · 7/14</small></div></td>
                  <td><div class="sched-block success"><strong>Portugués A1</strong><span>Ana Torres</span><small>Salón 302 · 12/16</small></div></td>
                </tr>
                <tr>
                  <td class="time-cell">8:00 – 10:00 p. m.<span>Franja 2</span></td>
                  <td><div class="sched-block violet"><strong>Francés A1</strong><span>Sophie Martin</span><small>Salón 108 · 11/16</small></div></td>
                  <td><div class="sched-block violet"><strong>Francés A2</strong><span>Sophie Martin</span><small>Salón 108 · 9/16</small></div></td>
                  <td><div class="sched-block violet"><strong>Francés A1</strong><span>Sophie Martin</span><small>Salón 108 · 11/16</small></div></td>
                  <td><div class="sched-block violet"><strong>Francés A2</strong><span>Sophie Martin</span><small>Salón 108 · 9/16</small></div></td>
                  <td><div class="sched-block"><strong>Inglés A1</strong><span>Carlos Mendoza</span><small>Salón 204 · 16/18</small></div></td>
                  <td><div class="sched-block dark"><strong>Inglés B2</strong><span>Carlos Mendoza</span><small>Salón 204 · 18/18</small></div></td>
                </tr>
                <tr>
                  <td class="time-cell">8:00 a. m. – 12:00 m.<span>Franja sábado</span></td>
                  <td class="text-muted">—</td>
                  <td class="text-muted">—</td>
                  <td class="text-muted">—</td>
                  <td class="text-muted">—</td>
                  <td class="text-muted">—</td>
                  <td><div class="sched-block dark"><strong>Inglés B2 · Portugués A1</strong><span>Carlos Mendoza · Ana Torres</span><small>Salones 204 y 302</small></div></td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="schedule-legend">
            <span class="legend-item"><span class="legend-dot" style="background:#0284C7"></span> Inglés</span>
            <span class="legend-item"><span class="legend-dot" style="background:#7C3AED"></span> Francés</span>
            <span class="legend-item"><span class="legend-dot" style="background:#10B981"></span> Portugués</span>
            <span class="legend-item"><span class="legend-dot" style="background:#F59E0B"></span> Italiano</span>
            <span class="badge badge-success">0 conflictos</span>
          </div>
        </section>

        <!-- ========================= NOTAS ========================= -->
        <section class="content-section" id="notas">
          <div class="section-head">
            <div>
              <h2>Notas y calificaciones</h2>
              <p>Periodo 2026-2 · Inglés B1 · Grupo B1-03</p>
            </div>
            <div class="section-actions">
              <select class="select" aria-label="Seleccionar curso" style="width:auto;height:36px;font-size:13px">
                <option>Inglés B1 · B1-03</option>
                <option>Inglés A1 · A1-05</option>
                <option>Francés A1 · A1-02</option>
              </select>
              <button class="btn btn-primary btn-sm" type="button" data-toast="Formulario de notas abierto (demo).">
                <span data-icon="edit"></span>
                Registrar notas
              </button>
            </div>
          </div>

          <div class="grade-summary">
            <div class="grade-stat"><span>Promedio del curso</span><strong>4.1</strong></div>
            <div class="grade-stat"><span>Aprobados</span><strong>13 / 16</strong></div>
            <div class="grade-stat"><span>En riesgo</span><strong class="text-danger">3</strong></div>
          </div>

          <div class="card">
            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Estudiante</th>
                    <th>Parcial 1</th>
                    <th>Parcial 2</th>
                    <th>Parcial 3</th>
                    <th>Promedio</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-teal avatar-sm">VR</span>
                        <div><strong>Valentina Ríos</strong><span>B1-03</span></div>
                      </div>
                    </td>
                    <td><span class="grade good">4.2</span></td>
                    <td><span class="grade good">4.5</span></td>
                    <td><span class="grade good">4.1</span></td>
                    <td><span class="grade final">4.3</span></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                  </tr>
                  <tr>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-violet avatar-sm">AF</span>
                        <div><strong>Andrés F. Castro</strong><span>B1-03</span></div>
                      </div>
                    </td>
                    <td><span class="grade good">3.8</span></td>
                    <td><span class="grade good">4.0</span></td>
                    <td><span class="grade good">3.9</span></td>
                    <td><span class="grade final">3.9</span></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                  </tr>
                  <tr>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-warning avatar-sm">MH</span>
                        <div><strong>María José Herrera</strong><span>B1-03</span></div>
                      </div>
                    </td>
                    <td><span class="grade good">4.6</span></td>
                    <td><span class="grade good">4.4</span></td>
                    <td><span class="grade good">4.7</span></td>
                    <td><span class="grade final">4.6</span></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                  </tr>
                  <tr>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-success avatar-sm">SR</span>
                        <div><strong>Santiago Ramírez</strong><span>B1-03</span></div>
                      </div>
                    </td>
                    <td><span class="grade bad">2.8</span></td>
                    <td><span class="grade bad">2.9</span></td>
                    <td><span class="grade bad">3.1</span></td>
                    <td><span class="grade bad">2.9</span></td>
                    <td><span class="badge badge-danger">En riesgo</span></td>
                  </tr>
                  <tr>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-danger avatar-sm">LO</span>
                        <div><strong>Luisa F. Ortiz</strong><span>B1-03</span></div>
                      </div>
                    </td>
                    <td><span class="grade good">3.5</span></td>
                    <td><span class="grade good">3.7</span></td>
                    <td><span class="grade good">3.6</span></td>
                    <td><span class="grade final">3.6</span></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                  </tr>
                  <tr>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-pink avatar-sm">DS</span>
                        <div><strong>Daniela Sánchez</strong><span>B1-03</span></div>
                      </div>
                    </td>
                    <td><span class="grade bad">2.5</span></td>
                    <td><span class="grade bad">2.9</span></td>
                    <td><span class="grade bad">2.7</span></td>
                    <td><span class="grade bad">2.7</span></td>
                    <td><span class="badge badge-danger">En riesgo</span></td>
                  </tr>
                  <tr>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-dark avatar-sm">JM</span>
                        <div><strong>Juan Pablo Mejía</strong><span>B1-03</span></div>
                      </div>
                    </td>
                    <td><span class="grade good">3.2</span></td>
                    <td><span class="grade good">3.0</span></td>
                    <td><span class="grade good">3.4</span></td>
                    <td><span class="grade final">3.2</span></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                  </tr>
                  <tr>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-success avatar-sm">CR</span>
                        <div><strong>Camila Restrepo</strong><span>B1-03</span></div>
                      </div>
                    </td>
                    <td><span class="grade good">4.8</span></td>
                    <td><span class="grade good">4.9</span></td>
                    <td><span class="grade good">4.7</span></td>
                    <td><span class="grade final">4.8</span></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="table-footer">
              <span>Escala 0.0–5.0 · Nota mínima aprobatoria: 3.0 (RN-03)</span>
              <span class="badge badge-primary">3 estudiantes en riesgo</span>
            </div>
          </div>
        </section>

        <!-- ========================= ASISTENCIA ========================= -->
        <section class="content-section" id="asistencia">
          <div class="section-head">
            <div>
              <h2>Control de asistencia</h2>
              <p>Miércoles, 5 de noviembre · Inglés B1 · Grupo B1-03</p>
            </div>
            <div class="section-actions">
              <input class="input" type="date" value="2026-11-05" aria-label="Fecha de asistencia" style="width:auto;height:36px;font-size:13px">
              <button class="btn btn-primary btn-sm" type="button" data-toast="Asistencia guardada y vinculada a la fecha (demo).">
                <span data-icon="check"></span>
                Guardar asistencia
              </button>
            </div>
          </div>

          <div class="card">
            <div class="attendance-summary">
              <span class="summary-item"><span class="dot" style="background:#10B981"></span> <strong>14</strong> asistieron</span>
              <span class="summary-item"><span class="dot" style="background:#EF4444"></span> <strong>2</strong> fallas</span>
              <span class="summary-item"><span class="dot" style="background:#F59E0B"></span> <strong>1</strong> excusa</span>
              <span class="summary-item text-muted">17 de 18 registrados</span>
              <span class="badge badge-success" style="margin-left:auto">En curso</span>
            </div>

            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Estudiante</th>
                    <th>Estado</th>
                    <th>Observación</th>
                    <th>Registrado por</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-teal avatar-sm">VR</span>
                        <div><strong>Valentina Ríos</strong><span>B1-03</span></div>
                      </div>
                    </td>
                    <td>
                      <div class="seg">
                        <button class="is-on good" type="button" data-tone="good">Asistió</button>
                        <button type="button" data-tone="bad">Falla</button>
                        <button type="button" data-tone="warn">Excusa</button>
                      </div>
                    </td>
                    <td class="text-muted">—</td>
                    <td class="text-muted">Carlos Mendoza</td>
                  </tr>
                  <tr>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-violet avatar-sm">AF</span>
                        <div><strong>Andrés F. Castro</strong><span>B1-03</span></div>
                      </div>
                    </td>
                    <td>
                      <div class="seg">
                        <button class="is-on good" type="button" data-tone="good">Asistió</button>
                        <button type="button" data-tone="bad">Falla</button>
                        <button type="button" data-tone="warn">Excusa</button>
                      </div>
                    </td>
                    <td class="text-muted">—</td>
                    <td class="text-muted">Carlos Mendoza</td>
                  </tr>
                  <tr>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-warning avatar-sm">MH</span>
                        <div><strong>María José Herrera</strong><span>B1-03</span></div>
                      </div>
                    </td>
                    <td>
                      <div class="seg">
                        <button type="button" data-tone="good">Asistió</button>
                        <button class="is-on bad" type="button" data-tone="bad">Falla</button>
                        <button type="button" data-tone="warn">Excusa</button>
                      </div>
                    </td>
                    <td class="text-muted">No presentó excusa.</td>
                    <td class="text-muted">Carlos Mendoza</td>
                  </tr>
                  <tr>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-success avatar-sm">SR</span>
                        <div><strong>Santiago Ramírez</strong><span>B1-03</span></div>
                      </div>
                    </td>
                    <td>
                      <div class="seg">
                        <button class="is-on good" type="button" data-tone="good">Asistió</button>
                        <button type="button" data-tone="bad">Falla</button>
                        <button type="button" data-tone="warn">Excusa</button>
                      </div>
                    </td>
                    <td class="text-muted">—</td>
                    <td class="text-muted">Carlos Mendoza</td>
                  </tr>
                  <tr>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-danger avatar-sm">LO</span>
                        <div><strong>Luisa F. Ortiz</strong><span>B1-03</span></div>
                      </div>
                    </td>
                    <td>
                      <div class="seg">
                        <button type="button" data-tone="good">Asistió</button>
                        <button type="button" data-tone="bad">Falla</button>
                        <button class="is-on warn" type="button" data-tone="warn">Excusa</button>
                      </div>
                    </td>
                    <td class="text-muted">Incapacidad médica.</td>
                    <td class="text-muted">Carlos Mendoza</td>
                  </tr>
                  <tr>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-pink avatar-sm">DS</span>
                        <div><strong>Daniela Sánchez</strong><span>B1-03</span></div>
                      </div>
                    </td>
                    <td>
                      <div class="seg">
                        <button class="is-on good" type="button" data-tone="good">Asistió</button>
                        <button type="button" data-tone="bad">Falla</button>
                        <button type="button" data-tone="warn">Excusa</button>
                      </div>
                    </td>
                    <td class="text-muted">—</td>
                    <td class="text-muted">Carlos Mendoza</td>
                  </tr>
                  <tr>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-dark avatar-sm">JM</span>
                        <div><strong>Juan Pablo Mejía</strong><span>B1-03</span></div>
                      </div>
                    </td>
                    <td>
                      <div class="seg">
                        <button type="button" data-tone="good">Asistió</button>
                        <button class="is-on bad" type="button" data-tone="bad">Falla</button>
                        <button type="button" data-tone="warn">Excusa</button>
                      </div>
                    </td>
                    <td class="text-muted">—</td>
                    <td class="text-muted">Carlos Mendoza</td>
                  </tr>
                  <tr>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-success avatar-sm">CR</span>
                        <div><strong>Camila Restrepo</strong><span>B1-03</span></div>
                      </div>
                    </td>
                    <td>
                      <div class="seg">
                        <button class="is-on good" type="button" data-tone="good">Asistió</button>
                        <button type="button" data-tone="bad">Falla</button>
                        <button type="button" data-tone="warn">Excusa</button>
                      </div>
                    </td>
                    <td class="text-muted">—</td>
                    <td class="text-muted">Carlos Mendoza</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="table-footer">
              <span>La asistencia queda vinculada a la fecha y al grupo seleccionado (RF-07).</span>
              <button class="btn btn-primary btn-sm" type="button" data-toast="Asistencia guardada correctamente (demo).">
                <span data-icon="check"></span>
                Guardar cambios
              </button>
            </div>
          </div>
        </section>

        <!-- Reglas de negocio -->
        <section class="content-section" aria-labelledby="rulesTitle">
          <div class="section-head">
            <div>
              <h2 id="rulesTitle">Reglas de negocio aplicadas</h2>
              <p>Validaciones que el sistema ejecuta antes de confirmar cada operación.</p>
            </div>
          </div>

          <div class="rules-grid">
            <article class="rule-card">
              <span class="icon-circle"><span data-icon="users"></span></span>
              <div>
                <code>RN-01 · CUPOS</code>
                <strong>Control de cupo máximo</strong>
                <p>Ningún estudiante puede matricularse si el curso alcanzó el tope de alumnos definido por la administración.</p>
              </div>
            </article>
            <article class="rule-card">
              <span class="icon-circle violet"><span data-icon="calendar"></span></span>
              <div>
                <code>RN-02 · HORARIOS</code>
                <strong>Bloqueo de choques</strong>
                <p>El sistema impide programar a un profesor o un salón en dos clases distintas durante la misma franja horaria.</p>
              </div>
            </article>
            <article class="rule-card">
              <span class="icon-circle success"><span data-icon="award"></span></span>
              <div>
                <code>RN-03 · CALIFICACIONES</code>
                <strong>Escala y aprobación</strong>
                <p>Las notas van de 0.0 a 5.0 y el nivel se aprueba con una nota final igual o superior a 3.0.</p>
              </div>
            </article>
          </div>
        </section>
      </main>
    </div>
  </div>

  <!-- Nuevo / editar estudiante (demo) -->
  <div class="modal-backdrop" id="studentModal" data-success="Estudiante guardado correctamente (demo).">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="studentTitle">
      <div class="modal-head">
        <div>
          <h3 id="studentTitle">Nuevo estudiante</h3>
          <p>Registra los datos básicos del alumno. Los campos con * son obligatorios.</p>
        </div>
        <button class="modal-close" type="button" data-modal-close aria-label="Cerrar"><span data-icon="x"></span></button>
      </div>

      <form>
        <div class="modal-body">
          <div class="form-grid">
            <div class="field col-span-2">
              <label class="label" for="stName">Nombre completo *</label>
              <input class="input" id="stName" type="text" placeholder="Ej. Laura Martínez" required>
            </div>
            <div class="field">
              <label class="label" for="stDoc">Documento *</label>
              <input class="input" id="stDoc" type="text" placeholder="1.000.000.000" required>
            </div>
            <div class="field">
              <label class="label" for="stPhone">Teléfono</label>
              <input class="input" id="stPhone" type="tel" placeholder="300 000 0000">
            </div>
            <div class="field col-span-2">
              <label class="label" for="stEmail">Correo electrónico *</label>
              <input class="input" id="stEmail" type="email" placeholder="correo@ejemplo.com" required>
            </div>
            <div class="field">
              <label class="label" for="stLang">Idioma</label>
              <select class="select" id="stLang">
                <option>Inglés</option>
                <option>Francés</option>
                <option>Portugués</option>
                <option>Italiano</option>
              </select>
            </div>
            <div class="field">
              <label class="label" for="stLevel">Nivel</label>
              <select class="select" id="stLevel">
                <option>A1</option>
                <option>A2</option>
                <option>B1</option>
                <option>B2</option>
                <option>C1</option>
              </select>
            </div>
            <div class="field">
              <label class="label" for="stSede">Sede</label>
              <select class="select" id="stSede">
                <option>Sede Norte</option>
                <option>Sede Centro</option>
                <option>Virtual</option>
              </select>
            </div>
            <div class="field">
              <label class="label" for="stState">Estado</label>
              <select class="select" id="stState">
                <option>Activo</option>
                <option>Inactivo</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-foot">
          <button class="btn btn-ghost" type="button" data-modal-close>Cancelar</button>
          <button class="btn btn-primary" type="submit"><span data-icon="check"></span> Guardar estudiante</button>
        </div>
      </form>
    </div>
  </div>

  <script src="js/shell.js?v=3"></script>
  <script src="js/main.js?v=3"></script>
</body>
</html>
