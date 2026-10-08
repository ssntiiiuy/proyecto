<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Citas - Panel Sienra</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../CSS/style.css">
</head>

<body>

  <div class="admin-wrapper">
    <?php
    $paginaActiva = 'citas';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Citas';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <h2>Listado de citas</h2>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <label class="visually-hidden" for="filtroEstado">Filtrar por estado</label>
              <select class="form-select form-select-sm admin-filtro" id="filtroEstado" data-bs-theme="dark">
                <option selected>Todos los estados</option>
                <option>Pendiente</option>
                <option>Confirmada</option>
                <option>Cancelada</option>
              </select>
              <button class="btn btn-sm admin-btn-agregar" type="button" data-bs-toggle="modal" data-bs-target="#modalCita">
                <i class="bi bi-plus-lg"></i> Agregar cita
              </button>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table admin-tabla mb-0">
              <thead>
                <tr>
                  <th scope="col">Fecha</th>
                  <th scope="col">Hora</th>
                  <th scope="col">Cliente</th>
                  <th scope="col">Mascota</th>
                  <th scope="col">Servicio</th>
                  <th scope="col">Estado</th>
                  <th scope="col" class="text-end">Acciones</th>
                </tr>
              </thead>
              <!-- TODO BD: citas con nombre del cliente, de la mascota y del servicio -->
              <tbody>
                <tr>
                  <td>08/10/2026</td>
                  <td>09:30</td>
                  <td>Jorge Pérez</td>
                  <td>Rex</td>
                  <td>Consulta general</td>
                  <td><span class="badge badge-success">Confirmada</span></td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>08/10/2026</td>
                  <td>11:00</td>
                  <td>María González</td>
                  <td>Luna</td>
                  <td>Vacuna antirrábica</td>
                  <td><span class="badge badge-success">Confirmada</span></td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>08/10/2026</td>
                  <td>15:30</td>
                  <td>Lucía Fernández</td>
                  <td>Milo</td>
                  <td>Baño y corte</td>
                  <td><span class="badge badge-info">Pendiente</span></td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>09/10/2026</td>
                  <td>10:00</td>
                  <td>Martín Silva</td>
                  <td>Toby</td>
                  <td>Castración</td>
                  <td><span class="badge badge-danger">Cancelada</span></td>
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

  <!-- modal para agregar cita, por ahora no guarda nada -->
  <div class="modal fade admin-modal" id="modalCita" tabindex="-1" aria-labelledby="modalCitaTitulo" aria-hidden="true" data-bs-theme="dark">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" action="#" method="post">
        <div class="modal-header">
          <h2 class="modal-title" id="modalCitaTitulo">Agregar cita</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <!-- TODO BD: opciones de clientes, mascotas y servicios -->
          <div class="row g-3">
            <div class="col-sm-6">
              <label class="form-label" for="CI">Cliente</label>
              <select class="form-select" id="CI" name="CI" required>
                <option value="" selected disabled>Elegir cliente</option>
                <option value="45678901">Jorge Pérez</option>
                <option value="38765432">María González</option>
                <option value="51234567">Lucía Fernández</option>
                <option value="42345678">Martín Silva</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="IdMascota">Mascota</label>
              <select class="form-select" id="IdMascota" name="IdMascota" required>
                <option value="" selected disabled>Elegir mascota</option>
                <option value="1">Rex</option>
                <option value="2">Luna</option>
                <option value="3">Milo</option>
                <option value="4">Toby</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label" for="IdServicio">Servicio</label>
              <select class="form-select" id="IdServicio" name="IdServicio" required>
                <option value="" selected disabled>Elegir servicio</option>
                <option value="1">Consulta general</option>
                <option value="2">Vacuna antirrábica</option>
                <option value="3">Baño y corte</option>
                <option value="4">Castración</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Fecha">Fecha</label>
              <input class="form-control" type="date" id="Fecha" name="Fecha" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Hora">Hora</label>
              <input class="form-control" type="time" id="Hora" name="Hora" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Estado">Estado</label>
              <select class="form-select" id="Estado" name="Estado" required>
                <option value="Pendiente" selected>Pendiente</option>
                <option value="Confirmada">Confirmada</option>
                <option value="Cancelada">Cancelada</option>
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
