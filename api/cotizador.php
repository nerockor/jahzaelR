<?php
/* [LOCK-MODULE: API-COTIZADOR] - NO MODIFICAR ESTE BLOQUE A MENOS QUE EL USUARIO LO ESPECIFIQUE */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

// Verificar autenticación para operaciones del cotizador dentro del panel
if (!is_authenticated()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

try {
    $pdo = Database::getConnection();
} catch (\Throwable $e) {
    // Si no hay conexión a DB en el entorno local, proveer fallback para operatividad
    $pdo = null;
}

/**
 * Función central de cálculo según especificación estricta:
 * Costo Energía = (Potencia W / 1000) * Horas * Tarifa kWh
 * Costo Material = Gramos * Costo por Gramo
 * Costo Base = (Costo Energía + Costo Material + Desgaste) * (1 + Tasa Falla)
 * Precio Sugerido = Costo Base / (1 - Margen de Ganancia)
 */
function calcular_cotizacion(
    float $horas,
    float $gramos,
    float $costo_por_gramo,
    float $potencia_w,
    float $tarifa_kwh,
    float $desgaste,
    float $tasa_falla_pct,
    float $margen_ganancia_pct,
    int $cantidad = 1,
    float $descuento_lote_pct = 0.0
): array {
    $costo_energia_unitario = ($potencia_w / 1000.0) * $horas * $tarifa_kwh;
    $costo_material_unitario = $gramos * $costo_por_gramo;
    $costo_subtotal_unitario = $costo_energia_unitario + $costo_material_unitario + $desgaste;
    $factor_falla = 1.0 + ($tasa_falla_pct / 100.0);
    $costo_base_unitario = $costo_subtotal_unitario * $factor_falla;

    $margen_factor = max(0.01, min(0.99, $margen_ganancia_pct / 100.0));
    $precio_sugerido_unitario = $costo_base_unitario / (1.0 - $margen_factor);
    $ganancia_neta_unitaria = $precio_sugerido_unitario - $costo_base_unitario;

    // Descuento por lote mayorista sobre el precio de lista
    $precio_unitario_con_descuento = $precio_sugerido_unitario * (1.0 - ($descuento_lote_pct / 100.0));
    $precio_total_lote = $precio_unitario_con_descuento * $cantidad;
    $costo_total_lote = $costo_base_unitario * $cantidad;
    $ganancia_total_lote = $precio_total_lote - $costo_total_lote;

    return [
        'unitario' => [
            'horas' => round($horas, 2),
            'gramos' => round($gramos, 2),
            'costo_energia' => round($costo_energia_unitario, 2),
            'costo_material' => round($costo_material_unitario, 2),
            'desgaste' => round($desgaste, 2),
            'costo_base' => round($costo_base_unitario, 2),
            'precio_sugerido' => round($precio_sugerido_unitario, 2),
            'ganancia_neta' => round($ganancia_neta_unitaria, 2),
            'margen_real_pct' => round(($ganancia_neta_unitaria / $precio_sugerido_unitario) * 100, 2)
        ],
        'lote' => [
            'cantidad' => $cantidad,
            'descuento_pct' => round($descuento_lote_pct, 2),
            'precio_unitario_final' => round($precio_unitario_con_descuento, 2),
            'precio_total' => round($precio_total_lote, 2),
            'costo_total' => round($costo_total_lote, 2),
            'ganancia_total' => round($ganancia_total_lote, 2),
            'horas_totales' => round($horas * $cantidad, 2),
            'gramos_totales' => round($gramos * $cantidad, 2)
        ]
    ];
}

// -------------------------------------------------------------
// ENDPOINTS
// -------------------------------------------------------------

// 1. Obtener parámetros y filamentos disponibles
if ($method === 'GET') {
    $config = [
        'tarifa_kwh' => 120.00,
        'potencia_promedio_w' => 150,
        'margen_ganancia_defecto' => 40.00,
        'costo_hora_mano_obra' => 2500.00,
        'tasa_falla_estimada' => 5.00
    ];

    $filamentos = [
        ['id' => 1, 'marca' => 'Printalot', 'material' => 'PLA', 'color' => 'Negro', 'costo_por_gramo_ars' => 22.00, 'costo_rollo_ars' => 22000.00, 'peso_inicial_g' => 1000],
        ['id' => 2, 'marca' => 'Grilon3', 'material' => 'PETG', 'color' => 'Cristal', 'costo_por_gramo_ars' => 25.50, 'costo_rollo_ars' => 25500.00, 'peso_inicial_g' => 1000],
        ['id' => 3, 'marca' => 'GST3D', 'material' => 'PLA+', 'color' => 'Blanco', 'costo_por_gramo_ars' => 19.50, 'costo_rollo_ars' => 19500.00, 'peso_inicial_g' => 1000],
        ['id' => 4, 'marca' => 'eSun', 'material' => 'ABS+', 'color' => 'Gris', 'costo_por_gramo_ars' => 31.00, 'costo_rollo_ars' => 31000.00, 'peso_inicial_g' => 1000]
    ];

    if ($pdo !== null) {
        try {
            $stmt = $pdo->query('SELECT * FROM configuracion_granja ORDER BY id ASC LIMIT 1');
            $row = $stmt->fetch();
            if ($row) {
                $config = array_merge($config, $row);
            }

            $stmtFil = $pdo->query('SELECT id, marca, material, color, costo_rollo_ars, peso_inicial_g, costo_por_gramo_ars, peso_actual_g FROM filamentos WHERE activo = 1 ORDER BY material, marca');
            $dbFilamentos = $stmtFil->fetchAll();
            if (!empty($dbFilamentos)) {
                $filamentos = $dbFilamentos;
            }
        } catch (\Throwable $e) {
            error_log('[API-COTIZADOR] Error leyendo DB: ' . $e->getMessage());
        }
    }

    echo json_encode([
        'status' => 'success',
        'configuracion' => $config,
        'filamentos' => $filamentos
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. Cálculo dinámico o actualización de configuración
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $subaction = $input['action'] ?? $action;

    if ($subaction === 'guardar_config') {
        if (!verify_csrf_token($input['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Token CSRF inválido']);
            exit;
        }

        $tarifa_kwh = floatval($input['tarifa_kwh'] ?? 120.0);
        $potencia_w = intval($input['potencia_promedio_w'] ?? 150);
        $margen_defecto = floatval($input['margen_ganancia_defecto'] ?? 40.0);
        $costo_mo = floatval($input['costo_hora_mano_obra'] ?? 0.0);
        $tasa_falla = floatval($input['tasa_falla_estimada'] ?? 5.0);

        if ($pdo !== null) {
            $isSqlite = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
            if ($isSqlite) {
                $sql = 'INSERT INTO configuracion_granja (id, tarifa_kwh, potencia_promedio_w, margen_ganancia_defecto, costo_hora_mano_obra, tasa_falla_estimada)
                        VALUES (1, :tarifa, :potencia, :margen, :mo, :falla)
                        ON CONFLICT(id) DO UPDATE SET 
                            tarifa_kwh = excluded.tarifa_kwh,
                            potencia_promedio_w = excluded.potencia_promedio_w,
                            margen_ganancia_defecto = excluded.margen_ganancia_defecto,
                            costo_hora_mano_obra = excluded.costo_hora_mano_obra,
                            tasa_falla_estimada = excluded.tasa_falla_estimada';
            } else {
                $sql = 'INSERT INTO configuracion_granja (id, tarifa_kwh, potencia_promedio_w, margen_ganancia_defecto, costo_hora_mano_obra, tasa_falla_estimada)
                        VALUES (1, :tarifa, :potencia, :margen, :mo, :falla)
                        ON DUPLICATE KEY UPDATE 
                            tarifa_kwh = VALUES(tarifa_kwh),
                            potencia_promedio_w = VALUES(potencia_promedio_w),
                            margen_ganancia_defecto = VALUES(margen_ganancia_defecto),
                            costo_hora_mano_obra = VALUES(costo_hora_mano_obra),
                            tasa_falla_estimada = VALUES(tasa_falla_estimada)';
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':tarifa' => $tarifa_kwh,
                ':potencia' => $potencia_w,
                ':margen' => $margen_defecto,
                ':mo' => $costo_mo,
                ':falla' => $tasa_falla
            ]);
        }

        echo json_encode(['status' => 'success', 'message' => 'Parámetros actualizados']);
        exit;
    }

    // Default: calcular cotización
    $horas = floatval($input['horas'] ?? 0);
    $gramos = floatval($input['gramos'] ?? 0);
    $costo_por_gramo = floatval($input['costo_por_gramo'] ?? 22.0);
    $potencia_w = floatval($input['potencia_w'] ?? 150.0);
    $tarifa_kwh = floatval($input['tarifa_kwh'] ?? 120.0);
    $desgaste = floatval($input['desgaste'] ?? 0.0);
    $tasa_falla_pct = floatval($input['tasa_falla_pct'] ?? 5.0);
    $margen_ganancia_pct = floatval($input['margen_ganancia_pct'] ?? 40.0);
    $cantidad = max(1, intval($input['cantidad'] ?? 1));
    $descuento_lote_pct = floatval($input['descuento_lote_pct'] ?? 0.0);

    $resultado = calcular_cotizacion(
        $horas,
        $gramos,
        $costo_por_gramo,
        $potencia_w,
        $tarifa_kwh,
        $desgaste,
        $tasa_falla_pct,
        $margen_ganancia_pct,
        $cantidad,
        $descuento_lote_pct
    );

    echo json_encode([
        'status' => 'success',
        'calculo' => $resultado
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
