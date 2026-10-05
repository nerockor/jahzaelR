<?php
/* [LOCK-MODULE: API-PIEZAS] - NO MODIFICAR ESTE BLOQUE A MENOS QUE EL USUARIO LO ESPECIFIQUE */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

// Verificación de autenticación estricta
if (!is_authenticated()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Acceso no autorizado al API de piezas.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

$input = [];
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        $input = json_decode($raw, true) ?? [];
    }
    $input = array_merge($_POST, $input);
    if (empty($action) && isset($input['action'])) {
        $action = $input['action'];
    }
}

try {
    $pdo = Database::getConnection();
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Fallo de conexión a base de datos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// -------------------------------------------------------------
// ENRUTADOR DE ACCIONES
// -------------------------------------------------------------

switch ($action) {
    case '':
    case 'listar':
        manejar_listar_piezas($pdo);
        break;

    case 'crear':
        manejar_crear_pieza($pdo, $input);
        break;

    case 'vender':
        manejar_vender_pieza($pdo, $input);
        break;

    case 'marcar_danada':
    case 'descartar':
        manejar_marcar_danada($pdo, $input);
        break;

    case 'cambiar_estado':
        manejar_cambiar_estado($pdo, $input);
        break;

    case 'eliminar':
        manejar_eliminar_pieza($pdo, $input);
        break;

    case 'editar_precio':
        manejar_editar_precio($pdo, $input);
        break;

    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "Acción no válida: '{$action}'"], JSON_UNESCAPED_UNICODE);
        break;
}

// -------------------------------------------------------------
// CONTROLADORES LÓGICOS (CIRCUITO PRODUCCIÓN / VENTA)
// -------------------------------------------------------------

/**
 * Edita el precio de venta sugerido de una pieza en stock.
 */
function manejar_editar_precio(PDO $pdo, array $input): void
{
    $id = intval($input['id'] ?? 0);
    $precio = floatval($input['precio_venta_ars'] ?? 0.0);

    if ($id <= 0 || $precio < 0) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'ID de pieza o precio inválido.'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $stmt = $pdo->prepare('UPDATE piezas_stock SET precio_venta_ars = :precio WHERE id = :id');
    $stmt->execute([':precio' => $precio, ':id' => $id]);

    echo json_encode(['status' => 'success', 'message' => 'Precio actualizado.'], JSON_UNESCAPED_UNICODE);
}

/**
 * Retorna las piezas en stock con datos del filamento utilizado.
 */
function manejar_listar_piezas(PDO $pdo): void
{
    $estado_filtro = trim((string)($_GET['estado'] ?? ''));

    $sql = '
        SELECT 
            p.id, p.nombre, p.filamento_id, p.peso_g, p.horas_impresion,
            p.costo_produccion_ars, p.precio_venta_ars, p.estado, p.creado_en,
            f.marca AS filamento_marca, f.material AS filamento_material, f.color AS filamento_color,
            v.id AS venta_id, v.precio_unitario_ars AS venta_precio_unitario,
            v.costo_unitario_ars AS venta_costo_unitario, v.ganancia_neta_ars, v.fecha_venta
        FROM piezas_stock p
        INNER JOIN filamentos f ON p.filamento_id = f.id
        LEFT JOIN ventas v ON p.id = v.pieza_id
    ';

    $params = [];
    if ($estado_filtro !== '' && in_array($estado_filtro, ['en_produccion', 'en_stock', 'vendida', 'dañada', 'archivada'], true)) {
        $sql .= ' WHERE p.estado = :estado';
        $params[':estado'] = $estado_filtro;
    } else {
        $sql .= ' WHERE p.estado != "archivada"';
    }

    $sql .= ' ORDER BY p.id DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $piezas = $stmt->fetchAll();

    $formateadas = array_map(function ($item) {
        $item['id'] = (int)$item['id'];
        $item['filamento_id'] = (int)$item['filamento_id'];
        $item['peso_g'] = (int)$item['peso_g'];
        $item['horas_impresion'] = (float)$item['horas_impresion'];
        $item['costo_produccion_ars'] = (float)$item['costo_produccion_ars'];
        $item['precio_venta_ars'] = (float)$item['precio_venta_ars'];
        $item['ganancia_estimada_ars'] = round($item['precio_venta_ars'] - $item['costo_produccion_ars'], 2);

        if (!empty($item['venta_id'])) {
            $item['venta_id'] = (int)$item['venta_id'];
            $item['venta_precio_unitario'] = (float)$item['venta_precio_unitario'];
            $item['venta_costo_unitario'] = (float)$item['venta_costo_unitario'];
            $item['ganancia_neta_ars'] = (float)$item['ganancia_neta_ars'];
        }
        return $item;
    }, $piezas);

    echo json_encode(['status' => 'success', 'data' => $formateadas], JSON_UNESCAPED_UNICODE);
}

/**
 * Da de alta una nueva pieza vinculada al filamento y descuenta gramos de forma atómica si se solicita.
 */
function manejar_crear_pieza(PDO $pdo, array $input): void
{
    $nombre = trim((string)($input['nombre'] ?? ''));
    $filamento_id = intval($input['filamento_id'] ?? 0);
    $peso_g = intval($input['peso_g'] ?? 0);
    $horas_impresion = floatval($input['horas_impresion'] ?? 0.0);
    $costo_produccion_ars = floatval($input['costo_produccion_ars'] ?? 0.0);
    $precio_venta_ars = floatval($input['precio_venta_ars'] ?? 0.0);
    $descontar_bobina = !empty($input['descontar_bobina']); // bool

    if ($nombre === '' || $filamento_id <= 0 || $peso_g <= 0 || $horas_impresion <= 0 || $costo_produccion_ars <= 0 || $precio_venta_ars < 0) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos de fabricación son obligatorios y deben ser mayores a cero (el precio de venta puede ser cero al crearse).'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $pdo->beginTransaction();

    try {
        $forUpdate = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? '' : ' FOR UPDATE';
        // Verificar existencia y stock del filamento
        $stmtFil = $pdo->prepare('SELECT id, peso_actual_g, marca, material FROM filamentos WHERE id = :id AND activo = 1' . $forUpdate);
        $stmtFil->execute([':id' => $filamento_id]);
        $bobina = $stmtFil->fetch();

        if (!$bobina) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Bobina de filamento no encontrada o inactiva.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Si se solicita descuento automático de la bobina al dar de alta la pieza
        if ($descontar_bobina) {
            $peso_anterior = (int)$bobina['peso_actual_g'];
            $peso_nuevo = $peso_anterior - $peso_g;

            if ($peso_nuevo < 0) {
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Stock insuficiente en bobina {$bobina['marca']}: disponibles {$peso_anterior}g, requeridos {$peso_g}g."
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $updFil = $pdo->prepare('UPDATE filamentos SET peso_actual_g = :nuevo WHERE id = :id');
            $updFil->execute([':nuevo' => $peso_nuevo, ':id' => $filamento_id]);

            // Auditoría Append-Only
            try {
                $audit = $pdo->prepare('INSERT INTO movimientos_filamento (filamento_id, tipo, gramos, peso_anterior_g, peso_nuevo_g, nota) VALUES (:id, "descuento_impresion", :gramos, :ant, :nuevo, :nota)');
                $audit->execute([
                    ':id' => $filamento_id,
                    ':gramos' => $peso_g,
                    ':ant' => $peso_anterior,
                    ':nuevo' => $peso_nuevo,
                    ':nota' => "Alta de pieza producida: {$nombre}"
                ]);
            } catch (\Throwable $eAudit) {}
        }

        $estado_pieza = in_array(trim((string)($input['estado'] ?? '')), ['en_produccion', 'en_stock', 'vendida', 'dañada']) ? trim((string)$input['estado']) : 'en_stock';

        // Insertar pieza en inventario
        $sqlPieza = 'INSERT INTO piezas_stock (nombre, filamento_id, peso_g, horas_impresion, costo_produccion_ars, precio_venta_ars, estado)
                     VALUES (:nombre, :filamento_id, :peso_g, :horas, :costo, :precio, :estado)';
        $stmtPieza = $pdo->prepare($sqlPieza);
        $stmtPieza->execute([
            ':nombre' => $nombre,
            ':filamento_id' => $filamento_id,
            ':peso_g' => $peso_g,
            ':horas' => $horas_impresion,
            ':costo' => $costo_produccion_ars,
            ':precio' => $precio_venta_ars,
            ':estado' => $estado_pieza
        ]);

        $pieza_id = (int)$pdo->lastInsertId();

        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => "Pieza '{$nombre}' ingresada a stock con éxito.",
            'pieza_id' => $pieza_id
        ], JSON_UNESCAPED_UNICODE);

    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[API-PIEZAS-CREAR-ERR] ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al crear la pieza: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * Simula y ejecuta la venta de una pieza mediante transacción ACID.
 * Inserta el registro en la tabla de auditoría contable Append-Only `ventas`
 * manteniendo intactos el costo unitario y precio de venta.
 * Control de Parada: Comprobar que se cree el registro en `ventas` con costo y precio intactos.
 */
function manejar_vender_pieza(PDO $pdo, array $input): void
{
    $pieza_id = intval($input['pieza_id'] ?? ($input['id'] ?? 0));
    $cantidad = max(1, intval($input['cantidad'] ?? 1));

    if ($pieza_id <= 0) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'ID de pieza inválido.'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $pdo->beginTransaction();

    try {
        $forUpdate = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? '' : ' FOR UPDATE';
        // Bloquear registro pesimista de la pieza
        $stmt = $pdo->prepare('SELECT id, nombre, filamento_id, peso_g, horas_impresion, costo_produccion_ars, precio_venta_ars, estado FROM piezas_stock WHERE id = :id' . $forUpdate);
        $stmt->execute([':id' => $pieza_id]);
        $pieza = $stmt->fetch();

        if (!$pieza) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Pieza no encontrada.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($pieza['estado'] === 'vendida') {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Esta pieza ya fue registrada como vendida.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($pieza['estado'] === 'dañada') {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No se puede vender una pieza marcada como dañada / scrap.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Determinar cantidad total actual en el lote
        $cantidad_total = 1;
        $nombre_base = $pieza['nombre'];
        if (preg_match('/^(.*?)\s*\(x(\d+)\)$/', $pieza['nombre'], $matches)) {
            $nombre_base = trim($matches[1]);
            $cantidad_total = max(1, (int)$matches[2]);
        }

        if ($cantidad > $cantidad_total) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => "Stock insuficiente en este lote. Tienes {$cantidad_total} unidades."], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Costo y Precio unitario
        $costo_unitario = (float)$pieza['costo_produccion_ars'] / $cantidad_total;
        $precio_sugerido_unitario = (float)$pieza['precio_venta_ars'] / $cantidad_total;
        
        $precio_unitario = isset($input['precio_unitario_ars']) && floatval($input['precio_unitario_ars']) > 0
            ? floatval($input['precio_unitario_ars'])
            : $precio_sugerido_unitario;

        if ($cantidad < $cantidad_total) {
            // Venta parcial: Dividir el lote
            $cantidad_restante = $cantidad_total - $cantidad;
            $peso_unitario = $pieza['peso_g'] / $cantidad_total;
            $horas_unitarias = $pieza['horas_impresion'] / $cantidad_total;

            // 1. Actualizar el lote original para que quede con la cantidad restante
            $nuevo_nombre_restante = $cantidad_restante > 1 ? "{$nombre_base} (x{$cantidad_restante})" : $nombre_base;
            $upd = $pdo->prepare('UPDATE piezas_stock SET 
                nombre = :n, 
                peso_g = :p, 
                horas_impresion = :h, 
                costo_produccion_ars = :c, 
                precio_venta_ars = :pv 
                WHERE id = :id');
            $upd->execute([
                ':n' => $nuevo_nombre_restante,
                ':p' => round($peso_unitario * $cantidad_restante),
                ':h' => round($horas_unitarias * $cantidad_restante, 2),
                ':c' => $costo_unitario * $cantidad_restante,
                ':pv' => $precio_sugerido_unitario * $cantidad_restante,
                ':id' => $pieza_id
            ]);

            // 2. Crear una nueva pieza para representar la fracción vendida
            $nuevo_nombre_vendido = $cantidad > 1 ? "{$nombre_base} (x{$cantidad})" : $nombre_base;
            $ins = $pdo->prepare('INSERT INTO piezas_stock (nombre, filamento_id, peso_g, horas_impresion, costo_produccion_ars, precio_venta_ars, estado) 
                                  VALUES (:n, :fid, :p, :h, :c, :pv, "vendida")');
            $ins->execute([
                ':n' => $nuevo_nombre_vendido,
                ':fid' => $pieza['filamento_id'],
                ':p' => round($peso_unitario * $cantidad),
                ':h' => round($horas_unitarias * $cantidad, 2),
                ':c' => $costo_unitario * $cantidad,
                ':pv' => $precio_sugerido_unitario * $cantidad
            ]);
            $pieza_vendida_id = (int)$pdo->lastInsertId();
        } else {
            // Venta total del lote
            $upd = $pdo->prepare('UPDATE piezas_stock SET estado = "vendida" WHERE id = :id');
            $upd->execute([':id' => $pieza_id]);
            $pieza_vendida_id = $pieza_id;
        }

        // 3. Insertar en tabla de ventas (Append-Only) apuntando al registro vendido
        $comision_pct = max(0, floatval($input['comision_pct'] ?? 0));
        $costo_fijo = max(0, floatval($input['costo_fijo'] ?? 0));
        
        $ingreso_bruto = $precio_unitario * $cantidad;
        $comision_descuento = $ingreso_bruto * ($comision_pct / 100);
        $costo_produccion = $costo_unitario * $cantidad;
        
        $ganancia_neta = round($ingreso_bruto - $comision_descuento - $costo_fijo - $costo_produccion, 2);

        $sqlVenta = 'INSERT INTO ventas (pieza_id, cantidad, precio_unitario_ars, costo_unitario_ars, ganancia_neta_ars)
                     VALUES (:pieza_id, :cantidad, :precio, :costo, :ganancia)';
        $stmtVenta = $pdo->prepare($sqlVenta);
        $stmtVenta->execute([
            ':pieza_id' => $pieza_vendida_id,
            ':cantidad' => $cantidad,
            ':precio' => $precio_unitario,
            ':costo' => $costo_unitario,
            ':ganancia' => $ganancia_neta
        ]);

        $venta_id = (int)$pdo->lastInsertId();

        // 3. Confirmar transacción ACID
        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => "Venta de '{$pieza['nombre']}' registrada exitosamente.",
            'venta_id' => $venta_id,
            'pieza_id' => $pieza_id,
            'cantidad' => $cantidad,
            'costo_unitario_ars' => $costo_unitario,
            'precio_unitario_ars' => $precio_unitario,
            'ganancia_neta_ars' => $ganancia_neta,
            'estado_pieza' => 'vendida'
        ], JSON_UNESCAPED_UNICODE);

    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[API-PIEZAS-VENTA-ERR] ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error transaccional al registrar la venta: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * Marca una pieza como dañada / scrap (desperdicio).
 */
function manejar_marcar_danada(PDO $pdo, array $input): void
{
    $pieza_id = intval($input['pieza_id'] ?? ($input['id'] ?? 0));
    $motivo = trim((string)($input['motivo'] ?? 'Falla de impresión'));
    $cantidad = intval($input['cantidad'] ?? 1);

    if ($pieza_id <= 0 || $cantidad < 1) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Parámetros inválidos.'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $pdo->beginTransaction();

    try {
        $forUpdate = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? '' : ' FOR UPDATE';
        $stmt = $pdo->prepare('SELECT id, nombre, filamento_id, peso_g, horas_impresion, costo_produccion_ars, precio_venta_ars, estado FROM piezas_stock WHERE id = :id' . $forUpdate);
        $stmt->execute([':id' => $pieza_id]);
        $pieza = $stmt->fetch();

        if (!$pieza) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Pieza no encontrada.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($pieza['estado'] === 'vendida') {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No se puede marcar como descarte una pieza ya vendida.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $cantidad_total = 1;
        $nombre_base = $pieza['nombre'];
        if (preg_match('/^(.*?)\s*\(x(\d+)\)$/', $pieza['nombre'], $matches)) {
            $nombre_base = trim($matches[1]);
            $cantidad_total = max(1, (int)$matches[2]);
        }

        if ($cantidad > $cantidad_total) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => "Stock insuficiente en este lote. Tienes {$cantidad_total} unidades."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $peso_unitario = $pieza['peso_g'] / $cantidad_total;
        $horas_unitarias = $pieza['horas_impresion'] / $cantidad_total;
        $costo_unitario = (float)$pieza['costo_produccion_ars'] / $cantidad_total;
        $precio_sugerido_unitario = (float)$pieza['precio_venta_ars'] / $cantidad_total;

        if ($cantidad < $cantidad_total) {
            $cantidad_restante = $cantidad_total - $cantidad;
            $nuevo_nombre_restante = $cantidad_restante > 1 ? "{$nombre_base} (x{$cantidad_restante})" : $nombre_base;
            $upd = $pdo->prepare('UPDATE piezas_stock SET nombre = :n, peso_g = :p, horas_impresion = :h, costo_produccion_ars = :c, precio_venta_ars = :pv WHERE id = :id');
            $upd->execute([
                ':n' => $nuevo_nombre_restante,
                ':p' => round($peso_unitario * $cantidad_restante),
                ':h' => round($horas_unitarias * $cantidad_restante, 2),
                ':c' => $costo_unitario * $cantidad_restante,
                ':pv' => $precio_sugerido_unitario * $cantidad_restante,
                ':id' => $pieza_id
            ]);

            $nuevo_nombre_scrap = $cantidad > 1 ? "{$nombre_base} (x{$cantidad})" : $nombre_base;
            $ins = $pdo->prepare('INSERT INTO piezas_stock (nombre, filamento_id, peso_g, horas_impresion, costo_produccion_ars, precio_venta_ars, estado) VALUES (:n, :fid, :p, :h, :c, :pv, "dañada")');
            $ins->execute([
                ':n' => $nuevo_nombre_scrap,
                ':fid' => $pieza['filamento_id'],
                ':p' => round($peso_unitario * $cantidad),
                ':h' => round($horas_unitarias * $cantidad, 2),
                ':c' => $costo_unitario * $cantidad,
                ':pv' => $precio_sugerido_unitario * $cantidad
            ]);
            $pieza_scrap_id = (int)$pdo->lastInsertId();
        } else {
            $upd = $pdo->prepare('UPDATE piezas_stock SET estado = "dañada" WHERE id = :id');
            $upd->execute([':id' => $pieza_id]);
            $pieza_scrap_id = $pieza_id;
        }

        try {
            $audit = $pdo->prepare('INSERT INTO movimientos_filamento (filamento_id, tipo, gramos, peso_anterior_g, peso_nuevo_g, nota) VALUES (:fil, "scrap", :g, 0, 0, :nota)');
            $audit->execute([
                ':fil' => $pieza['filamento_id'],
                ':g' => round($peso_unitario * $cantidad),
                ':nota' => "Scrap pieza #{$pieza_scrap_id}: {$motivo}"
            ]);
        } catch (\Throwable $eAudit) {}

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Scrap registrado.']);
    } catch (\Throwable $e) {
        $pdo->rollBack();
        error_log("[API-PIEZAS-SCRAP] " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al marcar scrap.']);
    }
}



/**
 * Cambia el estado de una pieza (ej. de en_produccion a en_stock).
 */
function manejar_cambiar_estado(PDO $pdo, array $input): void
{
    $id = intval($input['id'] ?? 0);
    $nuevo_estado = trim($input['estado'] ?? '');

    if ($id <= 0 || !in_array($nuevo_estado, ['en_produccion', 'en_stock', 'vendida', 'dañada'], true)) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Estado inválido.'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $stmt = $pdo->prepare('UPDATE piezas_stock SET estado = :est WHERE id = :id');
    $stmt->execute([':est' => $nuevo_estado, ':id' => $id]);

    echo json_encode(['status' => 'success', 'message' => 'Estado actualizado.'], JSON_UNESCAPED_UNICODE);
}

/**
 * Archiva/Oculta una pieza definitivamente del panel principal (Soft Delete).
 */
function manejar_eliminar_pieza(PDO $pdo, array $input): void
{
    $id = intval($input['id'] ?? 0);

    if ($id <= 0) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'ID de pieza inválido.'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $stmt = $pdo->prepare('UPDATE piezas_stock SET estado = "archivada" WHERE id = :id');
    $stmt->execute([':id' => $id]);

    echo json_encode(['status' => 'success', 'message' => 'Pieza archivada exitosamente.'], JSON_UNESCAPED_UNICODE);
}
