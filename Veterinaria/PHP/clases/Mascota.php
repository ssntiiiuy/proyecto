<?php
require_once __DIR__ . '/Conexion.php';

class Mascota
{
    private ?int $id;
    private ?string $nombre;
    private ?string $especie;
    private ?string $raza;
    private ?DateTime $fechaNacimiento;
    private ?int $ciDueño;

    public function __construct(?int $id = null, ?string $nombre = null, ?string $especie = null, ?string $raza = null, ?DateTime $fechaNacimiento = null, ?int $ciDueño = null)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->especie = $especie;
        $this->raza = $raza;
        $this->fechaNacimiento = $fechaNacimiento;
        $this->ciDueño = $ciDueño;
    }

    public function getId(): int
    {
        return $this->id ?? 0;
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

    public function setFechaNacimiento(DateTime $fechaNacimiento): void
    {
        $this->fechaNacimiento = $fechaNacimiento;
    }

    public function setCiDueño(int $ciDueño): void
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

    public function crearMascota(string $nombre, string $especie, string $raza, ?string $fechaNacimiento, string $ciDueño): bool
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

    public static function buscarPorId(int $id): ?array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sqlMascota = "SELECT IdMascota, Nombre, Raza, FechaNacimiento, Especie, CI
                   FROM MASCOTA
                   WHERE IdMascota = :id";
            $stmtMascota = $conexion->prepare($sqlMascota);
            $stmtMascota->execute([':id' => $id]);
            $mascota = $stmtMascota->fetch(PDO::FETCH_ASSOC);

            return $mascota ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function editarMascota(int $id, string $nombre, string $especie, string $raza, ?string $fechaNacimiento, string $ciDueño): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sqlMascota = "UPDATE MASCOTA
                SET Nombre = :nombre, Raza = :raza, FechaNacimiento = :fechaNacimiento, Especie = :especie, CI = :ci
                WHERE IdMascota = :id";
            $stmtMascota = $conexion->prepare($sqlMascota);
            $stmtMascota->execute([
                ':nombre' => $nombre,
                ':raza' => $raza,
                ':fechaNacimiento' => $fechaNacimiento,
                ':especie' => $especie,
                ':ci' => $ciDueño,
                ':id' => $id
            ]);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /* devuelve 'eliminado', 'bloqueado' (tiene datos asociados) o 'error' */
    public function eliminarMascota(int $id): string
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sqlMascota = "DELETE FROM MASCOTA WHERE IdMascota = :id";
            $stmtMascota = $conexion->prepare($sqlMascota);
            $stmtMascota->execute([':id' => $id]);

            return 'eliminado';
        } catch (PDOException $e) {
            /* 1451: otra tabla (CITA, HISTORIAL_CLINICO, CARNET_VACUNACION, ACCEDE) usa esta mascota */
            if ($e->errorInfo[1] == 1451) {
                return 'bloqueado';
            }
            return 'error';
        }
    }
}
