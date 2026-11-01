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

    // cargo los datos iniciales en las propiedades de este objeto
    public function __construct(?int $ci = null, ?string $nombre = null, ?string $apellido = null, ?int $telefono = null, ?string $direccion = null, ?string $tipoUsuario = null)
    {
        $this->ci = $ci;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->telefono = $telefono;
        $this->direccion = $direccion;
        $this->tipoUsuario = $tipoUsuario;
    }

    // devuelvo el valor de ci que tiene el objeto
    public function getCi(): int
    {
        return $this->ci ?? 0;
    }

    // devuelvo el valor de nombre que tiene el objeto
    public function getNombre(): string
    {
        return $this->nombre ?? '';
    }

    // devuelvo el valor de apellido que tiene el objeto
    public function getApellido(): string
    {
        return $this->apellido ?? '';
    }

    // devuelvo el valor de telefono que tiene el objeto
    public function getTelefono(): int
    {
        return $this->telefono ?? 0;
    }

    // devuelvo el valor de direccion que tiene el objeto
    public function getDireccion(): string
    {
        return $this->direccion ?? '';
    }

    // cambio el valor de ci dentro del objeto
    public function setCi(int $ci): void
    {
        $this->ci = $ci;
    }

    // cambio el valor de nombre dentro del objeto
    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    // cambio el valor de apellido dentro del objeto
    public function setApellido(string $apellido): void
    {
        $this->apellido = $apellido;
    }

    // cambio el valor de telefono dentro del objeto
    public function setTelefono(int $telefono): void
    {
        $this->telefono = $telefono;
    }

    // cambio el valor de direccion dentro del objeto
    public function setDireccion(string $direccion): void
    {
        $this->direccion = $direccion;
    }

    // traigo por separado los clientes y los empleados con los datos de sus cuentas
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
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtClientes = $conexion->prepare($sqlClientes);
            $stmtClientes->execute($params);
            // traigo todas las filas usando los nombres de las columnas como claves
            $clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

            $sqlEmpleados = "SELECT u.CI, u.Nombre, u.Apellido, u.Direccion,
                       CASE
                           WHEN l.TipoUsuario = 'admin' THEN 'Administrador'
                           WHEN e.Rol IS NULL OR LOWER(TRIM(e.Rol)) = 'null' OR TRIM(e.Rol) = '' THEN 'Empleado'
                           ELSE e.Rol
                       END AS Rol,
                       h.Correo, l.FechaCreado,
                       GROUP_CONCAT(t.numTelUsuario SEPARATOR '<br>') AS Telefonos
                FROM EMPLEADO e
                INNER JOIN USUARIO u ON e.CI = u.CI
                INNER JOIN HACE h ON u.CI = h.CI
                INNER JOIN LOGIN l ON h.Correo = l.Correo
                LEFT JOIN TELEFONO_USUARIO t ON u.CI = t.CI
                GROUP BY u.CI, u.Nombre, u.Apellido, u.Direccion, e.Rol, l.TipoUsuario, h.Correo, l.FechaCreado
                $condicionBusqueda";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtEmpleados = $conexion->prepare($sqlEmpleados);
            $stmtEmpleados->execute($params);
            // traigo todas las filas usando los nombres de las columnas como claves
            $empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);

            return ['clientes' => $clientes, 'empleados' => $empleados];
        } catch (PDOException $e) {
            echo "Error al cargar usuarios: " . $e->getMessage();
            return ['clientes' => [], 'empleados' => []];
        }
    }

    // creo la cuenta, el usuario, su telefono y su registro como cliente o empleado
    public function registrar(string $ci, string $nombre, string $apellido, string $correo, string $telefono, string $direccion, string $contraseña, string $tipoUsuario, ?string $rol = null): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            // agrupo los cambios para guardar todo junto o deshacerlo si algo falla
            $conexion->beginTransaction();

            $passHash = password_hash($contraseña, PASSWORD_DEFAULT);

            $sqlLogin = "INSERT INTO LOGIN (Correo, Contraseña, TipoUsuario, FechaCreado)
                     VALUES (:correo, :pass, :tipoUsuario, :fechaCreado)";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtLogin = $conexion->prepare($sqlLogin);
            $stmtLogin->execute([
                ':correo'      => $correo,
                ':pass'        => $passHash,
                ':tipoUsuario' => $tipoUsuario,
                ':fechaCreado' => date('Y-m-d-H:i:s')
            ]);

            $sqlUsuario = "INSERT INTO USUARIO (CI, Nombre, Apellido, Direccion)
                       VALUES (:ci, :nombre, :apellido, :direccion)";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtUsuario = $conexion->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':ci'        => $ci,
                ':nombre'    => $nombre,
                ':apellido'  => $apellido,
                ':direccion' => $direccion
            ]);

            // relaciono el correo de la cuenta con la cedula del usuario
            $sqlHace = "INSERT INTO HACE (Correo, CI) VALUES (:correo, :ci)";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtHace = $conexion->prepare($sqlHace);
            $stmtHace->execute([
                ':correo' => $correo,
                ':ci'     => $ci
            ]);

            // guardo los telefonos en su tabla aparte, relacionados con la cedula
            $sqlTel = "INSERT INTO TELEFONO_USUARIO (CI, numTelUsuario) VALUES (:ci, :telefono)";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtTel = $conexion->prepare($sqlTel);
            $stmtTel->execute([
                ':ci'       => $ci,
                ':telefono' => $telefono
            ]);


            // segun el tipo de cuenta creo el registro de empleado o el de cliente
            if ($tipoUsuario === 'empleado' || $tipoUsuario === 'admin') {
                $sqlEmpleado = "INSERT INTO EMPLEADO (CI, Rol) VALUES (:ci, :rol)";
                // preparo la consulta para pasar los valores por separado al ejecutarla
                $stmtEmpleado = $conexion->prepare($sqlEmpleado);
                $stmtEmpleado->execute([
                    ':ci'  => $ci,
                    ':rol' => $rol
                ]);
            } else {
                $sqlCliente = "INSERT INTO CLIENTE (CI) VALUES (:ci)";
                // preparo la consulta para pasar los valores por separado al ejecutarla
                $stmtCliente = $conexion->prepare($sqlCliente);
                $stmtCliente->execute([
                    ':ci' => $ci
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

    // busco el rol del empleado relacionado con ese correo
    public static function obtenerRolPorCorreo(?string $correo): ?string
    {
        if (!$correo) return null;

        $conexion = (new Conexion())->establecerConexion();
        $sql = "SELECT e.Rol
            FROM EMPLEADO e
            INNER JOIN USUARIO u ON e.CI = u.CI
            INNER JOIN HACE h ON u.CI = h.CI
            WHERE h.Correo = :correo";
        // preparo la consulta para pasar los valores por separado al ejecutarla
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':correo' => $correo]);

        // traigo una sola fila con los nombres de sus columnas como claves
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
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtUsuario = $conexion->prepare($sqlUsuario);
            $stmtUsuario->execute([':ci' => $ci]);
            // traigo una sola fila con los nombres de sus columnas como claves
            $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                return null;
            }

            $sqlTel = "SELECT numTelUsuario FROM TELEFONO_USUARIO WHERE CI = :ci ORDER BY numTelUsuario";
            // preparo la consulta para pasar los valores por separado al ejecutarla
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
            // agrupo los cambios para guardar todo junto o deshacerlo si algo falla
            $conexion->beginTransaction();

            $sqlUsuario = "UPDATE USUARIO SET Nombre = :nombre, Apellido = :apellido, Direccion = :direccion WHERE CI = :ci";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtUsuario = $conexion->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':nombre'    => $nombre,
                ':apellido'  => $apellido,
                ':direccion' => $direccion,
                ':ci'        => $ci
            ]);

            /* se borran los telefonos que tenia y se guardan los que llegan */
            $sqlBorrarTel = "DELETE FROM TELEFONO_USUARIO WHERE CI = :ci";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtBorrarTel = $conexion->prepare($sqlBorrarTel);
            $stmtBorrarTel->execute([':ci' => $ci]);

            // guardo los telefonos en su tabla aparte, relacionados con la cedula
            $sqlTel = "INSERT INTO TELEFONO_USUARIO (CI, numTelUsuario) VALUES (:ci, :telefono)";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtTel = $conexion->prepare($sqlTel);
            foreach ($telefonos as $telefono) {
                $stmtTel->execute([
                    ':ci'       => $ci,
                    ':telefono' => $telefono
                ]);
            }

            if ($rol !== null) {
                $sqlRol = "UPDATE EMPLEADO SET Rol = :rol WHERE CI = :ci";
                // preparo la consulta para pasar los valores por separado al ejecutarla
                $stmtRol = $conexion->prepare($sqlRol);
                $stmtRol->execute([
                    ':rol' => $rol,
                    ':ci'  => $ci
                ]);
            }

            /* misma forma de guardar la contraseña que en registrar(), para que el login la reconozca */
            if ($nuevaContraseña !== null) {
                $sqlPass = "UPDATE LOGIN SET Contraseña = :pass WHERE Correo IN (SELECT Correo FROM HACE WHERE CI = :ci)";
                // preparo la consulta para pasar los valores por separado al ejecutarla
                $stmtPass = $conexion->prepare($sqlPass);
                $stmtPass->execute([
                    ':pass' => password_hash($nuevaContraseña, PASSWORD_DEFAULT),
                    ':ci'   => $ci
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

    /* devuelve 'eliminado', 'bloqueado' (tiene datos asociados) o 'error' */
    public function eliminarUsuario(int $ci): string
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            // agrupo los cambios para guardar todo junto o deshacerlo si algo falla
            $conexion->beginTransaction();

            /* se guardan los correos antes de borrar HACE, para despues borrar su LOGIN */
            $sqlCorreos = "SELECT Correo FROM HACE WHERE CI = :ci";
            // preparo la consulta para pasar los valores por separado al ejecutarla
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

            // confirmo todos los cambios porque las operaciones anteriores terminaron bien
            $conexion->commit();
            return 'eliminado';
        } catch (PDOException $e) {
            /* si falla, se deshace todo (telefonos, login, etc. vuelven) */
            if (isset($conexion) && $conexion->inTransaction()) {
                // deshago los cambios de esta operacion si la transaccion sigue abierta
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
