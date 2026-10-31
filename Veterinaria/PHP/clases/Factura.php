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

    public function __construct(?int $id = null, ?string $nombreVeterinaria = null, ?DateTime $fecha = null, ?string $rut = null, ?DateTime $fechaVencimiento = null)
    {
        $this->id = $id;
        $this->nombreVeterinaria = $nombreVeterinaria;
        $this->fecha = $fecha;
        $this->rut = $rut;
        $this->fechaVencimiento = $fechaVencimiento;
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function getNombreVeterinaria(): string
    {
        return $this->nombreVeterinaria ?? '';
    }

    public function getFecha(): DateTime
    {
        return $this->fecha ?? new DateTime();
    }

    public function getRut(): string
    {
        return $this->rut ?? '';
    }

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

            $stmt = $conexion->prepare($sql);
            $stmt->execute($params);
            $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['facturas' => $facturas];
        } catch (PDOException $e) {
            return ['facturas' => [], 'error' => $e->getMessage()];
        }
    }

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
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id' => $id]);
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
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id' => $idFactura]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function crearFactura(?int $ci, string $rut, array $lineas, string $nombreVeterinaria = 'Clínica Veterinaria Sienra'): int
    {
        if (empty($lineas)) {
            throw new Exception('La factura necesita al menos una línea.');
        }

        $conexion = (new Conexion())->establecerConexion();

        try {
            $conexion->beginTransaction();

            $fechaVencimiento = date('Y-m-d', strtotime('+' . self::DIAS_VENCIMIENTO . ' days'));

            $sqlFactura = "INSERT INTO FACTURA (NombreVeterinaria, Fecha, Rut, FechaVencimiento)
                VALUES (:nombreVeterinaria, :fecha, :rut, :fechaVencimiento)";
            $stmtFactura = $conexion->prepare($sqlFactura);
            $stmtFactura->execute([
                ':nombreVeterinaria' => $nombreVeterinaria,
                ':fecha' => date('Y-m-d'),
                ':rut' => $rut,
                ':fechaVencimiento' => $fechaVencimiento,
            ]);
            $idFactura = (int)$conexion->lastInsertId();

            foreach ($lineas as $linea) {
            $tipo = $linea['tipo'] ?? 'p';
            $idItem = (int)$linea['id'];
            $cantidad = (int)$linea['cantidad'];

            if ($cantidad <= 0) {
                throw new Exception('La cantidad de cada línea debe ser mayor a 0.');
            }

            if ($tipo === 's') {
                $sqlServicio = "SELECT Precio, Nombre FROM SERVICIO WHERE IdServicio = :id";
                $stmtServicio = $conexion->prepare($sqlServicio);
                $stmtServicio->execute([':id' => $idItem]);
                $servicio = $stmtServicio->fetch(PDO::FETCH_ASSOC);

                if (!$servicio) {
                    throw new Exception("El servicio #$idItem no existe.");
                }

                $precioConIva = (float)$servicio['Precio'];
                $totalLinea = $precioConIva * $cantidad;
                $subtotalNeto = round($totalLinea / (1 + (self::IVA_GENERAL / 100)), 2);

                $sqlLinea = "INSERT INTO LINEA_FACTURA (IdFactura, Cantidad, IVA, Subtotal) VALUES (:idFactura, :cantidad, :iva, :subtotal)";
                $stmtLinea = $conexion->prepare($sqlLinea);
                $stmtLinea->execute([
                    ':idFactura' => $idFactura,
                    ':cantidad' => $cantidad,
                    ':iva' => self::IVA_GENERAL,
                    ':subtotal' => $subtotalNeto,
                ]);
            } else {
                $sqlProducto = "SELECT Precio, Stock, Nombre FROM PRODUCTO WHERE IdProducto = :id FOR UPDATE";
                $stmtProducto = $conexion->prepare($sqlProducto);
                $stmtProducto->execute([':id' => $idItem]);
                $producto = $stmtProducto->fetch(PDO::FETCH_ASSOC);

                if (!$producto) {
                    throw new Exception("El producto #$idItem no existe.");
                }
                if ($producto['Stock'] < $cantidad) {
                    throw new Exception("Stock insuficiente de \"{$producto['Nombre']}\" (quedan {$producto['Stock']}).");
                }

                $precioConIva = (float)$producto['Precio'];
                $totalLinea = $precioConIva * $cantidad;
                $subtotalNeto = round($totalLinea / (1 + (self::IVA_GENERAL / 100)), 2);

                $sqlLinea = "INSERT INTO LINEA_FACTURA (IdFactura, Cantidad, IVA, Subtotal) VALUES (:idFactura, :cantidad, :iva, :subtotal)";
                $stmtLinea = $conexion->prepare($sqlLinea);
                $stmtLinea->execute([
                    ':idFactura' => $idFactura,
                    ':cantidad' => $cantidad,
                    ':iva' => self::IVA_GENERAL,
                    ':subtotal' => $subtotalNeto,
                ]);
                $idLineaFactura = (int)$conexion->lastInsertId();

                $sqlArticulo = "INSERT INTO ARTICULO_FACTURA (IdFactura, IdLineaFactura, IdProducto) VALUES (:idFactura, :idLineaFactura, :idProducto)";
                $stmtArticulo = $conexion->prepare($sqlArticulo);
                $stmtArticulo->execute([
                    ':idFactura' => $idFactura,
                    ':idLineaFactura' => $idLineaFactura,
                    ':idProducto' => $idItem,
                ]);

                if ($ci !== null) {
                    $sqlCompra = "INSERT INTO COMPRA (CI, IdProducto, IdFactura, IdLineaFactura) VALUES (:ci, :idProducto, :idFactura, :idLineaFactura)";
                    $stmtCompra = $conexion->prepare($sqlCompra);
                    $stmtCompra->execute([
                        ':ci' => $ci,
                        ':idProducto' => $idItem,
                        ':idFactura' => $idFactura,
                        ':idLineaFactura' => $idLineaFactura,
                    ]);
                }

                $sqlStock = "UPDATE PRODUCTO SET Stock = Stock - :cantidad WHERE IdProducto = :id";
                $stmtStock = $conexion->prepare($sqlStock);
                $stmtStock->execute([':cantidad' => $cantidad, ':id' => $idItem]);
            }
        }

            $conexion->commit();
            return $idFactura;
        } catch (Exception $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            throw $e;
        }
    }

    public function enviarPorCorreo(int $idFactura, string $correo): bool
    {
        try {
            $factura = self::buscarPorId($idFactura);
            $lineas = self::listadoLineas($idFactura);
            if (!$factura || empty($lineas) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Faltan los datos de la factura o un correo válido.');
            }

            $configuracion = __DIR__ . '/../config/correo.php';
            if (!is_file($configuracion)) {
                throw new Exception('Falta la configuración del correo.');
            }
            require $configuracion;
            require_once __DIR__ . '/../PHPMailer/Exception.php';
            require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
            require_once __DIR__ . '/../PHPMailer/SMTP.php';

            $numero = str_pad((string) $idFactura, 4, '0', STR_PAD_LEFT);
            $nombreCliente = trim($factura['ClienteNombre'] . ' ' . $factura['ClienteApellido']);
            $fecha = date('d/m/Y', strtotime($factura['Fecha']));
            $vencimiento = date('d/m/Y', strtotime($factura['FechaVencimiento']));
            $cuerpo = '<html lang="es"><body style="font-family:Arial,sans-serif;color:#1F2733">'
                . '<h1 style="font-size:22px">' . htmlspecialchars($factura['NombreVeterinaria']) . '</h1>'
                . '<h2 style="font-size:18px">Factura N.º ' . $numero . '</h2>'
                . '<p>RUT: ' . htmlspecialchars($factura['Rut']) . '<br>Fecha: ' . $fecha . '<br>Vencimiento: ' . $vencimiento . '</p>'
                . '<p>Cliente: ' . htmlspecialchars($nombreCliente) . '<br>C.I.: ' . htmlspecialchars((string) $factura['ClienteCI']) . '</p>'
                . '<table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;width:100%;max-width:650px;border-color:#DDE2E8">'
                . '<thead><tr><th>Producto</th><th>Cantidad</th><th>Precio unitario (IVA incluido)</th><th>Importe</th></tr></thead><tbody>';
            $texto = $factura['NombreVeterinaria'] . "\nFactura N.º " . $numero
                . "\nRUT: " . $factura['Rut'] . "\nFecha: " . $fecha . "\nVencimiento: " . $vencimiento
                . "\nCliente: " . $nombreCliente . "\nC.I.: " . $factura['ClienteCI'] . "\n\n";
            $neto = 0;
            $total = 0;
            foreach ($lineas as $linea) {
                // Los importes salen de la factura guardada, no del precio actual del catálogo.
                $importe = (float) $linea['SubtotalConIva'];
                $unitario = $importe / (int) $linea['Cantidad'];
                $neto += (float) $linea['Subtotal'];
                $total += $importe;
                $cuerpo .= '<tr><td>' . htmlspecialchars($linea['ProductoNombre']) . '</td><td align="right">' . (int) $linea['Cantidad']
                    . '</td><td align="right">$ ' . number_format($unitario, 2, ',', '.')
                    . '</td><td align="right">$ ' . number_format($importe, 2, ',', '.') . '</td></tr>';
                $texto .= $linea['ProductoNombre'] . ' — ' . (int) $linea['Cantidad'] . ' × $ ' . number_format($unitario, 2, ',', '.')
                    . ' = $ ' . number_format($importe, 2, ',', '.') . "\n";
            }
            $cuerpo .= '</tbody></table><p>Subtotal neto: $ ' . number_format($neto, 2, ',', '.')
                . '<br>IVA: $ ' . number_format($total - $neto, 2, ',', '.')
                . '<br><strong>Total: $ ' . number_format($total, 2, ',', '.') . '</strong></p></body></html>';
            $texto .= "\nSubtotal neto: $ " . number_format($neto, 2, ',', '.')
                . "\nIVA: $ " . number_format($total - $neto, 2, ',', '.') . "\nTotal: $ " . number_format($total, 2, ',', '.');

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $correoUsuario;
            $mail->Password = $correoContrasena;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->Timeout = 15;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($correoUsuario, $correoNombre);
            $mail->addAddress($correo, $nombreCliente);
            $mail->isHTML(true);
            $mail->Subject = 'Factura N.º ' . $numero . ' - Clínica Veterinaria Sienra';
            $mail->Body = $cuerpo;
            $mail->AltBody = $texto;
            return $mail->send();
        } catch (\Throwable $e) {
            error_log('Factura N.º ' . $idFactura . ': no se pudo enviar el correo.');
            return false;
        }
    }

    public function eliminarFactura(int $id): bool
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            $conexion->beginTransaction();

            $sqlProductosFactura = "SELECT af.IdProducto, lf.Cantidad
                FROM LINEA_FACTURA lf
                INNER JOIN ARTICULO_FACTURA af ON af.IdLineaFactura = lf.IdLineaFactura AND af.IdFactura = lf.IdFactura
                WHERE lf.IdFactura = :id";
            $stmtProductosFactura = $conexion->prepare($sqlProductosFactura);
            $stmtProductosFactura->execute([':id' => $id]);

            $sqlDevolverStock = $conexion->prepare("UPDATE PRODUCTO SET Stock = Stock + :cantidad WHERE IdProducto = :idProducto");
            foreach ($stmtProductosFactura->fetchAll(PDO::FETCH_ASSOC) as $linea) {
                $sqlDevolverStock->execute([':cantidad' => $linea['Cantidad'], ':idProducto' => $linea['IdProducto']]);
            }

            foreach (['COMPRA', 'ARTICULO_FACTURA', 'LINEA_FACTURA'] as $tabla) {
                $stmt = $conexion->prepare("DELETE FROM $tabla WHERE IdFactura = :id");
                $stmt->execute([':id' => $id]);
            }

            $stmt = $conexion->prepare("DELETE FROM FACTURA WHERE IdFactura = :id");
            $stmt->execute([':id' => $id]);

            $conexion->commit();
            return true;
        } catch (PDOException $e) {
            if (isset($conexion) && $conexion->inTransaction()) {
                $conexion->rollBack();
            }
            return false;
        }
    }

    public static function contarFacturasHoy(): int
    {
        try {
            $conexion = (new Conexion())->establecerConexion();
            $sql = "SELECT COUNT(*) FROM FACTURA WHERE Fecha = :hoy";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':hoy' => date('Y-m-d')]);
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
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':hoy' => date('Y-m-d')]);
            return (float)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0.0;
        }
    }
}
