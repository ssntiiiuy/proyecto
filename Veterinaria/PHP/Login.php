<?php


class Login
{
    private ?string $correo;
    private ?string $contraseña;

    public function __construct(?string $correo = null, ?string $contraseña = null)
    {
        $this->correo = $correo;
        $this->contraseña = $contraseña;
    }

    public function getCorreo(): string
    {
        return $this->correo;
    }

    public function getContraseña(): string
    {
        return $this->contraseña;
    }

    public function setCorreo(string $correo): void
    {
        $this->correo = $correo;
    }

    public function setContraseña(string $contraseña): void
    {
        $this->contraseña = $contraseña;
    }

    public function presentarse(): void
    {
        echo "Correo: " . $this->getCorreo() . "\n";
        echo "Contraseña: " . $this->getContraseña() . "\n";
    }

    public function registrar()
    {
        $conexion = new Conexion();
        $conectar = $conexion->establecerConexion();
        $sqlquery = "SELECT usuario FROM login WHERE usuario = :usuario";
        $statement = $conectar->prepare($sqlquery);
        $statement->execute(["usuario" => $this->correo]);
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

    public function validar(string $correo, string $contraseña): bool
    {
        $conectar = new Conexion();
        $sqlquery = "SELECT usuario, contraseña FROM login WHERE usuario = :usuario";
        $statement = $conectar->EstablecerConexion()->prepare($sqlquery);
        $statement->execute(["usuario" => $correo]);

        $userFromBd = $statement->fetch();
        if ($userFromBd) {
            echo $userFromBd['usuario'] . " ha sido encontrado en nuestros registros \n";
            if (password_verify($contraseña, $userFromBd["contraseña"])) {
                echo "Verificado, adelante (Aquí se accederá al index del usuario pertinente)\n";
                return true;
            } else {
                echo "Hubo un error, intente nuevamente \n";
            }
        } else {
            echo "No existe el usuario \n";
        }
        return false;
    }
}