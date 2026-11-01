<?php
require_once __DIR__ . '/Conexion.php';

class Login
{
    private ?string $correo;
    private ?string $contraseña;

    // cargo los datos iniciales en las propiedades de este objeto
    public function __construct(?string $correo = null, ?string $contraseña = null)
    {
        $this->correo = $correo;
        $this->contraseña = $contraseña;
    }

    // devuelvo el valor de correo que tiene el objeto
    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    // devuelvo el valor de contraseña que tiene el objeto
    public function getContraseña(): ?string
    {
        return $this->contraseña;
    }

    // cambio el valor de correo dentro del objeto
    public function setCorreo(string $correo): void
    {
        $this->correo = $correo;
    }

    // cambio el valor de contraseña dentro del objeto
    public function setContraseña(string $contraseña): void
    {
        $this->contraseña = $contraseña;
    }

    // busco el correo y comparo la contraseña ingresada con el hash guardado
    public function validar(string $correo, string $contraseña): bool
    {
        try {
            $conectar = (new Conexion())->establecerConexion();

            $sqlquery = "SELECT * FROM LOGIN WHERE Correo = :correo";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $statement = $conectar->prepare($sqlquery);
            $statement->execute([":correo" => $correo]);

            // traigo una sola fila con los nombres de sus columnas como claves
            $userFromBd = $statement->fetch(PDO::FETCH_ASSOC);

            if ($userFromBd) {

                $hashEnBD = $userFromBd['Contraseña'];

                // password_verify compara la contraseña escrita con el hash, no con texto plano
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

            $statement = $conectar->prepare("SELECT Correo FROM LOGIN WHERE Correo = :correo");
            $statement->execute([":correo" => $correo]);

            // traigo una sola fila con los nombres de sus columnas como claves
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

            $statement = $conectar->prepare("UPDATE LOGIN SET Contraseña = :pass WHERE Correo = :correo");
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
