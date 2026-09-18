<?php
class TipoProducto
{
    private int $id;
    private string $nombre;
    private int $porcentajeDesc;

    public function __construct(int $id, string $nombre, int $porcentajeDesc)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->porcentajeDesc = $porcentajeDesc;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre)
    {
        $this->nombre = $nombre;
    }

    public function getPorcentajeDesc()
    {
        return $this->porcentajeDesc / 100;
    }

    public function setPorcentajeDesc(int $porcentajeDesc)
    {
        $this->porcentajeDesc = $porcentajeDesc;
    }
}
