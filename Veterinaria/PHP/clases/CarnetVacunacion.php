<?php
require_once __DIR__ . '/Conexion.php';

class CarnetVacunacion
{
    private ?int $id;
    private ?int $idMascota;
    private ?string $fechaProximaVacunacion;
    private ?string $fechaUltimaVacunacion;
    private ?string $tipoVacunacion;
    private ?string $desparasitacion;
    private ?string $tipoDesparasitacion;

    public function __construct(?int $id = null, ?int $idMascota = null, ?string $fechaProximaVacunacion = null, ?string $fechaUltimaVacunacion = null, ?string $tipoVacunacion = null, ?string $desparasitacion = null, ?string $tipoDesparasitacion = null) {
        $this->id = $id;
        $this->idMascota = $idMascota;
        $this->fechaProximaVacunacion = $fechaProximaVacunacion;
        $this->fechaUltimaVacunacion = $fechaUltimaVacunacion;
        $this->tipoVacunacion = $tipoVacunacion;
        $this->desparasitacion = $desparasitacion;
        $this->tipoDesparasitacion = $tipoDesparasitacion;
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function getIdMascota(): int
    {
        return $this->idMascota ?? 0;
    }

    public function getFechaProximaVacunacion(): string
    {
        return $this->fechaProximaVacunacion ?? '';
    }

    public function getFechaUltimaVacunacion(): string
    {
        return $this->fechaUltimaVacunacion ?? '';
    }

    public function getTipoVacunacion(): string
    {
        return $this->tipoVacunacion ?? '';
    }

    public function getDesparasitacion(): string
    {
        return $this->desparasitacion ?? '';
    }

    public function getTipoDesparasitacion(): string
    {
        return $this->tipoDesparasitacion ?? '';
    }

    public function setFechaProximaVacunacion(string $fechaProximaVacunacion): void
    {
        $this->fechaProximaVacunacion = $fechaProximaVacunacion;
    }

    public function setFechaUltimaVacunacion(string $fechaUltimaVacunacion): void
    {
        $this->fechaUltimaVacunacion = $fechaUltimaVacunacion;
    }

    public function setTipoVacunacion(string $tipoVacunacion): void
    {
        $this->tipoVacunacion = $tipoVacunacion;
    }

    public function setDesparasitacion(string $desparasitacion): void
    {
        $this->desparasitacion = $desparasitacion;
    }

    public function setTipoDesparasitacion(string $tipoDesparasitacion): void
    {
        $this->tipoDesparasitacion = $tipoDesparasitacion;
    }

    public function crearCarnet(int $idMascota, string $fProx, string $fUlt, string $tVac, string $desp, string $tDesp): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "INSERT INTO CARNET_VACUNACION (IdMascota, FechaProximaVacunacion, FechaUltimaVacunacion, TipoVacunacion, Desparasitacion, TipoDesparasitacion)
                    VALUES (:idMascota, :fProx, :fUlt, :tVac, :desp, :tDesp)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':idMascota' => $idMascota,
                ':fProx'     => $fProx,
                ':fUlt'      => $fUlt,
                ':tVac'      => $tVac,
                ':desp'      => $desp,
                ':tDesp'     => $tDesp
            ]);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function buscarPorId(int $idCarnet): ?array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "SELECT * FROM CARNET_VACUNACION WHERE IdCarnetVacunacion = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id' => $idCarnet]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return $resultado ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function editarCarnet(int $idCarnet, string $fProx, string $fUlt, string $tVac, string $desp, string $tDesp): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "UPDATE CARNET_VACUNACION
                    SET FechaProximaVacunacion = :fProx, FechaUltimaVacunacion = :fUlt, TipoVacunacion = :tVac, Desparasitacion = :desp, TipoDesparasitacion = :tDesp
                    WHERE IdCarnetVacunacion = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':fProx'  => $fProx,
                ':fUlt'   => $fUlt,
                ':tVac'   => $tVac,
                ':desp'   => $desp,
                ':tDesp'  => $tDesp,
                ':id'     => $idCarnet
            ]);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminarCarnet(int $idCarnet): string
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "DELETE FROM CARNET_VACUNACION WHERE IdCarnetVacunacion = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id' => $idCarnet]);

            return 'eliminado';
        } catch (PDOException $e) {
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451) {
                return 'bloqueado';
            }
            return 'error';
        }
    }

    public static function listadoPorMascota(string $nombreMascota = ''): array
    {
        $conexion = (new Conexion())->establecerConexion();
        $sql = "SELECT cv.*, m.IdMascota, m.Nombre AS NombreMascota, m.Especie, m.Raza
                FROM CARNET_VACUNACION cv
                INNER JOIN MASCOTA m ON cv.IdMascota = m.IdMascota
                WHERE m.Nombre LIKE :busqueda
                ORDER BY cv.FechaProximaVacunacion ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':busqueda' => '%' . $nombreMascota . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}