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

    // cargo los datos iniciales en las propiedades de este objeto
    public function __construct(?int $id = null, ?int $idMascota = null, ?string $fechaProximaVacunacion = null, ?string $fechaUltimaVacunacion = null, ?string $tipoVacunacion = null, ?string $desparasitacion = null, ?string $tipoDesparasitacion = null) {
        $this->id = $id;
        $this->idMascota = $idMascota;
        $this->fechaProximaVacunacion = $fechaProximaVacunacion;
        $this->fechaUltimaVacunacion = $fechaUltimaVacunacion;
        $this->tipoVacunacion = $tipoVacunacion;
        $this->desparasitacion = $desparasitacion;
        $this->tipoDesparasitacion = $tipoDesparasitacion;
    }

    // devuelvo el valor de id que tiene el objeto
    public function getId(): int
    {
        return $this->id ?? 0;
    }

    // devuelvo el valor de id mascota que tiene el objeto
    public function getIdMascota(): int
    {
        return $this->idMascota ?? 0;
    }

    // devuelvo el valor de fecha proxima vacunacion que tiene el objeto
    public function getFechaProximaVacunacion(): string
    {
        return $this->fechaProximaVacunacion ?? '';
    }

    // devuelvo el valor de fecha ultima vacunacion que tiene el objeto
    public function getFechaUltimaVacunacion(): string
    {
        return $this->fechaUltimaVacunacion ?? '';
    }

    // devuelvo el valor de tipo vacunacion que tiene el objeto
    public function getTipoVacunacion(): string
    {
        return $this->tipoVacunacion ?? '';
    }

    // devuelvo el valor de desparasitacion que tiene el objeto
    public function getDesparasitacion(): string
    {
        return $this->desparasitacion ?? '';
    }

    // devuelvo el valor de tipo desparasitacion que tiene el objeto
    public function getTipoDesparasitacion(): string
    {
        return $this->tipoDesparasitacion ?? '';
    }

    // cambio el valor de fecha proxima vacunacion dentro del objeto
    public function setFechaProximaVacunacion(string $fechaProximaVacunacion): void
    {
        $this->fechaProximaVacunacion = $fechaProximaVacunacion;
    }

    // cambio el valor de fecha ultima vacunacion dentro del objeto
    public function setFechaUltimaVacunacion(string $fechaUltimaVacunacion): void
    {
        $this->fechaUltimaVacunacion = $fechaUltimaVacunacion;
    }

    // cambio el valor de tipo vacunacion dentro del objeto
    public function setTipoVacunacion(string $tipoVacunacion): void
    {
        $this->tipoVacunacion = $tipoVacunacion;
    }

    // cambio el valor de desparasitacion dentro del objeto
    public function setDesparasitacion(string $desparasitacion): void
    {
        $this->desparasitacion = $desparasitacion;
    }

    // cambio el valor de tipo desparasitacion dentro del objeto
    public function setTipoDesparasitacion(string $tipoDesparasitacion): void
    {
        $this->tipoDesparasitacion = $tipoDesparasitacion;
    }

    // guardo las vacunas y la desparasitacion para esa mascota
    public function crearCarnet(int $idMascota, string $fProx, string $fUlt, string $tVac, string $desp, string $tDesp): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "INSERT INTO CARNET_VACUNACION (IdMascota, FechaProximaVacunacion, FechaUltimaVacunacion, TipoVacunacion, Desparasitacion, TipoDesparasitacion)
                    VALUES (:idMascota, :fProx, :fUlt, :tVac, :desp, :tDesp)";
            // preparo la consulta para pasar los valores por separado al ejecutarla
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

    // busco un registro de carnet vacunacion por su id; si no lo encuentro devuelvo null
    public static function buscarPorId(int $idCarnet): ?array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "SELECT * FROM CARNET_VACUNACION WHERE IdCarnetVacunacion = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id' => $idCarnet]);
            // traigo una sola fila con los nombres de sus columnas como claves
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return $resultado ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    // actualizo las fechas y los datos de ese carnet
    public function editarCarnet(int $idCarnet, string $fProx, string $fUlt, string $tVac, string $desp, string $tDesp): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "UPDATE CARNET_VACUNACION
                    SET FechaProximaVacunacion = :fProx, FechaUltimaVacunacion = :fUlt, TipoVacunacion = :tVac, Desparasitacion = :desp, TipoDesparasitacion = :tDesp
                    WHERE IdCarnetVacunacion = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
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

    // intento borrar el carnet y aviso si la base no lo permite
    public function eliminarCarnet(int $idCarnet): string
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "DELETE FROM CARNET_VACUNACION WHERE IdCarnetVacunacion = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id' => $idCarnet]);

            return 'eliminado';
        } catch (PDOException $e) {
            // este error indica que otro registro depende del que intento borrar
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451) {
                return 'bloqueado';
            }
            return 'error';
        }
    }

    // busco los registros por nombre de mascota y traigo tambien sus datos
    public static function listadoPorMascota(string $nombreMascota = ''): array
    {
        $conexion = (new Conexion())->establecerConexion();
        $sql = "SELECT cv.*, m.IdMascota, m.Nombre AS NombreMascota, m.Especie, m.Raza
                FROM CARNET_VACUNACION cv
                INNER JOIN MASCOTA m ON cv.IdMascota = m.IdMascota
                WHERE m.Nombre LIKE :busqueda
                ORDER BY cv.FechaProximaVacunacion ASC";
        // preparo la consulta para pasar los valores por separado al ejecutarla
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':busqueda' => '%' . $nombreMascota . '%']);
        // traigo todas las filas usando los nombres de las columnas como claves
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // traigo los registros que pertenecen a esa mascota, ordenados por fecha
    public static function listadoPorIdMascota(int $idMascota): array
{
    $conexion = (new Conexion())->establecerConexion();
    $sql = "SELECT * FROM CARNET_VACUNACION WHERE IdMascota = :id ORDER BY FechaProximaVacunacion ASC";
    // preparo la consulta para pasar los valores por separado al ejecutarla
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $idMascota]);
    // traigo todas las filas usando los nombres de las columnas como claves
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}