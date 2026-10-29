<?php
$rolesPermitidos = ['admin'];
require_once __DIR__ . '/../includes/verificar_sesion.php';

require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Mascota.php';
require_once __DIR__ . '/../clases/HistorialClinico.php';
require_once __DIR__ . '/../clases/CarnetVacunacion.php';

$id = (int) ($_GET['id'] ?? 0);
$mascota = Mascota::buscarPorId($id);

if (!$mascota) {
    header("Location: mascotas.php?msg=noexiste");
    exit();
}

$mensajes = [
    'HCeditado' => ['success', 'Los cambios del historial clínico se guardaron.'],
    'HCeliminado' => ['success', 'El historial clínico se eliminó.'],
    'HCbloqueado' => ['danger', 'No se puede eliminar el historial clínico.'],
    'HCnoexiste' => ['danger', 'El historial clínico no existe.'],
    'CVeditado' => ['success', 'Los cambios del carnet de vacunación se guardaron.'],
    'CVeliminado' => ['success', 'El carnet de vacunación se eliminó.'],
    'CVbloqueado' => ['danger', 'No se puede eliminar el carnet de vacunación.'],
    'CVnoexiste' => ['danger', 'El carnet de vacunación no existe.'],
    'error' => ['danger', 'No se pudo completar la operación.']
];

$mensaje = null;
if (isset($_GET['msg']) && isset($mensajes[$_GET['msg']])) {
    $mensaje = $mensajes[$_GET['msg']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['guardarHistorial'])) {
        $idHistorial  = (int) ($_POST['IdHistorialClinico'] ?? 0);
        $anamnesis    = trim($_POST['Anamnesis'] ?? '');
        $antecedentes = trim($_POST['Antecedentes'] ?? '');
        $tratamiento  = trim($_POST['Tratamiento'] ?? '');
        $diagnostico  = trim($_POST['Diagnostico'] ?? '');
        $fecha        = trim($_POST['Fecha'] ?? '');
        $peso         = !empty($_POST['Peso']) ? (float)$_POST['Peso'] : null;

        $historial = new HistorialClinico();
        if ($idHistorial > 0) {
            $ok = $historial->editarHistorialClinico($id, $idHistorial, $anamnesis, $antecedentes, $tratamiento, $diagnostico, $fecha, $peso);
        } else {
            $ok = $historial->crearHistorialClinico($id, $anamnesis, $antecedentes, $tratamiento, $diagnostico, $fecha, $peso);
        }

        // Determinamos qué mensaje mostrar según el resultado de $ok
        $msgKey = $ok ? 'HCeditado' : 'error';

        header("Location: fichaMascota.php?id=$id&msg=$msgKey");
        exit();
    }

    // Eliminar Historial
    if (isset($_POST['eliminarHistorial'])) {
        $idHistorialEliminar = (int) ($_POST['IdHistorialClinico'] ?? 0);
        $h = new HistorialClinico();
        $resultado = $h->eliminarHistorialClinico($id, $idHistorialEliminar); // Pasamos $idmascota e $idHistorial
        header("Location: fichaMascota.php?id=$id&msg=" . ($resultado ? 'HCeliminado' : 'error'));
        exit();
    }

    if (isset($_POST['guardarCarnet'])) {
        $idCarnet = (int) ($_POST['IdCarnetVacunacion'] ?? 0);
        $tVac     = trim($_POST['TipoVacunacion'] ?? '');
        $fUlt     = trim($_POST['FechaUltimaVacunacion'] ?? '');
        $fProx    = trim($_POST['FechaProximaVacunacion'] ?? '');
        $desp     = trim($_POST['Desparasitacion'] ?? '');
        $tDesp    = trim($_POST['TipoDesparasitacion'] ?? '');

        $c = new CarnetVacunacion();
        if ($idCarnet > 0) {
            $ok = $c->editarCarnet($idCarnet, $fProx, $fUlt, $tVac, $desp, $tDesp);
        } else {
            $ok = $c->crearCarnet($id, $fProx, $fUlt, $tVac, $desp, $tDesp);
        }

        $msgKey = $ok ? 'CVeditado' : 'error';
        header("Location: fichaMascota.php?id=$id&msg=$msgKey");
        exit();
    }

    // 4. Eliminar Carnet 
    if (isset($_POST['eliminarCarnet'])) {
        $idCarnetEliminar = (int) ($_POST['IdCarnetVacunacion'] ?? 0);
        $c = new CarnetVacunacion();
        $resultado = $c->eliminarCarnet($idCarnetEliminar);

        $msgKey = $resultado ? 'CVeliminado' : 'error';
        header("Location: fichaMascota.php?id=$id&msg=$msgKey");
        exit();
    }
}

if (isset($_GET['msg']) && isset($mensajes[$_GET['msg']])) {
    $mensaje = $mensajes[$_GET['msg']];
}

$historiales = HistorialClinico::listadoPorIdMascota($id);
$carnet = CarnetVacunacion::listadoPorIdMascota($id);
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de <?= htmlspecialchars($mascota['Nombre']) ?> - Panel Sienra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../CSS/style.css">
    <link rel="stylesheet" href="../../CSS/admin.css">
    <script src="../../JS/tema.js"></script>
</head>

<body>
    <div class="admin-wrapper">
        <?php $paginaActiva = 'mascotas';
        include __DIR__ . '/includes/sidebar.php'; ?>
        <div class="admin-contenido">
            <?php $tituloPagina = 'Ficha de mascota';
            include __DIR__ . '/includes/topbar.php'; ?>
            <main class="container-fluid px-3 px-lg-4 py-4">

                <?php if ($mensaje) { ?>
                    <div class="alert alert-<?= $mensaje[0] ?>" role="alert"><?= htmlspecialchars($mensaje[1]) ?></div>
                <?php } ?>

                <div class="admin-card mb-3">
                    <div class="admin-card-header flex-wrap">
                        <h2><?= htmlspecialchars($mascota['Nombre']) ?></h2>
                        <a class="admin-card-link" href="mascotas.php"><i class="bi bi-arrow-left"></i> Volver al listado</a>
                    </div>
                    <div class="row g-3 p-3 p-lg-4">
                        <div class="col-sm-3">
                            <p class="admin-dato-label">Especie</p>
                            <p class="admin-dato-valor"><?= htmlspecialchars($mascota['Especie']) ?></p>
                        </div>
                        <div class="col-sm-3">
                            <p class="admin-dato-label">Raza</p>
                            <p class="admin-dato-valor"><?= htmlspecialchars($mascota['Raza']) ?></p>
                        </div>
                        <div class="col-sm-3">
                            <p class="admin-dato-label">Fecha de nacimiento</p>
                            <p class="admin-dato-valor"><?= $mascota['FechaNacimiento'] ? date('d/m/Y', strtotime($mascota['FechaNacimiento'])) : '—' ?></p>
                        </div>
                    </div>
                </div>

                <!--  HISTORIAL CLINICO  -->
                <div class="admin-card mb-3">
                    <div class="admin-card-header flex-wrap">
                        <h2>Historial clínico</h2>
                        <button class="btn btn-sm admin-btn-agregar" type="button" data-bs-toggle="modal" data-bs-target="#modalHistorial"
                            onclick="nuevoHistorial()">
                            <i class="bi bi-plus-lg"></i> Agregar
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table admin-tabla mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Diagnóstico</th>
                                    <th scope="col">Anamnesis</th>
                                    <th scope="col">Antecedentes</th>
                                    <th scope="col">Tratamiento</th>
                                    <th scope="col">Peso</th>
                                    <th scope="col" class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($historiales)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Sin registros.</td>
                                    </tr>
                                    <?php else: foreach ($historiales as $h): ?>
                                        <tr>
                                            <td><?= $h['Fecha'] ? date('d/m/Y', strtotime($h['Fecha'])) : '—' ?></td>
                                            <td><?= htmlspecialchars($h['Diagnostico'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($h['Anamnesis'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($h['Antecedentes'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($h['Tratamiento'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($h['Peso'] ?? '—') ?> kg</td>
                                            <td class="text-end">
                                                <button class="btn btn-sm admin-btn-icono" type="button" data-bs-toggle="modal" data-bs-target="#modalHistorial"
                                                    onclick='editarHistorial(<?= json_encode($h) ?>)' aria-label="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" data-bs-toggle="modal" data-bs-target="#modalEliminarHistorial"
                                                    onclick="confirmarEliminarHistorial(<?= $h['IdHistorialClinico'] ?>)" aria-label="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                <?php endforeach;
                                endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- CARNET DE VACUNACION-->
                <div class="admin-card">
                    <div class="admin-card-header flex-wrap">
                        <h2>Carnet de vacunación</h2>
                        <button class="btn btn-sm admin-btn-agregar" type="button" data-bs-toggle="modal" data-bs-target="#modalCarnet"
                            onclick="nuevoCarnet()">
                            <i class="bi bi-plus-lg"></i> Agregar
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table admin-tabla mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Tipo vacuna</th>
                                    <th scope="col">Última vacunación</th>
                                    <th scope="col">Próxima vacunación</th>
                                    <th scope="col">Desparasitación</th>
                                    <th scope="col" class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($carnet)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Sin registros.</td>
                                    </tr>
                                    <?php else: foreach ($carnet as $c): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($c['TipoVacunacion'] ?? '—') ?></td>
                                            <td><?= $c['FechaUltimaVacunacion'] ? date('d/m/Y', strtotime($c['FechaUltimaVacunacion'])) : '—' ?></td>
                                            <td><?= $c['FechaProximaVacunacion'] ? date('d/m/Y', strtotime($c['FechaProximaVacunacion'])) : '—' ?></td>
                                            <td><?= htmlspecialchars($c['TipoDesparasitacion'] ?? '—') ?></td>
                                            <td class="text-end">
                                                <button class="btn btn-sm admin-btn-icono" type="button" data-bs-toggle="modal" data-bs-target="#modalCarnet"
                                                    onclick='editarCarnet(<?= json_encode($c) ?>)' aria-label="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" data-bs-toggle="modal" data-bs-target="#modalEliminarCarnet"
                                                    onclick="confirmarEliminarCarnet(<?= $c['IdCarnetVacunacion'] ?>)" aria-label="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                <?php endforeach;
                                endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- MODAL: agregar/editar historial  -->
    <div class="modal fade admin-modal" id="modalHistorial" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="fichaMascota.php?id=<?= $id ?>" method="post">
                <div class="modal-header">
                    <h2 class="modal-title" id="tituloModalHistorial">Agregar historial</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="IdHistorialClinico" id="hIdHistorial" value="">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="hFecha">Fecha</label>
                            <input class="form-control" type="date" id="hFecha" name="Fecha" max="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="hPeso">Peso (kg)</label>
                            <input class="form-control" type="number" step="0.1" id="hPeso" name="Peso">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="hDiagnostico">Diagnóstico</label>
                            <input class="form-control" type="text" id="hDiagnostico" name="Diagnostico" maxlength="255" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="hAnamnesis">Anamnesis</label>
                            <input class="form-control" type="text" id="hAnamnesis" name="Anamnesis" maxlength="255">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="hAntecedentes">Antecedentes</label>
                            <input class="form-control" type="text" id="hAntecedentes" name="Antecedentes" maxlength="255">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="hTratamiento">Tratamiento</label>
                            <input class="form-control" type="text" id="hTratamiento" name="Tratamiento" maxlength="255">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn admin-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn admin-btn-agregar" name="guardarHistorial">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!--  MODAL: confirmar eliminar historial  -->
    <div class="modal fade admin-modal" id="modalEliminarHistorial" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="fichaMascota.php?id=<?= $id ?>" method="post">
                <div class="modal-header">
                    <h2 class="modal-title">Eliminar registro</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="IdHistorialClinico" id="hIdEliminar" value="">
                    <p>¿Eliminar este registro de historial clínico? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn admin-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" name="eliminarHistorial">Sí, eliminar</button>
                </div>
            </form>
        </div>
    </div>

    <!--  MODAL: agregar/editar carnet  -->
    <div class="modal fade admin-modal" id="modalCarnet" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="fichaMascota.php?id=<?= $id ?>" method="post">
                <div class="modal-header">
                    <h2 class="modal-title" id="tituloModalCarnet">Agregar carnet</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="IdCarnetVacunacion" id="cIdCarnet" value="">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="cTipoVacuna">Tipo de vacuna</label>
                            <input class="form-control" type="text" id="cTipoVacuna" name="TipoVacunacion" maxlength="100" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="cFechaUltima">Última vacunación</label>
                            <input class="form-control" type="date" id="cFechaUltima" name="FechaUltimaVacunacion" max="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="cFechaProxima">Próxima vacunación</label>
                            <input class="form-control" type="date" id="cFechaProxima" name="FechaProximaVacunacion">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="cDesparasitacion">Desparasitación</label>
                            <input class="form-control" type="text" id="cDesparasitacion" name="Desparasitacion" maxlength="100">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="cTipoDesparasitacion">Tipo de desparasitación</label>
                            <input class="form-control" type="text" id="cTipoDesparasitacion" name="TipoDesparasitacion" maxlength="100">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn admin-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn admin-btn-agregar" name="guardarCarnet">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!--  MODAL: confirmar eliminar carnet  -->
    <div class="modal fade admin-modal" id="modalEliminarCarnet" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="fichaMascota.php?id=<?= $id ?>" method="post">
                <div class="modal-header">
                    <h2 class="modal-title">Eliminar registro</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="IdCarnetVacunacion" id="cIdEliminar" value="">
                    <p>¿Eliminar este registro de carnet de vacunación? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn admin-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" name="eliminarCarnet">Sí, eliminar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function nuevoHistorial() {
            document.getElementById('tituloModalHistorial').textContent = 'Agregar historial';
            document.getElementById('hIdHistorial').value = '';
            document.getElementById('hFecha').value = '';
            document.getElementById('hPeso').value = '';
            document.getElementById('hDiagnostico').value = '';
            document.getElementById('hAnamnesis').value = '';
            document.getElementById('hAntecedentes').value = '';
            document.getElementById('hTratamiento').value = '';
        }

        function editarHistorial(h) {
            document.getElementById('tituloModalHistorial').textContent = 'Editar historial';
            document.getElementById('hIdHistorial').value = h.IdHistorialClinico;
            document.getElementById('hFecha').value = h.Fecha ? h.Fecha.substring(0, 10) : '';
            document.getElementById('hPeso').value = h.Peso ?? '';
            document.getElementById('hDiagnostico').value = h['Diagnostico'] ?? '';
            document.getElementById('hAnamnesis').value = h.Anamnesis ?? '';
            document.getElementById('hAntecedentes').value = h.Antecedentes ?? '';
            document.getElementById('hTratamiento').value = h.Tratamiento ?? '';
        }

        function confirmarEliminarHistorial(idHistorial) {
            document.getElementById('hIdEliminar').value = idHistorial;
        }

        function nuevoCarnet() {
            document.getElementById('tituloModalCarnet').textContent = 'Agregar carnet';
            document.getElementById('cIdCarnet').value = '';
            document.getElementById('cTipoVacuna').value = '';
            document.getElementById('cFechaUltima').value = '';
            document.getElementById('cFechaProxima').value = '';
            document.getElementById('cDesparasitacion').value = '';
            document.getElementById('cTipoDesparasitacion').value = '';
        }

        function editarCarnet(c) {
            document.getElementById('tituloModalCarnet').textContent = 'Editar carnet';
            document.getElementById('cIdCarnet').value = c.IdCarnetVacunacion;
            document.getElementById('cTipoVacuna').value = c.TipoVacunacion ?? '';
            document.getElementById('cFechaUltima').value = c.FechaUltimaVacunacion ? c.FechaUltimaVacunacion.substring(0, 10) : '';
            document.getElementById('cFechaProxima').value = c.FechaProximaVacunacion ? c.FechaProximaVacunacion.substring(0, 10) : '';
            document.getElementById('cDesparasitacion').value = c.Desparasitacion ?? '';
            document.getElementById('cTipoDesparasitacion').value = c.TipoDesparasitacion ?? '';
        }

        function confirmarEliminarCarnet(idCarnet) {
            document.getElementById('cIdEliminar').value = idCarnet;
        }
    </script>
</body>

</html>