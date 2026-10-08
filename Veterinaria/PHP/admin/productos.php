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
              <!-- TODO BD: PRODUCTO + proveedor por PROVEE. Stock bajo si Stock < StockMinimo, por vencer si vence en 30 días o menos -->
              <tbody>
                <tr>
                  <td>1</td>
                  <td class="admin-celda-corta">Comida para perros 3kg</td>
                  <td>Alimentos</td>
                  <td>$ 1.299</td>
                  <td>4<span class="badge badge-danger admin-badge-abajo">Stock bajo</span></td>
                  <td>10</td>
                  <td>15/03/2027</td>
                  <td class="admin-celda-corta">Nutripet S.A.</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>2</td>
                  <td class="admin-celda-corta">Plato para perros</td>
                  <td>Accesorios</td>
                  <td>$ 499</td>
                  <td>25</td>
                  <td>5</td>
                  <td><span class="admin-sin-dato">—</span></td>
                  <td class="admin-celda-corta">Accesorios del Este</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>3</td>
                  <td class="admin-celda-corta">Cucha para perros</td>
                  <td>Accesorios</td>
                  <td>$ 899</td>
                  <td>2<span class="badge badge-danger admin-badge-abajo">Stock bajo</span></td>
                  <td>3</td>
                  <td><span class="admin-sin-dato">—</span></td>
                  <td class="admin-celda-corta">Mascotienda Mayorista</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>4</td>
                  <td class="admin-celda-corta">Plato para mascotas plateado</td>
                  <td>Accesorios</td>
                  <td>$ 499</td>
                  <td>12</td>
                  <td>5</td>
                  <td><span class="admin-sin-dato">—</span></td>
                  <td class="admin-celda-corta">Accesorios del Este</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>5</td>
                  <td class="admin-celda-corta">Comida para gatos 1.5kg</td>
                  <td>Alimentos</td>
                  <td>$ 799</td>
                  <td>18</td>
                  <td>8</td>
                  <td>25/10/2026<span class="badge badge-tipo-peluqueria admin-badge-abajo">Por vencer</span></td>
                  <td class="admin-celda-corta">Nutripet S.A.</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>6</td>
                  <td class="admin-celda-corta">Plato para gatos</td>
                  <td>Accesorios</td>
                  <td>$ 499</td>
                  <td>9</td>
                  <td>5</td>
                  <td><span class="admin-sin-dato">—</span></td>
                  <td class="admin-celda-corta">Accesorios del Este</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>7</td>
                  <td class="admin-celda-corta">Juguete para gatos</td>
                  <td>Accesorios</td>
                  <td>$ 299</td>
                  <td>30</td>
                  <td>10</td>
                  <td><span class="admin-sin-dato">—</span></td>
                  <td class="admin-celda-corta">Mascotienda Mayorista</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>8</td>
                  <td class="admin-celda-corta">Rascador para gatos</td>
                  <td>Accesorios</td>
                  <td>$ 899</td>
                  <td>6</td>
                  <td>3</td>
                  <td><span class="admin-sin-dato">—</span></td>
                  <td class="admin-celda-corta">Mascotienda Mayorista</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- modal para agregar producto, por ahora no guarda nada -->
  <div class="modal fade admin-modal" id="modalProducto" tabindex="-1" aria-labelledby="modalProductoTitulo" aria-hidden="true" data-bs-theme="dark">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" action="#" method="post">
        <div class="modal-header">
          <h2 class="modal-title" id="modalProductoTitulo">Agregar producto</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-sm-4">
              <label class="form-label" for="IdProducto">Id</label>
              <input class="form-control" type="number" id="IdProducto" name="IdProducto" min="1" placeholder="9" required>
            </div>
            <div class="col-sm-8">
              <label class="form-label" for="Nombre">Nombre</label>
              <input class="form-control" type="text" id="Nombre" name="Nombre" placeholder="Comida para perros 3kg" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Categoria">Categoría</label>
              <select class="form-select" id="Categoria" name="Categoria" required>
                <option value="" selected disabled>Elegir categoría</option>
                <option value="Accesorios">Accesorios</option>
                <option value="Alimentos">Alimentos</option>
                <option value="Farmacia">Farmacia</option>
                <option value="Higiene">Higiene</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Precio">Precio ($)</label>
              <input class="form-control" type="number" id="Precio" name="Precio" min="0" step="0.01" placeholder="1299" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Stock">Stock</label>
              <input class="form-control" type="number" id="Stock" name="Stock" min="0" placeholder="20">
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="StockMinimo">Stock mínimo</label>
              <input class="form-control" type="number" id="StockMinimo" name="StockMinimo" min="0" placeholder="5">
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="FechaVencimiento">Vencimiento <span class="admin-aclaracion">(si corresponde)</span></label>
              <input class="form-control" type="date" id="FechaVencimiento" name="FechaVencimiento">
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="IdProveedor">Proveedor</label>
              <!-- TODO BD: proveedores (PROVEEDOR), se guarda en PROVEE -->
              <select class="form-select" id="IdProveedor" name="IdProveedor" required>
                <option value="" selected disabled>Elegir proveedor</option>
                <option value="1">Nutripet S.A.</option>
                <option value="2">Accesorios del Este</option>
                <option value="3">Mascotienda Mayorista</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn admin-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn admin-btn-agregar">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
