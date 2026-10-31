<?php
// Se incluye antes de enviar HTML para poder procesar formularios y redirigir.
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../clases/Conexion.php';

$correoCarrito = $_SESSION['correo'] ?? '';
if (!empty($_SESSION['carritoCorreo']) && $_SESSION['carritoCorreo'] !== $correoCarrito) {
  unset($_SESSION['carrito'], $_SESSION['avisoCarrito']);
}
$_SESSION['carritoCorreo'] = $correoCarrito;
$_SESSION['carrito'] = $_SESSION['carrito'] ?? [];
$_SESSION['tokenCarrito'] = $_SESSION['tokenCarrito'] ?? bin2hex(random_bytes(32));

$avisoCarrito = $_SESSION['avisoCarrito'] ?? null;
unset($_SESSION['avisoCarrito']);
$productosCarrito = [];
$cantidadCarrito = 0;
$totalCarrito = 0;
$carritoDisponible = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accionCarrito'])) {
  try {
    if (!hash_equals($_SESSION['tokenCarrito'], (string) ($_POST['tokenCarrito'] ?? ''))) {
      throw new Exception('El formulario venció. Revisá el carrito e intentá nuevamente.');
    }
    $accionCarrito = $_POST['accionCarrito'];
    $idProductoCarrito = (int) ($_POST['IdProducto'] ?? 0);
    $unidadesCarrito = (int) ($_SESSION['carrito'][$idProductoCarrito] ?? 0);

    if ($accionCarrito === 'quitar') {
      unset($_SESSION['carrito'][$idProductoCarrito]);
    } elseif ($accionCarrito === 'restar') {
      if ($unidadesCarrito <= 1) {
        unset($_SESSION['carrito'][$idProductoCarrito]);
      } else {
        $_SESSION['carrito'][$idProductoCarrito] = $unidadesCarrito - 1;
      }
    } elseif ($accionCarrito === 'agregar') {
      $conexionCarrito = (new Conexion())->establecerConexion();
      $consultaCarrito = $conexionCarrito->prepare('SELECT Stock FROM PRODUCTO WHERE IdProducto = :id');
      $consultaCarrito->execute([':id' => $idProductoCarrito]);
      $stockCarrito = $consultaCarrito->fetchColumn();
      if ($stockCarrito === false || $unidadesCarrito >= (int) $stockCarrito) {
        throw new Exception('No hay más unidades disponibles de este producto.');
      }
      $_SESSION['carrito'][$idProductoCarrito] = $unidadesCarrito + 1;
    } elseif ($accionCarrito === 'comprar') {
      if (empty($_SESSION['carrito'])) {
        throw new Exception('Agregá productos al carrito antes de finalizar.');
      }
      $conexionCarrito = (new Conexion())->establecerConexion();
      $consultaClienteCarrito = $conexionCarrito->prepare("SELECT c.CI FROM CLIENTE c
        INNER JOIN HACE h ON c.CI = h.CI
        INNER JOIN LOGIN l ON h.Correo = l.Correo
        WHERE h.Correo = :correo AND LOWER(l.TipoUsuario) = 'cliente'");
      $consultaClienteCarrito->execute([':correo' => $correoCarrito]);
      $ciCarrito = $consultaClienteCarrito->fetchColumn();
      if ($ciCarrito === false) {
        throw new Exception('Iniciá sesión con una cuenta de cliente para finalizar la compra.');
      }

      $lineasCarrito = [];
      foreach ($_SESSION['carrito'] as $idProducto => $cantidad) {
        $lineasCarrito[] = ['tipo' => 'p', 'id' => (int) $idProducto, 'cantidad' => (int) $cantidad];
      }
      require_once __DIR__ . '/../../clases/Factura.php';
      $idFacturaCarrito = (new Factura())->crearFactura((int) $ciCarrito, '10.10.00.00.10', $lineasCarrito);
      $_SESSION['carrito'] = [];
      // Impide repetir la compra desde un formulario anterior.
      $_SESSION['tokenCarrito'] = bin2hex(random_bytes(32));
      $_SESSION['avisoCarrito'] = ['success', 'Compra registrada. Factura N.º ' . $idFacturaCarrito . '.'];
      if ((new Factura())->enviarPorCorreo($idFacturaCarrito, $correoCarrito)) {
        $_SESSION['avisoCarrito'][1] .= ' Enviamos la factura a tu correo.';
      } else {
        $_SESSION['avisoCarrito'] = ['warning', 'Compra registrada. Factura N.º ' . $idFacturaCarrito . '. No pudimos enviar el correo; contactá a la clínica para solicitar tu factura.'];
      }
    } else {
      throw new Exception('La acción del carrito no es válida.');
    }
  } catch (PDOException $e) {
    $_SESSION['avisoCarrito'] = ['danger', 'No se pudo completar la operación. Tu carrito se conserva; intentá nuevamente.'];
  } catch (Exception $e) {
    $_SESSION['avisoCarrito'] = ['danger', $e->getMessage()];
  }

  $paginaCarrito = basename($_SERVER['SCRIPT_NAME']);
  if (!in_array($paginaCarrito, ['catalogo.php', 'index.php', 'mascotas.php', 'nosotros.php'], true)) {
    $paginaCarrito = 'catalogo.php';
  }
  $parametrosCarrito = ['carrito' => '1'];
  if ($paginaCarrito === 'catalogo.php') {
    $parametrosCarrito += array_intersect_key($_GET, array_flip(['categoria', 'orden']));
  }
  header('Location: ' . $paginaCarrito . '?' . http_build_query($parametrosCarrito));
  exit();
}

if (!empty($_SESSION['carrito'])) {
  try {
    $conexionCarrito = (new Conexion())->establecerConexion();
    $marcasCarrito = implode(',', array_fill(0, count($_SESSION['carrito']), '?'));
    $consultaCarrito = $conexionCarrito->prepare("SELECT IdProducto, Nombre, Categoria, Precio, Stock FROM PRODUCTO WHERE IdProducto IN ($marcasCarrito)");
    $consultaCarrito->execute(array_keys($_SESSION['carrito']));
    $productosPorIdCarrito = $consultaCarrito->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

    foreach ($_SESSION['carrito'] as $idProducto => $cantidad) {
      $productoCarrito = $productosPorIdCarrito[$idProducto] ?? null;
      $disponibleCarrito = $productoCarrito && $cantidad <= (int) $productoCarrito['Stock'];
      $subtotalCarrito = $productoCarrito ? (int) round((float) $productoCarrito['Precio'] * 100) * $cantidad : 0;
      $productosCarrito[] = [
        'IdProducto' => $idProducto,
        'Nombre' => $productoCarrito['Nombre'] ?? 'Producto no disponible',
        'Categoria' => $productoCarrito['Categoria'] ?? '',
        'Precio' => $productoCarrito['Precio'] ?? 0,
        'Stock' => (int) ($productoCarrito['Stock'] ?? 0),
        'Cantidad' => $cantidad,
        'Subtotal' => $subtotalCarrito / 100,
        'Disponible' => $disponibleCarrito
      ];
      $cantidadCarrito += $cantidad;
      $totalCarrito += $subtotalCarrito;
      $carritoDisponible = $carritoDisponible && $disponibleCarrito;
    }
    $totalCarrito /= 100;
  } catch (PDOException $e) {
    $carritoDisponible = false;
    $avisoCarrito = ['danger', 'No se pudo consultar el carrito. Intentá nuevamente.'];
  }
}
