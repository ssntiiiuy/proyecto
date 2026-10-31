<aside class="offcanvas offcanvas-end carrito-panel <?= isset($_GET['carrito']) ? 'show' : '' ?>" tabindex="-1" id="panelCarrito" aria-labelledby="tituloCarrito">
  <div class="offcanvas-header border-bottom">
    <div>
      <h2 class="offcanvas-title" id="tituloCarrito">Carrito de compra</h2>
      <p class="carrito-detalle mb-0"><?= $cantidadCarrito ?> <?= $cantidadCarrito === 1 ? 'artículo' : 'artículos' ?></p>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar carrito"></button>
  </div>
  <div class="offcanvas-body">
    <?php if ($avisoCarrito) { ?>
      <div class="alert alert-<?= $avisoCarrito[0] ?>" role="alert"><?= htmlspecialchars($avisoCarrito[1]) ?></div>
    <?php } ?>
    <?php if (empty($productosCarrito) && $carritoDisponible) { ?>
      <div class="carrito-vacio">
        <i class="bi bi-bag" aria-hidden="true"></i>
        <h3>Tu carrito está vacío</h3>
        <p>Elegí productos del catálogo para empezar.</p>
        <a href="catalogo.php" class="btn btn-carrito">Explorar catálogo</a>
      </div>
    <?php } ?>
    <?php foreach ($productosCarrito as $articuloCarrito) { ?>
      <div class="carrito-articulo">
        <div class="d-flex justify-content-between gap-3">
          <div>
            <span class="carrito-detalle"><?= htmlspecialchars($articuloCarrito['Categoria']) ?></span>
            <h3><?= htmlspecialchars($articuloCarrito['Nombre']) ?></h3>
            <p class="carrito-detalle mb-2">$<?= number_format($articuloCarrito['Precio'], 2, ',', '.') ?> por unidad</p>
          </div>
          <form method="post">
            <input type="hidden" name="tokenCarrito" value="<?= htmlspecialchars($_SESSION['tokenCarrito']) ?>">
            <input type="hidden" name="IdProducto" value="<?= (int) $articuloCarrito['IdProducto'] ?>">
            <button class="btn carrito-quitar" name="accionCarrito" value="quitar" aria-label="Quitar <?= htmlspecialchars($articuloCarrito['Nombre']) ?>"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
          </form>
        </div>
        <div class="d-flex align-items-center justify-content-between gap-3">
          <form method="post" class="carrito-cantidad">
            <input type="hidden" name="tokenCarrito" value="<?= htmlspecialchars($_SESSION['tokenCarrito']) ?>">
            <input type="hidden" name="IdProducto" value="<?= (int) $articuloCarrito['IdProducto'] ?>">
            <button class="btn" name="accionCarrito" value="restar" aria-label="Restar una unidad de <?= htmlspecialchars($articuloCarrito['Nombre']) ?>">−</button>
            <span aria-label="Cantidad"><?= (int) $articuloCarrito['Cantidad'] ?></span>
            <button class="btn" name="accionCarrito" value="agregar" aria-label="Añadir una unidad de <?= htmlspecialchars($articuloCarrito['Nombre']) ?>" <?= $articuloCarrito['Cantidad'] >= $articuloCarrito['Stock'] ? 'disabled' : '' ?>>+</button>
          </form>
          <strong>$<?= number_format($articuloCarrito['Subtotal'], 2, ',', '.') ?></strong>
        </div>
        <?php if (!$articuloCarrito['Disponible']) { ?>
          <p class="text-danger small mt-2 mb-0">Stock disponible: <?= $articuloCarrito['Stock'] ?>. Reducí la cantidad o quitá el producto para continuar.</p>
        <?php } ?>
      </div>
    <?php } ?>
  </div>
  <?php if (!empty($productosCarrito)) { ?>
    <div class="carrito-pie border-top">
      <div class="d-flex justify-content-between gap-3 mb-1">
        <span>Subtotal</span>
        <strong>$<?= number_format($totalCarrito, 2, ',', '.') ?></strong>
      </div>
      <p class="carrito-detalle mb-3">IVA incluido</p>
      <a class="btn btn-outline-secondary w-100 mb-2" href="catalogo.php">Seguir comprando</a>
      <?php if (empty($_SESSION['correo'])) { ?>
        <a class="btn btn-carrito" href="../LoginHTML.php">Iniciar sesión para comprar</a>
      <?php } else { ?>
        <form method="post">
          <input type="hidden" name="tokenCarrito" value="<?= htmlspecialchars($_SESSION['tokenCarrito']) ?>">
          <button class="btn btn-carrito" name="accionCarrito" value="comprar" <?= !$carritoDisponible ? 'disabled' : '' ?>>Finalizar compra</button>
        </form>
        <p class="carrito-detalle small mt-2 mb-0">Al finalizar se registra la venta y se genera tu factura.</p>
      <?php } ?>
    </div>
  <?php } ?>
</aside>
