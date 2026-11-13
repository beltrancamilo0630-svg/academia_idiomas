<?php
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/config/database.php';

$correo = '';
$error_login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($correo === '' || $password === '') {
        $error_login = 'Ingresa tu correo y tu contraseña.';
    } else {
        try {
            $database = new Database();
            $conn = $database->conectar();

            // Busca al usuario por correo y obtiene el nombre de su rol.
            $stmt = $conn->prepare(
                "SELECT u.id_usuario, u.nombre, u.correo, u.contrasena, u.id_rol, r.nombre_rol
                 FROM usuarios u
                 LEFT JOIN roles r ON r.id_rol = u.id_rol
                 WHERE u.correo = :correo
                 LIMIT 1"
            );
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                $hash = (string) $usuario['contrasena'];

                // Acepta contraseñas hasheadas y, por compatibilidad, texto plano.
                if (password_verify($password, $hash) || hash_equals($hash, $password)) {
                    $idRol = (int) $usuario['id_rol'];
                    $nombreRol = strtolower(trim((string) ($usuario['nombre_rol'] ?? '')));

                    // El nombre del rol viene de la tabla roles mediante id_rol.
                    $rolesPorNombre = [
                        'admin' => 'admin',
                        'administrador' => 'admin',
                        'profesor' => 'profesor',
                        'docente' => 'profesor',
                        'estudiante' => 'estudiante',
                        'alumno' => 'estudiante',
                    ];

                    $rol = $rolesPorNombre[$nombreRol] ?? null;

                    // Respaldo por id_rol si la tabla roles no tiene el nombre esperado.
                    if ($rol === null) {
                        switch ($idRol) {
                            case 1:
                                $rol = 'admin';
                                break;
                            case 2:
                                $rol = 'profesor';
                                break;
                            case 3:
                                $rol = 'estudiante';
                                break;
                        }
                    }

                    if ($rol !== null) {
                        session_regenerate_id(true);
                        $_SESSION['id_usuario'] = (int) $usuario['id_usuario'];
                        $_SESSION['id_rol'] = $idRol;
                        $_SESSION['usuario'] = $usuario['nombre'];
                        $_SESSION['rol'] = $rol;

                        $destinos = [
                            'admin' => 'admin.php',
                            'profesor' => 'profesor.php',
                            'estudiante' => 'estudiante.php',
                        ];

                        redirect_to($destinos[$rol]);
                    }

                    $error_login = 'Tu usuario no tiene un rol válido. Contacta al administrador.';
                } else {
                    $error_login = 'Correo o contraseña incorrectos.';
                }
            } else {
                $error_login = 'Correo o contraseña incorrectos.';
            }
        } catch (PDOException $e) {
            $error_login = 'No se pudo conectar con la base de datos. Intenta más tarde.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Ingresa al sistema académico de la Academia de Idiomas: consulta cursos, horarios, notas, asistencia y pagos.">
  <meta name="theme-color" content="#0F172A">
  <title>Ingresar | Academia de Idiomas</title>
  <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/landing.css">
</head>
<body>
  <main class="login-page">
    <section class="login-visual" aria-label="Presentación de la Academia de Idiomas">
      <a class="site-logo" href="index.html" aria-label="Volver al sitio de la Academia de Idiomas">
        <span class="brand-mark">A</span>
        <span class="site-logo-text">
          <strong>Academia de Idiomas</strong>
          <span>Sistema académico</span>
        </span>
      </a>

      <div class="login-quote">
        <span class="quote-mark"><span data-icon="quote"></span></span>
        <blockquote>“Un idioma no se estudia: se vive. Aquí lo practicas desde la primera clase.”</blockquote>
        <p>Gestiona tus cursos, revisa tu progreso y mantén tus pagos al día desde un solo lugar.</p>
      </div>

      <div class="login-visual-foot">
        <div class="login-visual-stat">
          <strong>+1.200</strong>
          <span>Estudiantes formados</span>
        </div>
        <div class="login-visual-stat">
          <strong>4</strong>
          <span>Idiomas disponibles</span>
        </div>
        <div class="login-visual-stat">
          <strong>98%</strong>
          <span>Satisfacción</span>
        </div>
      </div>
    </section>

    <section class="login-panel">
      <div class="login-card">
        <a class="site-logo login-mobile-brand" href="index.html" aria-label="Volver al sitio">
          <span class="brand-mark">A</span>
          <span class="site-logo-text">
            <strong>Academia de Idiomas</strong>
            <span>Sistema académico</span>
          </span>
        </a>

        <span class="eyebrow no-line">Portal académico</span>
        <h1>Bienvenido de nuevo</h1>
        <p>Ingresa con tu cuenta para consultar tus cursos, notas, horarios y pagos.</p>

        <form class="login-form" action="login.php" method="POST">
          <div class="field">
            <label class="label" for="email">Correo electrónico</label>
            <div class="input-icon">
              <span data-icon="mail"></span>
              <input class="input" id="email" name="email" type="email" value="<?= htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') ?>" placeholder="nombre@academia.edu" autocomplete="email" required>
            </div>
          </div>

          <div class="field">
            <label class="label" for="password">Contraseña</label>
            <div class="input-icon">
              <span data-icon="lock"></span>
              <input class="input" id="password" name="password" type="password" placeholder="Tu contraseña" autocomplete="current-password" style="padding-right:48px" required>
              <button class="password-toggle" type="button" id="passwordToggle" aria-label="Mostrar contraseña">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
          </div>

          <?php if ($error_login !== ''): ?>
            <p class="error-text" id="loginError" role="alert"><?= htmlspecialchars($error_login, ENT_QUOTES, 'UTF-8') ?></p>
          <?php else: ?>
            <p class="error-text" id="loginError" role="alert" hidden></p>
          <?php endif; ?>

          <div class="login-form-row">
            <label class="checkbox">
              <input type="checkbox" name="remember" checked>
              Recordarme
            </label>
            <a href="#" data-toast="Te enviaremos un enlace de recuperación (demo).">¿Olvidaste tu contraseña?</a>
          </div>

          <button class="btn btn-primary btn-block" type="submit">
            Iniciar sesión
            <span data-icon="arrow-right"></span>
          </button>
        </form>

        <div class="login-divider">Acceso rápido de prueba</div>

        <p class="demo-title">Selecciona un perfil para completar el formulario:</p>
        <div class="demo-list">
          <a class="demo-link" href="#" data-demo-email="laura.gomez@academia.edu" data-demo-password="1234">
            <span class="icon-circle"><span data-icon="shield"></span></span>
            <span class="demo-link-info">
              <strong>Administración</strong>
              <span>Laura Gómez · gestión general</span>
            </span>
            <span data-icon="chevron-right"></span>
          </a>

          <a class="demo-link" href="#" data-demo-email="carlos.mendoza@academia.edu" data-demo-password="1234">
            <span class="icon-circle violet"><span data-icon="presentation"></span></span>
            <span class="demo-link-info">
              <strong>Profesor</strong>
              <span>Carlos Mendoza · grupos y notas</span>
            </span>
            <span data-icon="chevron-right"></span>
          </a>

          <a class="demo-link" href="#" data-demo-email="valentina.rios@academia.edu" data-demo-password="1234">
            <span class="icon-circle success"><span data-icon="graduation-cap"></span></span>
            <span class="demo-link-info">
              <strong>Estudiante</strong>
              <span>Valentina Ríos · portal personal</span>
            </span>
            <span data-icon="chevron-right"></span>
          </a>
        </div>

        <p class="login-note">
          <span data-icon="info"></span>
          Cuentas de prueba con contraseña <strong>1234</strong>. Selecciona un perfil para autocompletar el acceso.
        </p>

        <a class="login-back" href="index.html">
          <span data-icon="arrow-left"></span>
          Volver al sitio web
        </a>
      </div>
    </section>
  </main>

  <script src="js/shell.js?v=3"></script>
  <script src="js/main.js?v=3"></script>
</body>
</html>
