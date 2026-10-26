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

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function getIdMascota(): int
    {
        return $this->idMascota ?? 0;
    }

    public function getAnamnesis(): string
    {
        return $this->anamnesis ?? '';
    }

    public function getAntecedentes(): string
    {
        return $this->antecedentes ?? '';
    }

    public function getTratamiento(): string
    {
        return $this->tratamiento ?? '';
    }

    public function getDiagnostico(): string
    {
        return $this->diagnostico ?? '';
    }

    public function getFecha(): ?DateTime
    {
        return $this->fecha;
    }

    public function getPeso(): int
    {
        return $this->peso ?? 0;
    }

    public function setAnamnesis(string $anamnesis): void
    {
        $this->anamnesis = $anamnesis;
    }

    public function setAntecedentes(string $antecedentes): void
    {
        $this->antecedentes = $antecedentes;
    }

    public function setTratamiento(string $tratamiento): void
    {
        $this->tratamiento = $tratamiento;
    }

    public function setDiagnostico(string $diagnostico): void
    {
        $this->diagnostico = $diagnostico;
    }

    public function setFecha(DateTime $fecha): void
    {
        $this->fecha = $fecha;
    }

    public function setPeso(int $peso): void
    {
        $this->peso = $peso;
    }


    public function crearHistorialClinico(int $idMascota, ?string $anamnesis, ?string $antecedentes, ?string $tratamiento, ?string $diagnostico, ?string $fecha, ?float $peso) {
    try {
        $conexion = (new Conexion())->establecerConexion();

        // 1. Obtener el siguiente ID para esta mascota (clave compuesta)
        $sqlMax = "SELECT MAX(IdHistorialClinico) AS max_id FROM historial_clinico WHERE IdMascota = :idMascota";
        $stmtMax = $conexion->prepare($sqlMax);
        $stmtMax->execute([':idMascota' => $idMascota]);
        $row = $stmtMax->fetch(PDO::FETCH_ASSOC);
        $siguienteId = ($row && $row['max_id'] !== null) ? (int)$row['max_id'] + 1 : 1;

        // 2. Insertar usando el formato de array asociativo con parámetros nombrados
        $sql = "INSERT INTO historial_clinico (IdMascota, IdHistorialClinico, Anamnesis, Antecedentes, Tratamiento, Diagnostico, Fecha, Peso)
                VALUES (:idMascota, :idHistorial, :anamnesis, :antecedentes, :tratamiento, :diagnostico, :fecha, :peso)";

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

    public static function buscarPorId(int $idHistorial): ?array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "SELECT * FROM HISTORIAL_CLINICO WHERE IdHistorialClinico = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id' => $idHistorial]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return $resultado ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function editarHistorialClinico(?int $idMascota, ?int $idHistorial, ?string $anamnesis, ?string $antecedentes, ?string $tratamiento, ?string $diagnostico, ?string $fecha, ?float $peso) {
    try {
        $conexion = (new Conexion())->establecerConexion();

        // Al ser clave compuesta, filtramos tanto por IdMascota como por IdHistorialClinico
        $sql = "UPDATE historial_clinico
                SET Anamnesis = :anamnesis,
                    Antecedentes = :antecedentes,
                    Tratamiento = :tratamiento,
                    Diagnostico = :diagnostico,
                    Fecha = :fecha,
                    Peso = :peso
                WHERE IdMascota = :idMascota AND IdHistorialClinico = :idHistorial";

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

    public function eliminarHistorialClinico(?int $idMascota, ?int $idHistorial) {
    try {
        $conexion = (new Conexion())->establecerConexion();
        $sql = "DELETE FROM historial_clinico WHERE IdMascota = :idMascota AND IdHistorialClinico = :idHistorial";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            ':idMascota'   => $idMascota,
            ':idHistorial' => $idHistorial
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

    public static function listadoPorMascota(string $nombreMascota = ''): array
    {
        $conexion = (new Conexion())->establecerConexion();
        $sql = "SELECT hc.*, m.Nombre AS NombreMascota
                FROM HISTORIAL_CLINICO hc
                INNER JOIN MASCOTA m ON hc.IdMascota = m.IdMascota
                WHERE m.Nombre LIKE :busqueda
                ORDER BY hc.Fecha DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':busqueda' => '%' . $nombreMascota . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function listadoPorIdMascota(int $idMascota): array
    {
        $conexion = (new Conexion())->establecerConexion();
        $sql = "SELECT * FROM HISTORIAL_CLINICO WHERE IdMascota = :id ORDER BY Fecha DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $idMascota]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
