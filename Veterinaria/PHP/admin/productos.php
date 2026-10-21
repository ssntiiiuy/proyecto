<?php
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Producto.php';

/* funcion para guardar los datos */
$datosProductos = Producto::listadoProductos();
$productos = $datosProductos['productos'];

$conexion = (new Conexion())->establecerConexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
  $datosForm = $_POST;

  $nombre = trim($_POST['Nombre']);
  $categoria = trim($_POST['Categoria']);
  $precio = trim($_POST['Precio']);
  $stock = trim($_POST['Stock']);
  $stockMinimo = trim($_POST['StockMinimo']);
  $fechaVencimiento = trim($_POST['FechaVencimiento']);
  $nombreEmpresa = trim($_POST['NombreEmpresa']);

  $producto = new Producto();
  $resultado = $producto->crearProducto($nombre, $categoria, $precio, $stock, $stockMinimo, $fechaVencimiento, $nombreEmpresa);

  if ($resultado) {
    header("Location: productos.php");
    exit();
  } else {
    $error = "No se pudo completar el registro.";
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

        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <h2>Listado de productos</h2>
            <button class="btn btn-sm admin-btn-agregar" type="button" data-bs-toggle="modal" data-bs-target="#modalProducto">
              <i class="bi bi-plus-lg"></i> Agregar producto
            </button>
          </div>

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
                  <th scope="col" class="text-end">Acciones</th>
                </tr>
              </thead>
              <!-- listado de productos -->
              <tbody>

                <?php foreach ($productos as $prod): ?>

                  <tr>
                    <td><?= htmlspecialchars($prod['IdProducto']) ?></td>
                    <td><?= htmlspecialchars($prod['Nombre']) ?></td>
                    <td><?= htmlspecialchars($prod['Categoria']) ?></td>
                    <td><?= htmlspecialchars($prod['Precio']) ?></td>
                    <td><?= htmlspecialchars($prod['Stock']) ?></td>
                    <td><?= htmlspecialchars($prod['StockMinimo']) ?></td>
                    <td><?= date('d/m/Y', strtotime($prod['FechaVencimiento']) ?? '-') ?> </td>
                    <td><?= htmlspecialchars($prod['NombreEmpresa']) ?></td>
                    <td class="text-end">
                      <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                      <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                    </td>
                  </tr>
                <?php endforeach; ?>

              </tbody>
            </table>
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- agregar producto -->
  <div class="modal fade admin-modal" id="modalProducto" tabindex="-1" aria-labelledby="modalProductoTitulo" aria-hidden="true" data-bs-theme="dark">
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
              <input class="form-control" type="text" id="Nombre" name="Nombre" placeholder="Comida para perros 3kg" value="<?= htmlspecialchars($datosForm['Nombre'] ?? '') ?>" required>
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
              <input class="form-control" type="number" id="Precio" name="Precio" min="0" step="1" placeholder="1299" value="<?= htmlspecialchars($datosForm['Precio'] ?? '') ?>" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Stock">Stock</label>
              <input class="form-control" type="number" id="Stock" name="Stock" min="0" placeholder="20" value="<?= htmlspecialchars($datosForm['Stock'] ?? '') ?>" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="StockMinimo">Stock mínimo</label>
              <input class="form-control" type="number" id="StockMinimo" name="StockMinimo" min="0" placeholder="5" value="<?= htmlspecialchars($datosForm['StockMinimo'] ?? '') ?>" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="FechaVencimiento">Vencimiento <span class="admin-aclaracion">(si corresponde)</span></label>
              <input class="form-control" type="date" id="FechaVencimiento" name="FechaVencimiento" value="<?= htmlspecialchars($datosForm['FechaVencimiento'] ?? '') ?>">
            </div>

            <!-- listado de proveedores -->
            <div class="col-sm-6">
              <label class="form-label" for="IdProveedor">Proveedor</label>
              <select class="form-select" id="NombreEmpresa" name="NombreEmpresa" required>
                <option value="" <?= empty($datosForm['IdProveedor']) ? 'selected' : '' ?> disabled>Elegir proveedor</option>
                <?php
                $stmtProveedores = $conexion->query("SELECT p.NombreEmpresa FROM PROVEEDOR p");
                while ($prov = $stmtProveedores->fetch(PDO::FETCH_ASSOC)):
                ?>
                  <option value="<?= $prov['NombreEmpresa'] ?>" <?= (($datosForm['IdProveedor'] ?? '') === $prov['NombreEmpresa']) ? 'selected' : '' ?>>
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

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

</body>

</html>