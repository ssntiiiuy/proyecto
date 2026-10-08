<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Usuarios - Panel Sienra</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../CSS/style.css">
</head>

<body>

  <div class="admin-wrapper">
    <?php
    $paginaActiva = 'usuarios';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Usuarios';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <!-- las pestañas las maneja el bundle de Bootstrap -->
            <ul class="nav admin-tabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tabClientes" data-bs-toggle="tab" data-bs-target="#panelClientes" type="button" role="tab" aria-controls="panelClientes" aria-selected="true">Clientes</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="tabEmpleados" data-bs-toggle="tab" data-bs-target="#panelEmpleados" type="button" role="tab" aria-controls="panelEmpleados" aria-selected="false">Empleados</button>
              </li>
            </ul>
            <button class="btn btn-sm admin-btn-agregar" type="button" data-bs-toggle="modal" data-bs-target="#modalUsuario">
              <i class="bi bi-plus-lg"></i> Agregar usuario
            </button>
          </div>

          <div class="tab-content">
            <div class="tab-pane fade show active" id="panelClientes" role="tabpanel" aria-labelledby="tabClientes" tabindex="0">
          <div class="table-responsive">
            <table class="table admin-tabla mb-0">
              <thead>
                <tr>
                  <th scope="col">C.I.</th>
                  <th scope="col">Nombre</th>
                  <th scope="col" class="d-none d-xxl-table-cell">Dirección</th>
                  <th scope="col">Teléfono</th>
                  <th scope="col">Correo</th>
                  <th scope="col">Fecha de alta</th>
                  <th scope="col" class="text-end">Acciones</th>
                </tr>
              </thead>
              <!-- TODO BD: USUARIO + CLIENTE, teléfonos de TELEFONO_USUARIO y correo/fecha de LOGIN vía HACE -->
              <tbody>
                <tr>
                  <td>4.567.890-1</td>
                  <td>Jorge Pérez</td>
                  <td class="d-none d-xxl-table-cell">18 de Julio 1450</td>
                  <td>097 654 321</td>
                  <td>jorgeperez@gmail.com</td>
                  <td>12/03/2025</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>3.876.543-2</td>
                  <td>María González</td>
                  <td class="d-none d-xxl-table-cell">Sarandí 830</td>
                  <td>099 123 456<br>4222 3344</td>
                  <td>mariagonzalez@hotmail.com</td>
                  <td>28/05/2025</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>5.123.456-7</td>
                  <td>Lucía Fernández</td>
                  <td class="d-none d-xxl-table-cell">Ventura Alegre 512</td>
                  <td>091 987 654</td>
                  <td>luciaf@gmail.com</td>
                  <td>02/08/2026</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>4.234.567-8</td>
                  <td>Martín Silva</td>
                  <td class="d-none d-xxl-table-cell">Florida 1023</td>
                  <td>098 456 789</td>
                  <td>martinsilva@gmail.com</td>
                  <td>15/09/2026</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
            </div>
            <div class="tab-pane fade" id="panelEmpleados" role="tabpanel" aria-labelledby="tabEmpleados" tabindex="0">
          <div class="table-responsive">
            <table class="table admin-tabla mb-0">
              <thead>
                <tr>
                  <th scope="col">C.I.</th>
                  <th scope="col">Nombre</th>
                  <th scope="col" class="d-none d-xxl-table-cell">Dirección</th>
                  <th scope="col">Teléfono</th>
                  <th scope="col">Correo</th>
                  <th scope="col">Rol</th>
                  <th scope="col">Fecha de alta</th>
                  <th scope="col" class="text-end">Acciones</th>
                </tr>
              </thead>
              <!-- TODO BD: USUARIO + EMPLEADO (Rol), teléfonos y correo/fecha igual que clientes -->
              <tbody>
                <tr>
                  <td>3.456.789-0</td>
                  <td>Ana Rodríguez</td>
                  <td class="d-none d-xxl-table-cell">Dodera 745</td>
                  <td>099 888 777</td>
                  <td>ana.rodriguez@sienra.com.uy</td>
                  <td>Encargada</td>
                  <td>10/01/2024</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>2.987.654-3</td>
                  <td>Pablo Méndez</td>
                  <td class="d-none d-xxl-table-cell">Rincón 980</td>
                  <td>094 321 654</td>
                  <td>pablo.mendez@sienra.com.uy</td>
                  <td>Veterinario</td>
                  <td>10/01/2024</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>4.876.543-1</td>
                  <td>Carolina Díaz</td>
                  <td class="d-none d-xxl-table-cell">Joaquín de Viana 640</td>
                  <td>092 555 111</td>
                  <td>carolina.diaz@sienra.com.uy</td>
                  <td>Peluquera</td>
                  <td>03/06/2025</td>
                  <td class="text-end">
                    <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
            </div>
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- modal para agregar usuario, por ahora no guarda nada -->
  <div class="modal fade admin-modal" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioTitulo" aria-hidden="true" data-bs-theme="dark">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <form class="modal-content" action="#" method="post">
        <div class="modal-header">
          <h2 class="modal-title" id="modalUsuarioTitulo">Agregar usuario</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-sm-6">
              <label class="form-label" for="CI">C.I.</label>
              <input class="form-control" type="text" id="CI" name="CI" placeholder="45678901" inputmode="numeric" pattern="[0-9]{7,8}" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="numTelUsuario">Teléfono</label>
              <input class="form-control" type="tel" id="numTelUsuario" name="numTelUsuario" placeholder="097 654 321" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Nombre">Nombre</label>
              <input class="form-control" type="text" id="Nombre" name="Nombre" placeholder="Jorge" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Apellido">Apellido</label>
              <input class="form-control" type="text" id="Apellido" name="Apellido" placeholder="Pérez" required>
            </div>
            <div class="col-12">
              <label class="form-label" for="Direccion">Dirección</label>
              <input class="form-control" type="text" id="Direccion" name="Direccion" placeholder="18 de Julio 1450">
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Correo">Correo</label>
              <input class="form-control" type="email" id="Correo" name="Correo" placeholder="nombre@correo.com" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Contrasena">Contraseña</label>
              <input class="form-control" type="password" id="Contrasena" name="Contraseña" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="TipoUsuario">Tipo de usuario</label>
              <select class="form-select" id="TipoUsuario" name="TipoUsuario" required>
                <option value="" selected disabled>Elegir tipo</option>
                <option value="cliente">Cliente</option>
                <option value="empleado">Empleado</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Rol">Rol <span class="admin-aclaracion">(solo empleados)</span></label>
              <input class="form-control" type="text" id="Rol" name="Rol" placeholder="Veterinario">
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
