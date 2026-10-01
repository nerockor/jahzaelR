<?php
/* [LOCK-MODULE: API-FILAMENTOS] - NO MODIFICAR ESTE BLOQUE A MENOS QUE EL USUARIO LO ESPECIFIQUE */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

// Verificación de autenticación estricta
if (!is_authenticated()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Acceso no autorizado al API de filamentos.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// Procesar entrada JSON o POST tradicional
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
        'message' => 'Fallo al conectar con la base de datos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// -------------------------------------------------------------
// CONTROLADOR DE ACCIONES
// -------------------------------------------------------------

switch ($action) {
    case '':
    case 'listar':
        manejar_listar($pdo);
        break;

    case 'crear':
        manejar_crear($pdo, $input);
        break;

    case 'editar':
        manejar_editar($pdo, $input);
        break;

    case 'eliminar':
        manejar_eliminar($pdo, $input);
        break;

    case 'descontar':
        manejar_descontar($pdo, $input);
        break;

    case 'cargar-kilos':
        manejar_cargar_kilos($pdo, $input);
        break;

    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "Acción no válida: '{$action}'"], JSON_UNESCAPED_UNICODE);
        break;
}

// -------------------------------------------------------------
// FUNCIONES LÓGICAS DEL MÓDULO
// -------------------------------------------------------------

/**
 * Lista todas las bobinas activas con porcentaje y alerta de nivel crítico.
 */
function manejar_listar(PDO $pdo): void
{
    $stmt = $pdo->query('
        SELECT 
            id, marca, material, color, peso_inicial_g, peso_actual_g,
            costo_rollo_ars, costo_por_gramo_ars, alerta_stock_minimo_g, activo, creado_en
        FROM filamentos
        WHERE activo = 1
        ORDER BY material ASC, marca ASC, color ASC
    ');
    $filamentos = $stmt->fetchAll();

    // Fetch active prints (en_produccion)
    $stmtPrints = $pdo->query('
        SELECT id, filamento_id, nombre, peso_g, horas_impresion, costo_produccion_ars 
        FROM piezas_stock 
        WHERE estado = "en_produccion"
    ');
    $activePrints = $stmtPrints->fetchAll();
    
    $printsByFilament = [];
    foreach ($activePrints as $print) {
        $fId = (int)$print['filamento_id'];
        if (!isset($printsByFilament[$fId])) {
            $printsByFilament[$fId] = [];
        }
        $printsByFilament[$fId][] = $print;
    }

    // Enriquecer datos con porcentaje y estado
    $resultado = array_map(function ($item) use ($printsByFilament) {
        $inicial = (int)$item['peso_inicial_g'];
        $actual = (int)$item['peso_actual_g'];
        $minimo = (int)$item['alerta_stock_minimo_g'];
        $pct = $inicial > 0 ? round(($actual / $inicial) * 100, 1) : 0;

        $item['porcentaje_restante'] = max(0, min(100, $pct));
        $item['es_critico'] = $actual <= $minimo;
        $item['peso_actual_g'] = $actual;
        $item['peso_inicial_g'] = $inicial;
        $item['costo_rollo_ars'] = (float)$item['costo_rollo_ars'];
        $item['costo_por_gramo_ars'] = (float)$item['costo_por_gramo_ars'];
        $item['impresiones_activas'] = $printsByFilament[(int)$item['id']] ?? [];
        return $item;
    }, $filamentos);

    echo json_encode(['status' => 'success', 'data' => $resultado], JSON_UNESCAPED_UNICODE);
}

/**
 * Registra una nueva bobina en el inventario.
 */
function manejar_crear(PDO $pdo, array $input): void
{
    $marca = trim((string)($input['marca'] ?? ''));
    $material = strtoupper(trim((string)($input['material'] ?? '')));
    $color = trim((string)($input['color'] ?? ''));
    $peso_inicial_g = intval($input['peso_inicial_g'] ?? 1000);
    $costo_rollo_ars = floatval($input['costo_rollo_ars'] ?? 0.0);
    $alerta_min = intval($input['alerta_stock_minimo_g'] ?? 150);

    if ($marca === '' || $material === '' || $color === '' || $peso_inicial_g <= 0 || $costo_rollo_ars <= 0) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Parámetros inválidos. Marca, material, color, peso y costo son obligatorios.'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $sql = 'INSERT INTO filamentos (marca, material, color, peso_inicial_g, peso_actual_g, costo_rollo_ars, alerta_stock_minimo_g, activo)
            VALUES (:marca, :material, :color, :peso_ini, :peso_act, :costo, :alerta, 1)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':marca' => $marca,
        ':material' => $material,
        ':color' => $color,
        ':peso_ini' => $peso_inicial_g,
        ':peso_act' => $peso_inicial_g, // Al dar de alta, peso actual = peso inicial
        ':costo' => $costo_rollo_ars,
        ':alerta' => $alerta_min
    ]);

    $id = (int)$pdo->lastInsertId();

    echo json_encode([
        'status' => 'success',
        'message' => 'Bobina creada exitosamente.',
        'filamento_id' => $id
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Modifica los datos de una bobina existente.
 */
function manejar_editar(PDO $pdo, array $input): void
{
    $id = intval($input['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'ID de bobina inválido.'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $marca = trim((string)($input['marca'] ?? ''));
    $material = strtoupper(trim((string)($input['material'] ?? '')));
    $color = trim((string)($input['color'] ?? ''));
    $costo_rollo_ars = floatval($input['costo_rollo_ars'] ?? 0.0);
    $alerta_min = intval($input['alerta_stock_minimo_g'] ?? 150);

    $sql = 'UPDATE filamentos SET 
                marca = :marca,
                material = :material,
                color = :color,
                costo_rollo_ars = :costo,
                alerta_stock_minimo_g = :alerta
            WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':marca' => $marca,
        ':material' => $material,
        ':color' => $color,
        ':costo' => $costo_rollo_ars,
        ':alerta' => $alerta_min,
        ':id' => $id
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Bobina actualizada correctamente.'], JSON_UNESCAPED_UNICODE);
}

/**
 * Desactiva o elimina lógicamente una bobina.
 */
function manejar_eliminar(PDO $pdo, array $input): void
{
    $id = intval($input['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'ID de bobina inválido.'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $stmt = $pdo->prepare('UPDATE filamentos SET activo = 0 WHERE id = :id');
    $stmt->execute([':id' => $id]);

    echo json_encode(['status' => 'success', 'message' => 'Bobina eliminada del catálogo activo.'], JSON_UNESCAPED_UNICODE);
}

/**
 * Descuenta gramos de una bobina mediante transacción ACID con auditoría Append-Only.
 * Control de Parada: Descuenta X gramos y el remanente en base de datos queda actualizado de inmediato.
 */
function manejar_descontar(PDO $pdo, array $input): void
{
    $id = intval($input['id'] ?? ($input['filamento_id'] ?? 0));
    $gramos = intval($input['gramos'] ?? 0);
    $nota = trim((string)($input['nota'] ?? 'Impresión de pieza'));

    if ($id <= 0 || $gramos <= 0) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Se requiere un ID de bobina válido y una cantidad de gramos mayor a cero.'], JSON_UNESCAPED_UNICODE);
        return;
    }

    // Inicio de Transacción ACID
    $pdo->beginTransaction();

    try {
        $forUpdate = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? '' : ' FOR UPDATE';
        // Bloqueo pesimista de fila para concurrencia segura
        $stmt = $pdo->prepare('SELECT id, marca, material, color, peso_actual_g, peso_inicial_g, alerta_stock_minimo_g FROM filamentos WHERE id = :id AND activo = 1' . $forUpdate);
        $stmt->execute([':id' => $id]);
        $bobina = $stmt->fetch();

        if (!$bobina) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'La bobina especificada no existe o no está activa.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $peso_anterior = (int)$bobina['peso_actual_g'];
        $peso_nuevo = $peso_anterior - $gramos;

        if ($peso_nuevo < 0) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => "Stock insuficiente en la bobina {$bobina['marca']} {$bobina['material']}. Disponible: {$peso_anterior}g, Requerido: {$gramos}g."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Actualizar remanente exacto en base de datos
        $upd = $pdo->prepare('UPDATE filamentos SET peso_actual_g = :nuevo WHERE id = :id');
        $upd->execute([':nuevo' => $peso_nuevo, ':id' => $id]);

        // Registrar en tabla de auditoría Append-Only
        try {
            $audit = $pdo->prepare('INSERT INTO movimientos_filamento (filamento_id, tipo, gramos, peso_anterior_g, peso_nuevo_g, nota) VALUES (:id, "descuento_impresion", :gramos, :ant, :nuevo, :nota)');
            $audit->execute([
                ':id' => $id,
                ':gramos' => $gramos,
                ':ant' => $peso_anterior,
                ':nuevo' => $peso_nuevo,
                ':nota' => $nota
            ]);
        } catch (\Throwable $eAudit) {
            error_log('[API-FILAMENTOS-AUDIT] Aviso: No se pudo registrar auditoría: ' . $eAudit->getMessage());
        }

        // Confirmar transacción ACID
        $pdo->commit();

        $porcentaje = (int)$bobina['peso_inicial_g'] > 0 ? round(($peso_nuevo / (int)$bobina['peso_inicial_g']) * 100, 1) : 0;

        echo json_encode([
            'status' => 'success',
            'message' => "Se descontaron {$gramos}g correctamente.",
            'filamento_id' => $id,
            'peso_anterior_g' => $peso_anterior,
            'peso_descontado_g' => $gramos,
            'peso_actual_g' => $peso_nuevo,
            'porcentaje_restante' => max(0, min(100, $porcentaje)),
            'es_critico' => $peso_nuevo <= (int)$bobina['alerta_stock_minimo_g']
        ], JSON_UNESCAPED_UNICODE);

    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[API-FILAMENTOS-ERROR] ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error transaccional al descontar filamento: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * Añade gramos adicionales o restablece peso de una bobina (recarga).
 */
function manejar_cargar_kilos(PDO $pdo, array $input): void
{
    $id = intval($input['id'] ?? 0);
    $gramos_agregar = intval($input['gramos'] ?? 0);

    if ($id <= 0 || $gramos_agregar <= 0) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'ID de bobina y gramos a recargar válidos requeridos.'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $pdo->beginTransaction();

    try {
        $forUpdate = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? '' : ' FOR UPDATE';
        $stmt = $pdo->prepare('SELECT id, peso_actual_g, peso_inicial_g FROM filamentos WHERE id = :id' . $forUpdate);
        $stmt->execute([':id' => $id]);
        $bobina = $stmt->fetch();

        if (!$bobina) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Bobina no encontrada.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $peso_anterior = (int)$bobina['peso_actual_g'];
        $peso_nuevo = $peso_anterior + $gramos_agregar;

        $upd = $pdo->prepare('UPDATE filamentos SET peso_actual_g = :nuevo WHERE id = :id');
        $upd->execute([':nuevo' => $peso_nuevo, ':id' => $id]);

        try {
            $audit = $pdo->prepare('INSERT INTO movimientos_filamento (filamento_id, tipo, gramos, peso_anterior_g, peso_nuevo_g, nota) VALUES (:id, "recarga", :gramos, :ant, :nuevo, "Recarga de filamento")');
            $audit->execute([
                ':id' => $id,
                ':gramos' => $gramos_agregar,
                ':ant' => $peso_anterior,
                ':nuevo' => $peso_nuevo
            ]);
        } catch (\Throwable $e) {}

        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => "Se añadieron {$gramos_agregar}g a la bobina.",
            'peso_actual_g' => $peso_nuevo
        ], JSON_UNESCAPED_UNICODE);

    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al recargar bobina.'], JSON_UNESCAPED_UNICODE);
    }
}
