<?php

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/Usuario.php';

class AuthController {

    public function procesarLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $correo = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $usuario = new Usuario();
        $resultado = $usuario->login($correo, $password);

        if (!$resultado) {
            redirect_to('../login.php');
        }

        $idRol = (int) $resultado['id_rol'];

        switch ($idRol) {
            case 1:
                $rol = 'admin';
                $destino = '../admin.php';
                break;
            case 2:
                $rol = 'profesor';
                $destino = '../profesor.php';
                break;
            case 3:
                $rol = 'estudiante';
                $destino = '../estudiante.php';
                break;
            default:
                redirect_to('../login.php');
        }

        session_regenerate_id(true);
        $_SESSION['id_usuario'] = (int) $resultado['id_usuario'];
        $_SESSION['id_rol'] = $idRol;
        $_SESSION['usuario'] = $resultado['nombre'];
        $_SESSION['rol'] = $rol;

        redirect_to($destino);
    }
}

// Disparar la acción si se envía el formulario
if (isset($_POST['email'])) {
    $auth = new AuthController();
    $auth->procesarLogin();
}
