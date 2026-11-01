<?php
require_once __DIR__ . '/Conexion.php';

class Cita
{
    private ?int $id;
    private ?int $ci;
    private ?int $idServicio;
    private ?int $idMascota;
    private ?DateTime $fecha;
    private ?string $hora;
    private ?string $estado;

    // cargo los datos iniciales en las propiedades de este objeto
    public function __construct(?int $id = null, ?int $ci = null, ?int $idServicio = null, ?int $idMascota = null, ?DateTime $fecha = null, ?string $hora = null, ?string $estado = null)
    {
        $this->id = $id;
        $this->ci = $ci;
        $this->idServicio = $idServicio;
        $this->idMascota = $idMascota;
        $this->fecha = $fecha;
        $this->hora = $hora;
        $this->estado = $estado;
    }

    // devuelvo el valor de id que tiene el objeto
    public function getId(): int
    {
        return $this->id ?? 0;
    }

    // devuelvo el valor de ci que tiene el objeto
    public function getCi(): int
    {
        return $this->ci ?? 0;
    }

    // devuelvo el valor de id servicio que tiene el objeto
    public function getIdServicio(): int
    {
        return $this->idServicio ?? 0;
    }

    // devuelvo el valor de id mascota que tiene el objeto
    public function getIdMascota(): int
    {
        return $this->idMascota ?? 0;
    }

    // devuelvo el valor de fecha que tiene el objeto
    public function getFecha(): DateTime
    {
        return $this->fecha ?? new DateTime();
    }

    // devuelvo el valor de hora que tiene el objeto
    public function getHora(): string
    {
        return $this->hora ?? '';
    }

    // devuelvo el valor de estado que tiene el objeto
    public function getEstado(): string
    {
        return $this->estado ?? '';
    }


    // cambio el valor de fecha dentro del objeto
    public function setFecha(DateTime $fecha): void
    {
        $this->fecha = $fecha;
    }

    // cambio el valor de hora dentro del objeto
    public function setHora(string $hora): void
    {
        $this->hora = $hora;
    }

    // cambio el valor de estado dentro del objeto
    public function setEstado(string $estado): void
    {
        $this->estado = $estado;
    }

    // traigo las citas con su cliente, mascota y servicio, segun los filtros elegidos
    public static function listadoCita(?string $estado = null, ?string $filtro = null): array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "SELECT c.IdCita, c.CI, u.Nombre AS ClienteNombre, u.Apellido AS ClienteApellido,
                   c.IdMascota, m.Nombre AS MascotaNombre,
                   c.IdServicio, s.Nombre AS ServicioNombre,
                   c.Fecha, c.Hora, c.Estado
            FROM CITA c
            INNER JOIN CLIENTE cl ON c.CI = cl.CI
            INNER JOIN USUARIO u ON cl.CI = u.CI
            INNER JOIN MASCOTA m ON c.IdMascota = m.IdMascota
            INNER JOIN SERVICIO s ON c.IdServicio = s.IdServicio
            WHERE 1=1";

            $params = [];

            if (!empty($estado)) {
                $sql .= " AND c.Estado = :estado";
                $params[':estado'] = $estado;
            }

            if ($filtro === 'hoy') {
                $sql .= " AND c.Fecha = CURDATE()";
            }

            $sql .= " ORDER BY c.Fecha, c.Hora, u.Nombre, u.CI, m.Nombre, s.Nombre, c.Estado";

            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute($params);
            // traigo todas las filas usando los nombres de las columnas como claves
            $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['citas' => $citas];
        } catch (PDOException $e) {
            return ['citas' => [], 'error' => $e->getMessage()];
        }
    }

    // guardo una cita con el cliente, la mascota, el servicio y el horario
    public function crearCita(?int $ci, ?int $idServicio, ?int $idMascota, string $fecha, string $hora, string $estado,): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "INSERT INTO CITA (CI, IdServicio, IdMascota, Fecha, Hora, Estado)
                    VALUES (:ci, :idServicio, :idMascota, :fecha, :hora, :estado)";

            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':ci' => $ci,
                ':idServicio' => $idServicio,
                ':idMascota' => $idMascota,
                ':fecha' => $fecha,
                ':hora' => $hora,
                ':estado' => $estado
            ]);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // compruebo que esa mascota este registrada a nombre de ese cliente
    public static function mascotaPerteneceACliente(int $idMascota, int $ci): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            $stmt = $conexion->prepare("SELECT 1 FROM MASCOTA WHERE IdMascota = :idMascota AND CI = :ci");
            $stmt->execute([':idMascota' => $idMascota, ':ci' => $ci]);
            // leo un solo valor del resultado de la consulta
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Agenda filtrada por tipo(s) de servicio (Consulta, Vacunacion, Peluqueria, Cirugia).
     * Se usa para que cada rol de empleado vea solo las citas que le corresponden.
     */
    public static function listadoCitaPorTipo(array $tipos, ?string $estado = null, ?string $filtro = null): array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "SELECT c.IdCita, c.CI, u.Nombre AS ClienteNombre, u.Apellido AS ClienteApellido,
                   c.IdMascota, m.Nombre AS MascotaNombre,
                   c.IdServicio, s.Nombre AS ServicioNombre,
                   c.Fecha, c.Hora, c.Estado,
                   CASE
                       WHEN con.IdServicio IS NOT NULL THEN 'Consulta'
                       WHEN v.IdServicio IS NOT NULL THEN 'Vacunacion'
                       WHEN p.IdServicio IS NOT NULL THEN 'Peluqueria'
                       WHEN q.IdServicio IS NOT NULL THEN 'Cirugia'
                       ELSE 'Sin definir'
                   END AS Tipo
            FROM CITA c
            INNER JOIN CLIENTE cl ON c.CI = cl.CI
            INNER JOIN USUARIO u ON cl.CI = u.CI
            INNER JOIN MASCOTA m ON c.IdMascota = m.IdMascota
            INNER JOIN SERVICIO s ON c.IdServicio = s.IdServicio
            LEFT JOIN CONSULTA con ON s.IdServicio = con.IdServicio
            LEFT JOIN VACUNACION v ON s.IdServicio = v.IdServicio
            LEFT JOIN PELUQUERIA p ON s.IdServicio = p.IdServicio
            LEFT JOIN CIRUGIA q ON s.IdServicio = q.IdServicio
            WHERE 1=1";

            $params = [];

            if (!empty($tipos)) {
                $marcadores = [];
                foreach (array_values($tipos) as $i => $tipo) {
                    $clave = ":tipo{$i}";
                    $marcadores[] = $clave;
                    $params[$clave] = $tipo;
                }
                $sql .= " HAVING Tipo IN (" . implode(', ', $marcadores) . ")";
            }

            if (!empty($estado)) {
                $sql .= (!empty($tipos) ? " AND" : " HAVING") . " c.Estado = :estado";
                $params[':estado'] = $estado;
            }

            if ($filtro === 'hoy') {
                $sql .= (!empty($tipos) || !empty($estado) ? " AND" : " HAVING") . " c.Fecha = CURDATE()";
            }

            $sql .= " ORDER BY c.Fecha, c.Hora";

            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute($params);

            // traigo todas las filas usando los nombres de las columnas como claves
            return ['citas' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            return ['citas' => [], 'error' => $e->getMessage()];
        }
    }

    /* cuenta las citas de hoy restringidas a un conjunto de tipos de servicio */
    public static function contarCitasHoyPorTipo(array $tipos): int
    {
        if (empty($tipos)) {
            return 0;
        }

        try {
            $conexion = (new Conexion())->establecerConexion();

            $marcadores = [];
            $params = [':hoy' => date('Y-m-d')];
            foreach (array_values($tipos) as $i => $tipo) {
                $clave = ":tipo{$i}";
                $marcadores[] = $clave;
                $params[$clave] = $tipo;
            }

            $sql = "SELECT COUNT(*) FROM (
                SELECT c.IdCita,
                    CASE
                        WHEN con.IdServicio IS NOT NULL THEN 'Consulta'
                        WHEN v.IdServicio IS NOT NULL THEN 'Vacunacion'
                        WHEN p.IdServicio IS NOT NULL THEN 'Peluqueria'
                        WHEN q.IdServicio IS NOT NULL THEN 'Cirugia'
                        ELSE 'Sin definir'
                    END AS Tipo
                FROM CITA c
                INNER JOIN SERVICIO s ON c.IdServicio = s.IdServicio
                LEFT JOIN CONSULTA con ON s.IdServicio = con.IdServicio
                LEFT JOIN VACUNACION v ON s.IdServicio = v.IdServicio
                LEFT JOIN PELUQUERIA p ON s.IdServicio = p.IdServicio
                LEFT JOIN CIRUGIA q ON s.IdServicio = q.IdServicio
                WHERE c.Fecha = :hoy
            ) AS citasTipo
            WHERE Tipo IN (" . implode(', ', $marcadores) . ")";

            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute($params);
            // leo un solo valor del resultado de la consulta
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // cuento las citas que tienen la fecha de hoy
    public static function contarCitasHoy(): int
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            $sql = "SELECT COUNT(*) FROM CITA WHERE Fecha = :hoy";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':hoy' => date('Y-m-d')]);
            // leo un solo valor del resultado de la consulta
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // busco un registro de cita por su id; si no lo encuentro devuelvo null
    public static function buscarPorId(int $id): ?array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "SELECT c.IdCita, c.CI, u.Nombre AS ClienteNombre, u.Apellido AS ClienteApellido,
                       c.IdMascota, m.Nombre AS MascotaNombre, c.IdServicio, c.Fecha, c.Hora, c.Estado
                FROM CITA c
                LEFT JOIN USUARIO u ON c.CI = u.CI
                LEFT JOIN MASCOTA m ON c.IdMascota = m.IdMascota
                WHERE c.IdCita = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id' => $id]);
            // traigo una sola fila con los nombres de sus columnas como claves
            $cita = $stmt->fetch(PDO::FETCH_ASSOC);

            return $cita ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /* el cliente (CI) no se cambia al editar */
    public function editarCita(int $id, int $idServicio, int $idMascota, string $fecha, string $hora, string $estado): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "UPDATE CITA
                    SET IdServicio = :idServicio, IdMascota = :idMascota, Fecha = :fecha, Hora = :hora, Estado = :estado
                    WHERE IdCita = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':idServicio' => $idServicio,
                ':idMascota' => $idMascota,
                ':fecha' => $fecha,
                ':hora' => $hora,
                ':estado' => $estado,
                ':id' => $id
            ]);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // solo cambio citas pendientes; la llegada se confirma el dia de la cita
    public function cambiarEstado(int $id, string $estado, ?int $ci = null): bool
    {
        if (!in_array($estado, ['Confirmada', 'Cancelada'], true) || ($ci !== null && $estado !== 'Cancelada')) {
            return false;
        }

        try {
            $conexion = (new Conexion())->establecerConexion();
            $sql = "UPDATE CITA SET Estado = :estado WHERE IdCita = :id AND Estado = 'Pendiente'";
            $parametros = [':estado' => $estado, ':id' => $id];

            // para confirmar la llegada tambien exijo que la cita sea hoy
            if ($estado === 'Confirmada') {
                $sql .= " AND Fecha = :hoy";
                $parametros[':hoy'] = (new DateTimeImmutable('now', new DateTimeZone('America/Montevideo')))->format('Y-m-d');
            }
            // si viene una cedula limito el cambio a las citas de ese cliente
            if ($ci !== null) {
                $sql .= " AND CI = :ci";
                $parametros[':ci'] = $ci;
            }

            // preparo la consulta para pasar los valores por separado al ejecutarla
            $consulta = $conexion->prepare($sql);
            $consulta->execute($parametros);
            return $consulta->rowCount() === 1;
        } catch (PDOException $e) {
            return false;
        }
    }

    /* ninguna tabla usa CITA, asi que no hay borrado bloqueado */
    public function eliminarCita(int $id): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "DELETE FROM CITA WHERE IdCita = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id' => $id]);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // traigo las citas y, si llega una fecha, busco solo las de ese dia
    public static function listadoCitasPorFecha(string $fecha = ''): array
{
    try {
        $conexion = (new Conexion())->establecerConexion();

        $condicion = '';
        $params = [];
        if ($fecha !== '') {
            // filtramos por la fecha exacta usando la columna 'Fecha'
            $condicion = " WHERE c.Fecha = :fecha ";
            $params = [':fecha' => $fecha];
        }

        // realizamos los JOINs necesarios para obtener los nombres de cliente, mascota y servicio
        $sql = "SELECT c.*,
                       u.Nombre AS ClienteNombre, u.Apellido AS ClienteApellido,
                       m.Nombre AS MascotaNombre,
                       s.Nombre AS ServicioNombre
                FROM CITA c
                INNER JOIN CLIENTE cl ON c.CI = cl.CI
                INNER JOIN USUARIO u ON cl.CI = u.CI
                INNER JOIN MASCOTA m ON c.IdMascota = m.IdMascota
                INNER JOIN SERVICIO s ON c.IdServicio = s.IdServicio
                $condicion
                ORDER BY c.Fecha ASC, c.Hora ASC";

        // preparo la consulta para pasar los valores por separado al ejecutarla
        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);
        // traigo todas las filas usando los nombres de las columnas como claves
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        return [];
    }
}
}
