<?php
require_once __DIR__ . '/Conexion.php';

class Proveedor
{
    private ?int $id;
    private ?string $nombre;
    private ?string $empresa;
    private ?int $telefono;
    private ?string $producto;

    // cargo los datos iniciales en las propiedades de este objeto
    public function __construct(?int $id = null, ?string $nombre = null, ?string $empresa = null, ?int $telefono = null, ?string $producto = null)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->empresa = $empresa;
        $this->telefono = $telefono;
        $this->producto = $producto;
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

    // devuelvo el valor de empresa que tiene el objeto
    public function getEmpresa(): string
    {
        return $this->empresa ?? '';
    }

    // devuelvo el valor de telefono que tiene el objeto
    public function getTelefono(): int
    {
        return $this->telefono ?? 0;
    }

    // devuelvo el valor de producto que tiene el objeto
    public function getProducto(): string
    {
        return $this->producto ?? '';
    }


    // cambio el valor de nombre dentro del objeto
    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    // cambio el valor de empresa dentro del objeto
    public function setEmpresa(string $empresa): void
    {
        $this->empresa = $empresa;
    }

    // cambio el valor de telefono dentro del objeto
    public function setTelefono(int $telefono): void
    {
        $this->telefono = $telefono;
    }

    // cambio el valor de producto dentro del objeto
    public function setProducto(string $producto): void
    {
        $this->producto = $producto;
    }

    // traigo los proveedores con sus telefonos y aplico el texto de busqueda
    public static function listadoProveedor(string $busqueda = ''): array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sqlProveedor = "SELECT p.IdProveedor, p.Nombre, p.NombreEmpresa,
                        GROUP_CONCAT(DISTINCT tp.numTelProveedor ORDER BY tp.numTelProveedor SEPARATOR ' | ') AS Telefonos,
                        GROUP_CONCAT(DISTINCT pro.Nombre ORDER BY pro.Nombre SEPARATOR ', ') AS Productos
                        FROM PROVEEDOR p
                        LEFT JOIN TELEFONO_PROVEEDOR tp ON p.IdProveedor = tp.IdProveedor
                        LEFT JOIN PROVEE pr ON p.IdProveedor = pr.IdProveedor
                        LEFT JOIN producto pro ON pr.IdProducto = pro.IdProducto
                        GROUP BY p.IdProveedor, p.Nombre, p.NombreEmpresa";

            $params = [];

            // como usamos GROUP_CONCAT, filtramos con HAVING
            if ($busqueda !== '') {
                $sqlProveedor .= " HAVING p.Nombre LIKE :busqueda1 OR p.NombreEmpresa LIKE :busqueda2 OR Productos LIKE :busqueda3";
                $like = '%' . $busqueda . '%';
                $params = [
                    ':busqueda1' => $like,
                    ':busqueda2' => $like,
                    ':busqueda3' => $like
                ];
            }

            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sqlProveedor);
            $stmt->execute($params);
            // traigo todas las filas usando los nombres de las columnas como claves
            $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['proveedores' => $proveedores];
        } catch (PDOException $e) {
            echo "Error al cargar proveedores: " . $e->getMessage();
            return ['proveedores' => []];
        }
    }

    // guardo el proveedor y su telefono en las tablas que corresponden
    public function crearProveedor(string $nombre, string $empresa, string $telefono): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            // agrupo los cambios para guardar todo junto o deshacerlo si algo falla
            $conexion->beginTransaction();

            $sqlLogin = "INSERT INTO PROVEEDOR (Nombre, NombreEmpresa) VALUES (:nombre, :empresa)";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtLogin = $conexion->prepare($sqlLogin);
            $stmtLogin->execute([
                ':nombre' => $nombre,
                ':empresa' => $empresa
            ]);

            // recupero el id que genero la base para relacionar el registro recien creado
            $idProveedor = $conexion->lastInsertId();

            $sqlTel = "INSERT INTO TELEFONO_PROVEEDOR (IdProveedor, numTelProveedor) VALUES (:idProveedor, :telefono)";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtTel = $conexion->prepare($sqlTel);
            $stmtTel->execute([
                ':idProveedor' => $idProveedor,
                ':telefono' => $telefono
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

    /* devuelve el proveedor con sus telefonos en $proveedor['telefonos'] */
    public static function buscarPorId(int $id): ?array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sqlProveedor = "SELECT IdProveedor, Nombre, NombreEmpresa FROM PROVEEDOR WHERE IdProveedor = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtProveedor = $conexion->prepare($sqlProveedor);
            $stmtProveedor->execute([':id' => $id]);
            // traigo una sola fila con los nombres de sus columnas como claves
            $proveedor = $stmtProveedor->fetch(PDO::FETCH_ASSOC);

            if (!$proveedor) {
                return null;
            }

            $sqlTel = "SELECT numTelProveedor FROM TELEFONO_PROVEEDOR WHERE IdProveedor = :id ORDER BY numTelProveedor";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtTel = $conexion->prepare($sqlTel);
            $stmtTel->execute([':id' => $id]);
            $proveedor['telefonos'] = $stmtTel->fetchAll(PDO::FETCH_COLUMN);

            return $proveedor;
        } catch (PDOException $e) {
            return null;
        }
    }

    /* se borran los telefonos que tenia y se guardan los que llegan */
    public function editarProveedor(int $id, string $nombre, string $empresa, array $telefonos): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            // agrupo los cambios para guardar todo junto o deshacerlo si algo falla
            $conexion->beginTransaction();

            $sqlProveedor = "UPDATE PROVEEDOR SET Nombre = :nombre, NombreEmpresa = :empresa WHERE IdProveedor = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtProveedor = $conexion->prepare($sqlProveedor);
            $stmtProveedor->execute([
                ':nombre' => $nombre,
                ':empresa' => $empresa,
                ':id' => $id
            ]);

            // borro los telefonos anteriores para reemplazarlos con la lista nueva
            $sqlBorrarTel = "DELETE FROM TELEFONO_PROVEEDOR WHERE IdProveedor = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtBorrarTel = $conexion->prepare($sqlBorrarTel);
            $stmtBorrarTel->execute([':id' => $id]);

            $sqlTel = "INSERT INTO TELEFONO_PROVEEDOR (IdProveedor, numTelProveedor) VALUES (:idProveedor, :telefono)";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtTel = $conexion->prepare($sqlTel);
            foreach ($telefonos as $telefono) {
                $stmtTel->execute([
                    ':idProveedor' => $id,
                    ':telefono' => $telefono
                ]);
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

    /* devuelve 'eliminado', 'bloqueado' (tiene productos) o 'error' */
    public function eliminarProveedor(int $id): string
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            // agrupo los cambios para guardar todo junto o deshacerlo si algo falla
            $conexion->beginTransaction();

            /* primero los telefonos (tabla hija) y despues el proveedor */
            $sqlTel = "DELETE FROM TELEFONO_PROVEEDOR WHERE IdProveedor = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtTel = $conexion->prepare($sqlTel);
            $stmtTel->execute([':id' => $id]);

            $sqlProveedor = "DELETE FROM PROVEEDOR WHERE IdProveedor = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtProveedor = $conexion->prepare($sqlProveedor);
            $stmtProveedor->execute([':id' => $id]);

            // confirmo todos los cambios porque las operaciones anteriores terminaron bien
            $conexion->commit();
            return 'eliminado';
        } catch (PDOException $e) {
            /* si falla, se deshace todo y los telefonos vuelven */
            if (isset($conexion) && $conexion->inTransaction()) {
                // deshago los cambios de esta operacion si la transaccion sigue abierta
                $conexion->rollBack();
            }
            /* 1451: PROVEE usa este proveedor (tiene productos) */
            if ($e->errorInfo[1] == 1451) {
                return 'bloqueado';
            }
            return 'error';
        }
    }
}
