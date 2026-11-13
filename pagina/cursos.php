<?php
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/config/database.php';

require_role([1]);

$database = new Database();
$conn = $database->conectar();

$flash = get_flash();

// Crear curso.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_course') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'La sesión expiró. Intenta de nuevo.');
        redirect_to('cursos.php');
    }

    $nombreCurso = trim($_POST['nombre_curso'] ?? '');
    $nivel = trim($_POST['nivel'] ?? '');
    $precio = (float) ($_POST['precio'] ?? 0);
    $idProfesor = (int) ($_POST['id_profesor'] ?? 0);

    if ($nombreCurso === '' || $nivel === '' || $precio <= 0) {
        set_flash('danger', 'Completa el nombre, el nivel y un precio mayor que cero.');
        redirect_to('cursos.php');
    }

    if ($idProfesor > 0) {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM usuarios WHERE id_usuario = ? AND id_rol = 2');
        $stmt->execute([$idProfesor]);

        if ((int) $stmt->fetchColumn() === 0) {
            set_flash('danger', 'El profesor seleccionado no es válido.');
            redirect_to('cursos.php');
        }
    }

    try {
        $stmt = $conn->prepare(
            'INSERT INTO cursos (nombre_curso, nivel, precio, id_profesor) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $nombreCurso,
            $nivel,
            $precio,
            $idProfesor > 0 ? $idProfesor : null,
        ]);
        set_flash('success', 'Curso creado correctamente.');
    } catch (PDOException $e) {
        set_flash('danger', 'No se pudo crear el curso.');
    }

    redirect_to('cursos.php');
}

// Eliminar curso y sus matrículas/pagos asociados.
if (($_GET['action'] ?? '') === 'delete' && isset($_GET['id'])) {
    if (!verify_csrf($_GET['token'] ?? null)) {
        set_flash('danger', 'La sesión expiró. Intenta de nuevo.');
        redirect_to('cursos.php');
    }

    $idCurso = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if (!$idCurso || $idCurso <= 0) {
        set_flash('danger', 'El curso indicado no es válido.');
        redirect_to('cursos.php');
    }

    try {
        $stmt = $conn->prepare('SELECT id_curso, nombre_curso FROM cursos WHERE id_curso = ?');
        $stmt->execute([$idCurso]);

        if (!$stmt->fetch()) {
            set_flash('danger', 'El curso no existe.');
            redirect_to('cursos.php');
        }

        $conn->beginTransaction();

        $stmt = $conn->prepare(
            'DELETE p FROM pagos p
             INNER JOIN matriculas m ON m.id_matricula = p.id_matricula
             WHERE m.id_curso = ?'
        );
        $stmt->execute([$idCurso]);

        $stmt = $conn->prepare('DELETE FROM matriculas WHERE id_curso = ?');
        $stmt->execute([$idCurso]);

        $stmt = $conn->prepare('DELETE FROM cursos WHERE id_curso = ?');
        $stmt->execute([$idCurso]);

        $conn->commit();
        set_flash('success', 'Curso eliminado correctamente.');
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        set_flash('danger', 'No se pudo eliminar el curso.');
    }

    redirect_to('cursos.php');
}

$cursos = $conn->query(
    'SELECT c.id_curso, c.nombre_curso, c.nivel, c.precio, c.id_profesor,
            u.nombre AS profesor,
            COUNT(m.id_matricula) AS inscritos
     FROM cursos c
     LEFT JOIN usuarios u ON u.id_usuario = c.id_profesor
     LEFT JOIN matriculas m ON m.id_curso = c.id_curso
     GROUP BY c.id_curso, c.nombre_curso, c.nivel, c.precio, c.id_profesor, u.nombre
     ORDER BY c.id_curso'
)->fetchAll();

$profesores = $conn->query(
    'SELECT id_usuario, nombre FROM usuarios WHERE id_rol = 2 ORDER BY nombre'
)->fetchAll();

$stmt = $conn->prepare('SELECT nombre FROM usuarios WHERE id_usuario = ?');
$stmt->execute([(int) $_SESSION['id_usuario']]);
$usuarioActual = $stmt->fetch() ?: ['nombre' => $_SESSION['usuario']];

$totalInscritos = 0;
foreach ($cursos as $curso) {
    $totalInscritos += (int) $curso['inscritos'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Gestión de cursos de la Academia de Idiomas.">
  <meta name="theme-color" content="#0F172A">
  <title>Cursos | Academia de Idiomas</title>
  <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/layout.css">
  <link rel="stylesheet" href="css/pages.css">
</head>
<body data-role="admin" data-page="cursos" data-title="Cursos" data-subtitle="Oferta académica y asignación de profesores" data-user-name="<?= e($usuarioActual['nombre']) ?>" data-user-role="Administrador" data-user-initials="<?= e(initials($usuarioActual['nombre'])) ?>">
  <a class="skip-link" href="#main">Saltar al contenido</a>

  <div class="app-shell">
    <aside class="sidebar" id="sidebar"></aside>

    <div class="app-main">
      <header class="topbar" id="topbar"></header>

      <main class="page" id="main">
        <?php if ($flash): ?>
          <div class="notice notice-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> mb-16">
            <span data-icon="<?= $flash['type'] === 'success' ? 'check-circle' : 'alert-circle' ?>"></span>
            <div><?= e($flash['message']) ?></div>
          </div>
        <?php endif; ?>

        <div class="page-head">
          <div>
            <span class="eyebrow">Oferta académica</span>
            <h1>Gestión de cursos</h1>
            <p>Crea cursos, asigna un profesor y consulta cuántos estudiantes tiene cada grupo.</p>
          </div>
          <div class="page-actions">
            <button class="btn btn-primary" type="button" data-modal-open="courseModal">
              <span data-icon="plus"></span>
              Nuevo curso
            </button>
          </div>
        </div>

        <section class="kpi-grid" aria-label="Resumen de cursos">
          <article class="kpi-card">
            <div class="kpi-top">
              <span class="kpi-label">Cursos registrados</span>
              <span class="kpi-icon"><span data-icon="book"></span></span>
            </div>
            <div class="kpi-value"><?= count($cursos) ?></div>
            <div class="kpi-meta">Oferta académica actual</div>
          </article>

          <article class="kpi-card">
            <div class="kpi-top">
              <span class="kpi-label">Estudiantes inscritos</span>
              <span class="kpi-icon violet"><span data-icon="users"></span></span>
            </div>
            <div class="kpi-value"><?= $totalInscritos ?></div>
            <div class="kpi-meta">Matrículas activas</div>
          </article>

          <article class="kpi-card">
            <div class="kpi-top">
              <span class="kpi-label">Profesores disponibles</span>
              <span class="kpi-icon success"><span data-icon="presentation"></span></span>
            </div>
            <div class="kpi-value"><?= count($profesores) ?></div>
            <div class="kpi-meta">Con rol de profesor</div>
          </article>
        </section>

        <section class="content-section">
          <div class="section-head">
            <div>
              <h2>Listado de cursos</h2>
              <p>Administra la oferta y elimina los cursos que ya no se dictarán.</p>
            </div>
          </div>

          <div class="card">
            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Curso</th>
                    <th>Nivel</th>
                    <th>Profesor</th>
                    <th>Precio</th>
                    <th>Inscritos</th>
                    <th class="text-right">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($cursos)): ?>
                    <tr>
                      <td colspan="6">No hay cursos registrados. Usa “Nuevo curso” para crear el primero.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($cursos as $curso): ?>
                      <tr>
                        <td>
                          <div class="cell-main">
                            <span class="flag flag-<?= e(flag_class($curso['nombre_curso'])) ?>"></span>
                            <div>
                              <strong><?= e($curso['nombre_curso']) ?></strong>
                              <span>ID <?= (int) $curso['id_curso'] ?></span>
                            </div>
                          </div>
                        </td>
                        <td><span class="badge badge-primary"><?= e($curso['nivel']) ?></span></td>
                        <td><?= e($curso['profesor'] ?: 'Sin asignar') ?></td>
                        <td class="num"><strong><?= e(format_cop($curso['precio'])) ?></strong></td>
                        <td class="num"><?= (int) $curso['inscritos'] ?></td>
                        <td>
                          <div class="actions">
                            <a class="row-action" href="cursos.php?action=delete&amp;id=<?= (int) $curso['id_curso'] ?>&amp;token=<?= e(csrf_token()) ?>" aria-label="Eliminar curso" title="Eliminar curso" onclick="return confirm('¿Eliminar este curso? También se eliminarán sus matrículas y pagos asociados.');">
                              <span data-icon="trash"></span>
                            </a>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </main>
    </div>
  </div>

  <!-- Nuevo curso -->
  <div class="modal-backdrop" id="courseModal">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="courseModalTitle">
      <div class="modal-head">
        <div>
          <h3 id="courseModalTitle">Nuevo curso</h3>
          <p>Configura el nombre, el nivel, el precio y el profesor responsable.</p>
        </div>
        <button class="modal-close" type="button" data-modal-close aria-label="Cerrar">
          <span data-icon="x"></span>
        </button>
      </div>

      <form action="cursos.php" method="POST" data-real-form>
        <input type="hidden" name="action" value="create_course">
        <?= csrf_field() ?>

        <div class="modal-body">
          <div class="form-grid">
            <div class="field col-span-2">
              <label class="label" for="courseName">Nombre del curso *</label>
              <input class="input" id="courseName" name="nombre_curso" type="text" maxlength="100" placeholder="Ej. Inglés B2" required>
            </div>
            <div class="field">
              <label class="label" for="courseLevel">Nivel *</label>
              <select class="select" id="courseLevel" name="nivel" required>
                <option value="">Selecciona un nivel</option>
                <option value="A1">A1</option>
                <option value="A2">A2</option>
                <option value="B1">B1</option>
                <option value="B2">B2</option>
                <option value="C1">C1</option>
              </select>
            </div>
            <div class="field">
              <label class="label" for="coursePrice">Precio mensual (COP) *</label>
              <input class="input" id="coursePrice" name="precio" type="number" min="1" step="1" placeholder="320000" required>
            </div>
            <div class="field col-span-2">
              <label class="label" for="courseTeacher">Profesor asignado</label>
              <select class="select" id="courseTeacher" name="id_profesor">
                <option value="0">Sin asignar</option>
                <?php foreach ($profesores as $profesor): ?>
                  <option value="<?= (int) $profesor['id_usuario'] ?>"><?= e($profesor['nombre']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="notice notice-primary mt-16">
            <span data-icon="info"></span>
            <div>El curso quedará disponible para matrículas y aparecerá en el portal del profesor asignado.</div>
          </div>
        </div>

        <div class="modal-foot">
          <button class="btn btn-ghost" type="button" data-modal-close>Cancelar</button>
          <button class="btn btn-primary" type="submit">
            <span data-icon="check"></span>
            Crear curso
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="js/shell.js?v=3"></script>
  <script src="js/main.js?v=3"></script>
</body>
</html>
