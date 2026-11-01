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

    // cargo los datos iniciales en las propiedades de este objeto
    public function __construct(?int $id = null, ?string $nombre = null, ?string $especie = null, ?string $raza = null, ?DateTime $fechaNacimiento = null, ?int $ciDueño = null)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->especie = $especie;
        $this->raza = $raza;
        $this->fechaNacimiento = $fechaNacimiento;
        $this->ciDueño = $ciDueño;
    }

    // devuelvo el valor de id que tiene el objeto
    public function getId(): int
    {
        return $this->id ?? 0;
    }

    // devuelvo el valor de nombre que tiene el objeto
    public function getNombre(): string
    {
        return $this->nombre ?? '';
    }

    // devuelvo el valor de especie que tiene el objeto
    public function getEspecie(): string
    {
        return $this->especie ?? '';
    }

    // devuelvo el valor de raza que tiene el objeto
    public function getRaza(): string
    {
        return $this->raza ?? '';
    }

    // devuelvo el valor de fecha nacimiento que tiene el objeto
    public function getFechaNacimiento(): string
    {
        return $this->fechaNacimiento ?? '';
    }

    // devuelvo el valor de ci dueño que tiene el objeto
    public function getCiDueño(): string
    {
        return $this->ciDueño ?? '';
    }

    // cambio el valor de nombre dentro del objeto
    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    // cambio el valor de especie dentro del objeto
    public function setEspecie(string $especie): void
    {
        $this->especie = $especie;
    }

    // cambio el valor de raza dentro del objeto
    public function setRaza(string $raza): void
    {
        $this->raza = $raza;
    }

    // cambio el valor de fecha nacimiento dentro del objeto
    public function setFechaNacimiento(DateTime $fechaNacimiento): void
    {
        $this->fechaNacimiento = $fechaNacimiento;
    }

    // cambio el valor de ci dueño dentro del objeto
    public function setCiDueño(int $ciDueño): void
    {
        $this->ciDueño = $ciDueño;
    }

    // traigo las mascotas con los datos de sus dueños y aplico la busqueda
    public static function listadoMascotas(string $busqueda = ''): array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $condicionBusqueda = '';
            $params = [];
            if ($busqueda !== '') {
                $condicionBusqueda = " WHERE m.Nombre LIKE :busqueda1 OR c.CI LIKE :busqueda2
                                    OR u.Nombre LIKE :busqueda3 OR u.Apellido LIKE :busqueda4 ";
                $like = '%' . $busqueda . '%';
                $params = [':busqueda1' => $like, ':busqueda2' => $like, ':busqueda3' => $like, ':busqueda4' => $like];
            }

            $sqlMascota = "SELECT m.IdMascota, m.Nombre, m.Raza, m.FechaNacimiento, m.Especie, c.CI, u.Nombre AS NombreDueño, u.Apellido AS ApellidoDueño
               FROM MASCOTA m
               INNER JOIN CLIENTE c ON m.CI = c.CI
               INNER JOIN USUARIO u ON c.CI = u.CI
               $condicionBusqueda";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtMascota = $conexion->prepare($sqlMascota);
            $stmtMascota->execute($params);
            // traigo todas las filas usando los nombres de las columnas como claves
            $mascotas = $stmtMascota->fetchAll(PDO::FETCH_ASSOC);

            return ['mascotas' => $mascotas];
        } catch (PDOException $e) {
            echo "Error al cargar mascotas: " . $e->getMessage();
            return ['mascotas' => []];
        }
    }

    // guardo los datos de la mascota y la cedula de su dueño
    public function crearMascota(string $nombre, string $especie, string $raza, ?string $fechaNacimiento, string $ciDueño): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sqlLogin = "INSERT INTO MASCOTA (Nombre, Raza, FechaNacimiento, Especie, CI)
                VALUES (:nombre, :raza, :fechaNacimiento, :especie, :ci)";
            // preparo la consulta para pasar los valores por separado al ejecutarla
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

    // busco un registro de mascota por su id; si no lo encuentro devuelvo null
    public static function buscarPorId(int $id): ?array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sqlMascota = "SELECT IdMascota, Nombre, Raza, FechaNacimiento, Especie, CI
                   FROM MASCOTA
                   WHERE IdMascota = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtMascota = $conexion->prepare($sqlMascota);
            $stmtMascota->execute([':id' => $id]);
            // traigo una sola fila con los nombres de sus columnas como claves
            $mascota = $stmtMascota->fetch(PDO::FETCH_ASSOC);

            return $mascota ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    // actualizo los datos de la mascota que tiene ese id
    public function editarMascota(int $id, string $nombre, string $especie, string $raza, ?string $fechaNacimiento, string $ciDueño): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sqlMascota = "UPDATE MASCOTA
                SET Nombre = :nombre, Raza = :raza, FechaNacimiento = :fechaNacimiento, Especie = :especie, CI = :ci
                WHERE IdMascota = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
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
            // preparo la consulta para pasar los valores por separado al ejecutarla
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
    // traigo solo las mascotas de ese cliente con el orden elegido
    public static function listadoMascotasPorCliente(int $ci, string $orden = 'Nombre', string $dir = 'ASC'): array
    {

        $columnasValidas = ['Nombre', 'Especie', 'Raza', 'FechaNacimiento'];
        // solo acepto columnas conocidas para ordenar sin usar texto libre en la consulta
        if (!in_array($orden, $columnasValidas)) {
            $orden = 'Nombre';
        }
        $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';

        try {
            $conexion = (new Conexion())->establecerConexion();
            $sql = "SELECT IdMascota, Nombre, Raza, FechaNacimiento, Especie
                FROM MASCOTA
                WHERE CI = :ci
                ORDER BY $orden $dir";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':ci' => $ci]);
            // traigo todas las filas usando los nombres de las columnas como claves
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
