<?php
class Conexion
{
    /*
    Las siguientes variables contienen la información necesaria para la conexión con una BD.
    DSN tiene varios datos imporantes:
    1. Dirección ip de la base de datos a conectar
    2. Nombre de la base de datos
    3. Charset: El paquete de símbolos que soportará la conexión de la BD. Investigando (mínimamente) encontré que el motivo de porque
    es útil este dato -> Evitar que la información de la base de datos llegue dañada o mal interpretada por un conjunto de carácteres distinto.

    usuarioDb tiene el nombre de usuario DEL SERVIDOR DE BASE DE DATOS, generalmente es Root

    passwordDb es la contraseña del usuario, por defecto es root o simplemente "" (nada)

    PDO: Es el objeto de la clase PHP que permite el puente entre el código y la BD.
    */
    private $dsn = "mysql:host=127.0.0.1;dbname=ejemplo_poo;charset=utf8mb4";
    private $usuarioDb = "root";
    private $passwordDb = "root";
    private ?PDO $pdo = null;

    /*
    Esta función es importante, cada vez que se quiera realizar una consulta, se debe "Tender un puente" (Establecer una conexión) para que esta pueda viajar.
    La función genera una instancia del objeto, evitndo crear múltiples objetos PDO que tienen el mismo fin.
    En este caso es una función con retorno, retorna un objeto de la clase PDO (Php Data Object).
    El try en este caso sirve para capturar errores dinámicos (En tiempo de ejecución)  (ej. La base de datos no está disponible)
    */
    public function establecer_conexion(): PDO
    {
        if ($this->pdo !== null) {
            echo "Conexion ya establecida \n";
            return $this->pdo;
        }
        try {
            $this->pdo = new PDO($this->dsn, $this->usuarioDb, $this->passwordDb);
            echo "Conexion establecida por primera vez \n";
            return $this->pdo;
        } catch (PDOException $e) {
            throw new PDOException("Error de conexión a la base de datos " . $e->getMessage());
        }
    }
}
