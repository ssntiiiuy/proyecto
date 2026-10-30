<?php
$rolesPermitidos = ['Veterinario', 'Cajero', 'Peluquero', 'Cirujano'];
require_once __DIR__ . '/../includes/verificar_sesion.php';

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

// Capturar filtro de tipo si viene por la URL y está permitido para el rol
$filtroTipo = trim($_GET['filtro'] ?? '');
$tiposConsultaBD = $tiposPermitidos;

if ($filtroTipo !== '') {
    // Si es cajero (permisos vacíos) o el tipo está dentro de los permitidos del rol
    if (empty($tiposPermitidos) || in_array($filtroTipo, $tiposPermitidos)) {
        $tiposConsultaBD = [$filtroTipo];
    }
}

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

$datosServicios = Servicio::listadoServicios($tiposConsultaBD);
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

                    <!-- Botones de filtro rápido para roles con múltiples tipos (ej. Veterinario) -->
                    <?php if (count($tiposPermitidos) > 1 || empty($tiposPermitidos)): ?>
                        <div class="px-3 pt-3 pb-0 d-flex flex-wrap gap-2 align-items-center">
                            <span class="text-muted small fw-semibold">Filtrar por:</span>
                            <a href="servicios.php" class="btn btn-sm <?= $filtroTipo === '' ? 'btn-dark' : 'btn-outline-secondary' ?>">Todos</a>

                            <?php
                            $opcionesFiltro = empty($tiposPermitidos) ? ['Consulta', 'Vacunacion', 'Peluqueria', 'Cirugia'] : $tiposPermitidos;
                            foreach ($opcionesFiltro as $tp):
                            ?>
                                <a href="servicios.php?filtro=<?= $tp ?>" class="btn btn-sm <?= $filtroTipo === $tp ? 'btn-dark' : 'btn-outline-secondary' ?>">
                                    <?= $tp === 'Vacunacion' ? 'Vacunación' : $tp ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive p-3">
                        <table class="table admin-tabla mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Id</th>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Tipo</th>
                                    <th scope="col">Descripción</th>
                                    <th scope="col">Detalles específicos</th>
                                    <th scope="col">Duración</th>
                                    <th scope="col" class="text-end">Precio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($servicios)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">No hay servicios registrados.</td>
                                    </tr>
                                <?php else: foreach ($servicios as $serv): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($serv['IdServicio']) ?></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($serv['Nombre']) ?></td>
                                        <td>
                                            <?php
                                            $tipoServicio = $serv['Tipo'];
                                            $claseBadge = obtenerClaseBadge($tipoServicio);
                                            ?>
                                            <span class="badge <?= $claseBadge ?>"><?= htmlspecialchars($tipoServicio) ?></span>
                                        </td>
                                        <td class="admin-celda-texto"><?= htmlspecialchars($serv['Descripcion'] ?? '—') ?></td>

                                        <!-- Columna dinámica para mostrar atributos específicos según el tipo -->
                                        <td class="small text-muted">
                                            <?php
                                            switch (strtolower($tipoServicio)) {
                                                case 'consulta':
                                                    echo !empty($serv['Motivo']) ? 'Motivo: ' . htmlspecialchars($serv['Motivo']) : '';
                                                    break;
                                                case 'vacunacion':
                                                    $partesVac = [];
                                                    if (!empty($serv['TipoVacuna'])) $partesVac[] = 'Tipo: ' . htmlspecialchars($serv['TipoVacuna']);
                                                    if (!empty($serv['LoteVacuna'])) $partesVac[] = 'Lote: ' . htmlspecialchars($serv['LoteVacuna']);
                                                    if (!empty($serv['LaboratorioFabricante'])) $partesVac[] = 'Lab: ' . htmlspecialchars($serv['LaboratorioFabricante']);
                                                    echo implode(' · ', $partesVac);
                                                    break;
                                                case 'peluqueria':
                                                    $partesPel = [];
                                                    if (!empty($serv['TipoCorte'])) $partesPel[] = 'Corte: ' . htmlspecialchars($serv['TipoCorte']);
                                                    if (!empty($serv['TipoShampoo'])) $partesPel[] = 'Shampoo: ' . htmlspecialchars($serv['TipoShampoo']);
                                                    echo implode(' · ', $partesPel);
                                                    break;
                                                case 'cirugia':
                                                    $partesCir = [];
                                                    if (!empty($serv['TipoAnestesia'])) $partesCir[] = 'Anestesia: ' . htmlspecialchars($serv['TipoAnestesia']);
                                                    if (!empty($serv['RiesgoQuirurgico'])) $partesCir[] = 'Riesgo: ' . htmlspecialchars($serv['RiesgoQuirurgico']);
                                                    echo implode(' · ', $partesCir);
                                                    break;
                                                default:
                                                    echo '—';
                                            }
                                            ?>
                                        </td>

                                        <td><?= htmlspecialchars($serv['Duracion'] ?? '0') ?> min</td>
                                        <td class="text-end">$ <?= number_format($serv['Precio'] ?? 0, 0, ',', '.') ?></td>
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

    <!-- Modal para agregar servicio -->
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
                                <select class="form-select" id="TipoServicio" name="TipoServicio" required onchange="mostrarCamposTipo(this.value)">
                                    <option value="" selected disabled>Elegir tipo</option>
                                    <?php
                                    $opcionesModal = empty($tiposPermitidos) ? ['Consulta', 'Vacunacion', 'Peluqueria', 'Cirugia'] : $tiposPermitidos;
                                    foreach ($opcionesModal as $tipoOpcion):
                                    ?>
                                        <option value="<?= $tipoOpcion ?>"><?= $tipoOpcion === 'Vacunacion' ? 'Vacunación' : $tipoOpcion ?></option>
                                    <?php endforeach; ?>
                                </select>
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

                        <!-- Campos dinámicos de Consulta -->
                        <div id="camposConsulta" class="campos-tipo d-none row g-3 mt-1">
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

                        <!-- Campos dinámicos de Vacunación -->
                        <div id="camposVacunacion" class="campos-tipo d-none row g-3 mt-1">
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

                        <!-- Campos dinámicos de Peluquería -->
                        <div id="camposPeluqueria" class="campos-tipo d-none row g-3 mt-1">
                            <div class="col-sm-6">
                                <label class="form-label" for="TipoCorte">Tipo de corte</label>
                                <input class="form-control" type="text" id="TipoCorte" name="TipoCorte">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="TipoShampoo">Tipo de shampoo</label>
                                <input class="form-control" type="text" id="TipoShampoo" name="TipoShampoo">
                            </div>
                        </div>

                        <!-- Campos dinámicos de Cirugía -->
                        <div id="camposCirugia" class="campos-tipo d-none row g-3 mt-1">
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

        }
    </script>
</body>

</html>