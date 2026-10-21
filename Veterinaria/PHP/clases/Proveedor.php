<?php
require_once 'Conexion.php';

class Proveedor
{
    private ?int $id;
    private ?string $nombre;
    private ?string $empresa;
    private ?int $telefono;
    private ?string $producto;

    public function __construct(?int $id = null, ?string $nombre = null, ?string $empresa = null, ?int $telefono = null, ?string $producto = null)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->empresa = $empresa;
        $this->telefono = $telefono;
        $this->producto = $producto;
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function getNombre(): string
    {
        return $this->nombre ?? '';
    }

    public function getEmpresa(): string
    {
        return $this->empresa ?? '';
    }

    public function getTelefono(): int
    {
        return $this->telefono ?? 0;
    }

    public function getProducto(): string
    {
        return $this->producto ?? '';
    }


    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    public function setEmpresa(string $empresa): void
    {
        $this->empresa = $empresa;
    }

    public function setTelefono(int $telefono): void
    {
        $this->telefono = $telefono;
    }

    public function setProducto(string $producto): void
    {
        $this->producto = $producto;
    }

    public static function listadoProveedor(): array
{
    try {
        $conexion = (new Conexion())->establecerConexion();

        $sqlProveedor = "SELECT p.IdProveedor, p.Nombre, p.NombreEmpresa,
               GROUP_CONCAT(DISTINCT tp.numTelProveedor ORDER BY tp.numTelProveedor SEPARATOR '|') AS Telefonos,
               GROUP_CONCAT(DISTINCT pro.Nombre ORDER BY pro.Nombre SEPARATOR ', ') AS Productos
               FROM PROVEEDOR p
               LEFT JOIN TELEFONO_PROVEEDOR tp ON p.IdProveedor = tp.IdProveedor
               LEFT JOIN PROVEE pr ON p.IdProveedor = pr.IdProveedor
               LEFT JOIN producto pro ON pr.IdProducto = pro.IdProducto
               GROUP BY p.IdProveedor, p.Nombre, p.NombreEmpresa";
        $stmtProveedor = $conexion->query($sqlProveedor);
        $proveedores = $stmtProveedor->fetchAll(PDO::FETCH_ASSOC);

        return ['proveedores' => $proveedores];
    } catch (PDOException $e) {
        echo "Error al cargar proveedores: " . $e->getMessage();
        return [
            'proveedores' => []
        ];
    }
}

    public function crearProveedor(string $nombre, string $empresa, string $telefono): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sqlLogin = "INSERT INTO PROVEEDOR (Nombre, NombreEmpresa) VALUES (:nombre, :empresa)";
            $stmtLogin = $conexion->prepare($sqlLogin);
            $stmtLogin->execute([
                ':nombre' => $nombre,
                ':empresa' => $empresa
            ]);

            $idProveedor = $conexion->lastInsertId();

            $sqlTel = "INSERT INTO TELEFONO_PROVEEDOR (IdProveedor, numTelProveedor) VALUES (:idProveedor, :telefono)";
            $stmtTel = $conexion->prepare($sqlTel);
            $stmtTel->execute([
                ':idProveedor' => $idProveedor,
                ':telefono' => $telefono
            ]);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
