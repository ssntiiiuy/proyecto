<?php
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Producto.php';

/* filtros del listado, para volver con los mismos al terminar */
$filtrosListado = [];
if (!empty($_GET['categoria'])) {
  $filtrosListado['categoria'] = $_GET['categoria'];
}
if (!empty($_GET['filtro'])) {
  $filtrosListado['filtro'] = $_GET['filtro'];
}
$urlListado = 'productos.php?' . http_build_query($filtrosListado);

/* se busca el producto que llega en la URL: editar_producto.php?id=5 */
$id = (int) ($_GET['id'] ?? 0);
$producto = Producto::buscarPorId($id);

if (!$producto) {
  header("Location: productos.php?" . http_build_query(['msg' => 'noexiste'] + $filtrosListado));
  exit();
}

$conexion = (new Conexion())->establecerConexion();

$error = null;
$datosForm = $producto; /* el formulario arranca con los datos de la base */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
  $datosForm = $_POST;

  $nombre = trim($_POST['Nombre'] ?? '');
  $categoria = trim($_POST['Categoria'] ?? '');
  $precio = trim($_POST['Precio'] ?? '');
  $stock = trim($_POST['Stock'] ?? '');
  $stockMinimo = trim($_POST['StockMinimo'] ?? '');
  $fechaVencimiento = trim($_POST['FechaVencimiento'] ?? '');
  $fechaVencimiento = $fechaVencimiento === '' ? null : $fechaVencimiento; /* sin fecha de vencimiento se guarda NULL */
  $idProveedor = (int) ($_POST['IdProveedor'] ?? 0);

  /* el proveedor elegido tiene que existir */
  $sqlCheckProveedor = $conexion->prepare("SELECT 1 FROM PROVEEDOR WHERE IdProveedor = :idProveedor");
  $sqlCheckProveedor->execute([':idProveedor' => $idProveedor]);
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
      $resultado = (new Producto())->editarProducto($id, $nombre, $categoria, $precio, $stock, $stockMinimo, $fechaVencimiento, $idProveedor);
    } catch (Throwable $e) {
      $resultado = false;
    }

    if ($resultado) {
      header("Location: productos.php?" . http_build_query(['msg' => 'editado'] + $filtrosListado));
      exit();
    } else {
      $error = "No se pudieron guardar los cambios. Revisá los datos.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar producto - Panel Sienra</title>

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

        <?php if ($error) { ?>
          <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php } ?>

        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <h2>Editar producto (Id <?= $id ?>)</h2>
            <a class="admin-card-link" href="<?= htmlspecialchars($urlListado) ?>"><i class="bi bi-arrow-left"></i> Volver al listado</a>
          </div>

          <form class="p-3 p-lg-4" action="editar_producto.php?<?= htmlspecialchars(http_build_query(['id' => $id] + $filtrosListado)) ?>" method="post">
            <div class="row g-3">
              <div class="col-sm-12">
                <label class="form-label" for="Nombre">Nombre</label>
                <input class="form-control" type="text" id="Nombre" name="Nombre" placeholder="Comida para perros 3kg" maxlength="100" value="<?= htmlspecialchars($datosForm['Nombre'] ?? '') ?>" required>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="Categoria">Categoría</label>
                <select class="form-select" id="Categoria" name="Categoria" required>
                  <option value="" disabled>Elegir categoría</option>
                  <?php foreach (['Accesorios', 'Alimentos', 'Farmacia', 'Higiene'] as $cat) { ?>
                    <option value="<?= $cat ?>" <?= (($datosForm['Categoria'] ?? '') === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
                  <?php } ?>
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
              <div class="col-sm-6">
                <label class="form-label" for="IdProveedor">Proveedor</label>
                <select class="form-select" id="IdProveedor" name="IdProveedor" required>
                  <option value="" <?= empty($datosForm['IdProveedor']) ? 'selected' : '' ?> disabled>Elegir proveedor</option>
                  <?php
                  $stmtProveedores = $conexion->query("SELECT IdProveedor, NombreEmpresa FROM PROVEEDOR ORDER BY NombreEmpresa");
                  while ($prov = $stmtProveedores->fetch(PDO::FETCH_ASSOC)) {
                  ?>
                    <option value="<?= $prov['IdProveedor'] ?>" <?= (($datosForm['IdProveedor'] ?? '') == $prov['IdProveedor']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($prov['NombreEmpresa']) ?>
                    </option>
                  <?php } ?>
                </select>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a class="btn admin-btn-cancelar" href="<?= htmlspecialchars($urlListado) ?>">Cancelar</a>
              <button type="submit" class="btn admin-btn-agregar" name="guardar">Guardar cambios</button>
            </div>
          </form>
        </div>

      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
