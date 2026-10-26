<?php
require_once __DIR__ . '/Conexion.php';

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

            // Como usamos GROUP_CONCAT, filtramos con HAVING
            if ($busqueda !== '') {
                $sqlProveedor .= " HAVING p.Nombre LIKE :busqueda1 OR p.NombreEmpresa LIKE :busqueda2 OR Productos LIKE :busqueda3";
                $like = '%' . $busqueda . '%';
                $params = [
                    ':busqueda1' => $like,
                    ':busqueda2' => $like,
                    ':busqueda3' => $like
                ];
            }

            $stmt = $conexion->prepare($sqlProveedor);
            $stmt->execute($params);
            $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['proveedores' => $proveedores];
        } catch (PDOException $e) {
            echo "Error al cargar proveedores: " . $e->getMessage();
            return ['proveedores' => []];
        }
    }

    public function crearProveedor(string $nombre, string $empresa, string $telefono): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            $conexion->beginTransaction();

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

            $conexion->commit();
            return true;
        } catch (PDOException $e) {
            if (isset($conexion) && $conexion->inTransaction()) {
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
            $stmtProveedor = $conexion->prepare($sqlProveedor);
            $stmtProveedor->execute([':id' => $id]);
            $proveedor = $stmtProveedor->fetch(PDO::FETCH_ASSOC);

            if (!$proveedor) {
                return null;
            }

            $sqlTel = "SELECT numTelProveedor FROM TELEFONO_PROVEEDOR WHERE IdProveedor = :id ORDER BY numTelProveedor";
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
            $conexion->beginTransaction();

            $sqlProveedor = "UPDATE PROVEEDOR SET Nombre = :nombre, NombreEmpresa = :empresa WHERE IdProveedor = :id";
            $stmtProveedor = $conexion->prepare($sqlProveedor);
            $stmtProveedor->execute([
                ':nombre' => $nombre,
                ':empresa' => $empresa,
                ':id' => $id
            ]);

            $sqlBorrarTel = "DELETE FROM TELEFONO_PROVEEDOR WHERE IdProveedor = :id";
            $stmtBorrarTel = $conexion->prepare($sqlBorrarTel);
            $stmtBorrarTel->execute([':id' => $id]);

            $sqlTel = "INSERT INTO TELEFONO_PROVEEDOR (IdProveedor, numTelProveedor) VALUES (:idProveedor, :telefono)";
            $stmtTel = $conexion->prepare($sqlTel);
            foreach ($telefonos as $telefono) {
                $stmtTel->execute([
                    ':idProveedor' => $id,
                    ':telefono' => $telefono
                ]);
            }

            $conexion->commit();
            return true;
        } catch (PDOException $e) {
            if (isset($conexion) && $conexion->inTransaction()) {
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
            $conexion->beginTransaction();

            /* primero los telefonos (tabla hija) y despues el proveedor */
            $sqlTel = "DELETE FROM TELEFONO_PROVEEDOR WHERE IdProveedor = :id";
            $stmtTel = $conexion->prepare($sqlTel);
            $stmtTel->execute([':id' => $id]);

            $sqlProveedor = "DELETE FROM PROVEEDOR WHERE IdProveedor = :id";
            $stmtProveedor = $conexion->prepare($sqlProveedor);
            $stmtProveedor->execute([':id' => $id]);

            $conexion->commit();
            return 'eliminado';
        } catch (PDOException $e) {
            /* si falla, se deshace todo y los telefonos vuelven */
            if (isset($conexion) && $conexion->inTransaction()) {
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
