<?php
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/config/database.php';

require_role([2]);

$database = new Database();
$conn = $database->conectar();

$stmt = $conn->prepare('SELECT id_usuario, nombre, correo FROM usuarios WHERE id_usuario = ?');
$stmt->execute([(int) $_SESSION['id_usuario']]);
$usuarioActual = $stmt->fetch() ?: [
    'id_usuario' => (int) $_SESSION['id_usuario'],
    'nombre' => $_SESSION['usuario'],
    'correo' => '',
];

$stmt = $conn->prepare(
    'SELECT c.id_curso, c.nombre_curso, c.nivel, c.precio,
            COUNT(m.id_matricula) AS inscritos
     FROM cursos c
     LEFT JOIN matriculas m ON m.id_curso = c.id_curso
     WHERE c.id_profesor = ?
     GROUP BY c.id_curso, c.nombre_curso, c.nivel, c.precio
     ORDER BY c.nombre_curso'
);
$stmt->execute([(int) $_SESSION['id_usuario']]);
$cursosProfesor = $stmt->fetchAll();

$stmt = $conn->prepare(
    'SELECT u.id_usuario, u.nombre, u.correo,
            c.id_curso, c.nombre_curso, c.nivel, m.fecha_matricula
     FROM matriculas m
     INNER JOIN cursos c ON c.id_curso = m.id_curso
     INNER JOIN usuarios u ON u.id_usuario = m.id_usuario
     WHERE c.id_profesor = ?
     ORDER BY c.nombre_curso, u.nombre'
);
$stmt->execute([(int) $_SESSION['id_usuario']]);
$estudiantesProfesor = $stmt->fetchAll();

$totalCursos = count($cursosProfesor);
$totalEstudiantes = count(array_unique(array_column($estudiantesProfesor, 'id_usuario')));
$totalMatriculas = count($estudiantesProfesor);
$cursoPrincipal = $cursosProfesor[0] ?? null;
$avatares = ['avatar-teal', 'avatar-violet', 'avatar-warning', 'avatar-success', 'avatar-danger', 'avatar-pink'];
$partesNombre = preg_split('/\s+/', trim($usuarioActual['nombre'])) ?: [];
$nombrePila = $partesNombre[0] ?? $usuarioActual['nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Portal del profesor de la Academia de Idiomas: grupos, asistencia y registro de notas.">
  <meta name="theme-color" content="#0F172A">
  <title>Portal del profesor | Academia de Idiomas</title>
  <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/layout.css">
  <link rel="stylesheet" href="css/pages.css">
</head>
<body data-role="teacher" data-page="inicio" data-title="Portal del profesor" data-subtitle="Miércoles, 5 de noviembre de 2026" data-user-name="<?= e($usuarioActual['nombre']) ?>" data-user-role="Profesor" data-user-initials="<?= e(initials($usuarioActual['nombre'])) ?>">
  <a class="skip-link" href="#main">Saltar al contenido</a>

  <div class="app-shell">
    <aside class="sidebar" id="sidebar"></aside>

    <div class="app-main">
      <header class="topbar" id="topbar"></header>

      <main class="page" id="main">
        <div class="page-head">
          <div>
            <span class="eyebrow">Miércoles, 5 de noviembre</span>
            <h1>Hola, <?= e($_SESSION['usuario']) ?></h1>
            <p>Gestiona tus grupos, registra la asistencia y publica las notas de tus estudiantes.</p>
          </div>
          <div class="page-actions">
            <a class="btn btn-outline" href="#asistencia">
              <span data-icon="user-check"></span>
              Pasar lista
            </a>
            <button class="btn btn-primary" type="button" data-modal-open="gradeModal">
              <span data-icon="plus"></span>
              Registrar nota
            </button>
          </div>
        </div>

        <nav class="anchor-nav" aria-label="Secciones del portal del profesor">
          <a class="is-active" href="#inicio"><span data-icon="home"></span> Inicio</a>
          <a href="#grupos"><span data-icon="users"></span> Mis grupos</a>
          <a href="#asistencia"><span data-icon="user-check"></span> Asistencia</a>
          <a href="#notas"><span data-icon="file-text"></span> Notas</a>
        </nav>

        <!-- ========================= INICIO ========================= -->
        <section class="content-section" id="inicio">
          <div class="welcome-banner">
            <div class="welcome-copy">
              <span class="eyebrow no-line">Resumen docente</span>
              <h2>Tus grupos van muy bien, <?= e($_SESSION['usuario']) ?></h2>
              <p><?= $totalCursos > 0 ? 'Tienes ' . $totalCursos . ' grupo' . ($totalCursos === 1 ? '' : 's') . ' activo' . ($totalCursos === 1 ? '' : 's') . ' y ' . $totalEstudiantes . ' estudiante' . ($totalEstudiantes === 1 ? '' : 's') . ' a cargo.' : 'Aún no tienes grupos asignados.' ?> Gestiona la asistencia y publica las notas de tus estudiantes desde este portal.</p>
              <a class="btn btn-soft btn-sm" href="#grupos">
                Ver mis grupos
                <span data-icon="arrow-right"></span>
              </a>
            </div>
            <div class="welcome-art">
              <div class="welcome-chip">
                <span>Grupos activos</span>
                <strong><?= $totalCursos ?></strong>
              </div>
              <div class="welcome-chip">
                <span>Estudiantes</span>
                <strong><?= $totalEstudiantes ?></strong>
              </div>
              <div class="welcome-chip">
                <span>Asistencia</span>
                <strong>91%</strong>
                <div class="progress"><span style="width:91%"></span></div>
              </div>
            </div>
          </div>

          <section class="kpi-grid" aria-label="Indicadores docentes">
            <article class="kpi-card">
              <div class="kpi-top">
                <span class="kpi-label">Grupos activos</span>
                <span class="kpi-icon"><span data-icon="users"></span></span>
              </div>
              <div class="kpi-value"><?= $totalCursos ?></div>
              <div class="kpi-meta"><?= $totalCursos > 0 ? e(implode(', ', array_column($cursosProfesor, 'nombre_curso'))) : 'Sin grupos asignados' ?></div>
            </article>

            <article class="kpi-card">
              <div class="kpi-top">
                <span class="kpi-label">Estudiantes a cargo</span>
                <span class="kpi-icon violet"><span data-icon="graduation-cap"></span></span>
              </div>
              <div class="kpi-value"><?= $totalEstudiantes ?></div>
              <div class="kpi-meta">Matriculados en tus cursos</div>
            </article>

            <article class="kpi-card">
              <div class="kpi-top">
                <span class="kpi-label">Asistencia promedio</span>
                <span class="kpi-icon success"><span data-icon="user-check"></span></span>
              </div>
              <div class="kpi-value">91%</div>
              <div class="kpi-meta">
                <span class="kpi-trend up"><span data-icon="trending-up"></span> +2%</span>
                vs. periodo anterior
              </div>
            </article>

            <article class="kpi-card">
              <div class="kpi-top">
                <span class="kpi-label">Notas pendientes</span>
                <span class="kpi-icon warning"><span data-icon="file-text"></span></span>
              </div>
              <div class="kpi-value">12</div>
              <div class="kpi-meta">Calificaciones del periodo</div>
            </article>
          </section>

          <div class="portal-grid">
            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Próxima clase</h3>
                  <p><?= $cursoPrincipal ? e($cursoPrincipal['nombre_curso']) . ' · Nivel ' . e($cursoPrincipal['nivel']) : 'Sin clases programadas' ?></p>
                </div>
                <span class="badge badge-primary"><?= $cursoPrincipal ? 'Grupo activo' : 'Sin grupo' ?></span>
              </div>
              <div class="card-body">
                <?php if ($cursoPrincipal): ?>
                  <div class="next-class">
                    <div class="next-class-head">
                      <span><?= e($cursoPrincipal['nombre_curso']) ?> · Nivel <?= e($cursoPrincipal['nivel']) ?></span>
                      <span class="flag flag-<?= e(flag_class($cursoPrincipal['nombre_curso'])) ?>"></span>
                    </div>
                    <h3>Sesión de <?= e($cursoPrincipal['nombre_curso']) ?></h3>
                    <div class="next-meta">
                      <div><span data-icon="clock"></span> Consulta el horario del grupo</div>
                      <div><span data-icon="map-pin"></span> Sede principal</div>
                      <div><span data-icon="users"></span> <?= (int) $cursoPrincipal['inscritos'] ?> estudiantes inscritos</div>
                    </div>
                    <div class="next-teacher">
                      <span class="avatar avatar-violet avatar-sm"><?= e(initials($usuarioActual['nombre'])) ?></span>
                      <div>
                        <strong><?= e($usuarioActual['nombre']) ?></strong>
                        <span>Profesor del curso</span>
                      </div>
                    </div>
                  </div>
                <?php else: ?>
                  <div class="notice notice-primary">
                    <span data-icon="info"></span>
                    <div>No tienes cursos asignados. El administrador puede asignarte cursos desde la gestión académica.</div>
                  </div>
                <?php endif; ?>

                <div class="flex gap-8 mt-16">
                  <a class="btn btn-primary btn-sm" href="#asistencia">
                    <span data-icon="user-check"></span>
                    Pasar lista
                  </a>
                  <a class="btn btn-outline btn-sm" href="#notas">
                    <span data-icon="file-text"></span>
                    Registrar notas
                  </a>
                </div>

                <div class="notice notice-primary mt-16">
                  <span data-icon="info"></span>
                  <div><strong>Plan de clase:</strong> revisa la asistencia y el avance de tu grupo antes de la próxima sesión.</div>
                </div>
              </div>
            </article>

            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Mis grupos</h3>
                  <p>Periodo académico actual</p>
                </div>
                <a class="link" href="#grupos">
                  Ver todos
                  <span data-icon="arrow-right"></span>
                </a>
              </div>
              <div class="card-body">
                <div class="portal-course-list">
                  <?php if (empty($cursosProfesor)): ?>
                    <p class="text-muted">No tienes grupos asignados todavía.</p>
                  <?php else: ?>
                    <?php foreach (array_slice($cursosProfesor, 0, 3) as $curso): ?>
                      <a class="portal-course" href="#grupos">
                        <span class="flag flag-<?= e(flag_class($curso['nombre_curso'])) ?>"></span>
                        <div class="portal-course-info">
                          <strong><?= e($curso['nombre_curso']) ?></strong>
                          <span>Nivel <?= e($curso['nivel']) ?> · <?= (int) $curso['inscritos'] ?> estudiantes</span>
                          <div class="progress"><span style="width:100%"></span></div>
                        </div>
                        <strong><?= (int) $curso['inscritos'] ?></strong>
                      </a>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </div>
            </article>
          </div>

          <div class="dash-grid-2">
            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Mis grupos asignados</h3>
                  <p>Resumen de los cursos a tu cargo</p>
                </div>
                <span class="badge badge-primary"><?= $totalCursos ?> grupo<?= $totalCursos === 1 ? '' : 's' ?></span>
              </div>
              <div class="card-body">
                <div class="class-list">
                  <?php if (empty($cursosProfesor)): ?>
                    <p class="text-muted">No tienes grupos asignados.</p>
                  <?php else: ?>
                    <?php foreach ($cursosProfesor as $curso): ?>
                      <div class="class-row">
                        <div class="class-time"><?= e($curso['nivel']) ?><span>Nivel</span></div>
                        <div class="class-info">
                          <strong><?= e($curso['nombre_curso']) ?></strong>
                          <span><?= (int) $curso['inscritos'] ?> estudiantes inscritos</span>
                        </div>
                        <span class="badge badge-success">Activo</span>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>

                <div class="notice notice-primary mt-16">
                  <span data-icon="info"></span>
                  <div><strong>Recordatorio:</strong> registra la asistencia y las notas de cada grupo desde las secciones de abajo.</div>
                </div>
              </div>
            </article>

            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Pendientes</h3>
                  <p>Tareas que requieren tu atención</p>
                </div>
                <span class="badge badge-warning"><?= $totalMatriculas * 3 ?></span>
              </div>
              <div class="card-body">
                <div class="alert-list">
                  <div class="alert-item">
                    <span class="icon-circle warning"><span data-icon="file-text"></span></span>
                    <div>
                      <strong><?= $totalMatriculas * 3 ?> notas del tercer parcial</strong>
                      <p><?= $totalCursos ?> grupo<?= $totalCursos === 1 ? '' : 's' ?> activo<?= $totalCursos === 1 ? '' : 's' ?> · registra las calificaciones del periodo.</p>
                      <a href="#notas">Registrar notas <span data-icon="arrow-right"></span></a>
                    </div>
                  </div>
                  <div class="alert-item">
                    <span class="icon-circle"><span data-icon="user-check"></span></span>
                    <div>
                      <strong>Asistencia pendiente de registro</strong>
                      <p><?= $totalEstudiantes ?> estudiante<?= $totalEstudiantes === 1 ? '' : 's' ?> en tus grupos.</p>
                      <a href="#asistencia">Pasar lista <span data-icon="arrow-right"></span></a>
                    </div>
                  </div>
                  <div class="alert-item">
                    <span class="icon-circle danger"><span data-icon="alert-circle"></span></span>
                    <div>
                      <strong>Cursos asignados</strong>
                      <p><?= $totalCursos > 0 ? e(implode(', ', array_column($cursosProfesor, 'nombre_curso'))) : 'Sin grupos asignados' ?></p>
                      <a href="#grupos">Revisar grupos <span data-icon="arrow-right"></span></a>
                    </div>
                  </div>
                </div>
              </div>
            </article>
          </div>
        </section>

        <!-- ========================= MIS GRUPOS ========================= -->
        <section class="content-section" id="grupos">
          <div class="section-head">
            <div>
              <h2>Mis grupos</h2>
              <p><?= $totalCursos ?> grupo<?= $totalCursos === 1 ? '' : 's' ?> activo<?= $totalCursos === 1 ? '' : 's' ?> · <?= $totalEstudiantes ?> estudiante<?= $totalEstudiantes === 1 ? '' : 's' ?> a cargo</p>
            </div>
            <div class="section-actions">
              <div class="filter-chips" data-chip-group>
                <button class="chip is-active" type="button">Todos</button>
                <?php foreach (array_unique(array_column($cursosProfesor, 'nombre_curso')) as $nombreCurso): ?>
                  <button class="chip" type="button"><?= e($nombreCurso) ?></button>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <div class="course-grid">
            <?php if (empty($cursosProfesor)): ?>
              <article class="card">
                <div class="card-body">
                  <p>No tienes grupos asignados todavía. El administrador puede asignarte cursos desde la gestión de cursos.</p>
                </div>
              </article>
            <?php else: ?>
              <?php foreach ($cursosProfesor as $curso): ?>
                <?php $avance = min(100, (int) round(((int) $curso['inscritos'] / 18) * 100)); ?>
                <article class="course-card">
                  <div class="course-top">
                    <div class="course-title">
                      <span class="flag flag-<?= e(flag_class($curso['nombre_curso'])) ?>"></span>
                      <div>
                        <h4><?= e($curso['nombre_curso']) ?></h4>
                        <span>Nivel <?= e($curso['nivel']) ?></span>
                      </div>
                    </div>
                    <span class="badge badge-success">Activo</span>
                  </div>
                  <div class="course-meta">
                    <div><span data-icon="users"></span> <?= (int) $curso['inscritos'] ?> estudiantes inscritos</div>
                    <div><span data-icon="book"></span> Nivel <?= e($curso['nivel']) ?></div>
                    <div><span data-icon="calendar"></span> Periodo académico actual</div>
                  </div>
                  <div class="course-capacity">
                    <div class="progress-row">
                      <span>Ocupación del grupo</span>
                      <strong><?= $avance ?>%</strong>
                    </div>
                    <div class="progress"><span style="width:<?= $avance ?>%"></span></div>
                  </div>
                  <div class="course-foot">
                    <div class="course-price"><?= e(format_cop($curso['precio'])) ?> <span>/ mes</span></div>
                    <a class="btn btn-soft btn-sm" href="#estudiantes">
                      Ver estudiantes
                      <span data-icon="arrow-right"></span>
                    </a>
                  </div>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <div class="card mt-24" id="estudiantes">
            <div class="card-header">
              <div>
                <h3>Estudiantes a cargo</h3>
                <p><?= count($estudiantesProfesor) ?> matrícula<?= count($estudiantesProfesor) === 1 ? '' : 's' ?> en tus cursos · <?= $totalEstudiantes ?> estudiante<?= $totalEstudiantes === 1 ? '' : 's' ?> único<?= $totalEstudiantes === 1 ? '' : 's' ?></p>
              </div>
              <div class="flex gap-8">
                <button class="btn btn-outline btn-sm" type="button" data-toast="Listado de estudiantes exportado (demo).">
                  <span data-icon="download"></span>
                  Exportar
                </button>
                <a class="btn btn-primary btn-sm" href="#asistencia">
                  <span data-icon="user-check"></span>
                  Pasar lista
                </a>
              </div>
            </div>
            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Estudiante</th>
                    <th>Curso</th>
                    <th>Nivel</th>
                    <th>Matrícula</th>
                    <th class="text-right">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($estudiantesProfesor)): ?>
                    <tr>
                      <td colspan="5">No hay estudiantes matriculados en tus cursos.</td>
                    </tr>
                  <?php else: ?>
                    <?php $avatares = ['avatar-teal', 'avatar-violet', 'avatar-warning', 'avatar-success', 'avatar-danger', 'avatar-pink']; ?>
                    <?php foreach ($estudiantesProfesor as $indice => $estudiante): ?>
                      <tr>
                        <td>
                          <div class="cell-main">
                            <span class="avatar <?= $avatares[$indice % count($avatares)] ?>"><?= e(initials($estudiante['nombre'])) ?></span>
                            <div><strong><?= e($estudiante['nombre']) ?></strong><span><?= e($estudiante['correo']) ?></span></div>
                          </div>
                        </td>
                        <td><?= e($estudiante['nombre_curso']) ?></td>
                        <td><span class="badge badge-primary"><?= e($estudiante['nivel']) ?></span></td>
                        <td><?= e(format_date($estudiante['fecha_matricula'])) ?></td>
                        <td>
                          <div class="actions">
                            <button class="row-action" type="button" aria-label="Registrar nota" data-modal-open="gradeModal"><span data-icon="edit"></span></button>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            <div class="table-footer">
              <span>Mostrando <?= count($estudiantesProfesor) ?> matrícula<?= count($estudiantesProfesor) === 1 ? '' : 's' ?> de tus grupos</span>
            </div>
          </div>
        </section>

        <!-- ========================= ASISTENCIA ========================= -->
        <section class="content-section" id="asistencia">
          <div class="section-head">
            <div>
              <h2>Asistencia</h2>
              <p>Registra la asistencia de cada clase · miércoles, 5 de noviembre de 2026</p>
            </div>
            <div class="section-actions">
              <select class="select" aria-label="Seleccionar grupo">
                <?php if (empty($cursosProfesor)): ?>
                  <option>Sin grupos asignados</option>
                <?php else: ?>
                  <?php foreach ($cursosProfesor as $curso): ?>
                    <option><?= e($curso['nombre_curso']) ?> · Nivel <?= e($curso['nivel']) ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
              <button class="btn btn-primary btn-sm" type="button" data-toast="Asistencia guardada correctamente (demo).">
                <span data-icon="check"></span>
                Guardar asistencia
              </button>
            </div>
          </div>

          <div class="card">
            <div class="attendance-summary">
              <div class="summary-item"><span class="dot" style="background:var(--success)"></span> Presentes: <strong><?= $totalEstudiantes ?></strong></div>
              <div class="summary-item"><span class="dot" style="background:var(--warning)"></span> Tarde: <strong>0</strong></div>
              <div class="summary-item"><span class="dot" style="background:var(--danger)"></span> Ausentes: <strong>0</strong></div>
              <div class="summary-item"><span class="dot" style="background:var(--muted-2)"></span> Justificadas: <strong>0</strong></div>
              <div class="summary-item" style="margin-left:auto"><strong><?= $totalEstudiantes ?></strong> estudiantes en tus grupos</div>
            </div>

            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Estudiante</th>
                    <th>Curso</th>
                    <th>Asistencia</th>
                    <th>Observación</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($estudiantesProfesor)): ?>
                    <tr>
                      <td colspan="4">No hay estudiantes matriculados en tus cursos.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($estudiantesProfesor as $indice => $estudiante): ?>
                      <tr>
                        <td>
                          <div class="cell-main">
                            <span class="avatar <?= $avatares[$indice % count($avatares)] ?>"><?= e(initials($estudiante['nombre'])) ?></span>
                            <div><strong><?= e($estudiante['nombre']) ?></strong><span><?= e($estudiante['correo']) ?></span></div>
                          </div>
                        </td>
                        <td><?= e($estudiante['nombre_curso']) ?></td>
                        <td>
                          <div class="seg">
                            <button class="is-on good" type="button" data-tone="good">Presente</button>
                            <button type="button" data-tone="warn">Tarde</button>
                            <button type="button" data-tone="bad">Ausente</button>
                          </div>
                        </td>
                        <td><input class="input" type="text" placeholder="Opcional" aria-label="Observación de <?= e($estudiante['nombre']) ?>"></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            <div class="card-footer">
              <div class="flex flex-between flex-wrap gap-12">
                <span class="text-muted text-xs">Los cambios se guardan como borrador hasta confirmar la asistencia.</span>
                <span class="badge badge-primary">Regla: mínimo 80% de asistencia</span>
              </div>
            </div>
          </div>

          <div class="dash-grid-2 mt-24">
            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Resumen del grupo</h3>
                  <p><?= $cursoPrincipal ? e($cursoPrincipal['nombre_curso']) . ' · Nivel ' . e($cursoPrincipal['nivel']) : 'Sin grupo asignado' ?></p>
                </div>
                <span class="badge badge-success">91%</span>
              </div>
              <div class="card-body">
                <div class="ring-row">
                  <div class="ring" style="background:conic-gradient(var(--primary) 0 91%, var(--line-soft) 91% 100%)">
                    <div class="ring-center">
                      <strong>91%</strong>
                      <span>asistencia</span>
                    </div>
                  </div>
                  <div class="ring-copy">
                    <strong>Buen nivel de asistencia</strong>
                    <p><?= $totalEstudiantes ?> estudiantes a cargo en <?= $totalCursos ?> grupo<?= $totalCursos === 1 ? '' : 's' ?>.</p>
                    <span class="badge badge-warning"><?= $totalEstudiantes ?> en seguimiento</span>
                  </div>
                </div>
              </div>
            </article>

            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Grupos a tu cargo</h3>
                  <p>Resumen de tus cursos asignados</p>
                </div>
              </div>
              <div class="card-body">
                <div class="class-list">
                  <?php if (empty($cursosProfesor)): ?>
                    <p class="text-muted">No tienes grupos asignados.</p>
                  <?php else: ?>
                    <?php foreach ($cursosProfesor as $curso): ?>
                      <div class="class-row">
                        <div class="class-time"><?= e($curso['nivel']) ?><span>Nivel</span></div>
                        <div class="class-info">
                          <strong><?= e($curso['nombre_curso']) ?></strong>
                          <span><?= (int) $curso['inscritos'] ?> estudiantes inscritos</span>
                        </div>
                        <span class="badge badge-success">Activo</span>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </div>
            </article>
          </div>
        </section>

        <!-- ========================= NOTAS ========================= -->
        <section class="content-section" id="notas">
          <div class="section-head">
            <div>
              <h2>Notas</h2>
              <p>Registro de calificaciones · tercer parcial · escala 0.0 a 5.0</p>
            </div>
            <div class="section-actions">
              <select class="select" aria-label="Seleccionar grupo">
                <?php if (empty($cursosProfesor)): ?>
                  <option>Sin grupos asignados</option>
                <?php else: ?>
                  <?php foreach ($cursosProfesor as $curso): ?>
                    <option><?= e($curso['nombre_curso']) ?> · Nivel <?= e($curso['nivel']) ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
              <button class="btn btn-primary btn-sm" type="button" data-toast="Notas guardadas correctamente (demo).">
                <span data-icon="check"></span>
                Guardar notas
              </button>
            </div>
          </div>

          <div class="grade-summary">
            <div class="grade-stat">
              <span>Promedio del grupo</span>
              <strong>—</strong>
            </div>
            <div class="grade-stat">
              <span>Notas registradas</span>
              <strong>0</strong>
            </div>
            <div class="grade-stat">
              <span>Notas pendientes</span>
              <strong><?= $totalMatriculas * 3 ?></strong>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <div>
                <h3><?= $cursoPrincipal ? e($cursoPrincipal['nombre_curso']) . ' · Nivel ' . e($cursoPrincipal['nivel']) : 'Sin grupo asignado' ?></h3>
                <p>Tercer parcial · evaluación integradora</p>
              </div>
              <span class="badge badge-warning"><?= $totalMatriculas * 3 ?> pendientes</span>
            </div>
            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Estudiante</th>
                    <th>Curso</th>
                    <th>Parcial 1</th>
                    <th>Parcial 2</th>
                    <th>Parcial 3</th>
                    <th>Promedio</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($estudiantesProfesor)): ?>
                    <tr>
                      <td colspan="7">No hay estudiantes matriculados en tus cursos.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($estudiantesProfesor as $indice => $estudiante): ?>
                      <tr>
                        <td>
                          <div class="cell-main">
                            <span class="avatar <?= $avatares[$indice % count($avatares)] ?>"><?= e(initials($estudiante['nombre'])) ?></span>
                            <div><strong><?= e($estudiante['nombre']) ?></strong><span><?= e($estudiante['correo']) ?></span></div>
                          </div>
                        </td>
                        <td><?= e($estudiante['nombre_curso']) ?></td>
                        <td><input class="input" type="number" min="0" max="5" step="0.1" style="width:82px" aria-label="Parcial 1 de <?= e($estudiante['nombre']) ?>"></td>
                        <td><input class="input" type="number" min="0" max="5" step="0.1" style="width:82px" aria-label="Parcial 2 de <?= e($estudiante['nombre']) ?>"></td>
                        <td><input class="input" type="number" min="0" max="5" step="0.1" style="width:82px" aria-label="Parcial 3 de <?= e($estudiante['nombre']) ?>"></td>
                        <td><span class="grade">—</span></td>
                        <td><span class="badge badge-warning">Pendiente</span></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            <div class="card-footer">
              <div class="flex flex-between flex-wrap gap-12">
                <span class="text-muted text-xs">La nota mínima aprobatoria es <strong class="text-ink">3.0</strong>. El sistema calcula el promedio automáticamente.</span>
                <button class="btn btn-soft btn-sm" type="button" data-toast="Notas publicadas a los estudiantes (demo).">
                  <span data-icon="send"></span>
                  Publicar notas
                </button>
              </div>
            </div>
          </div>

          <div class="dash-grid-2 mt-24">
            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Distribución de notas</h3>
                  <p><?= $cursoPrincipal ? e($cursoPrincipal['nombre_curso']) : 'Sin grupo' ?> · tercer parcial</p>
                </div>
              </div>
              <div class="card-body">
                <div class="report-bars">
                  <div class="report-bar-row">
                    <span>4.5 – 5.0</span>
                    <div class="progress progress-success"><span style="width:36%"></span></div>
                    <strong>5 est.</strong>
                  </div>
                  <div class="report-bar-row">
                    <span>4.0 – 4.4</span>
                    <div class="progress"><span style="width:43%"></span></div>
                    <strong>6 est.</strong>
                  </div>
                  <div class="report-bar-row">
                    <span>3.0 – 3.9</span>
                    <div class="progress progress-warning"><span style="width:14%"></span></div>
                    <strong>2 est.</strong>
                  </div>
                  <div class="report-bar-row">
                    <span>0.0 – 2.9</span>
                    <div class="progress progress-danger"><span style="width:7%"></span></div>
                    <strong>1 est.</strong>
                  </div>
                </div>
                <div class="notice notice-primary mt-16">
                  <span data-icon="info"></span>
                  <div>Las notas se calcularán automáticamente cuando registres las calificaciones de cada estudiante.</div>
                </div>
              </div>
            </article>

            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Notas pendientes</h3>
                  <p>Estudiantes sin calificaciones registradas</p>
                </div>
                <span class="badge badge-warning"><?= $totalMatriculas ?></span>
              </div>
              <div class="card-body">
                <div class="alert-list">
                  <?php if (empty($estudiantesProfesor)): ?>
                    <p class="text-muted">No hay estudiantes matriculados en tus cursos.</p>
                  <?php else: ?>
                    <?php foreach (array_slice($estudiantesProfesor, 0, 3) as $estudiante): ?>
                      <div class="alert-item">
                        <span class="icon-circle warning"><span data-icon="clock"></span></span>
                        <div>
                          <strong><?= e($estudiante['nombre']) ?> · nota pendiente</strong>
                          <p><?= e($estudiante['nombre_curso']) ?> · registra las calificaciones del periodo.</p>
                          <a href="#notas">Registrar nota <span data-icon="arrow-right"></span></a>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>

                <div class="notice notice-warning mt-16">
                  <span data-icon="shield"></span>
                  <div><strong>Regla académica:</strong> los estudiantes con asistencia menor al 80% no pueden presentar el examen final.</div>
                </div>
              </div>
            </article>
          </div>
        </section>
      </main>
    </div>
  </div>

  <!-- Registrar nota (demo) -->
  <div class="modal-backdrop" id="gradeModal" data-success="Nota registrada correctamente (demo).">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="gradeTitle">
      <div class="modal-head">
        <div>
          <h3 id="gradeTitle">Registrar nota</h3>
          <p>Vista previa del formulario de calificación.</p>
        </div>
        <button class="modal-close" type="button" data-modal-close aria-label="Cerrar">
          <span data-icon="x"></span>
        </button>
      </div>

      <form>
        <div class="modal-body">
          <div class="form-grid">
            <div class="field col-span-2">
              <label class="label" for="gradeStudent">Estudiante</label>
              <select class="select" id="gradeStudent" required>
                <option value="">Selecciona un estudiante</option>
                <?php foreach ($estudiantesProfesor as $estudiante): ?>
                  <option><?= e($estudiante['nombre']) ?> · <?= e($estudiante['nombre_curso']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label class="label" for="gradeGroup">Grupo</label>
              <select class="select" id="gradeGroup" required>
                <?php if (empty($cursosProfesor)): ?>
                  <option>Sin grupos asignados</option>
                <?php else: ?>
                  <?php foreach ($cursosProfesor as $curso): ?>
                    <option><?= e($curso['nombre_curso']) ?> · Nivel <?= e($curso['nivel']) ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
            <div class="field">
              <label class="label" for="gradeEvaluation">Evaluación</label>
              <select class="select" id="gradeEvaluation" required>
                <option>Parcial 3</option>
                <option>Quiz</option>
                <option>Speaking task</option>
                <option>Writing task</option>
                <option>Examen final</option>
              </select>
            </div>
            <div class="field">
              <label class="label" for="gradeValue">Nota (0.0 – 5.0)</label>
              <input class="input" id="gradeValue" type="number" min="0" max="5" step="0.1" placeholder="4.0" required>
            </div>
            <div class="field">
              <label class="label" for="gradeDate">Fecha</label>
              <input class="input" id="gradeDate" type="date" value="2026-11-05" required>
            </div>
            <div class="field col-span-2">
              <label class="label" for="gradeComment">Observación</label>
              <input class="input" id="gradeComment" type="text" placeholder="Comentario opcional para el estudiante">
            </div>
          </div>

          <div class="notice notice-primary mt-16">
            <span data-icon="info"></span>
            <div>La nota se publicará en el portal del estudiante y actualizará su promedio automáticamente.</div>
          </div>
        </div>

        <div class="modal-foot">
          <button class="btn btn-ghost" type="button" data-modal-close>Cancelar</button>
          <button class="btn btn-primary" type="submit">
            <span data-icon="check"></span>
            Guardar nota
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="js/shell.js?v=3"></script>
  <script src="js/main.js?v=3"></script>
</body>
</html>
