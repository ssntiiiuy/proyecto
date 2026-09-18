<?php
require_once 'Conexion.php';
require_once 'Usuario.php';
require_once 'TipoProducto.php';
require_once 'Producto.php';


// REALIZA VARIAS CONSUTLAS DE PRUEBA UTILIZANDO CUALQUIERA DE LAS TABLAS CREADAS.
// 1 CONSULTA A LA BD UTILIZANDO SELECT.
// 1 INGRESO DE DATOS UTILIZANDO INSERT INTO.
// 1 ELIMINACIÓN DE UNA FILA DE DATOS UTILIZANDO DELETE FROM.
// 1 MODIFICACIÓN DE UN VALOR UTILIZANDO UPDATE TABLE.

/*
NOTA: Se omitirá la explicación del funcionamiento y motivo de uso de las estructuras Do/ Switch.
*/
$opc = 0;
do {
    echo "0. Salir \n";
    echo "1. Hacer descuento \n";
    echo "2. Agregar un usuario \n";
    echo "3. Borrar un usuario \n";
    echo "4. Modificar un usuario \n";
    echo "5. Listar usuarios \n";

    $opc = readline("Seleccione una opción");
    switch ($opc) {
        case 0:
            echo "Saliendo ... \n";
            break;
        case 1:
            /*CASO PARA REALIZAR DESCUENTO
            Esta funcionalidad utiliza otras como el listado de usuarios y productos.
            */
            $listaUsuariosDesdeBd = obtenerListaUsuarios();
            foreach ($listaUsuariosDesdeBd as $posicion => $usuario) {
                echo $posicion . " - " . $usuario["nombre"] . " " . $usuario["apellido"] . " " . $usuario["nacimiento"] . " " . $usuario["esSocio"] . "\n";
            };

            do {
                $usropc = readline(" Seleccione un usuario: ");
            } while (!($usropc < count($listaUsuariosDesdeBd) && $usropc >= 0));

            $listaProductosDesdeBd = obtenerListaProductos();
            foreach ($listaProductosDesdeBd as $index => $producto) {
                echo $index . " - " . $producto["nombre"] . " " . $producto["descripcion"] . " $" . $producto["precio"] . "\n";
            }

            do {
                $prdopc = readline(" Seleccione un producto: ");
            } while (!($prdopc < count($listaProductosDesdeBd) && $prdopc >= 0));

            $usuarioElegido = $listaUsuariosDesdeBd[$usropc];
            $productoElegido = $listaProductosDesdeBd[$prdopc];

            /*¿QUE ES ESTO?
            Debido a que Fetch o FetchAll devuelve la información de la Base de datos en forma de arreglo asociativo.
            Por tanto y en este caso particular, se crea los objetos con los datos del arreglo asociativo (En este caso, se seleccionó una tupla/fila de datos)
            */
            $obj_usuarioElegido = new Usuario(
                $usuarioElegido["id"],
                $usuarioElegido["nombre"],
                $usuarioElegido["apellido"],
                $usuarioElegido["nacimiento"],
                $usuarioElegido["esSocio"],
            );
            $obj_productoElegido = new Producto(
                $productoElegido["id"],
                $productoElegido["nombre"],
                $productoElegido["idTipoProducto"],
                $productoElegido["precio"],
                $productoElegido["descripcion"]
            );

            echo "Precio original: " . $obj_productoElegido->getPrecio() . " \n";
            $desc = $obj_productoElegido->precioDescuento($obj_usuarioElegido->getEsSocio());
            echo "Precio descuento: " . (($desc == 0) ? " No hay descuento " : "El descuento es de: $" . $desc) . " \n";
            break;
        case 2:
            /*
            Se toman los datos necesarios para crear un usuario, en este caso se piden por consola pero en la realidad la información vendría de un formulario HTML.
            */
            echo "-------- Registro de usuario ----------- \n";
            $nombre = readline("Nombre: ");
            $apellido = readline("Apellido: ");
            $nacimiento = readline("Nacimiento");
            $esSocio = (bool)readline("¿Socio? 1-yes/0-no");

            /*
            constructUsuario es una función pública estática *1 que juega el papel de un constructor para crear un objeto usuario (por tanto, retorna un objeto de este tipo).

            *1 Nota: En programación avanzada, vimos el concepto de función estática en Java. Investiga en caso de que hayas olvidado su funcionamiento. En terminos simples: La función estática permite llamarla sin necesidad de tener un objeto instanciado de la clase dónde pertenece la función.
            */
            $nuevoUsuario = Usuario::constructUsuario($nombre, $apellido, $nacimiento, $esSocio);
            ingresarUsuario($nuevoUsuario); // Esta función es la que permite ingresar al objeto usuario (En este caso $nuevoUsuario) en una base de datos

            break;

        case 3:
            $listaUsuariosDesdeBd = obtenerListaUsuarios();
            foreach ($listaUsuariosDesdeBd as $posicion => $usuario) {
                echo $posicion . " - " . $usuario["nombre"] . " " . $usuario["apellido"] . " " . $usuario["nacimiento"] . " " . $usuario["esSocio"] . "\n";
            };
            $pos_elegida = readline("¿Cuál usuario quieres eliminar? selecciona su indice");

            //usuarioAEliminar es la fila de información (array) de un usuario que se seleccionó eliminar. En este caso, de esta fila de información solo nos interesa el id del usuario para pasarselo a la función que realiza la sentencia DELETE, que es: eliminarUsuario($usuarioAEliminar["id"])
            $usuarioAEliminar = $listaUsuariosDesdeBd[$pos_elegida];
            eliminarUsuario($usuarioAEliminar["id"]);
            break;

        case 4:
            $listaUsuariosDesdeBd = obtenerListaUsuarios();
            foreach ($listaUsuariosDesdeBd as $posicion => $usuario) {
                echo $posicion . " - " . $usuario["nombre"] . " " . $usuario["apellido"] . " " . $usuario["nacimiento"] . " " . $usuario["esSocio"] . "\n";
            };
            $pos_elegida = readline("¿Cuál usuario quieres modificar? selecciona su indice");
            $usuarioElegido = $listaUsuariosDesdeBd[$pos_elegida];
            $opc_cambiar_valor = readline("¿Que valor cambiarás? \n 1- Nombre \n 2- Apellido \n 3- Nacimiento \n 4-Socio");

            /*
            Se le solicitan dos datos importantes para modificar una fila de usuarios: 1. El id del usuario a modificar y el valor determinado que se necesita modificar (Ej. El usuario con ID 5 y el valor de la columna nacimiento)
            */
            modificarUsuario($opc_cambiar_valor, $usuarioElegido["id"]);

            break;
        case 5:
            /*
            Recorre y muestra todos los datos de una tabla determinada, en este caso Usuario
            Si te fijas bien, estas instrucciones (Obtener lista y el foreach) aparecen en el resto de funcionalidades del programa (Ingresar, borrar, modificar)
            */
            $listaUsuariosDesdeBd = obtenerListaUsuarios();
            foreach ($listaUsuariosDesdeBd as $posicion => $usuario) {
                echo $posicion . " - " . $usuario["nombre"] . " " . $usuario["apellido"] . " " . $usuario["nacimiento"] . " " . $usuario["esSocio"] . "\n";
            };
            break;
        default:
            echo "Opción inválida";
            break;
    }
} while ($opc != 0);

/*
LAS SIGUIENTES FUNCIONES FUNCIONAN TODAS DE LA MISMA MANERA Y DEBERÍAN ESTAR EN LA CLASE QUE HACE REFERENCIA
EJ. OBTENERLISTAUSUARIOS DEBERÍA ESTAR EN LA CLASE USUARIO.

¿POR QUÉ NO SE HIZO ASÍ EN UN PRINCIPIO? PARA AGILIZAR LAS CLASES Y REDUCIR TIEMPOS. SIN EMBARGO ES AMPLIAMENTE RECOMENDABLE MUDAR ESTE CÓDIGO.
*/
function obtenerListaUsuarios(): array
{
    // EJEMPLO DE CONEXIÓN A BASE DE DATOS Y CONSULTA SELECT
    $conn = new Conexion();
    $sql = "SELECT * FROM USUARIO";
    $consulta = $conn->establecer_conexion()->prepare($sql);
    $consulta->execute();
    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerListaProductos(): array
{
    $conn = new Conexion();
    $sql = "SELECT * FROM producto";
    $consulta = $conn->establecer_conexion()->prepare($sql);
    $consulta->execute();
    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}

function ingresarUsuario(Usuario $nuevoUsuario)
{
    $conn = new Conexion();
    $sql = "INSERT INTO usuario (nombre, apellido, nacimiento, esSocio) VALUES ( :nombre, :apellido, :nacimiento, :esSocio)";
    $consulta = $conn->establecer_conexion()->prepare($sql);
    $success = $consulta->execute([
        "nombre" => $nuevoUsuario->getNombre(),
        "apellido" => $nuevoUsuario->getApellido(),
        "nacimiento" => $nuevoUsuario->getNacimiento()->format("Y-m-d"),
        "esSocio" => $nuevoUsuario->getEsSocio(),
    ]);

    if ($success) {
        echo "Se ha ingresado un nuevo usuario! :) \n";
    } else {
        echo "Algo salió mal al momento de crear el nuevo usuario : ( \n";
    }
}

function eliminarUsuario(int $id)
{
    $conn = new Conexion();
    $sql = "DELETE FROM usuario WHERE id = :id";
    $consulta = $conn->establecer_conexion()->prepare($sql);
    $success = $consulta->execute(["id" => $id]);

    if ($success) {
        echo "Se ha eliminado al usuario correctamente \n";
    } else {
        echo "Algo salió mal... \n";
    }
}

function modificarUsuario(int $id_usuario, int $opc_cambiar_valor)
{
    $conn = (new Conexion())->establecer_conexion();
    $sql = "";
    $consulta = "";
    switch ($opc_cambiar_valor) {
        case 1:
            $nuevoNombre = readline("Escribe el nuevo nombre");
            $sql = "UPDATE usuario SET nombre = :nombre WHERE id = :id";
            $consulta = $conn->prepare($sql);
            $consulta->execute(["nombre" => $nuevoNombre, "id" => $id_usuario]);
            break;
        case 2:
            $nuevoApellido = readline("Escribe el nuevo apellido");
            $sql = "UPDATE usuario SET apellido = :apellido WHERE id = :id";
            $consulta = $conn->prepare($sql);
            $consulta->execute(["apellido" => $nuevoApellido, "id" => $id_usuario]);
            break;
        case 3:
            $formateo = new DateTime();
            $nuevoNacimiento = readline("Escribe el nuevo nacimiento");
            $nuevoNacimiento = $formateo->createFromFormat("Y-m-d", $nuevoNacimiento);

            $sql = "UPDATE usuario SET nacimiento = :nacimiento WHERE id = :id";
            $consulta = $conn->prepare($sql);

            $consulta->execute(["nacimiento" => $nuevoNacimiento, "id" => $id_usuario]);
            break;
        case 4:
            (bool)$nuevoEsSocio = readline("Cambia el estado del socio a 1-Socio/ 0-No socio");
            if ($nuevoEsSocio) {
                $sql = "UPDATE usuario SET esSocio = 1 WHERE id = :id";
            } else {
                $sql = "UPDATE usuario SET esSocio = 0 WHERE id = :id";
            }
            $consulta = $conn->prepare($sql);
            $consulta->execute(["id" => $id_usuario]);
            break;
        default:
            break;
    }
}
