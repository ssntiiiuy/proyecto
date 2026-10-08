<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proveedores - Panel Sienra</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../CSS/style.css">
</head>

<body>

  <div class="admin-wrapper">
    <?php
    $paginaActiva = 'proveedores';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Proveedores';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <h2>Listado de proveedores</h2>
            <button class="btn btn-sm admin-btn-agregar" type="button" data-bs-toggle="modal" data-bs-target="#modalProveedor">
              <i class="bi bi-plus-lg"></i> Agregar proveedor
            </button>
          </div>

          <div class="table-responsive">
            <table class="table admin-tabla mb-0">
              <thead>
                <tr>
                  <th scope="col">Id</th>
                  <th scope="col">Nombre</th>
                  <th scope="col">Empresa</th>
                  <th scope="col">Teléfonos</th>
                  <th scope="col">Productos</th>
                  <th scope="col" class="text-end">Acciones</th>
                </tr>
              </thead>
              <!-- TODO BD: PROVEEDOR + teléfonos de TELEFONO_PROVEEDOR + nombres de productos por PROVEE -->
              <tbody>
                <tr>
                  <td>1</td>
                  <td>Carlos Núñez</td>
                  <td>Nutripet S.A.</td>
                  <td>2604 1122<br>099 456 123</td>
                  <td class="admin-celda-texto">Comida para perros 3kg, Comida para gatos 1.5kg</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>2</td>
                  <td>Laura Benítez</td>
                  <td>Accesorios del Este</td>
                  <td>4223 5566</td>
                  <td class="admin-celda-texto">Plato para perros, Plato para mascotas plateado, Plato para gatos</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>3</td>
                  <td>Gonzalo Ferreira</td>
                  <td>Mascotienda Mayorista</td>
                  <td>2908 7744<br>098 222 333</td>
                  <td class="admin-celda-texto">Cucha para perros, Juguete para gatos, Rascador para gatos</td>
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

  <!-- modal para agregar proveedor, por ahora no guarda nada -->
  <div class="modal fade admin-modal" id="modalProveedor" tabindex="-1" aria-labelledby="modalProveedorTitulo" aria-hidden="true" data-bs-theme="dark">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" action="#" method="post">
        <div class="modal-header">
          <h2 class="modal-title" id="modalProveedorTitulo">Agregar proveedor</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-sm-4">
              <label class="form-label" for="IdProveedor">Id</label>
              <input class="form-control" type="number" id="IdProveedor" name="IdProveedor" min="1" placeholder="4" required>
            </div>
            <div class="col-sm-8">
              <label class="form-label" for="Nombre">Nombre de contacto</label>
              <input class="form-control" type="text" id="Nombre" name="Nombre" placeholder="Carlos Núñez" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="NombreEmpresa">Empresa</label>
              <input class="form-control" type="text" id="NombreEmpresa" name="NombreEmpresa" placeholder="Nutripet S.A." required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="numTelProveedor">Teléfono</label>
              <input class="form-control" type="tel" id="numTelProveedor" name="numTelProveedor" placeholder="2604 1122" required>
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
