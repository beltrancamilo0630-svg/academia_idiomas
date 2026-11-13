<?php
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/config/database.php';

require_role([3]);

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
    'SELECT c.id_curso, c.nombre_curso, c.nivel, c.precio, m.fecha_matricula,
            u.nombre AS profesor
     FROM matriculas m
     INNER JOIN cursos c ON c.id_curso = m.id_curso
     LEFT JOIN usuarios u ON u.id_usuario = c.id_profesor
     WHERE m.id_usuario = ?
     ORDER BY m.fecha_matricula DESC, c.nombre_curso'
);
$stmt->execute([(int) $_SESSION['id_usuario']]);
$cursosEstudiante = $stmt->fetchAll();

$cursoActual = $cursosEstudiante[0] ?? null;
$totalCursos = count($cursosEstudiante);
$nivelesRuta = ['A1', 'A2', 'B1', 'B2', 'C1'];
$nivelActual = $cursoActual ? strtoupper(trim((string) $cursoActual['nivel'])) : null;
$indiceNivelActual = $nivelActual !== null ? array_search($nivelActual, $nivelesRuta, true) : false;
$fechasMatricula = array_filter(array_column($cursosEstudiante, 'fecha_matricula'));
$fechaIngreso = !empty($fechasMatricula) ? min($fechasMatricula) : null;
$partesNombre = preg_split('/\s+/', trim($usuarioActual['nombre'])) ?: [];
$nombrePila = $partesNombre[0] ?? $usuarioActual['nombre'];
$apellidos = count($partesNombre) > 1 ? implode(' ', array_slice($partesNombre, 1)) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Portal del estudiante de la Academia de Idiomas: cursos, horario, notas, pagos y perfil.">
  <meta name="theme-color" content="#0F172A">
  <title>Portal del estudiante | Academia de Idiomas</title>
  <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/layout.css">
  <link rel="stylesheet" href="css/pages.css">
</head>
<body data-role="student" data-page="inicio" data-title="Portal del estudiante" data-subtitle="Miércoles, 5 de noviembre de 2026" data-user-name="<?= e($usuarioActual['nombre']) ?>" data-user-role="Estudiante" data-user-initials="<?= e(initials($usuarioActual['nombre'])) ?>">
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
            <p>Este es tu espacio académico. Consulta tus clases, notas y pagos, y mantén tu proceso al día.</p>
          </div>
          <div class="page-actions">
            <a class="btn btn-outline" href="#horario">
              <span data-icon="calendar"></span>
              Ver mi horario
            </a>
            <button class="btn btn-primary" type="button" data-modal-open="reportPaymentModal">
              <span data-icon="upload"></span>
              Reportar pago
            </button>
          </div>
        </div>

        <nav class="anchor-nav" aria-label="Secciones del portal del estudiante">
          <a class="is-active" href="#inicio"><span data-icon="home"></span> Inicio</a>
          <a href="#cursos"><span data-icon="book"></span> Mis cursos</a>
          <a href="#horario"><span data-icon="calendar"></span> Mi horario</a>
          <a href="#notas"><span data-icon="file-text"></span> Mis notas</a>
          <a href="#pagos"><span data-icon="wallet"></span> Mis pagos</a>
          <a href="#perfil"><span data-icon="user"></span> Mi perfil</a>
        </nav>

        <!-- ========================= INICIO ========================= -->
        <section class="content-section" id="inicio">
          <div class="welcome-banner">
            <div class="welcome-copy">
              <span class="eyebrow no-line">Tu semana en un vistazo</span>
              <h2>Vas por buen camino, <?= e($_SESSION['usuario']) ?></h2>
              <p><?= $cursoActual ? 'Estás inscrito en ' . e($cursoActual['nombre_curso']) . ' (nivel ' . e($cursoActual['nivel']) . ').' : 'Aún no tienes cursos inscritos.' ?> Consulta tus clases, notas y pagos, y mantén tu proceso al día.</p>
              <a class="btn btn-soft btn-sm" href="#cursos">
                Continuar aprendiendo
                <span data-icon="arrow-right"></span>
              </a>
            </div>
            <div class="welcome-art">
              <div class="welcome-chip">
                <span>Promedio</span>
                <strong>4.3 / 5.0</strong>
              </div>
              <div class="welcome-chip">
                <span>Asistencia</span>
                <strong>94%</strong>
                <div class="progress"><span style="width:94%"></span></div>
              </div>
              <div class="welcome-chip">
                <span>Nivel actual</span>
                <strong><?= e($cursoActual['nivel'] ?? '—') ?></strong>
              </div>
            </div>
          </div>

          <section class="kpi-grid" aria-label="Resumen académico">
            <article class="kpi-card">
              <div class="kpi-top">
                <span class="kpi-label">Curso actual</span>
                <span class="kpi-icon"><span data-icon="book"></span></span>
              </div>
              <div class="kpi-value"><?= e($cursoActual['nombre_curso'] ?? 'Sin cursos') ?></div>
              <div class="kpi-meta"><?= $cursoActual ? 'Nivel ' . e($cursoActual['nivel']) . ' · ' . e($cursoActual['profesor'] ?: 'Sin profesor') : 'Aún no tienes matrículas' ?></div>
            </article>

            <article class="kpi-card">
              <div class="kpi-top">
                <span class="kpi-label">Promedio general</span>
                <span class="kpi-icon violet"><span data-icon="award"></span></span>
              </div>
              <div class="kpi-value">4.3</div>
              <div class="kpi-meta">
                <span class="kpi-trend up"><span data-icon="trending-up"></span> +0.2</span>
                vs. periodo anterior
              </div>
            </article>

            <article class="kpi-card">
              <div class="kpi-top">
                <span class="kpi-label">Asistencia</span>
                <span class="kpi-icon success"><span data-icon="user-check"></span></span>
              </div>
              <div class="kpi-value">94%</div>
              <div class="kpi-meta">17 de 18 clases asistidas</div>
            </article>

            <article class="kpi-card">
              <div class="kpi-top">
                <span class="kpi-label">Saldo vencido</span>
                <span class="kpi-icon warning"><span data-icon="wallet"></span></span>
              </div>
              <div class="kpi-value">$0</div>
              <div class="kpi-meta">
                <span class="badge badge-success">Al día</span>
              </div>
            </article>
          </section>

          <div class="portal-grid">
            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Próxima clase</h3>
                  <p><?= $cursoActual ? 'Curso ' . e($cursoActual['nombre_curso']) : 'Sin clases programadas' ?></p>
                </div>
                <span class="badge badge-primary"><?= $cursoActual ? 'Nivel ' . e($cursoActual['nivel']) : 'Sin matrícula' ?></span>
              </div>
              <div class="card-body">
                <?php if ($cursoActual): ?>
                  <div class="next-class">
                    <div class="next-class-head">
                      <span><?= e($cursoActual['nombre_curso']) ?> · Nivel <?= e($cursoActual['nivel']) ?></span>
                      <span class="flag flag-<?= e(flag_class($cursoActual['nombre_curso'])) ?>"></span>
                    </div>
                    <h3>Tu próxima sesión de <?= e($cursoActual['nombre_curso']) ?></h3>
                    <div class="next-meta">
                      <div><span data-icon="clock"></span> Consulta el horario de tu grupo</div>
                      <div><span data-icon="map-pin"></span> Sede principal</div>
                      <div><span data-icon="book"></span> Nivel <?= e($cursoActual['nivel']) ?></div>
                    </div>
                    <div class="next-teacher">
                      <span class="avatar avatar-violet avatar-sm"><?= e(initials($cursoActual['profesor'] ?: 'Sin profesor')) ?></span>
                      <div>
                        <strong><?= e($cursoActual['profesor'] ?: 'Sin profesor asignado') ?></strong>
                        <span>Profesor del curso</span>
                      </div>
                    </div>
                  </div>
                <?php else: ?>
                  <div class="notice notice-primary">
                    <span data-icon="info"></span>
                    <div>Aún no tienes cursos inscritos. Contacta a la academia para iniciar tu matrícula.</div>
                  </div>
                <?php endif; ?>

                <div class="notice notice-primary mt-16">
                  <span data-icon="info"></span>
                  <div><strong>Trae tu libro:</strong> consulta con tu profesor el material de la próxima unidad.</div>
                </div>
              </div>
            </article>

            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Mis cursos</h3>
                  <p>Programa activo</p>
                </div>
                <a class="link" href="#cursos">
                  Ver todos
                  <span data-icon="arrow-right"></span>
                </a>
              </div>
              <div class="card-body">
                <div class="portal-course-list">
                  <?php if (empty($cursosEstudiante)): ?>
                    <p class="text-muted">Aún no tienes cursos inscritos.</p>
                  <?php else: ?>
                    <?php foreach (array_slice($cursosEstudiante, 0, 3) as $curso): ?>
                      <a class="portal-course" href="#cursos">
                        <span class="flag flag-<?= e(flag_class($curso['nombre_curso'])) ?>"></span>
                        <div class="portal-course-info">
                          <strong><?= e($curso['nombre_curso']) ?></strong>
                          <span><?= e($curso['profesor'] ?: 'Sin profesor') ?> · Nivel <?= e($curso['nivel']) ?></span>
                          <div class="progress"><span style="width:100%"></span></div>
                        </div>
                        <strong><?= e($curso['nivel']) ?></strong>
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
                  <h3>Últimas notas</h3>
                  <p>Calificaciones del periodo actual</p>
                </div>
                <a class="link" href="#notas">
                  Ver todas
                  <span data-icon="arrow-right"></span>
                </a>
              </div>
              <div class="table-wrap">
                <table class="table table-compact">
                  <thead>
                    <tr>
                      <th>Evaluación</th>
                      <th>Curso</th>
                      <th>Fecha</th>
                      <th class="text-right">Nota</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>Parcial 3 · Listening</td>
                      <td>Inglés B1</td>
                      <td>1 nov. 2026</td>
                      <td class="text-right"><span class="grade good">4.1</span></td>
                    </tr>
                    <tr>
                      <td>Speaking task · Job interview</td>
                      <td>Inglés B1</td>
                      <td>28 oct. 2026</td>
                      <td class="text-right"><span class="grade good">4.5</span></td>
                    </tr>
                    <tr>
                      <td>Quiz · Past perfect</td>
                      <td>Inglés B1</td>
                      <td>23 oct. 2026</td>
                      <td class="text-right"><span class="grade good">4.8</span></td>
                    </tr>
                    <tr>
                      <td>Writing · Informal email</td>
                      <td>Inglés B1</td>
                      <td>18 oct. 2026</td>
                      <td class="text-right"><span class="grade">3.9</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </article>

            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Avisos de la academia</h3>
                  <p>Novedades y recordatorios</p>
                </div>
                <span class="badge badge-warning">2 nuevos</span>
              </div>
              <div class="card-body">
                <div class="alert-list">
                  <div class="alert-item">
                    <span class="icon-circle warning"><span data-icon="calendar"></span></span>
                    <div>
                      <strong>Semana de evaluaciones finales</strong>
                      <p>Del 24 al 28 de noviembre. Consulta el cronograma de tu grupo.</p>
                      <a href="#notas">Ver cronograma <span data-icon="arrow-right"></span></a>
                    </div>
                  </div>
                  <div class="alert-item">
                    <span class="icon-circle success"><span data-icon="award"></span></span>
                    <div>
                      <strong>Certificado académico disponible</strong>
                      <p>Ya puedes descargar tu certificado del nivel anterior.</p>
                      <a href="#perfil">Descargar certificado <span data-icon="arrow-right"></span></a>
                    </div>
                  </div>
                  <div class="alert-item">
                    <span class="icon-circle"><span data-icon="presentation"></span></span>
                    <div>
                      <strong>Club de conversación</strong>
                      <p>Jueves 13 de noviembre, 7:00 p. m. en la biblioteca. Cupos limitados.</p>
                      <a href="#" data-toast="Inscripción al club de conversación registrada (demo).">Inscribirme <span data-icon="arrow-right"></span></a>
                    </div>
                  </div>
                </div>
              </div>
            </article>
          </div>
        </section>

        <!-- ========================= MIS CURSOS ========================= -->
        <section class="content-section" id="cursos">
          <div class="section-head">
            <div>
              <h2>Mis cursos</h2>
              <p><?= $totalCursos ?> curso<?= $totalCursos === 1 ? '' : 's' ?> inscrito<?= $totalCursos === 1 ? '' : 's' ?> · matrículas activas en la academia</p>
            </div>
            <div class="section-actions">
              <div class="filter-chips" data-chip-group>
                <button class="chip is-active" type="button">Todos</button>
                <button class="chip" type="button">Activos</button>
              </div>
            </div>
          </div>

          <div class="level-strip" aria-label="Ruta de aprendizaje">
            <?php $primerNivelRuta = true; ?>
            <?php foreach ($nivelesRuta as $indice => $nivel): ?>
              <?php
                $claseNivel = '';
                $etiquetaNivel = 'Disponible';
                if ($indiceNivelActual !== false && $indice === (int) $indiceNivelActual) {
                    $claseNivel = 'is-active';
                    $etiquetaNivel = 'En curso';
                } elseif ($indiceNivelActual !== false && $indice < (int) $indiceNivelActual) {
                    $etiquetaNivel = 'Completado';
                }
              ?>
              <?php if (!$primerNivelRuta): ?>
                <span class="level-arrow"><span data-icon="chevron-right"></span></span>
              <?php endif; ?>
              <span class="level-pill <?= $claseNivel ?>"><?= e($nivel) ?> <small><?= e($etiquetaNivel) ?></small></span>
              <?php $primerNivelRuta = false; ?>
            <?php endforeach; ?>
          </div>

          <div class="course-grid">
            <?php if (empty($cursosEstudiante)): ?>
              <article class="card">
                <div class="card-body">
                  <p>No tienes cursos inscritos todavía. Cuando la academia registre tu matrícula, aparecerán aquí.</p>
                </div>
              </article>
            <?php else: ?>
              <?php foreach ($cursosEstudiante as $curso): ?>
                <article class="course-card">
                  <div class="course-top">
                    <div class="course-title">
                      <span class="flag flag-<?= e(flag_class($curso['nombre_curso'])) ?>"></span>
                      <div>
                        <h4><?= e($curso['nombre_curso']) ?></h4>
                        <span>Nivel <?= e($curso['nivel']) ?> · Inscrito el <?= e(format_date($curso['fecha_matricula'])) ?></span>
                      </div>
                    </div>
                    <span class="badge badge-success">En curso</span>
                  </div>
                  <div class="course-meta">
                    <div><span data-icon="user"></span> <?= e($curso['profesor'] ?: 'Sin profesor asignado') ?></div>
                    <div><span data-icon="book"></span> Nivel <?= e($curso['nivel']) ?></div>
                    <div><span data-icon="calendar"></span> Matrícula: <?= e(format_date($curso['fecha_matricula'])) ?></div>
                  </div>
                  <div class="course-foot">
                    <div class="course-price"><?= e(format_cop($curso['precio'])) ?> <span>/ mes</span></div>
                    <a class="btn btn-soft btn-sm" href="#horario">
                      Ver horario
                      <span data-icon="arrow-right"></span>
                    </a>
                  </div>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <div class="dash-grid-2 mt-24">
            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Material de estudio</h3>
                  <p>Recursos compartidos por tus profesores</p>
                </div>
                <span class="badge badge-primary"><?= e($cursoActual['nombre_curso'] ?? 'Sin curso') ?></span>
              </div>
              <div class="card-body">
                <div class="receipt-list">
                  <div class="receipt">
                    <span class="icon-circle"><span data-icon="file-text"></span></span>
                    <div class="receipt-info">
                      <strong>Unit 7 · Student's book</strong>
                      <span>PDF · 2.4 MB · publicado el 3 nov.</span>
                    </div>
                    <div class="receipt-actions">
                      <button class="row-action" type="button" aria-label="Descargar material" data-toast="Material descargado (demo)."><span data-icon="download"></span></button>
                    </div>
                  </div>
                  <div class="receipt">
                    <span class="icon-circle success"><span data-icon="file-check"></span></span>
                    <div class="receipt-info">
                      <strong>Guía de preparación · Parcial 3</strong>
                      <span>PDF · 1.1 MB · publicado el 30 oct.</span>
                    </div>
                    <div class="receipt-actions">
                      <button class="row-action" type="button" aria-label="Descargar guía" data-toast="Guía descargada (demo)."><span data-icon="download"></span></button>
                    </div>
                  </div>
                  <div class="receipt">
                    <span class="icon-circle warning"><span data-icon="message-circle"></span></span>
                    <div class="receipt-info">
                      <strong>Audios de pronunciación · Unit 6 y 7</strong>
                      <span>ZIP · 18 MB · publicado el 28 oct.</span>
                    </div>
                    <div class="receipt-actions">
                      <button class="row-action" type="button" aria-label="Descargar audios" data-toast="Audios descargados (demo)."><span data-icon="download"></span></button>
                    </div>
                  </div>
                </div>
              </div>
            </article>

            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Progreso por habilidad</h3>
                  <p><?= e($cursoActual['nombre_curso'] ?? 'Sin curso') ?> · periodo actual</p>
                </div>
              </div>
              <div class="card-body">
                <div class="progress-row">
                  <span>Listening</span>
                  <strong>82%</strong>
                </div>
                <div class="progress"><span style="width:82%"></span></div>

                <div class="progress-row mt-16">
                  <span>Speaking</span>
                  <strong>74%</strong>
                </div>
                <div class="progress progress-violet"><span style="width:74%"></span></div>

                <div class="progress-row mt-16">
                  <span>Reading</span>
                  <strong>88%</strong>
                </div>
                <div class="progress progress-success"><span style="width:88%"></span></div>

                <div class="progress-row mt-16">
                  <span>Writing</span>
                  <strong>69%</strong>
                </div>
                <div class="progress progress-warning"><span style="width:69%"></span></div>

                <div class="notice notice-primary mt-16">
                  <span data-icon="target"></span>
                  <div><strong>Meta del periodo:</strong> alcanzar 80% en escritura antes del parcial final.</div>
                </div>
              </div>
            </article>
          </div>
        </section>

        <!-- ========================= MI HORARIO ========================= -->
        <section class="content-section" id="horario">
          <div class="section-head">
            <div>
              <h2>Mi horario</h2>
              <p>Semana del 3 al 9 de noviembre de 2026 · 2 clases y 1 asesoría</p>
            </div>
            <div class="section-actions">
              <button class="btn btn-outline btn-sm" type="button" data-toast="Horario exportado a tu calendario (demo).">
                <span data-icon="download"></span>
                Exportar horario
              </button>
              <button class="btn btn-primary btn-sm" type="button" data-toast="Recordatorio de clase activado (demo).">
                <span data-icon="bell"></span>
                Recordarme
              </button>
            </div>
          </div>

          <div class="schedule-wrap">
            <table class="schedule">
              <thead>
                <tr>
                  <th>Hora</th>
                  <th>Lunes</th>
                  <th>Martes</th>
                  <th>Miércoles</th>
                  <th>Jueves</th>
                  <th>Viernes</th>
                  <th>Sábado</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="time-cell">10:00 a. m.<span>12:00 p. m.</span></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td>
                    <div class="sched-block violet">
                      <strong>Taller de conversación</strong>
                      <span>Laura Gómez</span>
                      <small>Biblioteca</small>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td class="time-cell">6:00 p. m.<span>8:00 p. m.</span></td>
                  <td></td>
                  <td>
                    <div class="sched-block">
                      <strong>Inglés B1</strong>
                      <span>Carlos Mendoza</span>
                      <small>Salón 204</small>
                    </div>
                  </td>
                  <td></td>
                  <td>
                    <div class="sched-block">
                      <strong>Inglés B1</strong>
                      <span>Carlos Mendoza</span>
                      <small>Salón 204</small>
                    </div>
                  </td>
                  <td></td>
                  <td></td>
                </tr>
                <tr>
                  <td class="time-cell">8:00 p. m.<span>9:00 p. m.</span></td>
                  <td></td>
                  <td></td>
                  <td>
                    <div class="sched-block success">
                      <strong>Asesoría de escritura</strong>
                      <span>Carlos Mendoza</span>
                      <small>Virtual · Teams</small>
                    </div>
                  </td>
                  <td></td>
                  <td></td>
                  <td></td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="schedule-legend">
            <div class="legend-item"><span class="legend-dot" style="background:#0284C7"></span> Inglés B1</div>
            <div class="legend-item"><span class="legend-dot" style="background:#7C3AED"></span> Taller de conversación</div>
            <div class="legend-item"><span class="legend-dot" style="background:#10B981"></span> Asesoría académica</div>
          </div>

          <div class="dash-grid-2 mt-24">
            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Próximas fechas clave</h3>
                  <p>Calendario académico del periodo</p>
                </div>
              </div>
              <div class="card-body">
                <div class="class-list">
                  <div class="class-row">
                    <div class="class-time">6 nov.<span>Jueves</span></div>
                    <div class="class-info">
                      <strong>Clase de Inglés B1 · Unit 7</strong>
                      <span>6:00 p. m. · Salón 204 · Carlos Mendoza</span>
                    </div>
                    <span class="badge badge-primary">Próxima</span>
                  </div>
                  <div class="class-row">
                    <div class="class-time">13 nov.<span>Jueves</span></div>
                    <div class="class-info">
                      <strong>Club de conversación</strong>
                      <span>7:00 p. m. · Biblioteca · Cupos limitados</span>
                    </div>
                    <span class="badge badge-neutral">Opcional</span>
                  </div>
                  <div class="class-row">
                    <div class="class-time">24 nov.<span>Martes</span></div>
                    <div class="class-info">
                      <strong>Inicio de evaluaciones finales</strong>
                      <span>Consulta el cronograma de tu grupo</span>
                    </div>
                    <span class="badge badge-warning">Evaluación</span>
                  </div>
                </div>
              </div>
            </article>

            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Asistencia del periodo</h3>
                  <p>Inglés B1 · 18 clases programadas</p>
                </div>
                <span class="badge badge-success">94%</span>
              </div>
              <div class="card-body">
                <div class="ring-row">
                  <div class="ring" style="background:conic-gradient(var(--success) 0 94%, var(--line-soft) 94% 100%)">
                    <div class="ring-center">
                      <strong>17</strong>
                      <span>de 18</span>
                    </div>
                  </div>
                  <div class="ring-copy">
                    <strong>Excelente asistencia</strong>
                    <p>Asististe a 17 de las 18 clases del periodo. Solo tienes 1 falta justificada.</p>
                    <span class="badge badge-success">Sin alertas</span>
                  </div>
                </div>

                <div class="info-list">
                  <div class="info-row"><span>Asistencias</span><strong>17</strong></div>
                  <div class="info-row"><span>Faltas justificadas</span><strong>1</strong></div>
                  <div class="info-row"><span>Faltas sin justificar</span><strong>0</strong></div>
                </div>
              </div>
            </article>
          </div>
        </section>

        <!-- ========================= MIS NOTAS ========================= -->
        <section class="content-section" id="notas">
          <div class="section-head">
            <div>
              <h2>Mis notas</h2>
              <p><?= e($cursoActual['nombre_curso'] ?? 'Sin curso') ?> · periodo agosto – diciembre de 2026</p>
            </div>
            <div class="section-actions">
              <button class="btn btn-outline btn-sm" type="button" data-toast="Boletín de notas descargado (demo).">
                <span data-icon="download"></span>
                Descargar boletín
              </button>
            </div>
          </div>

          <div class="grade-summary">
            <div class="grade-stat">
              <span>Promedio del periodo</span>
              <strong>4.3</strong>
            </div>
            <div class="grade-stat">
              <span>Nota más alta</span>
              <strong>4.8</strong>
            </div>
            <div class="grade-stat">
              <span>Notas registradas</span>
              <strong>12</strong>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <div>
                <h3>Detalle de calificaciones</h3>
                <p>Escala de 0.0 a 5.0 · aprueba con 3.0 o más</p>
              </div>
              <span class="badge badge-success">Aprobando</span>
            </div>
            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Evaluación</th>
                    <th>Tipo</th>
                    <th>Fecha</th>
                    <th>Peso</th>
                    <th class="text-right">Nota</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><strong>Parcial 1 · Grammar &amp; vocabulary</strong></td>
                    <td>Parcial</td>
                    <td>12 sep. 2026</td>
                    <td>20%</td>
                    <td class="text-right"><span class="grade good">4.2</span></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                  </tr>
                  <tr>
                    <td><strong>Parcial 2 · Reading &amp; writing</strong></td>
                    <td>Parcial</td>
                    <td>10 oct. 2026</td>
                    <td>20%</td>
                    <td class="text-right"><span class="grade good">4.0</span></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                  </tr>
                  <tr>
                    <td><strong>Parcial 3 · Listening</strong></td>
                    <td>Parcial</td>
                    <td>1 nov. 2026</td>
                    <td>20%</td>
                    <td class="text-right"><span class="grade good">4.1</span></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                  </tr>
                  <tr>
                    <td><strong>Speaking task · Job interview</strong></td>
                    <td>Oral</td>
                    <td>28 oct. 2026</td>
                    <td>10%</td>
                    <td class="text-right"><span class="grade good">4.5</span></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                  </tr>
                  <tr>
                    <td><strong>Quiz · Past perfect</strong></td>
                    <td>Quiz</td>
                    <td>23 oct. 2026</td>
                    <td>5%</td>
                    <td class="text-right"><span class="grade good">4.8</span></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                  </tr>
                  <tr>
                    <td><strong>Writing · Informal email</strong></td>
                    <td>Escrito</td>
                    <td>18 oct. 2026</td>
                    <td>10%</td>
                    <td class="text-right"><span class="grade">3.9</span></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                  </tr>
                  <tr>
                    <td><strong>Participación en clase</strong></td>
                    <td>Continua</td>
                    <td>Periodo completo</td>
                    <td>10%</td>
                    <td class="text-right"><span class="grade good">4.6</span></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                  </tr>
                  <tr>
                    <td><strong>Examen final integrador</strong></td>
                    <td>Final</td>
                    <td>28 nov. 2026</td>
                    <td>5%</td>
                    <td class="text-right"><span class="grade">—</span></td>
                    <td><span class="badge badge-warning">Pendiente</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="card-footer">
              <div class="flex flex-between flex-wrap gap-12">
                <span class="text-muted text-xs">Promedio ponderado actual: <strong class="text-ink">4.3 / 5.0</strong></span>
                <span class="badge badge-success">Nota mínima aprobatoria: 3.0</span>
              </div>
            </div>
          </div>

          <div class="dash-grid-2 mt-24">
            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Retroalimentación del profesor</h3>
                  <p><?= e($cursoActual['profesor'] ?: 'Sin profesor') ?> · <?= e($cursoActual['nombre_curso'] ?? 'Sin curso') ?></p>
                </div>
                <span class="avatar avatar-violet avatar-sm"><?= e(initials($cursoActual['profesor'] ?: 'Sin profesor')) ?></span>
              </div>
              <div class="card-body">
                <div class="notice notice-success">
                  <span data-icon="check-circle"></span>
                  <div><strong>Fortalezas:</strong> excelente comprensión auditiva y participación constante en clase. Tu pronunciación ha mejorado notablemente.</div>
                </div>
                <div class="notice notice-warning mt-16">
                  <span data-icon="alert-triangle"></span>
                  <div><strong>Por mejorar:</strong> refuerza la estructura de párrafos en textos formales. Te recomiendo la guía de escritura de la unidad 6.</div>
                </div>
                <div class="notice notice-primary mt-16">
                  <span data-icon="target"></span>
                  <div><strong>Próximo objetivo:</strong> alcanzar 4.5 en el examen final integrador para cerrar el nivel B1 con mención destacada.</div>
                </div>
              </div>
            </article>

            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Comparación con el grupo</h3>
                  <p>Promedio de <?= e($cursoActual['nombre_curso'] ?? 'tu curso') ?> · grupo actual</p>
                </div>
              </div>
              <div class="card-body">
                <div class="report-bars">
                  <div class="report-bar-row">
                    <span>Tu promedio</span>
                    <div class="progress progress-success"><span style="width:86%"></span></div>
                    <strong>4.3</strong>
                  </div>
                  <div class="report-bar-row">
                    <span>Promedio del grupo</span>
                    <div class="progress"><span style="width:74%"></span></div>
                    <strong>3.7</strong>
                  </div>
                  <div class="report-bar-row">
                    <span>Nota más alta</span>
                    <div class="progress progress-violet"><span style="width:96%"></span></div>
                    <strong>4.8</strong>
                  </div>
                </div>
                <div class="notice notice-primary mt-16">
                  <span data-icon="sparkles"></span>
                  <div>Estás <strong>0.6 puntos</strong> por encima del promedio de tu grupo. ¡Sigue así!</div>
                </div>
              </div>
            </article>
          </div>
        </section>

        <!-- ========================= MIS PAGOS ========================= -->
        <section class="content-section" id="pagos">
          <div class="section-head">
            <div>
              <h2>Mis pagos</h2>
              <p>Estado de cuenta · año 2026</p>
            </div>
            <div class="section-actions">
              <button class="btn btn-outline btn-sm" type="button" data-toast="Estado de cuenta descargado (demo).">
                <span data-icon="download"></span>
                Estado de cuenta
              </button>
              <button class="btn btn-primary btn-sm" type="button" data-modal-open="reportPaymentModal">
                <span data-icon="upload"></span>
                Reportar pago
              </button>
            </div>
          </div>

          <section class="kpi-grid" aria-label="Resumen de pagos">
            <article class="kpi-card">
              <div class="kpi-top">
                <span class="kpi-label">Total pagado en 2026</span>
                <span class="kpi-icon success"><span data-icon="banknote"></span></span>
              </div>
              <div class="kpi-value">$2.560.000</div>
              <div class="kpi-meta">8 pagos registrados</div>
            </article>

            <article class="kpi-card">
              <div class="kpi-top">
                <span class="kpi-label">Saldo vencido</span>
                <span class="kpi-icon"><span data-icon="wallet"></span></span>
              </div>
              <div class="kpi-value">$0</div>
              <div class="kpi-meta">
                <span class="badge badge-success">Sin pagos atrasados</span>
              </div>
            </article>

            <article class="kpi-card">
              <div class="kpi-top">
                <span class="kpi-label">Próximo vencimiento</span>
                <span class="kpi-icon warning"><span data-icon="calendar"></span></span>
              </div>
              <div class="kpi-value">5 dic.</div>
              <div class="kpi-meta">Mensualidad de diciembre · $320.000</div>
            </article>

            <article class="kpi-card">
              <div class="kpi-top">
                <span class="kpi-label">Método de pago</span>
                <span class="kpi-icon violet"><span data-icon="credit-card"></span></span>
              </div>
              <div class="kpi-value">Transferencia</div>
              <div class="kpi-meta">Banco Davivienda · cuenta terminada en 4821</div>
            </article>
          </section>

          <div class="finance-grid">
            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Historial de pagos</h3>
                  <p>Movimientos registrados en tu cuenta</p>
                </div>
                <span class="badge badge-success">8 aprobados</span>
              </div>
              <div class="table-wrap">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Fecha</th>
                      <th>Concepto</th>
                      <th>Método</th>
                      <th>Referencia</th>
                      <th class="text-right">Monto</th>
                      <th>Estado</th>
                      <th class="text-right">Recibo</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>5 nov. 2026</td>
                      <td><strong>Mensualidad noviembre</strong></td>
                      <td>Transferencia</td>
                      <td class="num">TRF-2026-11842</td>
                      <td class="num text-right"><strong>$320.000</strong></td>
                      <td><span class="badge badge-success">Aprobado</span></td>
                      <td class="text-right">
                        <button class="row-action" type="button" aria-label="Descargar recibo" data-toast="Recibo de noviembre descargado (demo)."><span data-icon="download"></span></button>
                      </td>
                    </tr>
                    <tr>
                      <td>5 oct. 2026</td>
                      <td><strong>Mensualidad octubre</strong></td>
                      <td>Transferencia</td>
                      <td class="num">TRF-2026-10418</td>
                      <td class="num text-right"><strong>$320.000</strong></td>
                      <td><span class="badge badge-success">Aprobado</span></td>
                      <td class="text-right">
                        <button class="row-action" type="button" aria-label="Descargar recibo" data-toast="Recibo de octubre descargado (demo)."><span data-icon="download"></span></button>
                      </td>
                    </tr>
                    <tr>
                      <td>5 sep. 2026</td>
                      <td><strong>Mensualidad septiembre</strong></td>
                      <td>PSE</td>
                      <td class="num">PSE-2026-09775</td>
                      <td class="num text-right"><strong>$320.000</strong></td>
                      <td><span class="badge badge-success">Aprobado</span></td>
                      <td class="text-right">
                        <button class="row-action" type="button" aria-label="Descargar recibo" data-toast="Recibo de septiembre descargado (demo)."><span data-icon="download"></span></button>
                      </td>
                    </tr>
                    <tr>
                      <td>5 ago. 2026</td>
                      <td><strong>Mensualidad agosto</strong></td>
                      <td>Transferencia</td>
                      <td class="num">TRF-2026-08103</td>
                      <td class="num text-right"><strong>$320.000</strong></td>
                      <td><span class="badge badge-success">Aprobado</span></td>
                      <td class="text-right">
                        <button class="row-action" type="button" aria-label="Descargar recibo" data-toast="Recibo de agosto descargado (demo)."><span data-icon="download"></span></button>
                      </td>
                    </tr>
                    <tr>
                      <td>28 jul. 2026</td>
                      <td><strong>Matrícula segundo semestre</strong></td>
                      <td>Tarjeta</td>
                      <td class="num">CRD-2026-07456</td>
                      <td class="num text-right"><strong>$640.000</strong></td>
                      <td><span class="badge badge-success">Aprobado</span></td>
                      <td class="text-right">
                        <button class="row-action" type="button" aria-label="Descargar recibo" data-toast="Recibo de matrícula descargado (demo)."><span data-icon="download"></span></button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="table-footer">
                <span>Mostrando 5 de 8 movimientos</span>
                <div class="pagination">
                  <a href="#" aria-label="Página anterior"><span data-icon="chevron-left"></span></a>
                  <span class="is-active">1</span>
                  <a href="#">2</a>
                  <a href="#" aria-label="Página siguiente"><span data-icon="chevron-right"></span></a>
                </div>
              </div>
            </article>

            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Plan de pagos</h3>
                  <p>Periodo agosto – diciembre de 2026</p>
                </div>
              </div>
              <div class="card-body">
                <div class="info-list" style="margin-top:0;border-top:0">
                  <div class="info-row"><span>Matrícula</span><strong>$640.000</strong></div>
                  <div class="info-row"><span>Mensualidad ago.</span><strong class="text-success">Pagada</strong></div>
                  <div class="info-row"><span>Mensualidad sep.</span><strong class="text-success">Pagada</strong></div>
                  <div class="info-row"><span>Mensualidad oct.</span><strong class="text-success">Pagada</strong></div>
                  <div class="info-row"><span>Mensualidad nov.</span><strong class="text-success">Pagada</strong></div>
                  <div class="info-row"><span>Mensualidad dic.</span><strong class="text-warning">Programada · 5 dic.</strong></div>
                </div>

                <div class="enroll-summary">
                  <div class="info-row"><span>Total del programa</span><strong>$2.240.000</strong></div>
                  <div class="info-row"><span>Pagado a la fecha</span><strong class="text-success">$1.920.000</strong></div>
                  <div class="total">
                    <span>Próxima cuota</span>
                    <strong>$320.000</strong>
                  </div>
                </div>

                <div class="notice notice-success">
                  <span data-icon="check-circle"></span>
                  <div><strong>¡Estás al día!</strong> No tienes pagos vencidos. Tu próxima cuota vence el 5 de diciembre.</div>
                </div>

                <button class="btn btn-soft btn-block mt-16" type="button" data-toast="Certificado de paz y salvo generado (demo).">
                  <span data-icon="file-check"></span>
                  Descargar paz y salvo
                </button>
              </div>
            </article>
          </div>
        </section>

        <!-- ========================= MI PERFIL ========================= -->
        <section class="content-section" id="perfil">
          <div class="section-head">
            <div>
              <h2>Mi perfil</h2>
              <p>Información personal y datos de contacto</p>
            </div>
            <div class="section-actions">
              <button class="btn btn-outline btn-sm" type="button" data-toast="Contraseña actualizada (demo).">
                <span data-icon="lock"></span>
                Cambiar contraseña
              </button>
            </div>
          </div>

          <div class="profile-grid">
            <article class="card profile-card">
              <span class="avatar avatar-teal"><?= e(initials($usuarioActual['nombre'])) ?></span>
              <h3><?= e($usuarioActual['nombre']) ?></h3>
              <p class="profile-role">Estudiante<?= $cursoActual ? ' · ' . e($cursoActual['nombre_curso']) : '' ?></p>
              <span class="badge badge-success">Cuenta activa</span>

              <div class="info-list">
                <div class="info-row"><span>Documento</span><strong>—</strong></div>
                <div class="info-row"><span>Correo</span><strong><?= e($usuarioActual['correo']) ?></strong></div>
                <div class="info-row"><span>Teléfono</span><strong>No registrado</strong></div>
                <div class="info-row"><span>Programa</span><strong><?= $cursoActual ? e($cursoActual['nombre_curso']) . ' · Nivel ' . e($cursoActual['nivel']) : 'Sin curso' ?></strong></div>
                <div class="info-row"><span>Nivel</span><strong><?= e($cursoActual['nivel'] ?? '—') ?></strong></div>
                <div class="info-row"><span>Ingreso</span><strong><?= e(format_date($fechaIngreso)) ?></strong></div>
              </div>

              <div class="mini-stats">
                <div class="mini-stat">
                  <strong>4.3</strong>
                  <span>Promedio</span>
                </div>
                <div class="mini-stat">
                  <strong>94%</strong>
                  <span>Asistencia</span>
                </div>
                <div class="mini-stat">
                  <strong><?= e($cursoActual['nivel'] ?? '—') ?></strong>
                  <span>Nivel</span>
                </div>
              </div>

              <div class="student-actions">
                <button class="btn btn-outline btn-sm" type="button" data-toast="Certificado disponible próximamente (demo).">
                  <span data-icon="download"></span>
                  Certificado
                </button>
                <button class="btn btn-soft btn-sm" type="button" data-toast="Ficha del estudiante descargada (demo).">
                  <span data-icon="file-text"></span>
                  Mi ficha
                </button>
              </div>
            </article>

            <article class="card profile-form">
              <div class="card-header">
                <div>
                  <h3>Datos personales</h3>
                  <p>Mantén tu información actualizada para recibir comunicaciones</p>
                </div>
                <span class="badge badge-primary">Verificado</span>
              </div>
              <div class="card-body">
                <form>
                  <div class="form-grid">
                    <div class="field">
                      <label class="label" for="profileName">Nombres</label>
                      <input class="input" id="profileName" type="text" value="<?= e($nombrePila) ?>" autocomplete="given-name">
                    </div>
                    <div class="field">
                      <label class="label" for="profileLast">Apellidos</label>
                      <input class="input" id="profileLast" type="text" value="<?= e($apellidos) ?>" autocomplete="family-name">
                    </div>
                    <div class="field">
                      <label class="label" for="profileDoc">Documento de identidad</label>
                      <input class="input" id="profileDoc" type="text" value="" placeholder="No registrado" readonly>
                    </div>
                    <div class="field">
                      <label class="label" for="profileBirth">Fecha de nacimiento</label>
                      <input class="input" id="profileBirth" type="date" value="">
                    </div>
                    <div class="field">
                      <label class="label" for="profileEmail">Correo electrónico</label>
                      <input class="input" id="profileEmail" type="email" value="<?= e($usuarioActual['correo']) ?>" autocomplete="email">
                    </div>
                    <div class="field">
                      <label class="label" for="profilePhone">Teléfono</label>
                      <input class="input" id="profilePhone" type="tel" value="" placeholder="No registrado" autocomplete="tel">
                    </div>
                    <div class="field col-span-2">
                      <label class="label" for="profileAddress">Dirección</label>
                      <input class="input" id="profileAddress" type="text" value="" placeholder="No registrada" autocomplete="street-address">
                    </div>
                    <div class="field">
                      <label class="label" for="profileContact">Contacto de emergencia</label>
                      <input class="input" id="profileContact" type="text" value="" placeholder="No registrado">
                    </div>
                    <div class="field">
                      <label class="label" for="profileCity">Ciudad</label>
                      <input class="input" id="profileCity" type="text" value="" placeholder="No registrada" autocomplete="address-level2">
                    </div>
                  </div>

                  <div class="divider"></div>

                  <h4 class="mb-8">Preferencias de comunicación</h4>
                  <div class="flex flex-wrap gap-24">
                    <label class="checkbox"><input type="checkbox" checked> Correo electrónico</label>
                    <label class="checkbox"><input type="checkbox" checked> Mensajes de texto</label>
                    <label class="checkbox"><input type="checkbox"> Llamadas telefónicas</label>
                  </div>

                  <div class="flex flex-between flex-wrap gap-12 mt-24">
                    <span class="text-muted text-xs">Última actualización: 12 oct. 2026</span>
                    <div class="flex gap-8">
                      <button class="btn btn-ghost" type="reset">Cancelar</button>
                      <button class="btn btn-primary" type="button" data-toast="Perfil actualizado correctamente (demo).">
                        <span data-icon="check"></span>
                        Guardar cambios
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </article>
          </div>
        </section>
      </main>
    </div>
  </div>

  <!-- Reportar pago (demo) -->
  <div class="modal-backdrop" id="reportPaymentModal" data-success="Pago reportado. La academia validará tu comprobante (demo).">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="reportPaymentTitle">
      <div class="modal-head">
        <div>
          <h3 id="reportPaymentTitle">Reportar pago</h3>
          <p>Adjunta tu comprobante para que la academia lo valide.</p>
        </div>
        <button class="modal-close" type="button" data-modal-close aria-label="Cerrar">
          <span data-icon="x"></span>
        </button>
      </div>

      <form>
        <div class="modal-body">
          <div class="form-grid">
            <div class="field col-span-2">
              <label class="label" for="reportConcept">Concepto</label>
              <select class="select" id="reportConcept" required>
                <option>Mensualidad de diciembre</option>
                <option>Mensualidad de noviembre</option>
                <option>Matrícula</option>
                <option>Certificación</option>
                <option>Otro</option>
              </select>
            </div>
            <div class="field">
              <label class="label" for="reportAmount">Monto (COP)</label>
              <input class="input" id="reportAmount" type="number" placeholder="320000" min="0" required>
            </div>
            <div class="field">
              <label class="label" for="reportMethod">Método de pago</label>
              <select class="select" id="reportMethod" required>
                <option>Transferencia</option>
                <option>PSE</option>
                <option>Tarjeta</option>
                <option>Efectivo</option>
              </select>
            </div>
            <div class="field">
              <label class="label" for="reportDate">Fecha del pago</label>
              <input class="input" id="reportDate" type="date" value="2026-11-05" required>
            </div>
            <div class="field">
              <label class="label" for="reportRef">Referencia</label>
              <input class="input" id="reportRef" type="text" placeholder="Número de transacción">
            </div>
            <div class="field col-span-2">
              <label class="label" for="reportFile">Comprobante</label>
              <input class="input" id="reportFile" type="file" accept="image/*,.pdf">
            </div>
          </div>

          <div class="notice notice-primary mt-16">
            <span data-icon="info"></span>
            <div>El pago quedará en estado <strong>En revisión</strong> hasta que el área financiera valide el comprobante.</div>
          </div>
        </div>

        <div class="modal-foot">
          <button class="btn btn-ghost" type="button" data-modal-close>Cancelar</button>
          <button class="btn btn-primary" type="submit">
            <span data-icon="upload"></span>
            Enviar comprobante
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="js/shell.js?v=3"></script>
  <script src="js/main.js?v=3"></script>
</body>
</html>
