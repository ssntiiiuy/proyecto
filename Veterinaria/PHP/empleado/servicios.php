<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Servicio.php';
require_once __DIR__ . '/../clases/Usuario.php';

$conexion = (new Conexion())->establecerConexion();

$sql = "SELECT e.Rol FROM empleado e INNER JOIN usuario u ON e.CI = u.CI INNER JOIN HACE h ON u.CI = h.CI WHERE h.Correo = :correo";
$stmt = $conexion->prepare($sql);
$stmt->execute([':correo' => $_SESSION['correo']]);

$fila = $stmt->fetch(PDO::FETCH_ASSOC);
$rol = $fila['Rol'] ?? null;

$mapaRolTipos = [
    'Cirujano'    => ['Cirugia'],
    'Veterinario' => ['Vacunacion', 'Consulta'],
    'Peluquero'   => ['Peluqueria'],
    'Cajero'      => [], // puede ver todos los servicios
];

$tiposPermitidos = $mapaRolTipos[$rol] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    $nombre = trim($_POST['Nombre']);
    $tipo = trim($_POST['TipoServicio']);
    $precio = trim($_POST['Precio']);
    $descripcion = trim($_POST['Descripcion']);
    $duracion = trim($_POST['Duracion']);

    $servicio = new Servicio();
    $resultado = $servicio->crearServicio($tipo, $nombre, $precio, $descripcion, $duracion, $_POST);

    if ($resultado) {
        header("Location: servicios.php");
        exit();
    } else {
        $error = "No se pudo completar el registro.";
    }
}

$datosServicios = Servicio::listadoServicios($tiposPermitidos);
$servicios = $datosServicios['servicios'];

function obtenerClaseBadge($tipo)
{
    $tipoLimpio = strtolower(trim($tipo));

    switch ($tipoLimpio) {
        case 'consulta':
            return 'badge-tipo-consulta';
        case 'vacunacion':
            return 'badge-tipo-vacunacion';
        case 'peluqueria':
            return 'badge-tipo-peluqueria';
        case 'cirugia':
            return 'badge-tipo-cirugia';
        default:
            return 'bg-secondary';
    }
}

?>

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
    <link rel="stylesheet" href="../../CSS/admin.css">
    <script src="../../JS/tema.js"></script>
</head>

<body>

    <div class="admin-wrapper" data-bs-theme="light">
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
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <button class="btn btn-sm admin-btn-agregar" type="button" data-bs-toggle="modal" data-bs-target="#modalServicio">
                                <i class="bi bi-plus-lg"></i> Agregar servicio
                            </button>
                        </div>
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

                            <!-- listado de servicios -->
                            <tbody>
                                <?php if (empty($servicios)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No hay servicios registrados.</td>
                                    </tr>
                                    <?php else: foreach ($servicios as $serv): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($serv['IdServicio']) ?></td>
                                            <td><?= htmlspecialchars($serv['Nombre']) ?></td>

                                            <td>
                                                <?php
                                                $tipoServicio = $serv['Tipo'];
                                                $claseBadge = obtenerClaseBadge($tipoServicio);
                                                ?>
                                                <span class="badge <?= $claseBadge ?>"><?= htmlspecialchars($tipoServicio) ?></span>
                                            </td>

                                            <td class="admin-celda-texto"><?= htmlspecialchars($serv['Descripcion'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($serv['Duracion'] ?? '') ?> min</td>
                                            <td>$ <?= number_format($serv['Precio'] ?? 0, 0, ',', '.') ?></td>

                                            <td class="text-end">
                                                <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>


                        </table>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- agregar servicio -->
    <div class="modal fade admin-modal" id="modalServicio" tabindex="-1" aria-labelledby="modalServicioTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="#" method="post">
                <div class="modal-header">
                    <h2 class="modal-title" id="modalServicioTitulo">Agregar servicio</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-sm-12">
                            <label class="form-label" for="Nombre">Nombre</label>
                            <input class="form-control" type="text" id="Nombre" name="Nombre" placeholder="Consulta general" required>
                        </div>

                        <div class="col-12">
                            <?php if (count($tiposPermitidos) === 1): ?>
                                <input type="hidden" id="TipoServicio" name="TipoServicio" value="<?= htmlspecialchars($tiposPermitidos[0]) ?>">
                            <?php else: ?>
                                <label class="form-label" for="TipoServicio">Tipo</label>
                                <?php if (!empty($tiposPermitidos)): ?>
                                    <select class="form-select" id="TipoServicio" name="TipoServicio" required onchange="mostrarCamposTipo(this.value)">
                                        <option value="" selected disabled>Elegir tipo</option>
                                        <?php foreach ($tiposPermitidos as $tipoOpcion): ?>
                                            <option value="<?= $tipoOpcion ?>"><?= $tipoOpcion === 'Vacunacion' ? 'Vacunación' : $tipoOpcion ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <select class="form-select" id="TipoServicio" name="TipoServicio" required onchange="mostrarCamposTipo(this.value)">
                                        <option value="" selected disabled>Elegir tipo</option>
                                        <option value="Consulta">Consulta</option>
                                        <option value="Vacunacion">Vacunación</option>
                                        <option value="Peluqueria">Peluquería</option>
                                        <option value="Cirugia">Cirugía</option>
                                    </select>
                                <?php endif; ?>
                            <?php endif; ?>
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
                        <div id="camposConsulta" class="campos-tipo d-none row g-3">
                            <div class="col-sm-12">
                                <label class="form-label" for="Motivo">Motivo</label>
                                <input class="form-control" type="text" id="Motivo" name="Motivo">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="EsEmergencia">¿Es emergencia?</label>
                                <select class="form-select" id="EsEmergencia" name="EsEmergencia">
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label" for="IndicacionesRecetadas">Indicaciones</label>
                                <textarea class="form-control" id="IndicacionesRecetadas" name="IndicacionesRecetadas"></textarea>
                            </div>
                        </div>

                        <div id="camposVacunacion" class="campos-tipo d-none row g-3">
                            <div class="col-sm-4">
                                <label class="form-label" for="LoteVacuna">Lote</label>
                                <input class="form-control" type="text" id="LoteVacuna" name="LoteVacuna">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label" for="TipoVacuna">Tipo de vacuna</label>
                                <input class="form-control" type="text" id="TipoVacuna" name="TipoVacuna">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label" for="LaboratorioFabricante">Laboratorio</label>
                                <input class="form-control" type="text" id="LaboratorioFabricante" name="LaboratorioFabricante">
                            </div>
                        </div>

                        <div id="camposPeluqueria" class="campos-tipo d-none row g-3">
                            <div class="col-sm-6">
                                <label class="form-label" for="TipoCorte">Tipo de corte</label>
                                <input class="form-control" type="text" id="TipoCorte" name="TipoCorte">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="TipoShampoo">Tipo de shampoo</label>
                                <input class="form-control" type="text" id="TipoShampoo" name="TipoShampoo">
                            </div>
                        </div>

                        <div id="camposCirugia" class="campos-tipo d-none row g-3">
                            <div class="col-sm-4">
                                <label class="form-label" for="TipoAnestesia">Anestesia</label>
                                <input class="form-control" type="text" id="TipoAnestesia" name="TipoAnestesia">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label" for="RiesgoQuirurgico">Riesgo quirúrgico</label>
                                <input class="form-control" type="text" id="RiesgoQuirurgico" name="RiesgoQuirurgico">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label" for="AutorizacionFirmada">¿Autorización firmada?</label>
                                <select class="form-select" id="AutorizacionFirmada" name="AutorizacionFirmada">
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>
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

    <?php if (count($tiposPermitidos) === 1): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => mostrarCamposTipo('<?= $tiposPermitidos[0] ?>'));
        </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function mostrarCamposTipo(tipo) {
            document.querySelectorAll('.campos-tipo').forEach(div => {
                div.classList.add('d-none');
                div.querySelectorAll('input, select, textarea').forEach(campo => campo.required = false);
            });

            const mapa = {
                'Consulta': 'camposConsulta',
                'Vacunacion': 'camposVacunacion',
                'Peluqueria': 'camposPeluqueria',
                'Cirugia': 'camposCirugia'
            };

            const idDiv = mapa[tipo];
            if (idDiv) {
                const div = document.getElementById(idDiv);
                div.classList.remove('d-none');
            }
        }
    </script>
</body>

</html>