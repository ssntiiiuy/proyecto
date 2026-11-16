<?php
// Solo se puede volver a estas páginas después de iniciar sesión o registrarse.
$destinosCliente = [
    'compra' => 'usuario/catalogo.php?carrito=1',
    'cita' => 'usuario/index.php?agendar=1',
    'mascotas' => 'usuario/mascotas.php',
];
$motivosCliente = [
    'compra' => 'para continuar con la compra',
    'cita' => 'para agendar una cita',
    'mascotas' => 'para ver tus mascotas',
];

$accion = $_GET['accion'] ?? '';
$destinoCliente = 'usuario/index.php';
$motivoCliente = '';
$consultaAccion = '';

if (is_string($accion) && isset($destinosCliente[$accion])) {
    $destinoCliente = $destinosCliente[$accion];
    $motivoCliente = $motivosCliente[$accion];
    $parametrosAccion = ['accion' => $accion];

    if ($accion === 'compra') {
        $producto = filter_var($_GET['producto'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($producto !== false) {
            $parametrosAccion['producto'] = $producto;
            $destinoCliente = 'usuario/catalogo.php#producto-' . $producto;
        }
    }

    $consultaAccion = '?' . http_build_query($parametrosAccion);
}
