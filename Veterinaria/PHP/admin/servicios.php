<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Servicios - Panel Sienra</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../CSS/style.css">
</head>

<body>

  <div class="admin-wrapper">
    <?php
    $paginaActiva = 'servicios';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Servicios';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <h2>Listado de servicios</h2>
            <button class="btn btn-sm admin-btn-agregar" type="button" data-bs-toggle="modal" data-bs-target="#modalServicio">
              <i class="bi bi-plus-lg"></i> Agregar servicio
            </button>
          </div>

          <div class="table-responsive">
            <table class="table admin-tabla mb-0">
              <thead>
                <tr>
                  <th scope="col">Id</th>
                  <th scope="col">Nombre</th>
                  <th scope="col">Tipo</th>
                  <th scope="col">Descripción</th>
                  <th scope="col">Duración</th>
                  <th scope="col">Precio</th>
                  <th scope="col" class="text-end">Acciones</th>
                </tr>
              </thead>
              <!-- TODO BD: SERVICIO + el tipo sale de la tabla de subtipo donde esté el IdServicio -->
              <tbody>
                <tr>
                  <td>1</td>
                  <td>Consulta general</td>
                  <td><span class="badge badge-tipo-consulta">Consulta</span></td>
                  <td class="admin-celda-texto">Revisión clínica completa, diagnóstico e indicaciones.</td>
                  <td>30 min</td>
                  <td>$ 900</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>2</td>
                  <td>Vacuna antirrábica</td>
                  <td><span class="badge badge-tipo-vacunacion">Vacunación</span></td>
                  <td class="admin-celda-texto">Aplicación anual de la vacuna contra la rabia.</td>
                  <td>15 min</td>
                  <td>$ 750</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>3</td>
                  <td>Baño y corte</td>
                  <td><span class="badge badge-tipo-peluqueria">Peluquería</span></td>
                  <td class="admin-celda-texto">Baño, secado, corte de pelo y de uñas.</td>
                  <td>90 min</td>
                  <td>$ 1.200</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>4</td>
                  <td>Castración</td>
                  <td><span class="badge badge-tipo-cirugia">Cirugía</span></td>
                  <td class="admin-celda-texto">Castración con anestesia general y control posoperatorio.</td>
                  <td>120 min</td>
                  <td>$ 6.500</td>
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

  <!-- modal para agregar servicio, los campos de cada subtipo quedan para después -->
  <div class="modal fade admin-modal" id="modalServicio" tabindex="-1" aria-labelledby="modalServicioTitulo" aria-hidden="true" data-bs-theme="dark">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" action="#" method="post">
        <div class="modal-header">
          <h2 class="modal-title" id="modalServicioTitulo">Agregar servicio</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-sm-4">
              <label class="form-label" for="IdServicio">Id</label>
              <input class="form-control" type="number" id="IdServicio" name="IdServicio" min="1" placeholder="5" required>
            </div>
            <div class="col-sm-8">
              <label class="form-label" for="Nombre">Nombre</label>
              <input class="form-control" type="text" id="Nombre" name="Nombre" placeholder="Consulta general" required>
            </div>
            <div class="col-12">
              <label class="form-label" for="TipoServicio">Tipo</label>
              <select class="form-select" id="TipoServicio" name="TipoServicio" required>
                <option value="" selected disabled>Elegir tipo</option>
                <option value="CONSULTA">Consulta</option>
                <option value="VACUNACION">Vacunación</option>
                <option value="PELUQUERIA">Peluquería</option>
                <option value="CIRUGIA">Cirugía</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label" for="Descripcion">Descripción</label>
              <textarea class="form-control" id="Descripcion" name="Descripcion" rows="2" maxlength="255" placeholder="Revisión clínica completa..."></textarea>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Duracion">Duración (minutos)</label>
              <input class="form-control" type="number" id="Duracion" name="Duracion" min="5" step="5" placeholder="30">
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Precio">Precio ($)</label>
              <input class="form-control" type="number" id="Precio" name="Precio" min="0" step="0.01" placeholder="900">
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
