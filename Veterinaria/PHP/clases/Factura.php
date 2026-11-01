<?php
require_once __DIR__ . '/Conexion.php';
require_once __DIR__ . '/Producto.php';

class Factura
{
    /* reglas de negocio fijas: no se dejan tocar desde el formulario */
    public const IVA_GENERAL = 22.0;
    public const DIAS_VENCIMIENTO = 30;

    private ?int $id;
    private ?string $nombreVeterinaria;
    private ?DateTime $fecha;
    private ?string $rut;
    private ?DateTime $fechaVencimiento;

    // cargo los datos iniciales en las propiedades de este objeto
    public function __construct(?int $id = null, ?string $nombreVeterinaria = null, ?DateTime $fecha = null, ?string $rut = null, ?DateTime $fechaVencimiento = null)
    {
        $this->id = $id;
        $this->nombreVeterinaria = $nombreVeterinaria;
        $this->fecha = $fecha;
        $this->rut = $rut;
        $this->fechaVencimiento = $fechaVencimiento;
    }

    // devuelvo el valor de id que tiene el objeto
    public function getId(): int
    {
        return $this->id ?? 0;
    }

    // devuelvo el valor de nombre veterinaria que tiene el objeto
    public function getNombreVeterinaria(): string
    {
        return $this->nombreVeterinaria ?? '';
    }

    // devuelvo el valor de fecha que tiene el objeto
    public function getFecha(): DateTime
    {
        return $this->fecha ?? new DateTime();
    }

    // devuelvo el valor de rut que tiene el objeto
    public function getRut(): string
    {
        return $this->rut ?? '';
    }

    // devuelvo el valor de fecha vencimiento que tiene el objeto
    public function getFechaVencimiento(): DateTime
    {
        return $this->fechaVencimiento ?? new DateTime();
    }

    /* listado para la tabla de facturas: cliente sale de COMPRA (CI repetido por cada linea) y el total se calcula sumando las lineas con su IVA */
    public static function listadoFactura(string $busqueda = ''): array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "SELECT f.IdFactura, f.NombreVeterinaria, f.Fecha, f.Rut, f.FechaVencimiento,
                    (SELECT u.CI FROM COMPRA cp INNER JOIN USUARIO u ON cp.CI = u.CI WHERE cp.IdFactura = f.IdFactura LIMIT 1) AS ClienteCI,
                    (SELECT u.Nombre FROM COMPRA cp INNER JOIN USUARIO u ON cp.CI = u.CI WHERE cp.IdFactura = f.IdFactura LIMIT 1) AS ClienteNombre,
                    (SELECT u.Apellido FROM COMPRA cp INNER JOIN USUARIO u ON cp.CI = u.CI WHERE cp.IdFactura = f.IdFactura LIMIT 1) AS ClienteApellido,
                    (SELECT COALESCE(SUM(lf.Subtotal * (1 + lf.IVA / 100)), 0) FROM LINEA_FACTURA lf WHERE lf.IdFactura = f.IdFactura) AS Total
                FROM FACTURA f
                WHERE 1=1";

            $params = [];

            if ($busqueda !== '') {
                $sql .= " HAVING f.IdFactura LIKE :busqueda1 OR ClienteNombre LIKE :busqueda2 OR ClienteApellido LIKE :busqueda3";
                $like = '%' . $busqueda . '%';
                $params = [
                    ':busqueda1' => $like,
                    ':busqueda2' => $like,
                    ':busqueda3' => $like,
                ];
            }

            $sql .= " ORDER BY f.IdFactura DESC";

            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute($params);
            // traigo todas las filas usando los nombres de las columnas como claves
            $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['facturas' => $facturas];
        } catch (PDOException $e) {
            return ['facturas' => [], 'error' => $e->getMessage()];
        }
    }

    // busco un registro de factura por su id; si no lo encuentro devuelvo null
    public static function buscarPorId(int $id): ?array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "SELECT f.IdFactura, f.NombreVeterinaria, f.Fecha, f.Rut, f.FechaVencimiento,
                    (SELECT u.CI FROM COMPRA cp INNER JOIN USUARIO u ON cp.CI = u.CI WHERE cp.IdFactura = f.IdFactura LIMIT 1) AS ClienteCI,
                    (SELECT u.Nombre FROM COMPRA cp INNER JOIN USUARIO u ON cp.CI = u.CI WHERE cp.IdFactura = f.IdFactura LIMIT 1) AS ClienteNombre,
                    (SELECT u.Apellido FROM COMPRA cp INNER JOIN USUARIO u ON cp.CI = u.CI WHERE cp.IdFactura = f.IdFactura LIMIT 1) AS ClienteApellido
                FROM FACTURA f
                WHERE f.IdFactura = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id' => $id]);
            // traigo una sola fila con los nombres de sus columnas como claves
            $factura = $stmt->fetch(PDO::FETCH_ASSOC);

            return $factura ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /* lineas de una factura con el producto que les corresponde via ARTICULO_FACTURA */
    public static function listadoLineas(int $idFactura): array
    {
        try {
            $conexion = (new Conexion())->establecerConexion();

            $sql = "SELECT lf.IdLineaFactura, lf.Cantidad, lf.IVA, lf.Subtotal,
                    p.IdProducto, p.Nombre AS ProductoNombre,
                    ROUND(lf.Subtotal / lf.Cantidad, 2) AS PrecioUnitario,
                    ROUND(lf.Subtotal * (1 + lf.IVA / 100), 2) AS SubtotalConIva
                FROM LINEA_FACTURA lf
                INNER JOIN ARTICULO_FACTURA af ON af.IdLineaFactura = lf.IdLineaFactura AND af.IdFactura = lf.IdFactura
                INNER JOIN PRODUCTO p ON p.IdProducto = af.IdProducto
                WHERE lf.IdFactura = :id
                ORDER BY lf.IdLineaFactura";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id' => $idFactura]);

            // traigo todas las filas usando los nombres de las columnas como claves
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // guardo la factura y sus lineas, y descuento el stock de los productos vendidos
    public function crearFactura(?int $ci, string $rut, array $lineas, string $nombreVeterinaria = 'Clínica Veterinaria Sienra'): int
    {
        if (empty($lineas)) {
            throw new Exception('La factura necesita al menos una línea.');
        }

        $conexion = (new Conexion())->establecerConexion();

        try {
            // agrupo los cambios para guardar todo junto o deshacerlo si algo falla
            $conexion->beginTransaction();

            // calculo el vencimiento sumando los dias definidos a la fecha actual
            $fechaVencimiento = date('Y-m-d', strtotime('+' . self::DIAS_VENCIMIENTO . ' days'));

            $sqlFactura = "INSERT INTO FACTURA (NombreVeterinaria, Fecha, Rut, FechaVencimiento)
                VALUES (:nombreVeterinaria, :fecha, :rut, :fechaVencimiento)";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtFactura = $conexion->prepare($sqlFactura);
            $stmtFactura->execute([
                ':nombreVeterinaria' => $nombreVeterinaria,
                ':fecha' => date('Y-m-d'),
                ':rut' => $rut,
                ':fechaVencimiento' => $fechaVencimiento,
            ]);
            // recupero el id que la base le dio a la factura recien creada
            $idFactura = (int)$conexion->lastInsertId();

            foreach ($lineas as $linea) {
            // distingo productos y servicios con el tipo de cada linea
            $tipo = $linea['tipo'] ?? 'p';
            $idItem = (int)$linea['id'];
            $cantidad = (int)$linea['cantidad'];

            if ($cantidad <= 0) {
                throw new Exception('La cantidad de cada línea debe ser mayor a 0.');
            }

            if ($tipo === 's') {
                $sqlServicio = "SELECT Precio, Nombre FROM SERVICIO WHERE IdServicio = :id";
                // preparo la consulta para pasar los valores por separado al ejecutarla
                $stmtServicio = $conexion->prepare($sqlServicio);
                $stmtServicio->execute([':id' => $idItem]);
                // traigo una sola fila con los nombres de sus columnas como claves
                $servicio = $stmtServicio->fetch(PDO::FETCH_ASSOC);

                if (!$servicio) {
                    throw new Exception("El servicio #$idItem no existe.");
                }

                $precioConIva = (float)$servicio['Precio'];
                $totalLinea = $precioConIva * $cantidad;
                // quito el IVA incluido en el precio y redondeo el neto a dos decimales
                $subtotalNeto = round($totalLinea / (1 + (self::IVA_GENERAL / 100)), 2);

                $sqlLinea = "INSERT INTO LINEA_FACTURA (IdFactura, Cantidad, IVA, Subtotal) VALUES (:idFactura, :cantidad, :iva, :subtotal)";
                // preparo la consulta para pasar los valores por separado al ejecutarla
                $stmtLinea = $conexion->prepare($sqlLinea);
                $stmtLinea->execute([
                    ':idFactura' => $idFactura,
                    ':cantidad' => $cantidad,
                    ':iva' => self::IVA_GENERAL,
                    ':subtotal' => $subtotalNeto,
                ]);
            } else {
                // bloqueo este producto durante la venta para que otra compra espere a que termine
                $sqlProducto = "SELECT Precio, Stock, Nombre FROM PRODUCTO WHERE IdProducto = :id FOR UPDATE";
                // preparo la consulta para pasar los valores por separado al ejecutarla
                $stmtProducto = $conexion->prepare($sqlProducto);
                $stmtProducto->execute([':id' => $idItem]);
                // traigo una sola fila con los nombres de sus columnas como claves
                $producto = $stmtProducto->fetch(PDO::FETCH_ASSOC);

                if (!$producto) {
                    throw new Exception("El producto #$idItem no existe.");
                }
                // no dejo vender mas unidades de las que hay disponibles
                if ($producto['Stock'] < $cantidad) {
                    throw new Exception("Stock insuficiente de \"{$producto['Nombre']}\" (quedan {$producto['Stock']}).");
                }

                $precioConIva = (float)$producto['Precio'];
                $totalLinea = $precioConIva * $cantidad;
                // quito el IVA incluido en el precio y redondeo el neto a dos decimales
                $subtotalNeto = round($totalLinea / (1 + (self::IVA_GENERAL / 100)), 2);

                $sqlLinea = "INSERT INTO LINEA_FACTURA (IdFactura, Cantidad, IVA, Subtotal) VALUES (:idFactura, :cantidad, :iva, :subtotal)";
                // preparo la consulta para pasar los valores por separado al ejecutarla
                $stmtLinea = $conexion->prepare($sqlLinea);
                $stmtLinea->execute([
                    ':idFactura' => $idFactura,
                    ':cantidad' => $cantidad,
                    ':iva' => self::IVA_GENERAL,
                    ':subtotal' => $subtotalNeto,
                ]);
                // recupero el id de la linea para relacionarla con el producto y la compra
                $idLineaFactura = (int)$conexion->lastInsertId();

                // relaciono el producto con su linea dentro de esta factura
                $sqlArticulo = "INSERT INTO ARTICULO_FACTURA (IdFactura, IdLineaFactura, IdProducto) VALUES (:idFactura, :idLineaFactura, :idProducto)";
                // preparo la consulta para pasar los valores por separado al ejecutarla
                $stmtArticulo = $conexion->prepare($sqlArticulo);
                $stmtArticulo->execute([
                    ':idFactura' => $idFactura,
                    ':idLineaFactura' => $idLineaFactura,
                    ':idProducto' => $idItem,
                ]);

                // solo registro al comprador si se recibio su cedula
                if ($ci !== null) {
                    $sqlCompra = "INSERT INTO COMPRA (CI, IdProducto, IdFactura, IdLineaFactura) VALUES (:ci, :idProducto, :idFactura, :idLineaFactura)";
                    // preparo la consulta para pasar los valores por separado al ejecutarla
                    $stmtCompra = $conexion->prepare($sqlCompra);
                    $stmtCompra->execute([
                        ':ci' => $ci,
                        ':idProducto' => $idItem,
                        ':idFactura' => $idFactura,
                        ':idLineaFactura' => $idLineaFactura,
                    ]);
                }

                // descuento del stock la cantidad vendida de este producto
                $sqlStock = "UPDATE PRODUCTO SET Stock = Stock - :cantidad WHERE IdProducto = :id";
                // preparo la consulta para pasar los valores por separado al ejecutarla
                $stmtStock = $conexion->prepare($sqlStock);
                $stmtStock->execute([':cantidad' => $cantidad, ':id' => $idItem]);
            }
        }

            // confirmo todos los cambios porque las operaciones anteriores terminaron bien
            $conexion->commit();
            return $idFactura;
        } catch (Exception $e) {
            if ($conexion->inTransaction()) {
                // deshago los cambios de esta operacion si la transaccion sigue abierta
                $conexion->rollBack();
            }
            throw $e;
        }
    }

    // busco la factura y preparo el correo para mandarselo al cliente
    public function enviarPorCorreo(int $idFactura, string $correo): bool
    {
        try {
            // cargo los datos generales y las lineas antes de preparar el correo
            $factura = self::buscarPorId($idFactura);
            $lineas = self::listadoLineas($idFactura);
            if (!$factura || empty($lineas) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Faltan los datos de la factura o un correo válido.');
            }

            // compruebo que exista la configuracion y cargo las clases de PHPMailer
            $configuracion = __DIR__ . '/../config/correo.php';
            if (!is_file($configuracion)) {
                throw new Exception('Falta la configuración del correo.');
            }
            require $configuracion;
            require_once __DIR__ . '/../PHPMailer/Exception.php';
            require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
            require_once __DIR__ . '/../PHPMailer/SMTP.php';

            // completo el numero con ceros y preparo el nombre y las fechas para el correo
            $numero = str_pad((string) $idFactura, 4, '0', STR_PAD_LEFT);
            $nombreCliente = trim($factura['ClienteNombre'] . ' ' . $factura['ClienteApellido']);
            $fecha = date('d/m/Y', strtotime($factura['Fecha']));
            $vencimiento = date('d/m/Y', strtotime($factura['FechaVencimiento']));
            // armo la version HTML del correo; htmlspecialchars hace que los datos se lean como texto
            $cuerpo = '<html lang="es"><body style="font-family:Arial,sans-serif;color:#1F2733">'
                . '<h1 style="font-size:22px">' . htmlspecialchars($factura['NombreVeterinaria']) . '</h1>'
                . '<h2 style="font-size:18px">Factura N.º ' . $numero . '</h2>'
                . '<p>RUT: ' . htmlspecialchars($factura['Rut']) . '<br>Fecha: ' . $fecha . '<br>Vencimiento: ' . $vencimiento . '</p>'
                . '<p>Cliente: ' . htmlspecialchars($nombreCliente) . '<br>C.I.: ' . htmlspecialchars((string) $factura['ClienteCI']) . '</p>'
                . '<table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;width:100%;max-width:650px;border-color:#DDE2E8">'
                . '<thead><tr><th>Producto</th><th>Cantidad</th><th>Precio unitario (IVA incluido)</th><th>Importe</th></tr></thead><tbody>';
            // preparo tambien una version sin etiquetas HTML
            $texto = $factura['NombreVeterinaria'] . "\nFactura N.º " . $numero
                . "\nRUT: " . $factura['Rut'] . "\nFecha: " . $fecha . "\nVencimiento: " . $vencimiento
                . "\nCliente: " . $nombreCliente . "\nC.I.: " . $factura['ClienteCI'] . "\n\n";
            // empiezo en cero las sumas del neto y del total con IVA
            $neto = 0;
            $total = 0;
            foreach ($lineas as $linea) {
                // los importes salen de la factura guardada, no del precio actual del catálogo.
                $importe = (float) $linea['SubtotalConIva'];
                // divido el importe entre las unidades para obtener el precio de una sola
                $unitario = $importe / (int) $linea['Cantidad'];
                // acumulo el subtotal sin IVA y el importe con IVA de cada linea
                $neto += (float) $linea['Subtotal'];
                $total += $importe;
                // agrego una fila al correo: tr es la fila, td cada celda y .= agrega texto al que ya habia
                $cuerpo .= '<tr><td>' . htmlspecialchars($linea['ProductoNombre']) . '</td><td align="right">' . (int) $linea['Cantidad']
                    . '</td><td align="right">$ ' . number_format($unitario, 2, ',', '.')
                    . '</td><td align="right">$ ' . number_format($importe, 2, ',', '.') . '</td></tr>';
                // en la version de texto escribo cantidad por precio; number_format deja dos decimales
                $texto .= $linea['ProductoNombre'] . ' — ' . (int) $linea['Cantidad'] . ' × $ ' . number_format($unitario, 2, ',', '.')
                    . ' = $ ' . number_format($importe, 2, ',', '.') . "\n";
            }
            // cierro la tabla y agrego los totales; la diferencia entre total y neto es el IVA
            $cuerpo .= '</tbody></table><p>Subtotal neto: $ ' . number_format($neto, 2, ',', '.')
                . '<br>IVA: $ ' . number_format($total - $neto, 2, ',', '.')
                . '<br><strong>Total: $ ' . number_format($total, 2, ',', '.') . '</strong></p></body></html>';
            $texto .= "\nSubtotal neto: $ " . number_format($neto, 2, ',', '.')
                . "\nIVA: $ " . number_format($total - $neto, 2, ',', '.') . "\nTotal: $ " . number_format($total, 2, ',', '.');

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            // uso el servidor de Gmail y los datos de la cuenta del archivo de configuracion
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $correoUsuario;
            $mail->Password = $correoContrasena;
            // el envio usa STARTTLS en el puerto 587 para cifrar la conexion
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->Timeout = 15;
            $mail->CharSet = 'UTF-8';
            // indico quien envia el correo y a que cliente se lo mando
            $mail->setFrom($correoUsuario, $correoNombre);
            $mail->addAddress($correo, $nombreCliente);
            $mail->isHTML(true);
            $mail->Subject = 'Factura N.º ' . $numero . ' - Clínica Veterinaria Sienra';
            // asigno las dos versiones del mensaje antes de enviarlo
            $mail->Body = $cuerpo;
            $mail->AltBody = $texto;
            // devuelvo si PHPMailer pudo completar el envio
            return $mail->send();
        } catch (\Throwable $e) {
            // dejo el fallo en el registro del servidor sin mostrar los datos del correo
            error_log('Factura N.º ' . $idFactura . ': no se pudo enviar el correo.');
            return false;
        }
    }

    // devuelvo el stock y borro primero los registros relacionados con la factura
    public function eliminarFactura(int $id): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            // agrupo los cambios para guardar todo junto o deshacerlo si algo falla
            $conexion->beginTransaction();

            $sqlProductosFactura = "SELECT af.IdProducto, lf.Cantidad
                FROM LINEA_FACTURA lf
                INNER JOIN ARTICULO_FACTURA af ON af.IdLineaFactura = lf.IdLineaFactura AND af.IdFactura = lf.IdFactura
                WHERE lf.IdFactura = :id";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmtProductosFactura = $conexion->prepare($sqlProductosFactura);
            $stmtProductosFactura->execute([':id' => $id]);

            // preparo la devolucion de las unidades que se habian vendido
            $sqlDevolverStock = $conexion->prepare("UPDATE PRODUCTO SET Stock = Stock + :cantidad WHERE IdProducto = :idProducto");
            // traigo todas las filas usando los nombres de las columnas como claves
            foreach ($stmtProductosFactura->fetchAll(PDO::FETCH_ASSOC) as $linea) {
                $sqlDevolverStock->execute([':cantidad' => $linea['Cantidad'], ':idProducto' => $linea['IdProducto']]);
            }

            // borro primero las relaciones y las lineas para poder borrar la factura
            foreach (['COMPRA', 'ARTICULO_FACTURA', 'LINEA_FACTURA'] as $tabla) {
                $stmt = $conexion->prepare("DELETE FROM $tabla WHERE IdFactura = :id");
                $stmt->execute([':id' => $id]);
            }

            $stmt = $conexion->prepare("DELETE FROM FACTURA WHERE IdFactura = :id");
            $stmt->execute([':id' => $id]);

            // confirmo todos los cambios porque las operaciones anteriores terminaron bien
            $conexion->commit();
            return true;
        } catch (PDOException $e) {
            if (isset($conexion) && $conexion->inTransaction()) {
                // deshago los cambios de esta operacion si la transaccion sigue abierta
                $conexion->rollBack();
            }
            return false;
        }
    }

    // cuento las facturas que tienen la fecha de hoy
    public static function contarFacturasHoy(): int
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            $sql = "SELECT COUNT(*) FROM FACTURA WHERE Fecha = :hoy";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':hoy' => date('Y-m-d')]);
            // leo un solo valor del resultado de la consulta
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /* suma el total (con IVA) de las facturas emitidas hoy */
    public static function totalVentasHoy(): float
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            $sql = "SELECT COALESCE(SUM(lf.Subtotal * (1 + lf.IVA / 100)), 0)
                FROM LINEA_FACTURA lf
                INNER JOIN FACTURA f ON f.IdFactura = lf.IdFactura
                WHERE f.Fecha = :hoy";
            // preparo la consulta para pasar los valores por separado al ejecutarla
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':hoy' => date('Y-m-d')]);
            // leo un solo valor del resultado de la consulta
            return (float)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0.0;
        }
    }
}
