<?php
require_once 'Conexion.php';
require_once 'Login.php';

class Usuario
{
    private ?string $ci;
    private ?string $nombre;
    private ?string $apellido;
    private ?string $telefono;
    private ?string $direccion;
    private ?string $tipoUsuario;

    public function __construct(?string $ci = null, ?string $nombre = null, ?string $apellido = null, ?string $telefono = null, ?string $direccion = null, ?string $tipoUsuario = null)
    {
        $this->ci = $ci;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->telefono = $telefono;
        $this->direccion = $direccion;
        $this->tipoUsuario = $tipoUsuario;
    }

    public function getCi(): string
    {
        return $this->ci ?? '';
    }

    public function getNombre(): string
    {
        return $this->nombre ?? '';
    }

    public function getApellido(): string
    {
        return $this->apellido ?? '';
    }

    public function getTelefono(): string
    {
        return $this->telefono ?? '';
    }

    public function getDireccion(): string
    {
        return $this->direccion ?? '';
    }

    public function setCi(string $ci): void
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

    public function setTelefono(string $telefono): void
    {
        $this->telefono = $telefono;
    }

    public function setDireccion(string $direccion): void
    {
        $this->direccion = $direccion;
    }

    public static function listadoUsuarios(): array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sqlClientes = "SELECT u.CI, u.Nombre, u.Apellido, u.Direccion, h.Correo, l.FechaCreado,
                          GROUP_CONCAT(t.numTelUsuario SEPARATOR '<br>') AS Telefonos
                   FROM CLIENTE c
                   INNER JOIN USUARIO u ON c.CI = u.CI
                   INNER JOIN HACE h ON u.CI = h.CI
                   INNER JOIN LOGIN l ON h.Correo = l.Correo
                   LEFT JOIN TELEFONO_USUARIO t ON u.CI = t.CI
                   GROUP BY u.CI, u.Nombre, u.Apellido, u.Direccion, h.Correo, l.FechaCreado";
            $stmtClientes = $conexion->query($sqlClientes);
            $clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

            $sqlEmpleados = "SELECT u.CI, u.Nombre, u.Apellido, u.Direccion, e.Rol, h.Correo, l.FechaCreado,
                           GROUP_CONCAT(t.numTelUsuario SEPARATOR '<br>') AS Telefonos
                    FROM EMPLEADO e
                    INNER JOIN USUARIO u ON e.CI = u.CI
                    INNER JOIN HACE h ON u.CI = h.CI
                    INNER JOIN LOGIN l ON h.Correo = l.Correo
                    LEFT JOIN TELEFONO_USUARIO t ON u.CI = t.CI
                    GROUP BY u.CI, u.Nombre, u.Apellido, u.Direccion, e.Rol, h.Correo, l.FechaCreado";
            $stmtEmpleados = $conexion->query($sqlEmpleados);
            $empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);

            return [
                'clientes' => $clientes,
                'empleados' => $empleados
            ];
        } catch (PDOException $e) {
            echo "Error al cargar usuarios: " . $e->getMessage();
            return [
                'clientes' => [],
                'empleados' => []
            ];
        }
    }

    public function registrar(string $ci, string $nombre, string $apellido, string $correo, string $telefono, string $direccion, string $contraseña, string $tipoUsuario, ?string $rol = null): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

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
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
