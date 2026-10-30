<?php
require_once __DIR__ . '/Conexion.php';
require_once __DIR__ . '/Login.php';

class Usuario
{
    private ?int $ci;
    private ?string $nombre;
    private ?string $apellido;
    private ?int $telefono;
    private ?string $direccion;
    private ?string $tipoUsuario;

    public function __construct(?int $ci = null, ?string $nombre = null, ?string $apellido = null, ?int $telefono = null, ?string $direccion = null, ?string $tipoUsuario = null)
    {
        $this->ci = $ci;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->telefono = $telefono;
        $this->direccion = $direccion;
        $this->tipoUsuario = $tipoUsuario;
    }

    public function getCi(): int
    {
        return $this->ci ?? 0;
    }

    public function getNombre(): string
    {
        return $this->nombre ?? '';
    }

    public function getApellido(): string
    {
        return $this->apellido ?? '';
    }

    public function getTelefono(): int
    {
        return $this->telefono ?? 0;
    }

    public function getDireccion(): string
    {
        return $this->direccion ?? '';
    }

    public function setCi(int $ci): void
    {
        $this->ci = $ci;
    }

    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    public function setApellido(string $apellido): void
    {
        $this->apellido = $apellido;
    }

    public function setTelefono(int $telefono): void
    {
        $this->telefono = $telefono;
    }

    public function setDireccion(string $direccion): void
    {
        $this->direccion = $direccion;
    }

    public static function listadoUsuarios(string $busqueda = ''): array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $condicionBusqueda = '';
            $params = [];
            if ($busqueda !== '') {
                $condicionBusqueda = " HAVING u.CI LIKE :busqueda1 OR u.Nombre LIKE :busqueda2
                                    OR u.Apellido LIKE :busqueda3 OR h.Correo LIKE :busqueda4 ";
                $like = '%' . $busqueda . '%';
                $params = [
                    ':busqueda1' => $like,
                    ':busqueda2' => $like,
                    ':busqueda3' => $like,
                    ':busqueda4' => $like,
                ];
            }

            $sqlClientes = "SELECT u.CI, u.Nombre, u.Apellido, u.Direccion, h.Correo, l.FechaCreado,
                      GROUP_CONCAT(t.numTelUsuario SEPARATOR '<br>') AS Telefonos
               FROM CLIENTE c
               INNER JOIN USUARIO u ON c.CI = u.CI
               INNER JOIN HACE h ON u.CI = h.CI
               INNER JOIN LOGIN l ON h.Correo = l.Correo
               LEFT JOIN TELEFONO_USUARIO t ON u.CI = t.CI
               GROUP BY u.CI, u.Nombre, u.Apellido, u.Direccion, h.Correo, l.FechaCreado
               $condicionBusqueda";
            $stmtClientes = $conexion->prepare($sqlClientes);
            $stmtClientes->execute($params);
            $clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

            $sqlEmpleados = "SELECT u.CI, u.Nombre, u.Apellido, u.Direccion, e.Rol, h.Correo, l.FechaCreado,
                       GROUP_CONCAT(t.numTelUsuario SEPARATOR '<br>') AS Telefonos
                FROM EMPLEADO e
                INNER JOIN USUARIO u ON e.CI = u.CI
                INNER JOIN HACE h ON u.CI = h.CI
                INNER JOIN LOGIN l ON h.Correo = l.Correo
                LEFT JOIN TELEFONO_USUARIO t ON u.CI = t.CI
                GROUP BY u.CI, u.Nombre, u.Apellido, u.Direccion, e.Rol, h.Correo, l.FechaCreado
                $condicionBusqueda";
            $stmtEmpleados = $conexion->prepare($sqlEmpleados);
            $stmtEmpleados->execute($params);
            $empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);

            return ['clientes' => $clientes, 'empleados' => $empleados];
        } catch (PDOException $e) {
            echo "Error al cargar usuarios: " . $e->getMessage();
            return ['clientes' => [], 'empleados' => []];
        }
    }

    public function registrar(string $ci, string $nombre, string $apellido, string $correo, string $telefono, string $direccion, string $contraseña, string $tipoUsuario, ?string $rol = null): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            $conexion->beginTransaction();

            $passHash = password_hash($contraseña, PASSWORD_DEFAULT);

            $sqlLogin = "INSERT INTO LOGIN (Correo, Contraseña, TipoUsuario, FechaCreado)
                     VALUES (:correo, :pass, :tipoUsuario, :fechaCreado)";
            $stmtLogin = $conexion->prepare($sqlLogin);
            $stmtLogin->execute([
                ':correo'      => $correo,
                ':pass'        => $passHash,
                ':tipoUsuario' => $tipoUsuario,
                ':fechaCreado' => date('Y-m-d-H:i:s')
            ]);

            $sqlUsuario = "INSERT INTO USUARIO (CI, Nombre, Apellido, Direccion)
                       VALUES (:ci, :nombre, :apellido, :direccion)";
            $stmtUsuario = $conexion->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':ci'        => $ci,
                ':nombre'    => $nombre,
                ':apellido'  => $apellido,
                ':direccion' => $direccion
            ]);

            $sqlHace = "INSERT INTO HACE (Correo, CI) VALUES (:correo, :ci)";
            $stmtHace = $conexion->prepare($sqlHace);
            $stmtHace->execute([
                ':correo' => $correo,
                ':ci'     => $ci
            ]);

            $sqlTel = "INSERT INTO TELEFONO_USUARIO (CI, numTelUsuario) VALUES (:ci, :telefono)";
            $stmtTel = $conexion->prepare($sqlTel);
            $stmtTel->execute([
                ':ci'       => $ci,
                ':telefono' => $telefono
            ]);


            if ($tipoUsuario === 'empleado' || $tipoUsuario === 'admin') {
                $sqlEmpleado = "INSERT INTO EMPLEADO (CI, Rol) VALUES (:ci, :rol)";
                $stmtEmpleado = $conexion->prepare($sqlEmpleado);
                $stmtEmpleado->execute([
                    ':ci'  => $ci,
                    ':rol' => $rol
                ]);
            } else {
                $sqlCliente = "INSERT INTO CLIENTE (CI) VALUES (:ci)";
                $stmtCliente = $conexion->prepare($sqlCliente);
                $stmtCliente->execute([
                    ':ci' => $ci
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

    public static function obtenerRolPorCorreo(?string $correo): ?string
    {
        if (!$correo) return null;

        $conexion = (new Conexion())->establecerConexion();
        $sql = "SELECT e.Rol
            FROM EMPLEADO e
            INNER JOIN USUARIO u ON e.CI = u.CI
            INNER JOIN HACE h ON u.CI = h.CI
            WHERE h.Correo = :correo";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':correo' => $correo]);

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila['Rol'] ?? null;
    }

    /* devuelve el usuario con su correo, tipo, rol y telefonos en $usuario['telefonos'] */
    public static function buscarPorCi(int $ci): ?array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sqlUsuario = "SELECT u.CI, u.Nombre, u.Apellido, u.Direccion, h.Correo, l.TipoUsuario, e.Rol,
                       c.CI AS CiCliente, e.CI AS CiEmpleado
                   FROM USUARIO u
                   LEFT JOIN HACE h ON u.CI = h.CI
                   LEFT JOIN LOGIN l ON h.Correo = l.Correo
                   LEFT JOIN CLIENTE c ON u.CI = c.CI
                   LEFT JOIN EMPLEADO e ON u.CI = e.CI
                   WHERE u.CI = :ci";
            $stmtUsuario = $conexion->prepare($sqlUsuario);
            $stmtUsuario->execute([':ci' => $ci]);
            $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                return null;
            }

            $sqlTel = "SELECT numTelUsuario FROM TELEFONO_USUARIO WHERE CI = :ci ORDER BY numTelUsuario";
            $stmtTel = $conexion->prepare($sqlTel);
            $stmtTel->execute([':ci' => $ci]);
            $usuario['telefonos'] = $stmtTel->fetchAll(PDO::FETCH_COLUMN);

            return $usuario;
        } catch (PDOException $e) {
            return null;
        }
    }

    /* CI, correo y tipo no se cambian. $rol solo para empleados y $nuevaContraseña solo si se escribio una */
    public function editarUsuario(int $ci, string $nombre, string $apellido, string $direccion, array $telefonos, ?string $rol, ?string $nuevaContraseña): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            $conexion->beginTransaction();

            $sqlUsuario = "UPDATE USUARIO SET Nombre = :nombre, Apellido = :apellido, Direccion = :direccion WHERE CI = :ci";
            $stmtUsuario = $conexion->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':nombre'    => $nombre,
                ':apellido'  => $apellido,
                ':direccion' => $direccion,
                ':ci'        => $ci
            ]);

            /* se borran los telefonos que tenia y se guardan los que llegan */
            $sqlBorrarTel = "DELETE FROM TELEFONO_USUARIO WHERE CI = :ci";
            $stmtBorrarTel = $conexion->prepare($sqlBorrarTel);
            $stmtBorrarTel->execute([':ci' => $ci]);

            $sqlTel = "INSERT INTO TELEFONO_USUARIO (CI, numTelUsuario) VALUES (:ci, :telefono)";
            $stmtTel = $conexion->prepare($sqlTel);
            foreach ($telefonos as $telefono) {
                $stmtTel->execute([
                    ':ci'       => $ci,
                    ':telefono' => $telefono
                ]);
            }

            if ($rol !== null) {
                $sqlRol = "UPDATE EMPLEADO SET Rol = :rol WHERE CI = :ci";
                $stmtRol = $conexion->prepare($sqlRol);
                $stmtRol->execute([
                    ':rol' => $rol,
                    ':ci'  => $ci
                ]);
            }

            /* misma forma de guardar la contraseña que en registrar(), para que el login la reconozca */
            if ($nuevaContraseña !== null) {
                $sqlPass = "UPDATE LOGIN SET Contraseña = :pass WHERE Correo IN (SELECT Correo FROM HACE WHERE CI = :ci)";
                $stmtPass = $conexion->prepare($sqlPass);
                $stmtPass->execute([
                    ':pass' => password_hash($nuevaContraseña, PASSWORD_DEFAULT),
                    ':ci'   => $ci
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

    /* devuelve 'eliminado', 'bloqueado' (tiene datos asociados) o 'error' */
    public function eliminarUsuario(int $ci): string
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            $conexion->beginTransaction();

            /* se guardan los correos antes de borrar HACE, para despues borrar su LOGIN */
            $sqlCorreos = "SELECT Correo FROM HACE WHERE CI = :ci";
            $stmtCorreos = $conexion->prepare($sqlCorreos);
            $stmtCorreos->execute([':ci' => $ci]);
            $correos = $stmtCorreos->fetchAll(PDO::FETCH_COLUMN);

            /* orden: TELEFONO_USUARIO, HACE, LOGIN, CLIENTE o EMPLEADO, USUARIO */
            $stmt = $conexion->prepare("DELETE FROM TELEFONO_USUARIO WHERE CI = :ci");
            $stmt->execute([':ci' => $ci]);

            $stmt = $conexion->prepare("DELETE FROM HACE WHERE CI = :ci");
            $stmt->execute([':ci' => $ci]);

            $stmt = $conexion->prepare("DELETE FROM LOGIN WHERE Correo = :correo");
            foreach ($correos as $correo) {
                $stmt->execute([':correo' => $correo]);
            }

            /* solo una de las dos tiene la fila */
            $stmt = $conexion->prepare("DELETE FROM CLIENTE WHERE CI = :ci");
            $stmt->execute([':ci' => $ci]);

            $stmt = $conexion->prepare("DELETE FROM EMPLEADO WHERE CI = :ci");
            $stmt->execute([':ci' => $ci]);

            $stmt = $conexion->prepare("DELETE FROM USUARIO WHERE CI = :ci");
            $stmt->execute([':ci' => $ci]);

            $conexion->commit();
            return 'eliminado';
        } catch (PDOException $e) {
            /* si falla, se deshace todo (telefonos, login, etc. vuelven) */
            if (isset($conexion) && $conexion->inTransaction()) {
                $conexion->rollBack();
            }
            /* 1451: MASCOTA, CITA, COMPRA, CLIENTE_COMPRA o BRINDA usan este usuario */
            if ($e->errorInfo[1] == 1451) {
                return 'bloqueado';
            }
            return 'error';
        }
    }
}
