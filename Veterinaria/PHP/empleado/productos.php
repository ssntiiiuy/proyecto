<?php
$rolesPermitidos = ['Cajero'];
require_once __DIR__ . '/../includes/verificar_sesion.php';

require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Producto.php';

/* filtros del listado, para volver con los mismos despues de editar o eliminar */
$filtrosListado = [];
if (!empty($_GET['categoria'])) {
  $filtrosListado['categoria'] = $_GET['categoria'];
}
if (!empty($_GET['filtro'])) {
  $filtrosListado['filtro'] = $_GET['filtro'];
}
// NUEVO: Capturamos la búsqueda si existe
if (!empty($_GET['busqueda'])) {
  $filtrosListado['busqueda'] = $_GET['busqueda'];
}

/* eliminar: solo se borra por POST, despues de confirmar */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
  $producto = new Producto();
  $resultado = $producto->eliminarProducto((int) $_POST['IdProducto']);
  header("Location: productos.php?" . http_build_query(['msg' => $resultado] + $filtrosListado));
  exit();
}

/* mensajes fijos que se muestran despues de editar o eliminar */
$mensajes = [
  'editado' => ['success', 'Los cambios del producto se guardaron.'],
  'eliminado' => ['success', 'El producto se eliminó.'],
  'bloqueado' => ['danger', 'No se puede eliminar el producto porque aparece en facturas o compras de clientes.'],
  'noexiste' => ['danger', 'El producto no existe.'],
  'error' => ['danger', 'No se pudo completar la operación.']
];
$mensaje = $mensajes[$_GET['msg'] ?? ''] ?? null;

/* confirmacion: productos.php?eliminar=Id muestra el aviso arriba del listado */
$productoEliminar = null;
if (isset($_GET['eliminar'])) {
  $productoEliminar = Producto::buscarPorId((int) $_GET['eliminar']);
  if (!$productoEliminar) {
    $mensaje = $mensajes['noexiste'];
  }
}

$categoria = $_GET['categoria'] ?? null;
$filtroEspecial = $_GET['filtro'] ?? null;
$busqueda = $_GET['busqueda'] ?? null; // NUEVO

// Llamamos al método pasando los tres filtros
$datosProductos = Producto::listadoProductos($categoria, $filtroEspecial, $busqueda);
$productos = $datosProductos['productos'];

$conexion = (new Conexion())->establecerConexion();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
  $datosForm = $_POST;

  $nombre = trim($_POST['Nombre'] ?? '');
  $categoria = trim($_POST['Categoria'] ?? '');
  $precio = trim($_POST['Precio'] ?? '');
  $stock = trim($_POST['Stock'] ?? '');
  $stockMinimo = trim($_POST['StockMinimo'] ?? '');
  $fechaVencimiento = trim($_POST['FechaVencimiento'] ?? '');
  $fechaVencimiento = $fechaVencimiento === '' ? null : $fechaVencimiento; /* si no pone nada en la fecha de vencimiento, lo establece como null */
  $nombreEmpresa = trim($_POST['NombreEmpresa'] ?? '');

  /* el proveedor elegido tiene que existir */
  $sqlCheckProveedor = $conexion->prepare("SELECT 1 FROM PROVEEDOR WHERE NombreEmpresa = :nombreEmpresa");
  $sqlCheckProveedor->execute([':nombreEmpresa' => $nombreEmpresa]);
  $proveedorExiste = $sqlCheckProveedor->fetch();

  /* se valida antes de guardar */
  if ($nombre === '') {
    $error = "Falta el nombre del producto.";
  } elseif (!in_array($categoria, ['Accesorios', 'Alimentos', 'Farmacia', 'Higiene'])) {
    $error = "Elegí la categoría del producto.";
  } elseif ($precio === '') {
    $error = "Falta el precio del producto.";
  } elseif (!is_numeric($precio) || $precio < 0) {
    $error = "El precio no puede ser negativo.";
  } elseif ($stock === '') {
    $error = "Falta el stock del producto.";
  } elseif (!ctype_digit($stock)) {
    $error = "El stock debe ser un número entero, 0 o mayor.";
  } elseif ($stockMinimo === '') {
    $error = "Falta el stock mínimo del producto.";
  } elseif (!ctype_digit($stockMinimo)) {
    $error = "El stock mínimo debe ser un número entero, 0 o mayor.";
  } elseif ($fechaVencimiento !== null && strtotime($fechaVencimiento) === false) {
    $error = "La fecha de vencimiento no es válida.";
  } elseif (!$proveedorExiste) {
    $error = "Elegí el proveedor del producto.";
  } else {
    try {
      $producto = new Producto();
      $resultado = $producto->crearProducto($nombre, $categoria, $precio, $stock, $stockMinimo, $fechaVencimiento, $nombreEmpresa);
    } catch (Throwable $e) {
      $resultado = false;
    }

    if ($resultado) {
      header("Location: productos.php");
      exit();
    } else {
      $error = "No se pudo guardar el producto. Revisá los datos.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Productos - Panel Sienra</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../CSS/style.css">
  <link rel="stylesheet" href="../../CSS/admin.css">
  <script src="../../JS/tema.js"></script>
</head>

<body>

  <div class="admin-wrapper">
    <?php
    $paginaActiva = 'productos';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Productos';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <?php if ($mensaje) { ?>
          <div class="alert alert-<?= $mensaje[0] ?>" role="alert"><?= htmlspecialchars($mensaje[1]) ?></div>
        <?php } ?>

        <?php if ($error) { ?>
          <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php } ?>

        <?php if ($productoEliminar) { ?>
          <div class="alert alert-warning" role="alert">
            <form class="d-flex flex-wrap align-items-center gap-2" action="productos.php?<?= htmlspecialchars(http_build_query($filtrosListado)) ?>" method="post">
              <span class="me-auto">
                ¿Eliminar el producto <strong><?= htmlspecialchars($productoEliminar['Nombre']) ?></strong> (Id <?= $productoEliminar['IdProducto'] ?>)? Esta acción no se puede deshacer.
              </span>
              <input type="hidden" name="IdProducto" value="<?= $productoEliminar['IdProducto'] ?>">
              <a class="btn btn-sm admin-btn-cancelar" href="productos.php?<?= htmlspecialchars(http_build_query($filtrosListado)) ?>">Cancelar</a>
              <button type="submit" class="btn btn-sm btn-danger" name="eliminar">Sí, eliminar</button>
            </form>
          </div>
        <?php } ?>

        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <h2>Listado de productos</h2>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <form method="get" action="productos.php" class="d-flex gap-2">
                <label class="visually-hidden" for="filtroCategoria">Filtrar por categoría</label>
                <select class="form-select form-select-sm admin-filtro" id="filtroCategoria" name="categoria">
                  <option value="" <?= $categoria === '' ? 'selected' : '' ?>>Todas las categorías</option>
                  <option value="Accesorios" <?= $categoria === 'Accesorios' ? 'selected' : '' ?>>Accesorios</option>
                  <option value="Alimentos" <?= $categoria === 'Alimentos' ? 'selected' : '' ?>>Alimentos</option>
                  <option value="Farmacia" <?= $categoria === 'Farmacia' ? 'selected' : '' ?>>Farmacia</option>
                  <option value="Higiene" <?= $categoria === 'Higiene' ? 'selected' : '' ?>>Higiene</option>
                </select>
                <button type="submit" class="btn btn-sm admin-btn-filtrar">Filtrar</button>
              </form>
              <button type="button" class="btn btn-sm admin-btn-agregar" data-bs-toggle="modal" data-bs-target="#modalProducto">
                <i class="bi bi-plus-lg"></i> Agregar producto
              </button>
            </div>
          </div>

          <form class="d-flex gap-2 mb-3 mt-3 mx-3 mx-lg-4" action="productos.php" method="get">
            <?php if (!empty($categoria)): ?>
              <input type="hidden" name="categoria" value="<?= htmlspecialchars($categoria) ?>">
            <?php endif; ?>
            <?php if (!empty($filtroEspecial)): ?>
              <input type="hidden" name="filtro" value="<?= htmlspecialchars($filtroEspecial) ?>">
            <?php endif; ?>

            <input class="form-control" type="text" name="busqueda" placeholder="Buscar por nombre de producto..."
              value="<?= htmlspecialchars($busqueda ?? '') ?>">
            <button class="btn admin-btn-agregar ms-2" type="submit" title="Buscar"><i class="bi bi-search"></i></button>

            <?php if (!empty($busqueda)): ?>
              <a class="btn admin-btn-cancelar ms-2" href="productos.php<?= (!empty($categoria) || !empty($filtroEspecial)) ? '?' . http_build_query(array_filter(['categoria' => $categoria, 'filtro' => $filtroEspecial])) : '' ?>">Limpiar</a>
            <?php endif; ?>
          </form>

          <div class="table-responsive">
            <table class="table admin-tabla mb-0">
              <thead>
                <tr>
                  <th scope="col">Id</th>
                  <th scope="col">Nombre</th>
                  <th scope="col">Categoría</th>
                  <th scope="col">Precio</th>
                  <th scope="col">Stock</th>
                  <th scope="col">Stock mín.</th>
                  <th scope="col">Vencimiento</th>
                  <th scope="col">Proveedor</th>
                </tr>
              </thead>
              <!-- listado de productos -->
              <tbody>

                <?php if (empty($productos)): ?>
                  <tr>
                    <td colspan="7" class="text-center">No hay productos registrados.</td>
                  </tr>
                  <?php else: foreach ($productos as $prod): ?>

                    <tr>
                      <td><?= htmlspecialchars($prod['IdProducto']) ?></td>
                      <td><?= htmlspecialchars($prod['Nombre']) ?></td>
                      <td><?= htmlspecialchars($prod['Categoria']) ?></td>
                      <td><?= htmlspecialchars($prod['Precio']) ?></td>

                      <!-- alerta de stock si es menor al stock minimo -->
                      <td>
                        <?= htmlspecialchars($prod['Stock']) ?>
                        <?php if ((int)$prod['Stock'] <= (int)$prod['StockMinimo']): ?>
                          <br><span class="badge badge-danger">Stock bajo</span>
                        <?php endif; ?>
                      </td>

                      <td><?= htmlspecialchars($prod['StockMinimo']) ?></td>

                      <!-- alerta de vencimiento si esta por vencer o ha vencido -->
                      <td>
                        <?php if (empty($prod['FechaVencimiento'])): ?>
                          —
                        <?php else:
                          $fechaVenc = new DateTime($prod['FechaVencimiento']);
                          $hoy = new DateTime();
                          $diasRestantes = (int)$hoy->diff($fechaVenc)->format('%r%a');
                        ?>
                          <?= htmlspecialchars($fechaVenc->format('d/m/Y')) ?>
                          <?php if ($diasRestantes < 0): ?>
                            <br><span class="badge badge-danger">Vencido</span>
                          <?php elseif ($diasRestantes <= 30): ?>
                            <br><span class="badge badge-warning">Por vencer</span>
                          <?php endif; ?>
                        <?php endif; ?>
                      </td>

                      <td><?= htmlspecialchars($prod['NombreEmpresa']) ?></td>
                    </tr>
                <?php endforeach;
                endif; ?>

              </tbody>
            </table>
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- agregar producto -->
  <div class="modal fade admin-modal" id="modalProducto" tabindex="-1" aria-labelledby="modalProductoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" action="#" method="post">
        <div class="modal-header">
          <h2 class="modal-title" id="modalProductoTitulo">Agregar producto</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-sm-12">
              <label class="form-label" for="Nombre">Nombre</label>
              <input class="form-control" type="text" id="Nombre" name="Nombre" placeholder="Comida para perros 3kg" maxlength="100" value="<?= htmlspecialchars($datosForm['Nombre'] ?? '') ?>" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Categoria">Categoría</label>
              <select class="form-select" id="Categoria" name="Categoria" required>
                <option value="" <?= empty($datosForm['Categoria']) ? 'selected' : '' ?> disabled>Elegir categoría</option>
                <option value="Accesorios" <?= (($datosForm['Categoria'] ?? '') === 'Accesorios') ? 'selected' : '' ?>>Accesorios</option>
                <option value="Alimentos" <?= (($datosForm['Categoria'] ?? '') === 'Alimentos') ? 'selected' : '' ?>>Alimentos</option>
                <option value="Farmacia" <?= (($datosForm['Categoria'] ?? '') === 'Farmacia') ? 'selected' : '' ?>>Farmacia</option>
                <option value="Higiene" <?= (($datosForm['Categoria'] ?? '') === 'Higiene') ? 'selected' : '' ?>>Higiene</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Precio">Precio ($)</label>
              <input class="form-control" type="number" id="Precio" name="Precio" min="0" step="0.01" placeholder="1299" value="<?= htmlspecialchars($datosForm['Precio'] ?? '') ?>" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Stock">Stock</label>
              <input class="form-control" type="number" id="Stock" name="Stock" min="0" step="1" placeholder="20" value="<?= htmlspecialchars($datosForm['Stock'] ?? '') ?>" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="StockMinimo">Stock mínimo</label>
              <input class="form-control" type="number" id="StockMinimo" name="StockMinimo" min="0" step="1" placeholder="5" value="<?= htmlspecialchars($datosForm['StockMinimo'] ?? '') ?>" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="FechaVencimiento">Vencimiento <span class="admin-aclaracion">(si corresponde)</span></label>
              <input class="form-control" type="date" id="FechaVencimiento" name="FechaVencimiento" value="<?= htmlspecialchars($datosForm['FechaVencimiento'] ?? '') ?>">
            </div>

            <!-- listado de proveedores -->
            <div class="col-sm-6">
              <label class="form-label" for="IdProveedor">Proveedor</label>
              <select class="form-select" id="NombreEmpresa" name="NombreEmpresa" required>
                <option value="" <?= empty($datosForm['NombreEmpresa']) ? 'selected' : '' ?> disabled>Elegir proveedor</option>
                <?php
                $stmtProveedores = $conexion->query("SELECT p.NombreEmpresa FROM PROVEEDOR p");
                while ($prov = $stmtProveedores->fetch(PDO::FETCH_ASSOC)):
                ?>
                  <option value="<?= htmlspecialchars($prov['NombreEmpresa']) ?>" <?= (($datosForm['NombreEmpresa'] ?? '') === $prov['NombreEmpresa']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($prov['NombreEmpresa']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn admin-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn admin-btn-agregar" name="guardar">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <?php if ($error): ?>
    <script>
      new bootstrap.Modal(document.getElementById('modalProducto')).show();
    </script>
  <?php endif; ?>

</body>

</html>