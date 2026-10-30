<?php
$rolesPermitidos = ['admin'];
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../clases/Conexion.php';

$conexion = (new Conexion())->establecerConexion();

// 1. Tipo de reporte (diario, mensual, anual) y valores de filtrado
$tipoReporte = isset($_GET['tipo']) ? $_GET['tipo'] : 'mensual';
$fechaDiaria = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
$mesSeleccionado = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$anioSeleccionado = isset($_GET['anio']) ? $_GET['anio'] : date('Y');

// Construir la condición de filtrado SQL según el tipo elegido
if ($tipoReporte === 'diario') {
  $condicionSql = "DATE(f.Fecha) = :fecha";
  $parametros = [':fecha' => $fechaDiaria];
} elseif ($tipoReporte === 'anual') {
  $condicionSql = "YEAR(f.Fecha) = :anio";
  $parametros = [':anio' => $anioSeleccionado];
} else { // Mensual por defecto
  $condicionSql = "MONTH(f.Fecha) = :mes AND YEAR(f.Fecha) = :anio";
  $parametros = [':mes' => $mesSeleccionado, ':anio' => $anioSeleccionado];
}

// 2. Consultas para las Tarjetas de Resumen (KPIs)
$sqlKpi = $conexion->prepare("
    SELECT
        COALESCE(SUM(p.Precio * lf.Cantidad), 0) as VentasTotales,
        COUNT(DISTINCT f.IdFactura) as FacturasEmitidas,
        COALESCE(SUM(lf.Cantidad), 0) as UnidadesVendidas
    FROM factura f
    JOIN linea_factura lf ON f.IdFactura = lf.IdFactura
    JOIN articulo_factura af ON lf.IdFactura = af.IdFactura AND lf.IdLineaFactura = af.IdLineaFactura
    JOIN producto p ON af.IdProducto = p.IdProducto
    WHERE $condicionSql
");
$sqlKpi->execute($parametros);
$kpi = $sqlKpi->fetch(PDO::FETCH_ASSOC);

$ventasTotales = $kpi['VentasTotales'] ?? 0;
$facturasEmitidas = $kpi['FacturasEmitidas'] ?? 0;
$unidadesVendidas = $kpi['UnidadesVendidas'] ?? 0;
$ticketPromedio = $facturasEmitidas > 0 ? ($ventasTotales / $facturasEmitidas) : 0;

// 3. Consulta para la tabla de "Ventas por categoría"
$sqlCategorias = $conexion->prepare("
    SELECT
        p.Categoria,
        SUM(lf.Cantidad) as Unidades,
        SUM(p.Precio * lf.Cantidad) as TotalVendido
    FROM factura f
    JOIN linea_factura lf ON f.IdFactura = lf.IdFactura
    JOIN articulo_factura af ON lf.IdFactura = af.IdFactura AND lf.IdLineaFactura = af.IdLineaFactura
    JOIN producto p ON af.IdProducto = p.IdProducto
    WHERE $condicionSql
    GROUP BY p.Categoria
");
$sqlCategorias->execute($parametros);
$categorias = $sqlCategorias->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reporte de ventas - Panel Sienra</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../CSS/style.css">
  <link rel="stylesheet" href="../../CSS/admin.css">
  <script src="../../JS/tema.js"></script>
  <script>
    function cambiarTipoReporte() {
      const tipo = document.getElementById('tipoReporte').value;
      document.getElementById('filtroDiario').style.display = tipo === 'diario' ? 'block' : 'none';
      document.getElementById('filtroMensual').style.display = tipo === 'mensual' ? 'block' : 'none';
      document.getElementById('filtroAnual').style.display = tipo === 'anual' ? 'block' : 'none';
    }
    window.addEventListener('DOMContentLoaded', cambiarTipoReporte);
  </script>
</head>

<body>
  <div class="admin-wrapper">
    <?php
    $paginaActiva = 'reportes';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Reportes';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">
        <h1 class="h3 mb-1">Reporte de ventas</h1>
        <p class="text-muted mb-4">Resumen y estadísticas de facturación del período</p>

        <!-- Filtros Dinámicos (Diario, Mensual, Anual) -->
        <div class="admin-card mb-4 p-3">
          <form method="get" action="reportes.php" class="row g-3 align-items-end">
            <input type="hidden" name="tipo" id="hiddenTipo" value="<?= htmlspecialchars($tipoReporte) ?>">
            <div class="col-md-3">
              <label class="form-label">Tipo de reporte</label>
              <select name="tipo" id="tipoReporte" class="form-select" onchange="document.getElementById('hiddenTipo').value = this.value; cambiarTipoReporte();">
                <option value="diario" <?= $tipoReporte === 'diario' ? 'selected' : '' ?>>Diario</option>
                <option value="mensual" <?= $tipoReporte === 'mensual' ? 'selected' : '' ?>>Mensual</option>
                <option value="anual" <?= $tipoReporte === 'anual' ? 'selected' : '' ?>>Anual</option>
              </select>
            </div>

            <!-- Selector Diario -->
            <div class="col-md-3" id="filtroDiario" style="display:none;">
              <label class="form-label">Fecha específica</label>
              <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($fechaDiaria) ?>">
            </div>

            <!-- Selector Mensual -->
            <div class="col-md-3" id="filtroMensual" style="display:none;">
              <label class="form-label">Mes</label>
              <select name="mes" class="form-select">
                <option value="1" <?= $mesSeleccionado == 1 ? 'selected' : '' ?>>Enero</option>
                <option value="2" <?= $mesSeleccionado == 2 ? 'selected' : '' ?>>Febrero</option>
                <option value="3" <?= $mesSeleccionado == 3 ? 'selected' : '' ?>>Marzo</option>
                <option value="4" <?= $mesSeleccionado == 4 ? 'selected' : '' ?>>Abril</option>
                <option value="5" <?= $mesSeleccionado == 5 ? 'selected' : '' ?>>Mayo</option>
                <option value="6" <?= $mesSeleccionado == 6 ? 'selected' : '' ?>>Junio</option>
                <option value="7" <?= $mesSeleccionado == 7 ? 'selected' : '' ?>>Julio</option>
                <option value="8" <?= $mesSeleccionado == 8 ? 'selected' : '' ?>>Agosto</option>
                <option value="9" <?= $mesSeleccionado == 9 ? 'selected' : '' ?>>Septiembre</option>
                <option value="10" <?= $mesSeleccionado == 10 ? 'selected' : '' ?>>Octubre</option>
                <option value="11" <?= $mesSeleccionado == 11 ? 'selected' : '' ?>>Noviembre</option>
                <option value="12" <?= $mesSeleccionado == 12 ? 'selected' : '' ?>>Diciembre</option>
              </select>
            </div>

            <!-- Selector Anual -->
            <div class="col-md-3" id="filtroAnual" style="display:none;">
              <label class="form-label">Año</label>
              <select name="anio" class="form-select">
                <option value="2026" <?= $anioSeleccionado == 2026 ? 'selected' : '' ?>>2026</option>
                <option value="2025" <?= $anioSeleccionado == 2025 ? 'selected' : '' ?>>2025</option>
                <option value="2024" <?= $anioSeleccionado == 2024 ? 'selected' : '' ?>>2024</option>
              </select>
            </div>

            <div class="col-md-3">
              <button type="submit" class="btn admin-btn-agregar w-100"><i class="bi bi-filter"></i> Generar reporte</button>
            </div>
            <div class="col-md-3 text-end">
              <button type="button" class="btn admin-btn-cancelar w-100" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
            </div>
          </form>
        </div>

        <!-- Tarjetas de Resumen (KPIs) -->
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="admin-card p-3 border-start border-primary border-4">
              <span class="text-muted small">VENTAS TOTALES</span>
              <h3 class="fw-bold mt-1">$ <?= number_format($ventasTotales, 2, ',', '.') ?></h3>
            </div>
          </div>
          <div class="col-md-3">
            <div class="admin-card p-3 border-start border-success border-4">
              <span class="text-muted small">FACTURAS EMITIDAS</span>
              <h3 class="fw-bold mt-1"><?= $facturasEmitidas ?></h3>
            </div>
          </div>
          <div class="col-md-3">
            <div class="admin-card p-3 border-start border-warning border-4">
              <span class="text-muted small">TICKET PROMEDIO</span>
              <h3 class="fw-bold mt-1">$ <?= number_format($ticketPromedio, 2, ',', '.') ?></h3>
            </div>
          </div>
          <div class="col-md-3">
            <div class="admin-card p-3 border-start border-info border-4">
              <span class="text-muted small">UNIDADES VENDIDAS</span>
              <h3 class="fw-bold mt-1"><?= $unidadesVendidas ?></h3>
            </div>
          </div>
        </div>

        <!-- Tabla de Ventas por Categoría -->
        <div class="admin-card mb-4">
          <div class="admin-card-header">
            <h2>Ventas por categoría</h2>
          </div>
          <div class="table-responsive">
            <table class="table admin-tabla mb-0">
              <thead>
                <tr>
                  <th>Categoría</th>
                  <th class="text-end">Unidades</th>
                  <th class="text-end">Total vendido</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($categorias)): ?>
                  <tr>
                    <td colspan="3" class="text-center text-muted py-3">No hay ventas registradas para este período.</td>
                  </tr>
                  <?php else: foreach ($categorias as $cat): ?>
                    <tr>
                      <td><?= htmlspecialchars($cat['Categoria']) ?></td>
                      <td class="text-end"><?= $cat['Unidades'] ?></td>
                      <td class="text-end">$ <?= number_format($cat['TotalVendido'], 2, ',', '.') ?></td>
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>