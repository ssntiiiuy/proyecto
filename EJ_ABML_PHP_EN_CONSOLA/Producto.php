<?php
require_once 'Conexion.php';
require_once 'TipoProducto.php';

class Producto
{
    private int $id;
    private string $nombre;
    private TipoProducto $tipo;
    private float $precio;
    private string $descripcion;

    public function __construct(int $id, string $nombre, int $idTipoProducto, float $precio, string $descripcion)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->setTipo($idTipoProducto);
        $this->precio = $precio;
        $this->descripcion = $descripcion;
    }

    //GETTERS Y SETTERS
    public function getId()
    {
        return $this->id;
    }
    public function setId(int $id)
    {
        $this->id = $id;
    }
    public function getNombre()
    {
        return $this->nombre;
    }
    public function setNombre(string $nombre)
    {
        $this->nombre = $nombre;
    }
    public function getTipo()
    {
        return $this->tipo;
    }
    public function setTipo(int $idTipoProducto)
    {
        $conn = new Conexion();
        $sql = "SELECT * FROM tipoproducto WHERE id = :id";
        $consulta = $conn->establecer_conexion()->prepare($sql);
        $consulta->execute(["id" => $idTipoProducto]);
        $productoObtenido = $consulta->fetch(PDO::FETCH_ASSOC);

        $tipoProducto = new TipoProducto(
            $productoObtenido["id"],
            $productoObtenido["nombre"],
            $productoObtenido["porcentajeDescuento"]
        );
        $this->tipo = $tipoProducto;
    }
    public function getPrecio()
    {
        return $this->precio;
    }
    public function setPrecio(float $precio)
    {
        $this->precio = $precio;
    }
    public function getDescripcion()
    {
        return $this->descripcion;
    }
    public function setDescripcion(string $descripcion)
    {
        $this->descripcion = $descripcion;
    }

    //FUNCIONES
    public function precioDescuento(bool $esSocio): int
    {
        if ($esSocio) {
            return $this->precio - ($this->precio * $this->tipo->getPorcentajeDesc());
        }
        return 0;
    }
}
