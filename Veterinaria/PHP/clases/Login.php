<?php
require_once __DIR__ . '/Conexion.php';

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

            $sqlquery = "SELECT * FROM login WHERE Correo = :correo";
            $statement = $conectar->prepare($sqlquery);
            $statement->execute([":correo" => $correo]);

            $userFromBd = $statement->fetch(PDO::FETCH_ASSOC);

            if ($userFromBd) {

                $hashEnBD = $userFromBd['Contraseña'];

                if (password_verify($contraseña, $hashEnBD)) {
                    return true;
                }
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /* para "Olvidé mi contraseña": true si el correo está en LOGIN */
    public function existeCorreo(string $correo): bool
    {
        try {
            $conectar = (new Conexion())->establecerConexion();

            $statement = $conectar->prepare("SELECT Correo FROM login WHERE Correo = :correo");
            $statement->execute([":correo" => $correo]);

            if ($statement->fetch(PDO::FETCH_ASSOC)) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /* guarda la contraseña nueva igual que en Usuario::registrar() (password_hash) */
    public function cambiarContrasena(string $correo, string $nueva): bool
    {
        try {
            $conectar = (new Conexion())->establecerConexion();

            $statement = $conectar->prepare("UPDATE login SET Contraseña = :pass WHERE Correo = :correo");
            $statement->execute([
                ":pass"   => password_hash($nueva, PASSWORD_DEFAULT),
                ":correo" => $correo
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}