<?php
require_once 'Conexion.php';
require_once 'Login.php';

class Usuario
{
    private ?string $ci;
    private ?string $nombre;
    private ?string $apellido;
    private ?string $telefono;
    private ?string $direccion;
    private ?string $tipoUsuario;

    public function __construct(?string $ci = null, ?string $nombre = null, ?string $apellido = null, ?string $telefono = null, ?string $direccion = null, ?string $tipoUsuario = null)
    {
        $this->ci = $ci;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->telefono = $telefono;
        $this->direccion = $direccion;
        $this->tipoUsuario = $tipoUsuario;
    }

    public function getCi(): string
    {
        return $this->ci ?? '';
    }

    public function getNombre(): string
    {
        return $this->nombre ?? '';
    }

    public function getApellido(): string
    {
        return $this->apellido ?? '';
    }

    public function getTelefono(): string
    {
        return $this->telefono ?? '';
    }

    public function getDireccion(): string
    {
        return $this->direccion ?? '';
    }

    public function setCi(string $ci): void
    {
        $this->ci = $ci;
    }

    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    public function setApellido(string $apellido): void
    {
        $this->apellido = $apellido;
    }

    public function setTelefono(string $telefono): void
    {
        $this->telefono = $telefono;
    }

    public function setDireccion(string $direccion): void
    {
        $this->direccion = $direccion;
    }

    public function presentarse(): void
    {
        echo "CI: " . $this->getCi() . "\n";
        echo "Nombre: " . $this->getNombre() . "\n";
        echo "Apellido: " . $this->getApellido() . "\n";
        echo "Teléfono: " . $this->getTelefono() . "\n";
        echo "Dirección: " . $this->getDireccion() . "\n";
    }

    public function registrar(string $ci, string $nombre, string $apellido, string $correo, string $telefono, string $direccion, string $contraseña, string $tipoUsuario): bool {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $passHash = password_hash($contraseña, PASSWORD_DEFAULT);

            $sqlLogin = "INSERT INTO login (correo, tipoUsuario, contrasena) VALUES (:correo, :tipoUsuario, :pass)";
            $stmt = $conexion->prepare($sqlLogin);
            $stmt->execute([
                ':correo' => $correo,
                ':tipoUsuario' => $tipoUsuario,
                ':pass'   => $passHash
            ]);

            $sqlUsuario = "INSERT INTO usuarios (ci, nombre, apellido, telefono, direccion, correo_login)
                           VALUES (:ci, :nombre, :apellido, :telefono, :direccion, :correo)";
            $stmtUsuario = $conexion->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':ci'        => $ci,
                ':nombre'    => $nombre,
                ':apellido'  => $apellido,
                ':telefono'  => $telefono,
                ':direccion' => $direccion,
                ':correo'    => $correo,
            ]);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}