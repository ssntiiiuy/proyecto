<?php
require_once 'Conexion.php';

class Mascota
{
    private ?string $id;
    private ?string $nombre;
    private ?string $especie;
    private ?string $raza;
    private ?string $fechaNacimiento;
    private ?string $ciDueño;

    public function __construct(?string $id = null, ?string $nombre = null, ?string $especie = null, ?string $raza = null, ?string $fechaNacimiento = null, ?string $ciDueño = null)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->especie = $especie;
        $this->raza = $raza;
        $this->fechaNacimiento = $fechaNacimiento;
        $this->ciDueño = $ciDueño;
    }

    public function getId(): string
    {
        return $this->id ?? '';
    }

    public function getNombre(): string
    {
        return $this->nombre ?? '';
    }

    public function getEspecie(): string
    {
        return $this->especie ?? '';
    }

    public function getRaza(): string
    {
        return $this->raza ?? '';
    }

    public function getFechaNacimiento(): string
    {
        return $this->fechaNacimiento ?? '';
    }

    public function getCiDueño(): string
    {
        return $this->ciDueño ?? '';
    }

    public function getTelefono(): string
    {
        return $this->telefono ?? '';
    }

    public function getDireccion(): string
    {
        return $this->direccion ?? '';
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    public function setEspecie(string $especie): void
    {
        $this->especie = $especie;
    }

    public function setRaza(string $raza): void
    {
        $this->raza = $raza;
    }

    public function setFechaNacimiento(string $fechaNacimiento): void
    {
        $this->fechaNacimiento = $fechaNacimiento;
    }

    public function setCiDueño(string $ciDueño): void
    {
        $this->ciDueño = $ciDueño;
    }

    public static function listadoMascotas(): array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sqlMascota = "SELECT m.IdMascota, m.Nombre, m.Raza, m.FechaNacimiento, m.Especie, c.CI, u.Nombre AS NombreDueño, u.Apellido AS ApellidoDueño
                   FROM MASCOTA m
                   INNER JOIN CLIENTE c ON m.CI = c.CI
                   INNER JOIN USUARIO u ON c.CI = u.CI";
            $stmtMascota = $conexion->query($sqlMascota);
            $mascotas = $stmtMascota->fetchAll(PDO::FETCH_ASSOC);

            return ['mascotas' => $mascotas];

        } catch (PDOException $e) {
            echo "Error al cargar mascotas: " . $e->getMessage();
            return [
                'mascotas' => []
            ];
        }
    }

    public function crearMascota(string $nombre, string $especie, string $raza, string $fechaNacimiento, string $ciDueño): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sqlLogin = "INSERT INTO MASCOTA (Nombre, Raza, FechaNacimiento, Especie, CI)
                VALUES (:nombre, :raza, :fechaNacimiento, :especie, :ci)";
            $stmtLogin = $conexion->prepare($sqlLogin);
            $stmtLogin->execute([
                ':nombre' => $nombre,
                ':raza' => $raza,
                ':fechaNacimiento' => $fechaNacimiento,
                ':especie' => $especie,
                ':ci' => $ciDueño
            ]);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
