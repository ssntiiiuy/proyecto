<?php
require_once "Conexion.php";

class Login
{
    private ?string $correo;
    private ?string $contraseña;

    public function __construct(?string $correo = null, ?string $contraseña = null)
    {
        $this->correo = $correo;
        $this->contraseña = $contraseña;
    }

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function getContraseña(): ?string
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

    public function validar(string $correo, string $contraseña): bool
    {
        try {
            $conectar = (new Conexion())->establecerConexion();

            $sqlquery = "SELECT * FROM login WHERE correo = :correo";
            $statement = $conectar->prepare($sqlquery);
            $statement->execute([":correo" => $correo]);

            $userFromBd = $statement->fetch(PDO::FETCH_ASSOC);

            if ($userFromBd) {
                
                $hashEnBD = $userFromBd['contrasena'];

                if (password_verify($contraseña, $hashEnBD)) {
                    return true;
                }
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
}