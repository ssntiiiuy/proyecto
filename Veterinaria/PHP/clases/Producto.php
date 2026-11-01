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

    // cargo los datos iniciales en las propiedades de este objeto
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

    // devuelvo el valor de categoria que tiene el objeto
    public function getCategoria(): string
    {
        return $this->categoria ?? '';
    }

    // devuelvo el valor de stock que tiene el objeto
    public function getStock(): int
    {
        return $this->stock ?? 0;
    }

    // devuelvo el valor de stock minimo que tiene el objeto
    public function getStockMinimo(): int
    {
        return $this->stockMinimo ?? 0;
    }

    // devuelvo el valor de fecha vencimiento que tiene el objeto
    public function getFechaVencimiento(): string
    {
        return $this->fechaVencimiento ?? '';
    }


    // cambio el valor de nombre dentro del objeto
    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    // cambio el valor de categoria dentro del objeto
    public function setCategoria(string $categoria): void
    {
        $this->categoria = $categoria;
    }

    // cambio el valor de stock dentro del objeto
    public function setStock(int $stock): void
    {
        $this->stock = $stock;
    }

    // cambio el valor de stock minimo dentro del objeto
    public function setStockMinimo(int $stockMinimo): void
    {
        $this->stockMinimo = $stockMinimo;
    }

    // cambio el valor de fecha vencimiento dentro del objeto
    public function setFechaVencimiento(DateTime $fechaVencimiento): void
    {
        $this->fechaVencimiento = $fechaVencimiento;
    }


    // traigo los productos con su proveedor y aplico los filtros del listado
    public static function listadoProductos(?string $categoria = null, ?string $filtroEspecial = null, ?string $busqueda = null): array
{
    try {
        $conexion = (new Conexion())->establecerConexion();

        $sqlProducto = "SELECT p.IdProducto, p.Nombre, p.Categoria, p.Precio, p.Stock, p.StockMinimo, p.FechaVencimiento, prov.NombreEmpresa
                        FROM PRODUCTO p
                        INNER JOIN PROVEE pr ON p.IdProducto = pr.IdProducto
                        INNER JOIN PROVEEDOR prov ON pr.IdProveedor = prov.IdProveedor
                        WHERE 1=1";

        $params = [];

        if (!empty($categoria)) {
            $sqlProducto .= " AND p.Categoria = :Categoria";
            $params[':Categoria'] = $categoria;
        }

        if ($filtroEspecial === 'stock_bajo') {
            $sqlProducto .= " AND p.Stock <= p.StockMinimo";
        } elseif ($filtroEspecial === 'por_vencer') {
            $sqlProducto .= " AND p.FechaVencimiento IS NOT NULL
                             AND p.FechaVencimiento >= :hoy
                             AND p.FechaVencimiento <= DATE_ADD(:hoy, INTERVAL 30 DAY)";
            $params[':hoy'] = date('Y-m-d');
        }

        // NUEVO: Filtro por texto (Nombre del producto)
        if (!empty($busqueda)) {
            $sqlProducto .= " AND p.Nombre LIKE :busqueda";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        // preparo la consulta para pasar los valores por separado al ejecutarla
        $stmt = $conexion->prepare($sqlProducto);
        $stmt->execute($params);
        // traigo todas las filas usando los nombres de las columnas como claves
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['productos' => $productos];
    } catch (PDOException $e) {
        return ['productos' => [], 'error' => $e->getMessage()];
    }
}

    // guardo el producto y lo relaciono con el proveedor elegido
    public function crearProducto(string $nombre, string $categoria, string $precio, string $stock, string $stockMinimo, ?string $fechaVencimiento, string $NombreEmpresa): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            // agrupo los cambios para guardar todo junto o deshacerlo si algo falla
            $conexion->beginTransaction();

            $sqlProducto = "INSERT INTO PRODUCTO (Nombre, Categoria, Precio, Stock, StockMinimo, FechaVencimiento)
                VALUES (:nombre, :categoria, :precio, :stock, :stockMinimo, :fechaVencimiento)";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtProducto = $conexion->prepare($sqlProducto);
            $stmtProducto->execute([
                ':nombre' => $nombre,
                ':categoria' => $categoria,
                ':precio' => $precio,
                ':stock' => $stock,
                ':stockMinimo' => $stockMinimo,
                ':fechaVencimiento' => $fechaVencimiento
            ]);

            // recupero el id que genero la base para relacionar el registro recien creado
            $Idproducto = $conexion->lastInsertId();
            // busco el id del proveedor usando el nombre de empresa recibido
            $sqlId  = "SELECT IdProveedor FROM Proveedor WHERE NombreEmpresa = :nombreEmpresa";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtId = $conexion->prepare($sqlId);
            $stmtId->execute([':nombreEmpresa' => $NombreEmpresa]);
            // leo un solo valor del resultado de la consulta
            $idProveedor = $stmtId->fetchColumn();

            $sqlProvee = "INSERT INTO PROVEE (IdProducto, IdProveedor)
                VALUES (:idProducto, :idProveedor)";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtProvee = $conexion->prepare($sqlProvee);
            $stmtProvee->execute([
                ':idProducto' => $Idproducto,
                ':idProveedor' => $idProveedor
            ]);

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

    // cuento los productos cuyo stock llego al minimo o esta por debajo
    public static function contarStockBajo(): int
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            $sql = "SELECT COUNT(*) FROM PRODUCTO WHERE Stock <= StockMinimo";
            $stmt = $conexion->query($sql);
            // leo un solo valor del resultado de la consulta
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // cuento los productos que vencen dentro de la cantidad de dias indicada
    public static function contarPorVencer(int $dias = 30): int
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            $sql = "SELECT COUNT(*) FROM PRODUCTO
                WHERE FechaVencimiento IS NOT NULL
                AND FechaVencimiento >= :hoy
                AND FechaVencimiento <= DATE_ADD(:hoy2, INTERVAL :dias DAY)";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':hoy' => date('Y-m-d'),
                ':hoy2' => date('Y-m-d'),
                ':dias' => $dias
            ]);
            // leo un solo valor del resultado de la consulta
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // busco un registro de producto por su id; si no lo encuentro devuelvo null
    public static function buscarPorId(int $id): ?array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sqlProducto = "SELECT p.IdProducto, p.Nombre, p.Categoria, p.Precio, p.Stock, p.StockMinimo, p.FechaVencimiento, pr.IdProveedor
                        FROM PRODUCTO p
                        LEFT JOIN PROVEE pr ON p.IdProducto = pr.IdProducto
                        WHERE p.IdProducto = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sqlProducto);
            $stmt->execute([':id' => $id]);
            // traigo una sola fila con los nombres de sus columnas como claves
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            return $producto ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /* actualiza el producto y su proveedor (PROVEE) en una transaccion */
    public function editarProducto(int $id, string $nombre, string $categoria, string $precio, string $stock, string $stockMinimo, ?string $fechaVencimiento, int $idProveedor): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            // agrupo los cambios para guardar todo junto o deshacerlo si algo falla
            $conexion->beginTransaction();

            $sqlProducto = "UPDATE PRODUCTO
                SET Nombre = :nombre, Categoria = :categoria, Precio = :precio, Stock = :stock,
                    StockMinimo = :stockMinimo, FechaVencimiento = :fechaVencimiento
                WHERE IdProducto = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtProducto = $conexion->prepare($sqlProducto);
            $stmtProducto->execute([
                ':nombre' => $nombre,
                ':categoria' => $categoria,
                ':precio' => $precio,
                ':stock' => $stock,
                ':stockMinimo' => $stockMinimo,
                ':fechaVencimiento' => $fechaVencimiento,
                ':id' => $id
            ]);

            /* se borra el proveedor que tenia y se guarda el elegido */
            $sqlBorrarProvee = "DELETE FROM PROVEE WHERE IdProducto = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtBorrarProvee = $conexion->prepare($sqlBorrarProvee);
            $stmtBorrarProvee->execute([':id' => $id]);

            $sqlProvee = "INSERT INTO PROVEE (IdProducto, IdProveedor) VALUES (:idProducto, :idProveedor)";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtProvee = $conexion->prepare($sqlProvee);
            $stmtProvee->execute([
                ':idProducto' => $id,
                ':idProveedor' => $idProveedor
            ]);

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

    /* devuelve 'eliminado', 'bloqueado' (esta en facturas o compras) o 'error' */
    public function eliminarProducto(int $id): string
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            // agrupo los cambios para guardar todo junto o deshacerlo si algo falla
            $conexion->beginTransaction();

            /* primero PROVEE (tabla hija) y despues el producto */
            $sqlProvee = "DELETE FROM PROVEE WHERE IdProducto = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtProvee = $conexion->prepare($sqlProvee);
            $stmtProvee->execute([':id' => $id]);

            $sqlProducto = "DELETE FROM PRODUCTO WHERE IdProducto = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtProducto = $conexion->prepare($sqlProducto);
            $stmtProducto->execute([':id' => $id]);

            // confirmo todos los cambios porque las operaciones anteriores terminaron bien
            $conexion->commit();
            return 'eliminado';
        } catch (PDOException $e) {
            /* si falla, se deshace todo y PROVEE vuelve */
            if (isset($conexion) && $conexion->inTransaction()) {
                // deshago los cambios de esta operacion si la transaccion sigue abierta
                $conexion->rollBack();
            }
            /* 1451: ARTICULO_FACTURA, COMPRA o CLIENTE_COMPRA usan este producto */
            if ($e->errorInfo[1] == 1451) {
                return 'bloqueado';
            }
            return 'error';
        }
    }
}
