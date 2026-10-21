<?php
require_once __DIR__ . '/Conexion.php';

class Servicio
{
    private ?int $id;
    private ?string $nombre;
    private ?float $precio;
    private ?string $descripcion;
    private ?int $duracion;

    public function __construct(?string $id = null, ?string $nombre = null, ?string $precio = null, ?string $descripcion = null, ?string $duracion = null)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->descripcion = $descripcion;
        $this->duracion = $duracion;
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function getNombre(): string
    {
        return $this->nombre ?? '';
    }

    public function getPrecio(): float
    {
        return $this->precio ?? '';
    }

    public function getDescripcion(): string
    {
        return $this->descripcion ?? '';
    }

    public function getDuracion(): string
    {
        return $this->duracion ?? '';
    }


    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    public function setPrecio(float $precio): void
    {
        $this->precio = $precio;
    }

    public function setDescripcion(string $descripcion): void
    {
        $this->descripcion = $descripcion;
    }

    public function setDuracion(int $duracion): void
    {
        $this->duracion = $duracion;
    }

    public static function listadoServicios(): array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "SELECT s.IdServicio, s.Nombre, s.Precio, s.Descripcion, s.Duracion,
                   CASE
                     WHEN c.IdServicio IS NOT NULL THEN 'Consulta'
                     WHEN v.IdServicio IS NOT NULL THEN 'Vacunacion'
                     WHEN p.IdServicio IS NOT NULL THEN 'Peluqueria'
                     WHEN q.IdServicio IS NOT NULL THEN 'Cirugia'
                     ELSE 'Sin definir'
                   END AS Tipo
                FROM SERVICIO s
                LEFT JOIN CONSULTA c ON s.IdServicio = c.IdServicio
                LEFT JOIN VACUNACION v ON s.IdServicio = v.IdServicio
                LEFT JOIN PELUQUERIA p ON s.IdServicio = p.IdServicio
                LEFT JOIN CIRUGIA q ON s.IdServicio = q.IdServicio
                GROUP BY s.IdServicio, s.Nombre, Tipo, s.Descripcion, s.Duracion, s.Precio";

            $stmt = $conexion->query($sql);
            $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['servicios' => $servicios];
        } catch (PDOException $e) {
            echo "Error al cargar servicios: " . $e->getMessage();
            return ['servicios' => []];
        }
    }

    public function crearServicio(string $tipo, string $nombre, float $precio, string $descripcion, int $duracion, array $datosEspecificos): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            $conexion->beginTransaction();

            $sqlServicio = "INSERT INTO SERVICIO (Nombre, Precio, Descripcion, Duracion) VALUES (:nombre, :precio, :descripcion, :duracion)";
            $stmt = $conexion->prepare($sqlServicio);
            $stmt->execute([
                ':nombre' => $nombre,
                ':precio' => $precio,
                ':descripcion' => $descripcion,
                ':duracion' => $duracion
            ]);

            $idServicio = $conexion->lastInsertId();

            switch ($tipo) {
                case 'Consulta':
                    $sql = "INSERT INTO CONSULTA (IdServicio, Anamnesis, Motivo, EsEmergencia, IndicacionesRecetadas)
                        VALUES (:id, :anamnesis, :motivo, :emergencia, :indicaciones)";
                    $stmt = $conexion->prepare($sql);
                    $stmt->execute([
                        ':id' => $idServicio,
                        ':anamnesis' => $datosEspecificos['Anamnesis'] ?? '',
                        ':motivo' => $datosEspecificos['Motivo'] ?? '',
                        ':emergencia' => $datosEspecificos['EsEmergencia'] ?? 0,
                        ':indicaciones' => $datosEspecificos['IndicacionesRecetadas'] ?? ''
                    ]);
                    break;

                case 'Vacunacion':
                    $sql = "INSERT INTO VACUNACION (IdServicio, LoteVacuna, TipoVacuna, LaboratorioFabricante)
                        VALUES (:id, :lote, :tipoVacuna, :laboratorio)";
                    $stmt = $conexion->prepare($sql);
                    $stmt->execute([
                        ':id' => $idServicio,
                        ':lote' => $datosEspecificos['LoteVacuna'] ?? '',
                        ':tipoVacuna' => $datosEspecificos['TipoVacuna'] ?? '',
                        ':laboratorio' => $datosEspecificos['LaboratorioFabricante'] ?? ''
                    ]);
                    break;

                case 'Peluqueria':
                    $sql = "INSERT INTO PELUQUERIA (IdServicio, TipoCorte, TipoShampoo) VALUES (:id, :corte, :shampoo)";
                    $stmt = $conexion->prepare($sql);
                    $stmt->execute([
                        ':id' => $idServicio,
                        ':corte' => $datosEspecificos['TipoCorte'] ?? '',
                        ':shampoo' => $datosEspecificos['TipoShampoo'] ?? ''
                    ]);
                    break;

                case 'Cirugia':
                    $sql = "INSERT INTO CIRUGIA (IdServicio, Anestesia, RiesgoQuirurgico, AutorizacionFirmada)
                        VALUES (:id, :anestesia, :riesgo, :autorizacion)";
                    $stmt = $conexion->prepare($sql);
                    $stmt->execute([
                        ':id' => $idServicio,
                        ':anestesia' => $datosEspecificos['TipoAnestesia'] ?? '',
                        ':riesgo' => $datosEspecificos['RiesgoQuirurgico'] ?? '',
                        ':autorizacion' => $datosEspecificos['AutorizacionFirmada'] ?? 0
                    ]);
                    break;

                default:
                    throw new Exception("Tipo de servicio inválido: $tipo");
            }

            $conexion->commit();
            return true;
        } catch (Exception $e) {
            if (isset($conexion) && $conexion->inTransaction()) {
                $conexion->rollBack();
            }
            return false;
        }
    }
}
