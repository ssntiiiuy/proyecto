<?php
require_once __DIR__ . '/Conexion.php';

class Producto
{
    private ?int $id;
    private ?string $nombre;
    private ?string $categoria;
    private ?float $precio;
    private ?int $stock;
    private ?int $stockMinimo;
    private ?DateTime $fechaVencimiento;

    public function __construct(?int $id = null, ?string $nombre = null, ?string $categoria = null, ?float $precio = null, ?int $stock = null, ?int $stockMinimo = null, ?DateTime $fechaVencimiento = null)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->categoria = $categoria;
        $this->precio = $precio;
        $this->stock = $stock;
        $this->stockMinimo = $stockMinimo;
        $this->fechaVencimiento = $fechaVencimiento;
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function getNombre(): string
    {
        return $this->nombre ?? '';
    }

    public function getCategoria(): string
    {
        return $this->categoria ?? '';
    }

    public function getStock(): int
    {
        return $this->stock ?? 0;
    }

    public function getStockMinimo(): int
    {
        return $this->stockMinimo ?? 0;
    }

    public function getFechaVencimiento(): string
    {
        return $this->fechaVencimiento ?? '';
    }


    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    public function setCategoria(string $categoria): void
    {
        $this->categoria = $categoria;
    }

    public function setStock(int $stock): void
    {
        $this->stock = $stock;
    }

    public function setStockMinimo(int $stockMinimo): void
    {
        $this->stockMinimo = $stockMinimo;
    }

    public function setFechaVencimiento(DateTime $fechaVencimiento): void
    {
        $this->fechaVencimiento = $fechaVencimiento;
    }

    public static function listadoProductos(): array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sqlProducto = "SELECT p.IdProducto, p.Nombre, p.Categoria, p.Precio, p.Stock, p.StockMinimo, p.FechaVencimiento, prov.NombreEmpresa
                   FROM PRODUCTO p
                   INNER JOIN PROVEE pr ON p.IdProducto = pr.IdProducto
                   INNER JOIN PROVEEDOR prov ON pr.IdProveedor = prov.IdProveedor";
            $stmtProducto = $conexion->query($sqlProducto);
            $productos = $stmtProducto->fetchAll(PDO::FETCH_ASSOC);

            return ['productos' => $productos];

        } catch (PDOException $e) {
            echo "Error al cargar productos: " . $e->getMessage();
            return [
                'productos' => []
            ];
        }
    }

    public function crearProducto(string $nombre, string $categoria, string $precio, string $stock, string $stockMinimo, string $fechaVencimiento, string $NombreEmpresa): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sqlProducto = "INSERT INTO PRODUCTO (Nombre, Categoria, Precio, Stock, StockMinimo, FechaVencimiento)
                VALUES (:nombre, :categoria, :precio, :stock, :stockMinimo, :fechaVencimiento)";
            $stmtProducto = $conexion->prepare($sqlProducto);
            $stmtProducto->execute([
                ':nombre' => $nombre,
                ':categoria' => $categoria,
                ':precio' => $precio,
                ':stock' => $stock,
                ':stockMinimo' => $stockMinimo,
                ':fechaVencimiento' => $fechaVencimiento
            ]);

            $Idproducto = $conexion->lastInsertId();
            $sqlId  = "SELECT IdProveedor FROM Proveedor WHERE NombreEmpresa = :nombreEmpresa";
            $stmtId = $conexion->prepare($sqlId);
            $stmtId->execute([':nombreEmpresa' => $NombreEmpresa]);
            $idProveedor = $stmtId->fetchColumn();

            $sqlProvee = "INSERT INTO PROVEE (IdProducto, IdProveedor)
                VALUES (:idProducto, :idProveedor)";
            $stmtProvee = $conexion->prepare($sqlProvee);
            $stmtProvee->execute([
                ':idProducto' => $Idproducto,
                ':idProveedor' => $idProveedor
            ]);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
