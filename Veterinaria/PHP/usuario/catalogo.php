<?php
session_start();
require_once __DIR__ . '/includes/carrito.php';
require_once __DIR__ . '/../clases/Conexion.php';
$conexion = (new Conexion())->establecerConexion();

$categoriasSeleccionadas = $_GET['categoria'] ?? [];
$categoriasValidas = ['accesorios', 'alimentos', 'farmacia', 'higiene'];
$categoriasSeleccionadas = array_intersect($categoriasSeleccionadas, $categoriasValidas); // filtra cualquier valor

$ordenesValidos = [
  'ultimos'     => 'IdProducto DESC',
  'precio_asc'  => 'Precio ASC',
  'precio_desc' => 'Precio DESC',
];
$ordenGet = $_GET['orden'] ?? 'ultimos';
$orderBy = $ordenesValidos[$ordenGet] ?? $ordenesValidos['ultimos'];

$sql = "SELECT IdProducto, Nombre, Categoria, Precio, Stock FROM PRODUCTO";
$params = [];

if (!empty($categoriasSeleccionadas)) {
  $marcas = implode(',', array_fill(0, count($categoriasSeleccionadas), '?'));
  $sql .= " WHERE Categoria IN ($marcas)";
  $params = $categoriasSeleccionadas;
}
$sql .= " ORDER BY $orderBy";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
$categoriasVisuales = [
  'Accesorios' => ['icono' => 'bi-bag-heart', 'clase' => 'accesorios'],
  'Alimentos' => ['icono' => 'bi-basket2-fill', 'clase' => 'alimentos'],
  'Farmacia' => ['icono' => 'bi-capsule', 'clase' => 'farmacia'],
  'Higiene' => ['icono' => 'bi-droplet-fill', 'clase' => 'higiene']
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Catálogo - Clínica Veterinaria Sienra</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../CSS/style.css">
  <script src="../../JS/tema.js"></script>
</head>

<body>
  <?php $paginaActiva = 'catalogo';
  $mostrarCarrito = true;
  include __DIR__ . '/includes/header.php'; ?>

  <main class="catalogo">
    <div class="container-fluid px-3 px-lg-4 py-4">
      <div class="row g-4">
        <aside class="col-md-4 col-lg-3">
          <form class="filtros-card" action="catalogo.php" method="get">
            <a href="catalogo.php" class="btn btn-filtros-limpiar w-100">Limpiar filtros</a>
            <h2 class="filtros-titulo">Categoría</h2>
            <?php foreach ($categoriasValidas as $cat): ?>
              <div class="form-check filtros-opcion">
                <input class="form-check-input" type="checkbox" name="categoria[]" value="<?= $cat ?>"
                  id="cat<?= ucfirst($cat) ?>" <?= in_array($cat, $categoriasSeleccionadas) ? 'checked' : '' ?>
                  onchange="this.form.submit()">
                <label class="form-check-label" for="cat<?= ucfirst($cat) ?>"><?= ucfirst($cat) ?></label>
              </div>
            <?php endforeach; ?>
          </form>
        </aside>

        <section class="col-md-8 col-lg-9">
          <form class="d-flex justify-content-end mb-3" action="catalogo.php" method="get" id="formOrden">
            <?php foreach ($categoriasSeleccionadas as $cat): ?>
              <input type="hidden" name="categoria[]" value="<?= htmlspecialchars($cat) ?>">
            <?php endforeach; ?>
            <select class="form-select catalogo-orden" name="orden" onchange="document.getElementById('formOrden').submit()">
              <option value="ultimos" <?= $ordenGet === 'ultimos' ? 'selected' : '' ?>>Ordenar por los últimos</option>
              <option value="precio_asc" <?= $ordenGet === 'precio_asc' ? 'selected' : '' ?>>Ordenar por precio: de más bajo a más alto</option>
              <option value="precio_desc" <?= $ordenGet === 'precio_desc' ? 'selected' : '' ?>>Ordenar por precio: de más alto a más bajo</option>
            </select>
          </form>

          <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3">
            <?php if (empty($productos)): ?>
              <p class="text-center">No hay productos para esos filtros.</p>
              <?php else: foreach ($productos as $p): ?>
                <div class="col">
                  <div class="producto-card">
                    <?php $visual = $categoriasVisuales[$p['Categoria']] ?? ['icono' => 'bi-box-seam', 'clase' => 'general']; ?>
                    <div class="producto-categoria producto-categoria-<?= $visual['clase'] ?>">
                      <i class="bi <?= $visual['icono'] ?>" aria-hidden="true"></i>
                      <span><?= htmlspecialchars($p['Categoria']) ?></span>
                    </div>
                    <p class="producto-nombre"><?= htmlspecialchars($p['Nombre']) ?></p>
                    <p class="producto-precio">$<?= number_format($p['Precio'], 2, ',', '.') ?></p>
                    <form method="post">
                      <input type="hidden" name="tokenCarrito" value="<?= htmlspecialchars($_SESSION['tokenCarrito']) ?>">
                      <input type="hidden" name="IdProducto" value="<?= (int) $p['IdProducto'] ?>">
                      <button class="btn btn-carrito" name="accionCarrito" value="agregar" <?= (int) $p['Stock'] <= 0 ? 'disabled' : '' ?>><?= (int) $p['Stock'] > 0 ? 'Añadir al carrito' : 'Sin stock' ?></button>
                    </form>
                  </div>
                </div>
            <?php endforeach;
            endif; ?>
          </div>
        </section>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
