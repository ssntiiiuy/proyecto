<?php
class Usuario
{
    private int $id;
    private string $nombre;
    private string $apellido;
    private DateTime $nacimiento;
    private bool $esSocio;

    public function __construct(int $id, string $nombre, string $apellido, string $nacimiento, bool $esSocio)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        //$this->nacimiento = new DateTime()->createFromFormat("d/m/Y", $nacimiento);
        $this->nacimiento = new DateTime($nacimiento);
        $this->esSocio = $esSocio;
    }

    public static function constructUsuario(string $nombre, string $apellido, string $nacimiento, bool $esSocio)
    {
        return new self(0, $nombre, $apellido, $nacimiento, $esSocio);
    }

    public function getEdad(): string
    {
        $edad = $this->nacimiento->diff(new DateTime("now"))->format("%y");
        return $edad;
    }
    public function getNacimiento(): DateTime
    {
        return $this->nacimiento;
    }

    public function setNacimiento(DateTime $nacimiento)
    {
        $this->nacimiento = $nacimiento;
    }

    public function getNombre()
    {
        return $this->nombre;
    }
    public function getApellido()
    {
        return $this->apellido;
    }
    public function getEsSocio()
    {
        return $this->esSocio;
    }

    public function presentarse(): string
    {
        $presentacion = $this->nombre . " " . $this->apellido . " Naciste el " . $this->getNacimiento()->format("d/m/Y") . " tienes " . $this->getEdad() . " años";
        if ($this->esSocio) {
            $presentacion = $presentacion . " ¡Bienvenido socio!";
        } else {
            $presentacion = $presentacion . " HOLA.";
        }
        return $presentacion;
    }
}
