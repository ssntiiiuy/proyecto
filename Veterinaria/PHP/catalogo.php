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

  <link rel="stylesheet" href="../CSS/style.css">
</head>

<body>

  <?php
  $paginaActiva = 'catalogo';
  $mostrarCarrito = true;
  include __DIR__ . '/includes/header.php';
  ?>

  <main class="catalogo">
    <div class="container-fluid px-3 px-lg-4 py-4">
      <div class="row g-4">

        <aside class="col-md-4 col-lg-3">
          <form class="filtros-card" action="#" method="get">
            <button class="btn btn-filtros-limpiar w-100" type="reset">Limpiar filtros</button>

            <h2 class="filtros-titulo">Categoría</h2>
            <div class="form-check filtros-opcion">
              <input class="form-check-input" type="checkbox" name="categoria[]" value="accesorios" id="catAccesorios">
              <label class="form-check-label" for="catAccesorios">Accesorios</label>
            </div>
            <div class="form-check filtros-opcion">
              <input class="form-check-input" type="checkbox" name="categoria[]" value="alimentos" id="catAlimentos">
              <label class="form-check-label" for="catAlimentos">Alimentos</label>
            </div>
            <div class="form-check filtros-opcion">
              <input class="form-check-input" type="checkbox" name="categoria[]" value="farmacia" id="catFarmacia">
              <label class="form-check-label" for="catFarmacia">Farmacia</label>
            </div>
            <div class="form-check filtros-opcion">
              <input class="form-check-input" type="checkbox" name="categoria[]" value="higiene" id="catHigiene">
              <label class="form-check-label" for="catHigiene">Higiene</label>
            </div>
          </form>
        </aside>

        <section class="col-md-8 col-lg-9">
          <form class="d-flex justify-content-end mb-3" action="#" method="get">
            <label class="visually-hidden" for="orden">Ordenar productos</label>
            <select class="form-select catalogo-orden" name="orden" id="orden">
              <option value="ultimos" selected>Ordenar por los últimos</option>
              <option value="precio_asc">Ordenar por precio: de más bajo a más alto</option>
              <option value="precio_desc">Ordenar por precio: de más alto a más bajo</option>
            </select>
          </form>

          <!-- TODO BD: traer productos filtrados y ordenados -->
          <!-- las imágenes son de relleno hasta tener las fotos -->
          <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3">

            <div class="col">
              <div class="producto-card">
                <div class="producto-img" aria-hidden="true">Alimentos</div>
                <p class="producto-nombre">Comida para perros 3kg</p>
                <p class="producto-precio">$1299</p>
                <button class="btn btn-carrito" type="button">Añadir al carrito</button>
              </div>
            </div>

            <div class="col">
              <div class="producto-card">
                <div class="producto-img" aria-hidden="true">Accesorios</div>
                <p class="producto-nombre">Plato para perros</p>
                <p class="producto-precio">$499</p>
                <button class="btn btn-carrito" type="button">Añadir al carrito</button>
              </div>
            </div>

            <div class="col">
              <div class="producto-card">
                <div class="producto-img" aria-hidden="true">Accesorios</div>
                <p class="producto-nombre">Cucha para perros</p>
                <p class="producto-precio">$899</p>
                <button class="btn btn-carrito" type="button">Añadir al carrito</button>
              </div>
            </div>

            <div class="col">
              <div class="producto-card">
                <div class="producto-img" aria-hidden="true">Accesorios</div>
                <p class="producto-nombre">Plato para mascotas plateado</p>
                <p class="producto-precio">$499</p>
                <button class="btn btn-carrito" type="button">Añadir al carrito</button>
              </div>
            </div>

            <div class="col">
              <div class="producto-card">
                <div class="producto-img" aria-hidden="true">Alimentos</div>
                <p class="producto-nombre">Comida para gatos 1.5kg</p>
                <p class="producto-precio">$799</p>
                <button class="btn btn-carrito" type="button">Añadir al carrito</button>
              </div>
            </div>

            <div class="col">
              <div class="producto-card">
                <div class="producto-img" aria-hidden="true">Accesorios</div>
                <p class="producto-nombre">Plato para gatos</p>
                <p class="producto-precio">$499</p>
                <button class="btn btn-carrito" type="button">Añadir al carrito</button>
              </div>
            </div>

            <div class="col">
              <div class="producto-card">
                <div class="producto-img" aria-hidden="true">Accesorios</div>
                <p class="producto-nombre">Juguete para gatos</p>
                <p class="producto-precio">$299</p>
                <button class="btn btn-carrito" type="button">Añadir al carrito</button>
              </div>
            </div>

            <div class="col">
              <div class="producto-card">
                <div class="producto-img" aria-hidden="true">Accesorios</div>
                <p class="producto-nombre">Rascador para gatos</p>
                <p class="producto-precio">$899</p>
                <button class="btn btn-carrito" type="button">Añadir al carrito</button>
              </div>
            </div>

          </div>
        </section>

      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
