<?php

require_once __DIR__ . '/config/database.php';

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->conectar();
    }

    // Método para iniciar sesión buscando por correo
    public function login($correo, $password) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE correo = :correo LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":correo", $correo);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Acepta contraseñas hasheadas y, por compatibilidad, texto plano.
            $hash = (string) $row['contrasena'];
            if (password_verify($password, $hash) || hash_equals($hash, $password)) {
                return $row; // Retorna los datos del usuario si coincide
            }
        }
        return false;
    }
}

?>