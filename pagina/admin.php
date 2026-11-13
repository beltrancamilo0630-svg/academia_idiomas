<?php
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/config/database.php';

require_role([1]);

$database = new Database();
$conn = $database->conectar();

$flash = get_flash();

// Crear usuario desde el formulario del modal.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_user') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'La sesión expiró. Intenta de nuevo.');
        redirect_to('admin.php#usuarios');
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $idRol = (int) ($_POST['id_rol'] ?? 0);

    if ($nombre === '' || $correo === '' || $contrasena === '' || $idRol <= 0) {
        set_flash('danger', 'Completa todos los campos del usuario.');
        redirect_to('admin.php#usuarios');
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        set_flash('danger', 'El correo no tiene un formato válido.');
        redirect_to('admin.php#usuarios');
    }

    if (strlen($contrasena) < 4) {
        set_flash('danger', 'La contraseña debe tener al menos 4 caracteres.');
        redirect_to('admin.php#usuarios');
    }

    $stmt = $conn->prepare('SELECT COUNT(*) FROM roles WHERE id_rol = ?');
    $stmt->execute([$idRol]);

    if ((int) $stmt->fetchColumn() === 0) {
        set_flash('danger', 'El rol seleccionado no existe.');
        redirect_to('admin.php#usuarios');
    }

    try {
        $stmt = $conn->prepare(
            'INSERT INTO usuarios (nombre, correo, contrasena, id_rol) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $nombre,
            $correo,
            password_hash($contrasena, PASSWORD_DEFAULT),
            $idRol,
        ]);
        set_flash('success', 'Usuario creado correctamente.');
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            set_flash('danger', 'Ya existe un usuario con ese correo.');
        } else {
            set_flash('danger', 'No se pudo crear el usuario.');
        }
    }

    redirect_to('admin.php#usuarios');
}

// Eliminar usuario con confirmación desde la tabla.
if (($_GET['action'] ?? '') === 'delete' && isset($_GET['id'])) {
    if (!verify_csrf($_GET['token'] ?? null)) {
        set_flash('danger', 'La sesión expiró. Intenta de nuevo.');
        redirect_to('admin.php#usuarios');
    }

    $idEliminar = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if (!$idEliminar || $idEliminar <= 0) {
        set_flash('danger', 'El usuario indicado no es válido.');
        redirect_to('admin.php#usuarios');
    }

    if ($idEliminar === (int) $_SESSION['id_usuario']) {
        set_flash('danger', 'No puedes eliminar tu propia cuenta.');
        redirect_to('admin.php#usuarios');
    }

    try {
        $stmt = $conn->prepare('SELECT id_usuario, nombre, id_rol FROM usuarios WHERE id_usuario = ?');
        $stmt->execute([$idEliminar]);
        $usuarioEliminar = $stmt->fetch();

        if (!$usuarioEliminar) {
            set_flash('danger', 'El usuario no existe.');
            redirect_to('admin.php#usuarios');
        }

        if ((int) $usuarioEliminar['id_rol'] === 1) {
            $stmt = $conn->prepare('SELECT COUNT(*) FROM usuarios WHERE id_rol = 1');
            $stmt->execute();

            if ((int) $stmt->fetchColumn() <= 1) {
                set_flash('danger', 'No puedes eliminar al último administrador.');
                redirect_to('admin.php#usuarios');
            }
        }

        $conn->beginTransaction();

        $stmt = $conn->prepare(
            'DELETE p FROM pagos p
             INNER JOIN matriculas m ON m.id_matricula = p.id_matricula
             WHERE m.id_usuario = ?'
        );
        $stmt->execute([$idEliminar]);

        $stmt = $conn->prepare('DELETE FROM matriculas WHERE id_usuario = ?');
        $stmt->execute([$idEliminar]);

        $stmt = $conn->prepare('UPDATE cursos SET id_profesor = NULL WHERE id_profesor = ?');
        $stmt->execute([$idEliminar]);

        $stmt = $conn->prepare('DELETE FROM usuarios WHERE id_usuario = ?');
        $stmt->execute([$idEliminar]);

        $conn->commit();
        set_flash('success', 'Usuario eliminado correctamente.');
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        set_flash('danger', 'No se pudo eliminar el usuario. Revisa sus matrículas asociadas.');
    }

    redirect_to('admin.php#usuarios');
}

$usuarios = $conn->query(
    'SELECT u.id_usuario, u.nombre, u.correo, u.id_rol, r.nombre_rol
     FROM usuarios u
     LEFT JOIN roles r ON r.id_rol = u.id_rol
     ORDER BY u.id_usuario'
)->fetchAll();

$roles = $conn->query('SELECT id_rol, nombre_rol FROM roles ORDER BY id_rol')->fetchAll();

$stmt = $conn->prepare('SELECT id_usuario, nombre, correo, id_rol FROM usuarios WHERE id_usuario = ?');
$stmt->execute([(int) $_SESSION['id_usuario']]);
$usuarioActual = $stmt->fetch() ?: [
    'nombre' => $_SESSION['usuario'],
    'correo' => '',
    'id_rol' => 1,
];

$totalUsuarios = count($usuarios);
$totalEstudiantes = 0;
$totalProfesores = 0;

foreach ($usuarios as $usuario) {
    if ((int) $usuario['id_rol'] === 2) {
        $totalProfesores++;
    } elseif ((int) $usuario['id_rol'] === 3) {
        $totalEstudiantes++;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Dashboard administrativo de la Academia de Idiomas.">
  <meta name="theme-color" content="#0F172A">
  <title>Dashboard | Academia de Idiomas</title>
  <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/layout.css">
  <link rel="stylesheet" href="css/pages.css">
</head>
<body data-role="admin" data-page="dashboard" data-title="Dashboard" data-subtitle="Resumen general · Miércoles, 5 de noviembre de 2026" data-user-name="<?= e($usuarioActual['nombre']) ?>" data-user-role="Administrador" data-user-initials="<?= e(initials($usuarioActual['nombre'])) ?>">
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
            <span class="eyebrow">Miércoles, 5 de noviembre</span>
            <h1>Buenos días, <?= e($_SESSION['usuario']) ?></h1>
            <p>Esto es lo que está pasando hoy en la academia. Tienes 3 pendientes por revisar.</p>
          </div>
          <div class="page-actions">
            <a class="btn btn-outline" href="finanzas.php#reportes">
              <span data-icon="download"></span>
              Descargar reporte
            </a>
            <button class="btn btn-primary" type="button" data-modal-open="paymentModal">
              <span data-icon="plus"></span>
              Registrar pago
            </button>
          </div>
        </div>

        <!-- KPIs -->
        <section class="kpi-grid" aria-label="Indicadores principales">
          <article class="kpi-card">
            <div class="kpi-top">
              <span class="kpi-label">Estudiantes activos</span>
              <span class="kpi-icon"><span data-icon="users"></span></span>
            </div>
            <div class="kpi-value">248</div>
            <div class="kpi-meta">
              <span class="kpi-trend up"><span data-icon="trending-up"></span> +12</span>
              este mes
            </div>
          </article>

          <article class="kpi-card">
            <div class="kpi-top">
              <span class="kpi-label">Cursos activos</span>
              <span class="kpi-icon violet"><span data-icon="book"></span></span>
            </div>
            <div class="kpi-value">18</div>
            <div class="kpi-meta">4 idiomas · 9 niveles disponibles</div>
          </article>

          <article class="kpi-card">
            <div class="kpi-top">
              <span class="kpi-label">Ingresos del mes</span>
              <span class="kpi-icon success"><span data-icon="wallet"></span></span>
            </div>
            <div class="kpi-value">$18.640.000</div>
            <div class="kpi-meta">
              <span class="kpi-trend up"><span data-icon="trending-up"></span> +8.4%</span>
              vs. octubre
            </div>
          </article>

          <article class="kpi-card">
            <div class="kpi-top">
              <span class="kpi-label">Cartera pendiente</span>
              <span class="kpi-icon warning"><span data-icon="alert-circle"></span></span>
            </div>
            <div class="kpi-value">$1.240.000</div>
            <div class="kpi-meta">6 estudiantes con saldo pendiente</div>
          </article>
        </section>

        <!-- Charts -->
        <section class="dash-grid">
          <article class="card">
            <div class="card-body">
              <div class="chart-head">
                <div>
                  <h3>Ingresos por mes</h3>
                  <p>Recaudo total · periodo 2026</p>
                </div>
                <div class="chart-total">
                  <strong>$18.640.000</strong>
                  <span>Noviembre</span>
                  <div class="kpi-trend up"><span data-icon="trending-up"></span> 8.4% vs. octubre</div>
                </div>
              </div>

              <div class="bar-chart" role="img" aria-label="Ingresos mensuales: abril 12.4 millones, mayo 13.1, junio 13.8, julio 14.2, agosto 15.6, septiembre 16.2, octubre 17.2, noviembre 18.64 millones de pesos">
                <div class="bar-col">
                  <div class="bar" style="height:62%"><span class="bar-value">$12.4M</span></div>
                  <span class="bar-label">Abr</span>
                </div>
                <div class="bar-col">
                  <div class="bar" style="height:68%"><span class="bar-value">$13.1M</span></div>
                  <span class="bar-label">May</span>
                </div>
                <div class="bar-col">
                  <div class="bar" style="height:72%"><span class="bar-value">$13.8M</span></div>
                  <span class="bar-label">Jun</span>
                </div>
                <div class="bar-col">
                  <div class="bar" style="height:75%"><span class="bar-value">$14.2M</span></div>
                  <span class="bar-label">Jul</span>
                </div>
                <div class="bar-col">
                  <div class="bar" style="height:82%"><span class="bar-value">$15.6M</span></div>
                  <span class="bar-label">Ago</span>
                </div>
                <div class="bar-col">
                  <div class="bar" style="height:86%"><span class="bar-value">$16.2M</span></div>
                  <span class="bar-label">Sep</span>
                </div>
                <div class="bar-col">
                  <div class="bar" style="height:91%"><span class="bar-value">$17.2M</span></div>
                  <span class="bar-label">Oct</span>
                </div>
                <div class="bar-col is-current">
                  <div class="bar" style="height:100%"><span class="bar-value">$18.64M</span></div>
                  <span class="bar-label">Nov</span>
                </div>
              </div>
            </div>
          </article>

          <article class="card">
            <div class="card-header">
              <div>
                <h3>Estudiantes por idioma</h3>
                <p>Distribución del periodo actual</p>
              </div>
            </div>
            <div class="card-body">
              <div class="donut-wrap">
                <div class="donut" role="img" aria-label="Inglés 62%, francés 18%, portugués 12%, italiano 8%">
                  <div class="donut-center">
                    <strong>248</strong>
                    <span>estudiantes</span>
                  </div>
                </div>
                <div class="donut-legend">
                  <div class="legend-item">
                    <span class="legend-dot" style="background:#0284C7"></span>
                    <span>Inglés</span>
                    <strong>154 · 62%</strong>
                  </div>
                  <div class="legend-item">
                    <span class="legend-dot" style="background:#7C3AED"></span>
                    <span>Francés</span>
                    <strong>45 · 18%</strong>
                  </div>
                  <div class="legend-item">
                    <span class="legend-dot" style="background:#F59E0B"></span>
                    <span>Portugués</span>
                    <strong>30 · 12%</strong>
                  </div>
                  <div class="legend-item">
                    <span class="legend-dot" style="background:#10B981"></span>
                    <span>Italiano</span>
                    <strong>19 · 8%</strong>
                  </div>
                </div>
              </div>
            </div>
          </article>
        </section>

        <!-- Classes + alerts -->
        <section class="dash-grid-2">
          <article class="card">
            <div class="card-header">
              <div>
                <h3>Clases de hoy</h3>
                <p>Miércoles, 5 de noviembre · 4 clases programadas</p>
              </div>
              <a class="link" href="academico.php#horarios">
                Ver horario
                <span data-icon="arrow-right"></span>
              </a>
            </div>
            <div class="card-body">
              <div class="class-list">
                <div class="class-row">
                  <div class="class-time">6:00 p. m.<span>8:00 p. m.</span></div>
                  <div class="class-info">
                    <strong>Inglés B1 · Grupo B1-03</strong>
                    <span>Carlos Mendoza · Salón 204 · 14/18 estudiantes</span>
                  </div>
                  <span class="badge badge-success">En curso</span>
                </div>
                <div class="class-row">
                  <div class="class-time">6:00 p. m.<span>8:00 p. m.</span></div>
                  <div class="class-info">
                    <strong>Francés A1 · Grupo A1-02</strong>
                    <span>Sophie Martin · Salón 108 · 11/16 estudiantes</span>
                  </div>
                  <span class="badge badge-success">En curso</span>
                </div>
                <div class="class-row">
                  <div class="class-time">8:00 p. m.<span>10:00 p. m.</span></div>
                  <div class="class-info">
                    <strong>Inglés A1 · Grupo A1-05</strong>
                    <span>Carlos Mendoza · Salón 204 · 16/18 estudiantes</span>
                  </div>
                  <span class="badge badge-primary">Próxima</span>
                </div>
                <div class="class-row">
                  <div class="class-time">8:00 p. m.<span>10:00 p. m.</span></div>
                  <div class="class-info">
                    <strong>Portugués A1 · Grupo A1-01</strong>
                    <span>Ana Torres · Salón 302 · 12/16 estudiantes</span>
                  </div>
                  <span class="badge badge-primary">Próxima</span>
                </div>
              </div>
            </div>
          </article>

          <article class="card">
            <div class="card-header">
              <div>
                <h3>Alertas y pendientes</h3>
                <p>Requieren tu atención</p>
              </div>
              <span class="badge badge-warning">3</span>
            </div>
            <div class="card-body">
              <div class="alert-list">
                <div class="alert-item">
                  <span class="icon-circle danger"><span data-icon="alert-triangle"></span></span>
                  <div>
                    <strong>Inglés B2 alcanzó el cupo máximo</strong>
                    <p>18 de 18 estudiantes. No se permiten nuevas matrículas.</p>
                    <a href="academico.php#cursos">Ver curso <span data-icon="arrow-right"></span></a>
                  </div>
                </div>
                <div class="alert-item">
                  <span class="icon-circle warning"><span data-icon="clock"></span></span>
                  <div>
                    <strong>3 pagos vencen esta semana</strong>
                    <p>$450.000 en cartera por vencer.</p>
                    <a href="finanzas.php#cartera">Gestionar cartera <span data-icon="arrow-right"></span></a>
                  </div>
                </div>
                <div class="alert-item">
                  <span class="icon-circle"><span data-icon="file-check"></span></span>
                  <div>
                    <strong>2 comprobantes por validar</strong>
                    <p>Pagos reportados por estudiantes.</p>
                    <a href="finanzas.php#cartera">Revisar comprobantes <span data-icon="arrow-right"></span></a>
                  </div>
                </div>
              </div>
            </div>
          </article>
        </section>

        <!-- Payments + quick actions -->
        <section class="dash-grid-2">
          <article class="card">
            <div class="card-header">
              <div>
                <h3>Últimos pagos</h3>
                <p>Movimientos registrados hoy</p>
              </div>
              <a class="link" href="finanzas.php#pagos">
                Ver todos
                <span data-icon="arrow-right"></span>
              </a>
            </div>
            <div class="table-wrap">
              <table class="table table-compact">
                <thead>
                  <tr>
                    <th>Estudiante</th>
                    <th>Concepto</th>
                    <th>Método</th>
                    <th>Monto</th>
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
                    <td>Mensualidad nov.</td>
                    <td>Transferencia</td>
                    <td class="num"><strong>$320.000</strong></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                  </tr>
                  <tr>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-violet avatar-sm">AF</span>
                        <div><strong>Andrés F. Castro</strong><span>Francés A2</span></div>
                      </div>
                    </td>
                    <td>Mensualidad nov.</td>
                    <td>Efectivo</td>
                    <td class="num"><strong>$300.000</strong></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                  </tr>
                  <tr>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-warning avatar-sm">LO</span>
                        <div><strong>Luisa F. Ortiz</strong><span>Portugués A1</span></div>
                      </div>
                    </td>
                    <td>Matrícula</td>
                    <td>Tarjeta</td>
                    <td class="num"><strong>$100.000</strong></td>
                    <td><span class="badge badge-warning">Pendiente</span></td>
                  </tr>
                  <tr>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-success avatar-sm">SV</span>
                        <div><strong>Sebastián Vargas</strong><span>Italiano A1</span></div>
                      </div>
                    </td>
                    <td>Mensualidad nov.</td>
                    <td>Transferencia</td>
                    <td class="num"><strong>$280.000</strong></td>
                    <td><span class="badge badge-danger">Rechazado</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </article>

          <article class="card">
            <div class="card-header">
              <div>
                <h3>Acciones rápidas</h3>
                <p>Atajos de gestión</p>
              </div>
            </div>
            <div class="card-body">
              <div class="quick-grid">
                <a class="quick-btn" href="academico.php#estudiantes">
                  <span class="icon-circle"><span data-icon="user-check"></span></span>
                  <div><strong>Nuevo estudiante</strong><span>Registrar alumno</span></div>
                </a>
                <a class="quick-btn" href="academico.php#matriculas">
                  <span class="icon-circle violet"><span data-icon="clipboard-check"></span></span>
                  <div><strong>Nueva matrícula</strong><span>Asignar cupo</span></div>
                </a>
                <a class="quick-btn" href="academico.php#horarios">
                  <span class="icon-circle success"><span data-icon="calendar"></span></span>
                  <div><strong>Programar clase</strong><span>Validar horario</span></div>
                </a>
                <button class="quick-btn" type="button" data-modal-open="paymentModal">
                  <span class="icon-circle warning"><span data-icon="banknote"></span></span>
                  <div><strong>Registrar pago</strong><span>Actualizar cartera</span></div>
                </button>
              </div>

              <div class="notice notice-primary mt-16">
                <span data-icon="info"></span>
                <div><strong>Regla de negocio activa:</strong> el sistema bloquea matrículas cuando el curso alcanza su cupo máximo.</div>
              </div>
            </div>
          </article>
        </section>

        <!-- ========================= USUARIOS ========================= -->
        <section class="content-section" id="usuarios">
          <div class="section-head">
            <div>
              <h2>Usuarios del sistema</h2>
              <p><?= $totalUsuarios ?> usuarios registrados · <?= $totalEstudiantes ?> estudiantes · <?= $totalProfesores ?> profesores</p>
            </div>
            <div class="section-actions">
              <button class="btn btn-primary btn-sm" type="button" data-modal-open="userModal">
                <span data-icon="plus"></span>
                Nuevo usuario
              </button>
            </div>
          </div>

          <div class="card">
            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>ID rol</th>
                    <th class="text-right">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($usuarios)): ?>
                    <tr>
                      <td colspan="5">No hay usuarios registrados.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($usuarios as $usuario): ?>
                      <?php
                        $rolClase = 'badge-neutral';
                        if ((int) $usuario['id_rol'] === 1) {
                            $rolClase = 'badge-primary';
                        } elseif ((int) $usuario['id_rol'] === 2) {
                            $rolClase = 'badge-violet';
                        } elseif ((int) $usuario['id_rol'] === 3) {
                            $rolClase = 'badge-success';
                        }
                        $esPropio = (int) $usuario['id_usuario'] === (int) $_SESSION['id_usuario'];
                      ?>
                      <tr>
                        <td>
                          <div class="cell-main">
                            <span class="avatar avatar-teal avatar-sm"><?= e(initials($usuario['nombre'])) ?></span>
                            <div>
                              <strong><?= e($usuario['nombre']) ?></strong>
                              <span>ID <?= (int) $usuario['id_usuario'] ?></span>
                            </div>
                          </div>
                        </td>
                        <td><?= e($usuario['correo']) ?></td>
                        <td><span class="badge <?= $rolClase ?>"><?= e($usuario['nombre_rol'] ?: 'Sin rol') ?></span></td>
                        <td class="num"><?= (int) $usuario['id_rol'] ?></td>
                        <td>
                          <div class="actions">
                            <?php if ($esPropio): ?>
                              <span class="badge badge-neutral">Tu cuenta</span>
                            <?php else: ?>
                              <a class="row-action" href="admin.php?action=delete&amp;id=<?= (int) $usuario['id_usuario'] ?>&amp;token=<?= e(csrf_token()) ?>" aria-label="Eliminar usuario" title="Eliminar usuario" onclick="return confirm('¿Eliminar este usuario? También se eliminarán sus matrículas y pagos asociados.');">
                                <span data-icon="trash"></span>
                              </a>
                            <?php endif; ?>
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

  <!-- Registrar pago (demo) -->
  <div class="modal-backdrop" id="paymentModal" data-success="Pago registrado correctamente (demo).">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="paymentTitle">
      <div class="modal-head">
        <div>
          <h3 id="paymentTitle">Registrar pago</h3>
          <p>Vista previa del formulario de recaudo.</p>
        </div>
        <button class="modal-close" type="button" data-modal-close aria-label="Cerrar">
          <span data-icon="x"></span>
        </button>
      </div>

      <form>
        <div class="modal-body">
          <div class="form-grid">
            <div class="field col-span-2">
              <label class="label" for="payStudent">Estudiante</label>
              <select class="select" id="payStudent" required>
                <option value="">Selecciona un estudiante</option>
                <option>Valentina Ríos · Inglés B1</option>
                <option>Andrés Felipe Castro · Francés A2</option>
                <option>Luisa Fernanda Ortiz · Portugués A1</option>
                <option>Sebastián Vargas · Italiano A1</option>
              </select>
            </div>
            <div class="field">
              <label class="label" for="payConcept">Concepto</label>
              <select class="select" id="payConcept" required>
                <option>Mensualidad</option>
                <option>Matrícula</option>
                <option>Certificación</option>
                <option>Otro</option>
              </select>
            </div>
            <div class="field">
              <label class="label" for="payAmount">Monto (COP)</label>
              <input class="input" id="payAmount" type="number" placeholder="320000" min="0" required>
            </div>
            <div class="field">
              <label class="label" for="payMethod">Método de pago</label>
              <select class="select" id="payMethod" required>
                <option>Transferencia</option>
                <option>Efectivo</option>
                <option>Tarjeta</option>
                <option>PSE</option>
              </select>
            </div>
            <div class="field">
              <label class="label" for="payDate">Fecha</label>
              <input class="input" id="payDate" type="date" value="2026-11-05" required>
            </div>
            <div class="field col-span-2">
              <label class="label" for="payRef">Referencia o comprobante</label>
              <input class="input" id="payRef" type="text" placeholder="Número de transacción, recibo o soporte">
            </div>
          </div>

          <div class="notice notice-primary mt-16">
            <span data-icon="info"></span>
            <div>El saldo del estudiante se actualizará automáticamente al guardar el pago.</div>
          </div>
        </div>

        <div class="modal-foot">
          <button class="btn btn-ghost" type="button" data-modal-close>Cancelar</button>
          <button class="btn btn-primary" type="submit">
            <span data-icon="check"></span>
            Guardar pago
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Nuevo usuario -->
  <div class="modal-backdrop" id="userModal">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="userModalTitle">
      <div class="modal-head">
        <div>
          <h3 id="userModalTitle">Nuevo usuario</h3>
          <p>Crea una cuenta y asígnale un rol del sistema.</p>
        </div>
        <button class="modal-close" type="button" data-modal-close aria-label="Cerrar">
          <span data-icon="x"></span>
        </button>
      </div>

      <form action="admin.php" method="POST" data-real-form>
        <input type="hidden" name="action" value="create_user">
        <?= csrf_field() ?>

        <div class="modal-body">
          <div class="form-grid">
            <div class="field col-span-2">
              <label class="label" for="userName">Nombre completo *</label>
              <input class="input" id="userName" name="nombre" type="text" maxlength="100" placeholder="Ej. Ana María Torres" required>
            </div>
            <div class="field">
              <label class="label" for="userEmail">Correo electrónico *</label>
              <input class="input" id="userEmail" name="correo" type="email" maxlength="100" placeholder="nombre@academia.edu" required>
            </div>
            <div class="field">
              <label class="label" for="userPassword">Contraseña *</label>
              <input class="input" id="userPassword" name="contrasena" type="password" minlength="4" placeholder="Mínimo 4 caracteres" required>
            </div>
            <div class="field col-span-2">
              <label class="label" for="userRole">Rol *</label>
              <select class="select" id="userRole" name="id_rol" required>
                <option value="">Selecciona un rol</option>
                <?php foreach ($roles as $rol): ?>
                  <option value="<?= (int) $rol['id_rol'] ?>"><?= e($rol['nombre_rol']) ?> (ID <?= (int) $rol['id_rol'] ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="notice notice-primary mt-16">
            <span data-icon="info"></span>
            <div>La contraseña se guarda cifrada con <code>password_hash()</code>.</div>
          </div>
        </div>

        <div class="modal-foot">
          <button class="btn btn-ghost" type="button" data-modal-close>Cancelar</button>
          <button class="btn btn-primary" type="submit">
            <span data-icon="check"></span>
            Crear usuario
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="js/shell.js?v=3"></script>
  <script src="js/main.js?v=3"></script>
</body>
</html>
