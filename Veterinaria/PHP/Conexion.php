<?php
class Conexion
{

    private $dsn = "mysql:host=127.0.0.1;dbname=vet;charset=utf8mb4";
    private $usuario = "root";
    private $password = "";
    private ?PDO $pdo = null;

    public function establecerConexion(): PDO
    {
        if ($this->pdo !== null) {
            // echo "Conexion establecida.\n";
            return $this->pdo;
        }
        try {
            $this->pdo = new PDO($this->dsn, $this->usuario, $this->password);
            // echo "Conexion establecida por primera vez.\n";
            return $this->pdo;
        } catch (PDOException $e) {
            throw new PDOException("Error de conexión: " . $e->getMessage());
        }
    }
}