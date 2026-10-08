<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mascotas - Panel Sienra</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../CSS/style.css">
</head>

<body>

  <div class="admin-wrapper">
    <?php
    $paginaActiva = 'mascotas';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Mascotas';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <h2>Listado de mascotas</h2>
            <button class="btn btn-sm admin-btn-agregar" type="button" data-bs-toggle="modal" data-bs-target="#modalMascota">
              <i class="bi bi-plus-lg"></i> Agregar mascota
            </button>
          </div>

          <div class="table-responsive">
            <table class="table admin-tabla mb-0">
              <thead>
                <tr>
                  <th scope="col">Id</th>
                  <th scope="col">Nombre</th>
                  <th scope="col">Especie</th>
                  <th scope="col">Raza</th>
                  <th scope="col">Fecha de nacimiento</th>
                  <th scope="col">Dueño</th>
                  <th scope="col" class="text-end">Acciones</th>
                </tr>
              </thead>
              <!-- TODO BD: MASCOTA con nombre del dueño (USUARIO por CI) -->
              <tbody>
                <tr>
                  <td>1</td>
                  <td>Rex</td>
                  <td>Perro</td>
                  <td>Labrador</td>
                  <td>05/08/2015</td>
                  <td>Jorge Pérez<span class="admin-subtexto">4.567.890-1</span></td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>2</td>
                  <td>Luna</td>
                  <td>Gato</td>
                  <td>Siamés</td>
                  <td>12/03/2021</td>
                  <td>María González<span class="admin-subtexto">3.876.543-2</span></td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>3</td>
                  <td>Milo</td>
                  <td>Gato</td>
                  <td>Común europeo</td>
                  <td>20/01/2023</td>
                  <td>Lucía Fernández<span class="admin-subtexto">5.123.456-7</span></td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>4</td>
                  <td>Toby</td>
                  <td>Perro</td>
                  <td>Caniche</td>
                  <td>20/11/2019</td>
                  <td>Martín Silva<span class="admin-subtexto">4.234.567-8</span></td>
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

  <!-- modal para agregar mascota, por ahora no guarda nada -->
  <div class="modal fade admin-modal" id="modalMascota" tabindex="-1" aria-labelledby="modalMascotaTitulo" aria-hidden="true" data-bs-theme="dark">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" action="#" method="post">
        <div class="modal-header">
          <h2 class="modal-title" id="modalMascotaTitulo">Agregar mascota</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-sm-4">
              <label class="form-label" for="IdMascota">Id</label>
              <input class="form-control" type="number" id="IdMascota" name="IdMascota" min="1" placeholder="5" required>
            </div>
            <div class="col-sm-8">
              <label class="form-label" for="Nombre">Nombre</label>
              <input class="form-control" type="text" id="Nombre" name="Nombre" placeholder="Rex" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Especie">Especie</label>
              <select class="form-select" id="Especie" name="Especie" required>
                <option value="" selected disabled>Elegir especie</option>
                <option value="Perro">Perro</option>
                <option value="Gato">Gato</option>
                <option value="Otro">Otro</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Raza">Raza</label>
              <input class="form-control" type="text" id="Raza" name="Raza" placeholder="Labrador">
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="FechaNacimiento">Fecha de nacimiento</label>
              <input class="form-control" type="date" id="FechaNacimiento" name="FechaNacimiento">
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="CI">Dueño</label>
              <!-- TODO BD: clientes (CLIENTE + USUARIO) -->
              <select class="form-select" id="CI" name="CI" required>
                <option value="" selected disabled>Elegir cliente</option>
                <option value="45678901">Jorge Pérez</option>
                <option value="38765432">María González</option>
                <option value="51234567">Lucía Fernández</option>
                <option value="42345678">Martín Silva</option>
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
