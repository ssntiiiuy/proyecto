<?php
$rolesPermitidos = ['admin', 'Cajero'];
require_once __DIR__ . '/../includes/verificar_sesion.php';

require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Usuario.php';
require_once __DIR__ . '/../clases/Producto.php';
require_once __DIR__ . '/../clases/Factura.php';

/* el admin tambien puede emitir facturas (RF46, actor Encargado), ademas del cajero */

/* RUT y nombre fijos de la clinica: FACTURA no guarda estos datos del cliente, son los datos de quien emite */
const RUT_CLINICA = '217654320018';
const NOMBRE_CLINICA = 'Clínica Veterinaria Sienra';

$clientes = Usuario::listadoUsuarios()['clientes'];
$productos = Producto::listadoProductos()['productos'];

$error = null;
$idFacturaCreada = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
  /* el cliente es opcional: '' = venta sin cliente registrado */
  $ciTexto = trim($_POST['ci'] ?? '');
  $ci = $ciTexto === '' ? null : (int)$ciTexto;
  $idsProducto = $_POST['idProducto'] ?? [];
  $cantidades = $_POST['cantidad'] ?? [];

  /* se arma la lista de lineas descartando las filas que quedaron vacias */
  $lineas = [];
  foreach ($idsProducto as $i => $idProducto) {
    $idProducto = trim($idProducto);
    $cantidad = (int)($cantidades[$i] ?? 0);
    if ($idProducto === '' || $cantidad <= 0) {
      continue;
    }
    $lineas[] = [
      'idProducto' => (int)$idProducto,
      'cantidad' => $cantidad,
    ];
  }

  if (empty($lineas)) {
    $error = 'Agregá al menos un producto con cantidad mayor a 0.';
  } else {
    try {
      $factura = new Factura();
      $idFacturaCreada = $factura->crearFactura($ci, RUT_CLINICA, $lineas, NOMBRE_CLINICA);
      header("Location: factura.php?IdFactura={$idFacturaCreada}&msg=creada");
      exit();
    } catch (Exception $e) {
      $error = $e->getMessage();
      /* el stock pudo haber cambiado: se recarga para que los selects muestren numeros actuales */
      $productos = Producto::listadoProductos()['productos'];
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nueva venta - Panel Sienra</title>

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
    $paginaActiva = 'facturas';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Nueva factura';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <?php if ($error) { ?>
          <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php } ?>

        <form method="post" action="caja.php" id="formFactura">
          <div class="admin-card mb-4">
            <div class="admin-card-header">
              <h2>Datos de la factura</h2>
            </div>
            <div class="row g-3 p-3 p-lg-4 pt-0">
              <div class="col-sm-6 col-lg-6">
                <label class="form-label" for="ci">Cliente <span class="admin-sin-dato">(opcional)</span></label>
                <select class="form-select" id="ci" name="ci">
                  <option value="" selected>Sin cliente registrado</option>
                  <?php foreach ($clientes as $cl) { ?>
                    <option value="<?= (int)$cl['CI'] ?>"><?= htmlspecialchars($cl['Nombre'] . ' ' . $cl['Apellido']) ?> (CI <?= htmlspecialchars($cl['CI']) ?>)</option>
                  <?php } ?>
                </select>
                <?php if (empty($clientes)) { ?>
                  <div class="form-text text-warning">No hay clientes registrados todavía.</div>
                <?php } ?>
              </div>
              <div class="col-sm-6 col-lg-3">
                <label class="form-label">Vencimiento</label>
                <input class="form-control" type="text" value="<?= htmlspecialchars(date('d/m/Y', strtotime('+' . Factura::DIAS_VENCIMIENTO . ' days'))) ?> (<?= Factura::DIAS_VENCIMIENTO ?> días)" disabled>
              </div>
              <div class="col-sm-6 col-lg-3">
                <label class="form-label">Emitida por</label>
                <input class="form-control" type="text" value="<?= htmlspecialchars(NOMBRE_CLINICA) ?> · RUT <?= RUT_CLINICA ?>" disabled>
              </div>
            </div>
          </div>

          <div class="admin-card mb-4">
            <div class="admin-card-header">
              <h2>Productos</h2>
              <button type="button" class="btn btn-sm admin-btn-agregar" id="btnAgregarLinea"><i class="bi bi-plus-lg"></i> Agregar línea</button>
            </div>
            <div class="table-responsive">
              <table class="table admin-tabla mb-0" id="tablaLineas">
                <thead>
                  <tr>
                    <th scope="col">Producto</th>
                    <th scope="col" style="width:110px">Cantidad</th>
                    <th scope="col" class="text-end" style="width:140px">Subtotal (IVA incl.)</th>
                    <th scope="col" style="width:48px"></th>
                  </tr>
                </thead>
                <tbody id="cuerpoLineas"></tbody>
              </table>
            </div>
          </div>

          <div class="admin-card mb-4">
            <dl class="admin-factura-totales">
              <div>
                <dt>Subtotal</dt>
                <dd id="totalSubtotal">$ 0,00</dd>
              </div>
              <div>
                <dt>IVA</dt>
                <dd id="totalIva">$ 0,00</dd>
              </div>
              <div class="admin-factura-total">
                <dt>Total</dt>
                <dd id="totalGeneral">$ 0,00</dd>
              </div>
            </dl>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <a href="facturas.php" class="btn admin-btn-cancelar">Cancelar</a>
            <button type="submit" name="guardar" class="btn admin-btn-agregar">Emitir factura</button>
          </div>
        </form>

      </main>
    </div>
  </div>

  <!-- fila molde: la copia el JS, por eso no tiene name (si no, se mandaria una linea vacia) -->
  <template id="plantillaLinea">
    <tr class="fila-linea">
      <td>
        <select class="form-select form-select-sm select-producto" required>
          <option value="" selected disabled>Elegir producto</option>
          <?php foreach ($productos as $p) { ?>
            <option value="<?= (int)$p['IdProducto'] ?>" data-precio="<?= htmlspecialchars($p['Precio']) ?>" data-stock="<?= (int)$p['Stock'] ?>">
              <?= htmlspecialchars($p['Nombre']) ?> ($ <?= number_format((float)$p['Precio'], 2, ',', '.') ?> · stock <?= (int)$p['Stock'] ?>)
            </option>
          <?php } ?>
        </select>
      </td>
      <td><input type="number" class="form-control form-control-sm input-cantidad" min="1" value="1" required></td>
      <td class="text-end subtotal-linea">$ 0,00</td>
      <td class="text-end">
        <button type="button" class="btn btn-sm admin-btn-icono admin-btn-eliminar btn-quitar-linea" aria-label="Quitar"><i class="bi bi-trash"></i></button>
      </td>
    </tr>
  </template>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const cuerpoLineas = document.getElementById('cuerpoLineas');
    const plantilla = document.getElementById('plantillaLinea');
    const IVA_GENERAL = <?= Factura::IVA_GENERAL ?>; // fijo: no se puede editar desde el formulario

    function formatearMoneda(valor) {
      return '$ ' + valor.toLocaleString('es-UY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalcularFila(fila) {
      const select = fila.querySelector('.select-producto');
      const opcion = select.selectedOptions[0];
      const precio = opcion ? parseFloat(opcion.dataset.precio || '0') : 0;
      const cantidad = parseFloat(fila.querySelector('.input-cantidad').value || '0');

      const subtotal = precio * cantidad;
      const conIva = subtotal * (1 + IVA_GENERAL / 100);

      fila.querySelector('.subtotal-linea').textContent = formatearMoneda(conIva);
      fila.dataset.subtotal = subtotal;
      fila.dataset.iva = conIva - subtotal;

      recalcularTotales();
    }

    function recalcularTotales() {
      let subtotal = 0;
      let iva = 0;
      cuerpoLineas.querySelectorAll('.fila-linea').forEach((fila) => {
        subtotal += parseFloat(fila.dataset.subtotal || '0');
        iva += parseFloat(fila.dataset.iva || '0');
      });
      document.getElementById('totalSubtotal').textContent = formatearMoneda(subtotal);
      document.getElementById('totalIva').textContent = formatearMoneda(iva);
      document.getElementById('totalGeneral').textContent = formatearMoneda(subtotal + iva);
    }

    function agregarLinea() {
      const fila = plantilla.content.firstElementChild.cloneNode(true);

      /* recien aca se le ponen los name, asi las filas vacias nunca llegan al servidor */
      fila.querySelector('.select-producto').setAttribute('name', 'idProducto[]');
      fila.querySelector('.input-cantidad').setAttribute('name', 'cantidad[]');

      fila.querySelectorAll('.select-producto, .input-cantidad').forEach((campo) => {
        campo.addEventListener('input', () => recalcularFila(fila));
        campo.addEventListener('change', () => recalcularFila(fila));
      });

      fila.querySelector('.btn-quitar-linea').addEventListener('click', () => {
        fila.remove();
        recalcularTotales();
      });

      cuerpoLineas.appendChild(fila);
      recalcularFila(fila);
    }

    document.getElementById('btnAgregarLinea').addEventListener('click', agregarLinea);

    /* arranca con una linea lista para completar */
    agregarLinea();

    document.getElementById('formFactura').addEventListener('submit', (evento) => {
      if (!cuerpoLineas.querySelector('.fila-linea')) {
        evento.preventDefault();
        alert('Agregá al menos un producto.');
      }
    });
  </script>
</body>

</html>
