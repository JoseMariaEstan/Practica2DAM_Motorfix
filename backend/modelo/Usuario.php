<?php
require_once __DIR__ . "/conexion.php";
class Usuario {
    private $id_usuario;
    private $email;
    private $password;
    private $nombre;
    private $apellidos;
    private $rol;
    private $activo;

    //iniciar las variables con valor null, nos permite crear el objeto sin pasarle parametros
    public function __construct($id_usuario = null, $email = null, $password = null, $nombre = null,
                                $apellidos = null, $rol = 'cliente', $activo = 1) {
        $this->id_usuario = $id_usuario;
        $this->email      = $email;
        $this->password   = $password;
        $this->nombre     = $nombre;
        $this->apellidos  = $apellidos;
        $this->rol        = $rol;
        $this->activo     = $activo;
    }

    //Lo usa el registro para enlazar la ficha de cliente con el usuario recien creado
    public function getIdUsuario(){
        return $this->id_usuario;
    }

    public function insertUsuario() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "INSERT INTO usuario (email, password, nombre, apellidos, rol, activo)
                    VALUES (:email, :password, :nombre, :apellidos, :rol, :activo)";
            $stmt = $conexion->prepare($sql);

            $resultado = $stmt->execute([
                ':email'     => $this->email,
                ':password'  => password_hash($this->password, PASSWORD_BCRYPT),
                ':nombre'    => $this->nombre,
                ':apellidos' => $this->apellidos,
                ':rol'       => $this->rol,
                ':activo'    => $this->activo
            ]);

            //guardamos el id generado, lo necesitamos para crear la ficha de cliente en el registro
            $this->id_usuario = $conexion->lastInsertId();
            return $resultado;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Devuelve el usuario por su id (sin la contraseña)
    public function obtenerUsuario() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "SELECT id_usuario, email, nombre, apellidos, rol, activo, fecha_alta
                    FROM usuario WHERE id_usuario = :id_usuario";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id_usuario' => $this->id_usuario]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Busca un usuario activo por email (se usa en el login). Incluye la contraseña cifrada
    public function obtenerPorEmail() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "SELECT id_usuario, email, password, nombre, apellidos, rol
                    FROM usuario WHERE email = :email AND activo = 1";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':email' => $this->email]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Comprueba si la contraseña escrita coincide con el hash de la base de datos
    public function verificarPassword($passwordEscrita, $hashGuardado) {
        return password_verify($passwordEscrita, $hashGuardado);
    }

    // Actualiza los datos del usuario (NO la contraseña, eso va en updateContra)
    public function updateUsuario() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "UPDATE usuario SET email = :email, nombre = :nombre, apellidos = :apellidos,
                    rol = :rol, activo = :activo WHERE id_usuario = :id_usuario";
            $stmt = $conexion->prepare($sql);

            return $stmt->execute([
                ':id_usuario' => $this->id_usuario,
                ':email'      => $this->email,
                ':nombre'     => $this->nombre,
                ':apellidos'  => $this->apellidos,
                ':rol'        => $this->rol,
                ':activo'     => $this->activo
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Cambia la contraseña, cifrándola de nuevo
    public function updateContra() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "UPDATE usuario SET password = :password WHERE id_usuario = :id_usuario";
            $stmt = $conexion->prepare($sql);

            return $stmt->execute([
                ':id_usuario' => $this->id_usuario,
                ':password'   => password_hash($this->password, PASSWORD_BCRYPT)
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteUsuario() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "DELETE FROM usuario WHERE id_usuario = :id_usuario";
            $stmt = $conexion->prepare($sql);

            return $stmt->execute([':id_usuario' => $this->id_usuario]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
//TODO
//Metodo igual al de cliente para listar todos los usuarios en oficina