<?php
require_once __DIR__ . '/Conexion.php';

class Servicio
{
    private ?int $id;
    private ?string $nombre;
    private ?float $precio;
    private ?string $descripcion;
    private ?int $duracion;

    // cargo los datos iniciales en las propiedades de este objeto
    public function __construct(?string $id = null, ?string $nombre = null, ?string $precio = null, ?string $descripcion = null, ?string $duracion = null)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->descripcion = $descripcion;
        $this->duracion = $duracion;
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

    // devuelvo el valor de precio que tiene el objeto
    public function getPrecio(): float
    {
        return $this->precio ?? '';
    }

    // devuelvo el valor de descripcion que tiene el objeto
    public function getDescripcion(): string
    {
        return $this->descripcion ?? '';
    }

    // devuelvo el valor de duracion que tiene el objeto
    public function getDuracion(): string
    {
        return $this->duracion ?? '';
    }


    // cambio el valor de nombre dentro del objeto
    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    // cambio el valor de precio dentro del objeto
    public function setPrecio(float $precio): void
    {
        $this->precio = $precio;
    }

    // cambio el valor de descripcion dentro del objeto
    public function setDescripcion(string $descripcion): void
    {
        $this->descripcion = $descripcion;
    }

    // cambio el valor de duracion dentro del objeto
    public function setDuracion(int $duracion): void
    {
        $this->duracion = $duracion;
    }

    // traigo los servicios con su tipo y los filtros de tipo o busqueda
    public static function listadoServicios($estado = null, string $busqueda = ''): array
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
                END AS Tipo,
                c.Anamnesis, c.Motivo, c.EsEmergencia, c.IndicacionesRecetadas,
                v.LoteVacuna, v.TipoVacuna, v.LaboratorioFabricante,
                p.TipoCorte, p.TipoShampoo,
                q.TipoAnestesia, q.RiesgoQuirurgico, q.AutorizacionFirmada
                FROM SERVICIO s
                LEFT JOIN CONSULTA c ON s.IdServicio = c.IdServicio
                LEFT JOIN VACUNACION v ON s.IdServicio = v.IdServicio
                LEFT JOIN PELUQUERIA p ON s.IdServicio = p.IdServicio
                LEFT JOIN CIRUGIA q ON s.IdServicio = q.IdServicio
                WHERE 1=1";

            $params = [];

            // filtro por categoría (select)
            if (!empty($estado)) {
                $tipos = is_array($estado) ? $estado : [$estado];
                $marcadores = [];
                foreach ($tipos as $i => $tipoValor) {
                    $clave = ":estado{$i}";
                    $marcadores[] = $clave;
                    $params[$clave] = $tipoValor;
                }
                $sql .= " HAVING Tipo IN (" . implode(', ', $marcadores) . ") ";
            }

            // filtro por texto (Nombre del servicio o Tipo de vacuna si aplica)
            if ($busqueda !== '') {
                $sql .= (!empty($estado) ? " AND" : " HAVING") . " (s.Nombre LIKE :busqueda OR v.TipoVacuna LIKE :busqueda)";
                $params[':busqueda'] = '%' . $busqueda . '%';
            }

            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute($params);
            // traigo todas las filas usando los nombres de las columnas como claves
            $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['servicios' => $servicios];
        } catch (PDOException $e) {
            return ['servicios' => [], 'error' => $e->getMessage()];
        }
    }

    // guardo los datos comunes del servicio y despues los de su tipo
    public function crearServicio(string $tipo, string $nombre, float $precio, string $descripcion, int $duracion, array $datosEspecificos): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            // agrupo los cambios para guardar todo junto o deshacerlo si algo falla
            $conexion->beginTransaction();

            $sqlServicio = "INSERT INTO SERVICIO (Nombre, Precio, Descripcion, Duracion) VALUES (:nombre, :precio, :descripcion, :duracion)";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sqlServicio);
            $stmt->execute([
                ':nombre' => $nombre,
                ':precio' => $precio,
                ':descripcion' => $descripcion,
                ':duracion' => $duracion
            ]);

            // recupero el id que genero la base para relacionar el registro recien creado
            $idServicio = $conexion->lastInsertId();

            // segun el tipo guardo los campos especificos en su tabla correspondiente
            switch ($tipo) {
                case 'Consulta':
                    $sql = "INSERT INTO CONSULTA (IdServicio, Anamnesis, Motivo, EsEmergencia, IndicacionesRecetadas)
                        VALUES (:id, :anamnesis, :motivo, :emergencia, :indicaciones)";
                    // preparo la consulta para pasar los valores por separado al ejecutarla
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
                    // preparo la consulta para pasar los valores por separado al ejecutarla
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
                    // preparo la consulta para pasar los valores por separado al ejecutarla
                    $stmt = $conexion->prepare($sql);
                    $stmt->execute([
                        ':id' => $idServicio,
                        ':corte' => $datosEspecificos['TipoCorte'] ?? '',
                        ':shampoo' => $datosEspecificos['TipoShampoo'] ?? ''
                    ]);
                    break;

                case 'Cirugia':
                    $sql = "INSERT INTO CIRUGIA (IdServicio, TipoAnestesia, RiesgoQuirurgico, AutorizacionFirmada)
                        VALUES (:id, :anestesia, :riesgo, :autorizacion)";
                    // preparo la consulta para pasar los valores por separado al ejecutarla
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

            // confirmo todos los cambios porque las operaciones anteriores terminaron bien
            $conexion->commit();
            return true;
        } catch (Exception $e) {
            if (isset($conexion) && $conexion->inTransaction()) {
                // deshago los cambios de esta operacion si la transaccion sigue abierta
                $conexion->rollBack();
            }
            return false;
        }
    }

    /* devuelve el servicio con su Tipo y los campos de su subtipo */
    public static function buscarPorId(int $id): ?array
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
               END AS Tipo,
               c.Motivo, c.EsEmergencia, c.IndicacionesRecetadas,
               v.LoteVacuna, v.TipoVacuna, v.LaboratorioFabricante,
               p.TipoCorte, p.TipoShampoo,
               q.TipoAnestesia, q.RiesgoQuirurgico, q.AutorizacionFirmada
            FROM SERVICIO s
            LEFT JOIN CONSULTA c ON s.IdServicio = c.IdServicio
            LEFT JOIN VACUNACION v ON s.IdServicio = v.IdServicio
            LEFT JOIN PELUQUERIA p ON s.IdServicio = p.IdServicio
            LEFT JOIN CIRUGIA q ON s.IdServicio = q.IdServicio
            WHERE s.IdServicio = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id' => $id]);
            // traigo una sola fila con los nombres de sus columnas como claves
            $servicio = $stmt->fetch(PDO::FETCH_ASSOC);

            return $servicio ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /* el tipo no se cambia: se actualiza SERVICIO y la fila de su subtipo */
    public function editarServicio(int $id, string $tipo, string $nombre, float $precio, string $descripcion, int $duracion, array $datosEspecificos): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            // agrupo los cambios para guardar todo junto o deshacerlo si algo falla
            $conexion->beginTransaction();

            $sqlServicio = "UPDATE SERVICIO
                SET Nombre = :nombre, Precio = :precio, Descripcion = :descripcion, Duracion = :duracion
                WHERE IdServicio = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sqlServicio);
            $stmt->execute([
                ':nombre' => $nombre,
                ':precio' => $precio,
                ':descripcion' => $descripcion,
                ':duracion' => $duracion,
                ':id' => $id
            ]);

            // segun el tipo guardo los campos especificos en su tabla correspondiente
            switch ($tipo) {
                case 'Consulta':
                    $sql = "UPDATE CONSULTA
                        SET Motivo = :motivo, EsEmergencia = :emergencia, IndicacionesRecetadas = :indicaciones
                        WHERE IdServicio = :id";
                    // preparo la consulta para pasar los valores por separado al ejecutarla
                    $stmt = $conexion->prepare($sql);
                    $stmt->execute([
                        ':motivo' => $datosEspecificos['Motivo'] ?? '',
                        ':emergencia' => $datosEspecificos['EsEmergencia'] ?? 0,
                        ':indicaciones' => $datosEspecificos['IndicacionesRecetadas'] ?? '',
                        ':id' => $id
                    ]);
                    break;

                case 'Vacunacion':
                    $sql = "UPDATE VACUNACION
                        SET LoteVacuna = :lote, TipoVacuna = :tipoVacuna, LaboratorioFabricante = :laboratorio
                        WHERE IdServicio = :id";
                    // preparo la consulta para pasar los valores por separado al ejecutarla
                    $stmt = $conexion->prepare($sql);
                    $stmt->execute([
                        ':lote' => $datosEspecificos['LoteVacuna'] ?? '',
                        ':tipoVacuna' => $datosEspecificos['TipoVacuna'] ?? '',
                        ':laboratorio' => $datosEspecificos['LaboratorioFabricante'] ?? '',
                        ':id' => $id
                    ]);
                    break;

                case 'Peluqueria':
                    $sql = "UPDATE PELUQUERIA SET TipoCorte = :corte, TipoShampoo = :shampoo WHERE IdServicio = :id";
                    // preparo la consulta para pasar los valores por separado al ejecutarla
                    $stmt = $conexion->prepare($sql);
                    $stmt->execute([
                        ':corte' => $datosEspecificos['TipoCorte'] ?? '',
                        ':shampoo' => $datosEspecificos['TipoShampoo'] ?? '',
                        ':id' => $id
                    ]);
                    break;

                case 'Cirugia':
                    $sql = "UPDATE CIRUGIA
                        SET TipoAnestesia = :anestesia, RiesgoQuirurgico = :riesgo, AutorizacionFirmada = :autorizacion
                        WHERE IdServicio = :id";
                    // preparo la consulta para pasar los valores por separado al ejecutarla
                    $stmt = $conexion->prepare($sql);
                    $stmt->execute([
                        ':anestesia' => $datosEspecificos['TipoAnestesia'] ?? '',
                        ':riesgo' => $datosEspecificos['RiesgoQuirurgico'] ?? '',
                        ':autorizacion' => $datosEspecificos['AutorizacionFirmada'] ?? 0,
                        ':id' => $id
                    ]);
                    break;
            }

            // confirmo todos los cambios porque las operaciones anteriores terminaron bien
            $conexion->commit();
            return true;
        } catch (PDOException $e) {
            if (isset($conexion) && $conexion->inTransaction()) {
                // deshago los cambios de esta operacion si la transaccion sigue abierta
                $conexion->rollBack();
            }
            return false;
        }
    }

    /* devuelve 'eliminado', 'bloqueado' (tiene citas, BRINDA o ACCEDE) o 'error' */
    public function eliminarServicio(int $id): string
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            // agrupo los cambios para guardar todo junto o deshacerlo si algo falla
            $conexion->beginTransaction();

            /* primero el subtipo (solo una de estas tablas tiene la fila) y despues SERVICIO */
            foreach (['CONSULTA', 'VACUNACION', 'PELUQUERIA', 'CIRUGIA'] as $tabla) {
                $stmt = $conexion->prepare("DELETE FROM $tabla WHERE IdServicio = :id");
                $stmt->execute([':id' => $id]);
            }

            $stmt = $conexion->prepare("DELETE FROM SERVICIO WHERE IdServicio = :id");
            $stmt->execute([':id' => $id]);

            // confirmo todos los cambios porque las operaciones anteriores terminaron bien
            $conexion->commit();
            return 'eliminado';
        } catch (PDOException $e) {
            /* si falla, se deshace todo y el subtipo vuelve */
            if (isset($conexion) && $conexion->inTransaction()) {
                // deshago los cambios de esta operacion si la transaccion sigue abierta
                $conexion->rollBack();
            }
            /* 1451: CITA, BRINDA o ACCEDE usan este servicio */
            if ($e->errorInfo[1] == 1451) {
                return 'bloqueado';
            }
            return 'error';
        }
    }
}
