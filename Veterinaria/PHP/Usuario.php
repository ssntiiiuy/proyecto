<?php
require_once 'Login.php';

class Usuario
{
    private ?string $ci;
    private ?string $nombre;
    private ?string $apellido;
    private ?string $telefono;
    private ?string $direccion;

    public function __construct(?string $ci = null, ?string $nombre = null, ?string $apellido = null, ?string $telefono = null, ?string $direccion = null)
    {
        $this->ci = $ci;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->telefono = $telefono;
        $this->direccion = $direccion;
    }

    public function getCi(): string
    {
        return $this->ci;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getApellido(): string
    {
        return $this->apellido;
    }

    public function getTelefono(): string
    {
        return $this->telefono;
    }

    public function getDireccion(): string
    {
        return $this->direccion;
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

    public function registrar()
    {
        $conexion = new Conexion();
        $conectar = $conexion->establecerConexion();
        $sqlquery = "SELECT usuario FROM login WHERE usuario = :usuario";
        $statement = $conectar->prepare($sqlquery);
        $statement->execute(["usuario" => $this->ci]);
        $usuarioEncontrado = $statement->fetch();

        if ($usuarioEncontrado == false) {
            $this->contraseña = password_hash($this->contraseña, PASSWORD_DEFAULT);
            $sqlinsert = "INSERT INTO login VALUES (?, ?)";
            $statement = $conectar->prepare($sqlinsert);
            if ($statement->execute(
                [$this->correo, $this->contraseña]
            )) {
                echo "Usuario registrado ";
            } else {
                echo "Algo salió mal al registrar el usuario \n";
            }
        } else {
            echo "El usuario existe, debes elegir otro nombre \n";
        }
    }

}
