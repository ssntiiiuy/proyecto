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

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function getCi(): int
    {
        return $this->ci ?? 0;
    }

    public function getIdServicio(): int
    {
        return $this->idServicio ?? 0;
    }

    public function getIdMascota(): int
    {
        return $this->idMascota ?? 0;
    }

    public function getFecha(): DateTime
    {
        return $this->fecha ?? new DateTime();
    }

    public function getHora(): string
    {
        return $this->hora ?? '';
    }

    public function getEstado(): string
    {
        return $this->estado ?? '';
    }


    public function setFecha(DateTime $fecha): void
    {
        $this->fecha = $fecha;
    }

    public function setHora(string $hora): void
    {
        $this->hora = $hora;
    }

    public function setEstado(string $estado): void
    {
        $this->estado = $estado;
    }

    public static function listadoCita(?string $estado = null): array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "SELECT c.IdCita, c.CI, u.Nombre AS ClienteNombre, u.Apellido AS ClienteApellido,
                       c.IdMascota, m.Nombre AS MascotaNombre,
                       c.IdServicio, s.Nombre AS ServicioNombre,
                       c.Fecha, c.Hora, c.Tipo, c.Estado
                FROM CITA c
                INNER JOIN CLIENTE cl ON c.CI = cl.CI
                INNER JOIN USUARIO u ON cl.CI = u.CI
                INNER JOIN MASCOTA m ON c.IdMascota = m.IdMascota
                INNER JOIN SERVICIO s ON c.IdServicio = s.IdServicio";

            $params = [];

            if (!empty($estado)) {
                $sql .= " WHERE c.Estado = :estado";
                $params[':estado'] = $estado;
            }

            $sql .= " ORDER BY c.Fecha, c.Hora";

            $stmt = $conexion->prepare($sql);
            $stmt->execute($params);
            $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['citas' => $citas];
        } catch (PDOException $e) {
            return ['citas' => [], 'error' => $e->getMessage()];
        }
    }

    public function crearCita(?int $ci, ?int $idServicio, ?int $idMascota, string $fecha, string $hora, string $estado,): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "INSERT INTO CITA (CI, IdServicio, IdMascota, Fecha, Hora, Estado)
                    VALUES (:ci, :idServicio, :idMascota, :fecha, :hora, :estado)";

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

    public static function contarCitasHoy(): int
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            $sql = "SELECT COUNT(*) FROM CITA WHERE Fecha = :hoy";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':hoy' => date('Y-m-d')]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
}
