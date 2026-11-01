<?php
require_once __DIR__ . '/Conexion.php';

class HistorialClinico
{
    private ?int $id;
    private ?int $idMascota;
    private ?string $anamnesis;
    private ?string $antecedentes;
    private ?string $tratamiento;
    private ?string $diagnostico;
    private ?DateTime $fecha;
    private ?int $peso;

    // cargo los datos iniciales en las propiedades de este objeto
    public function __construct(?int $id = null, ?int $idMascota = null, ?string $anamnesis = null, ?string $antecedentes = null, ?string $tratamiento = null, ?string $diagnostico = null, ?DateTime $fecha = null, ?int $peso = null)
    {
        $this->id = $id;
        $this->idMascota = $idMascota;
        $this->anamnesis = $anamnesis;
        $this->antecedentes = $antecedentes;
        $this->tratamiento = $tratamiento;
        $this->diagnostico = $diagnostico;
        $this->fecha = $fecha;
        $this->peso = $peso;
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

    // devuelvo el valor de anamnesis que tiene el objeto
    public function getAnamnesis(): string
    {
        return $this->anamnesis ?? '';
    }

    // devuelvo el valor de antecedentes que tiene el objeto
    public function getAntecedentes(): string
    {
        return $this->antecedentes ?? '';
    }

    // devuelvo el valor de tratamiento que tiene el objeto
    public function getTratamiento(): string
    {
        return $this->tratamiento ?? '';
    }

    // devuelvo el valor de diagnostico que tiene el objeto
    public function getDiagnostico(): string
    {
        return $this->diagnostico ?? '';
    }

    // devuelvo el valor de fecha que tiene el objeto
    public function getFecha(): ?DateTime
    {
        return $this->fecha;
    }

    // devuelvo el valor de peso que tiene el objeto
    public function getPeso(): int
    {
        return $this->peso ?? 0;
    }

    // cambio el valor de anamnesis dentro del objeto
    public function setAnamnesis(string $anamnesis): void
    {
        $this->anamnesis = $anamnesis;
    }

    // cambio el valor de antecedentes dentro del objeto
    public function setAntecedentes(string $antecedentes): void
    {
        $this->antecedentes = $antecedentes;
    }

    // cambio el valor de tratamiento dentro del objeto
    public function setTratamiento(string $tratamiento): void
    {
        $this->tratamiento = $tratamiento;
    }

    // cambio el valor de diagnostico dentro del objeto
    public function setDiagnostico(string $diagnostico): void
    {
        $this->diagnostico = $diagnostico;
    }

    // cambio el valor de fecha dentro del objeto
    public function setFecha(DateTime $fecha): void
    {
        $this->fecha = $fecha;
    }

    // cambio el valor de peso dentro del objeto
    public function setPeso(int $peso): void
    {
        $this->peso = $peso;
    }


    // agrego un registro al historial de la mascota con los datos de la consulta
    public function crearHistorialClinico(int $idMascota, ?string $anamnesis, ?string $antecedentes, ?string $tratamiento, ?string $diagnostico, ?string $fecha, ?float $peso) {
    try {
        $conexion = (new Conexion())->establecerConexion();

        // 1. Obtener el siguiente ID para esta mascota (clave compuesta)
        $sqlMax = "SELECT MAX(IdHistorialClinico) AS max_id FROM historial_clinico WHERE IdMascota = :idMascota";
        // preparo la consulta para pasar los valores por separado al ejecutarla
        $stmtMax = $conexion->prepare($sqlMax);
        $stmtMax->execute([':idMascota' => $idMascota]);
        // traigo una sola fila con los nombres de sus columnas como claves
        $row = $stmtMax->fetch(PDO::FETCH_ASSOC);
        $siguienteId = ($row && $row['max_id'] !== null) ? (int)$row['max_id'] + 1 : 1;

        // 2. Insertar usando el formato de array asociativo con parámetros nombrados
        $sql = "INSERT INTO historial_clinico (IdMascota, IdHistorialClinico, Anamnesis, Antecedentes, Tratamiento, Diagnóstico, Fecha, Peso)
                VALUES (:idMascota, :idHistorial, :anamnesis, :antecedentes, :tratamiento, :diagnostico, :fecha, :peso)";

        // preparo la consulta para pasar los valores por separado al ejecutarla
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            ':idMascota'    => $idMascota,
            ':idHistorial'  => $siguienteId,
            ':anamnesis'    => $anamnesis,
            ':antecedentes' => $antecedentes,
            ':tratamiento'  => $tratamiento,
            ':diagnostico'  => $diagnostico,
            ':fecha'        => $fecha,
            ':peso'         => ($peso !== '' ? $peso : null)
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

    // busco un registro de historial clinico por su id; si no lo encuentro devuelvo null
    public static function buscarPorId(int $idHistorial): ?array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "SELECT * FROM HISTORIAL_CLINICO WHERE IdHistorialClinico = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id' => $idHistorial]);
            // traigo una sola fila con los nombres de sus columnas como claves
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return $resultado ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    // actualizo el registro usando el id de la mascota y el del historial
    public function editarHistorialClinico(?int $idMascota, ?int $idHistorial, ?string $anamnesis, ?string $antecedentes, ?string $tratamiento, ?string $diagnostico, ?string $fecha, ?float $peso) {
    try {
        $conexion = (new Conexion())->establecerConexion();

        // al ser clave compuesta, filtramos tanto por IdMascota como por IdHistorialClinico
        $sql = "UPDATE historial_clinico
                SET Anamnesis = :anamnesis,
                    Antecedentes = :antecedentes,
                    Tratamiento = :tratamiento,
                    Diagnóstico = :diagnostico,
                    Fecha = :fecha,
                    Peso = :peso
                WHERE IdMascota = :idMascota AND IdHistorialClinico = :idHistorial";

        // preparo la consulta para pasar los valores por separado al ejecutarla
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            ':anamnesis'    => $anamnesis,
            ':antecedentes' => $antecedentes,
            ':tratamiento'  => $tratamiento,
            ':diagnostico'  => $diagnostico,
            ':fecha'        => $fecha,
            ':peso'         => ($peso !== '' ? $peso : null),
            ':idMascota'    => $idMascota,
            ':idHistorial'  => $idHistorial
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

    // borro solo el registro que coincide con esa mascota y ese historial
    public function eliminarHistorialClinico(?int $idMascota, ?int $idHistorial) {
    try {
        $conexion = (new Conexion())->establecerConexion();
        $sql = "DELETE FROM historial_clinico WHERE IdMascota = :idMascota AND IdHistorialClinico = :idHistorial";
        // preparo la consulta para pasar los valores por separado al ejecutarla
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            ':idMascota'   => $idMascota,
            ':idHistorial' => $idHistorial
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

    // busco los registros por nombre de mascota y traigo tambien sus datos
    public static function listadoPorMascota(string $nombreMascota = ''): array
    {
        $conexion = (new Conexion())->establecerConexion();
        $sql = "SELECT hc.*, m.Nombre AS NombreMascota
                FROM HISTORIAL_CLINICO hc
                INNER JOIN MASCOTA m ON hc.IdMascota = m.IdMascota
                WHERE m.Nombre LIKE :busqueda
                ORDER BY hc.Fecha DESC";
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
        $sql = "SELECT * FROM HISTORIAL_CLINICO WHERE IdMascota = :id ORDER BY Fecha ASC";
        // preparo la consulta para pasar los valores por separado al ejecutarla
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $idMascota]);
        // traigo todas las filas usando los nombres de las columnas como claves
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
