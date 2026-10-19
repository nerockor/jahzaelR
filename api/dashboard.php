<?php
/* [LOCK-MODULE: API-DASHBOARD] - NO MODIFICAR ESTE BLOQUE A MENOS QUE EL USUARIO LO ESPECIFIQUE */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

// Verificación de autenticación estricta
if (!is_authenticated()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Acceso no autorizado al API del Dashboard.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = Database::getConnection();
} catch (\Throwable $e) {
    $pdo = null;
}

/**
 * Recopila todas las métricas ejecutivas y queries agregadas de la granja 3D.
 */
function obtener_metricas_dashboard(?PDO $pdo): array
{
    // Valores por defecto seguros (Mock / Fallback)
    $ganancias_totales = 0.0;
    $ventas_totales_ars = 0.0;
    $cantidad_ventas = 0;
    $ventas_mes_ars = 0.0;
    $piezas_vendidas_mes = 0;

    $filamentos_criticos = [];
    $total_bobinas = 0;
    $gramos_totales_disponibles = 0;
    $capital_filamento_ars = 0.0;

    $estados_piezas = [
        'en_stock' => 0,
        'vendida' => 0,
        'dañada' => 0,
        'en_produccion' => 0
    ];
    $capital_inmovilizado_stock_ars = 0.0;
    $valor_venta_stock_ars = 0.0;

    $materiales_distribucion = [];

    $historico_ventas = [
        'labels' => ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
        'ventas_ars' => [0.0, 0.0, 0.0, 0.0],
        'piezas' => [0, 0, 0, 0]
    ];

    if ($pdo !== null) {
        try {
            $isSqlite = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';

            // 1. Queries agregadas de Ventas: SUM(ganancia_neta_ars) y facturación total
            $stmtVentas = $pdo->query('
                SELECT 
                    COALESCE(SUM(ganancia_neta_ars), 0) AS total_ganancia,
                    COALESCE(SUM(precio_unitario_ars * cantidad), 0) AS total_facturado,
                    COALESCE(SUM(cantidad), 0) AS total_cantidad
                FROM ventas
            ');
            $rowV = $stmtVentas->fetch();
            if ($rowV) {
                $ganancias_totales = (float)$rowV['total_ganancia'];
                $ventas_totales_ars = (float)$rowV['total_facturado'];
                $cantidad_ventas = (int)$rowV['total_cantidad'];
            }

            // Ventas del mes actual (últimos 30 días)
            $dateClause = $isSqlite ? "datetime('now', '-30 days')" : "DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmtMes = $pdo->query("
                SELECT 
                    COALESCE(SUM(precio_unitario_ars * cantidad), 0) AS mes_facturado,
                    COALESCE(SUM(cantidad), 0) AS mes_piezas
                FROM ventas 
                WHERE fecha_venta >= {$dateClause}
            ");
            $rowMes = $stmtMes->fetch();
            if ($rowMes) {
                $ventas_mes_ars = (float)$rowMes['mes_facturado'];
                $piezas_vendidas_mes = (int)$rowMes['mes_piezas'];
            }

            // Gráfico 2: Ventas reales de las últimas 4 semanas (Sem 1 a Sem 4)
            $daysAgo = $isSqlite ? "datetime('now', '-28 days')" : "DATE_SUB(NOW(), INTERVAL 28 DAY)";
            $stmtHist = $pdo->query("
                SELECT fecha_venta, (precio_unitario_ars * cantidad) AS monto, cantidad
                FROM ventas
                WHERE fecha_venta >= {$daysAgo}
                ORDER BY fecha_venta ASC
            ");
            $ventasRows = $stmtHist->fetchAll();
            if (!empty($ventasRows)) {
                $now = time();
                foreach ($ventasRows as $vr) {
                    $vTime = strtotime((string)$vr['fecha_venta']);
                    $diffDays = floor(($now - $vTime) / 86400);
                    if ($diffDays < 7) {
                        $idx = 3; // Sem 4 (más reciente)
                    } elseif ($diffDays < 14) {
                        $idx = 2; // Sem 3
                    } elseif ($diffDays < 21) {
                        $idx = 1; // Sem 2
                    } else {
                        $idx = 0; // Sem 1
                    }
                    $historico_ventas['ventas_ars'][$idx] += (float)$vr['monto'];
                    $historico_ventas['piezas'][$idx] += (int)$vr['cantidad'];
                }
            }

            // 2. Filamentos con peso_actual_g <= alerta_stock_minimo_g
            $stmtCrit = $pdo->query('
                SELECT id, marca, material, color, peso_actual_g, peso_inicial_g, alerta_stock_minimo_g, costo_rollo_ars
                FROM filamentos 
                WHERE activo = 1 AND peso_actual_g <= alerta_stock_minimo_g
                ORDER BY peso_actual_g ASC
            ');
            $filamentos_criticos = $stmtCrit->fetchAll();

            // Total de bobinas y capital inmovilizado en filamento
            $stmtFilTotal = $pdo->query('
                SELECT 
                    COUNT(*) as total_bobinas,
                    COALESCE(SUM(peso_actual_g), 0) as total_gramos,
                    COALESCE(SUM(peso_actual_g * (costo_rollo_ars / peso_inicial_g)), 0) as capital_filamento
                FROM filamentos
                WHERE activo = 1
            ');
            $rowFil = $stmtFilTotal->fetch();
            if ($rowFil) {
                $total_bobinas = (int)$rowFil['total_bobinas'];
                $gramos_totales_disponibles = (int)$rowFil['total_gramos'];
                $capital_filamento_ars = (float)$rowFil['capital_filamento'];
            }

            // Gráfico 1: Distribución real de filamentos en stock por material (Inventario de Bobinas)
            $stmtMat = $pdo->query('
                SELECT material, COALESCE(SUM(peso_actual_g), 0) as total_gramos
                FROM filamentos
                WHERE activo = 1
                GROUP BY material
                ORDER BY total_gramos DESC
            ');
            $dbMats = $stmtMat->fetchAll();
            foreach ($dbMats as $dm) {
                $g = (int)$dm['total_gramos'];
                if ($g > 0) {
                    $materiales_distribucion[$dm['material']] = $g;
                }
            }

            // Gráfico 3 & KPIs: Conteo de piezas por estado y Capital Inmovilizado en Stock (excluyendo archivadas)
            $stmtEstados = $pdo->query("
                SELECT estado, COUNT(*) as cantidad, 
                       COALESCE(SUM(costo_produccion_ars), 0) as costo_total,
                       COALESCE(SUM(precio_venta_ars), 0) as valor_venta
                FROM piezas_stock
                WHERE estado != 'archivada'
                GROUP BY estado
            ");
            while ($rowE = $stmtEstados->fetch()) {
                $est = $rowE['estado'];
                if (isset($estados_piezas[$est])) {
                    $estados_piezas[$est] = (int)$rowE['cantidad'];
                }
                if ($est === 'en_stock') {
                    $capital_inmovilizado_stock_ars = (float)$rowE['costo_total'];
                    $valor_venta_stock_ars = (float)$rowE['valor_venta'];
                }
            }

        } catch (\Throwable $e) {
            error_log('[API-DASHBOARD-ERR] ' . $e->getMessage());
        }
    }

    return [
        'status' => 'success',
        'kpis' => [
            'ganancias_totales_ars' => round($ganancias_totales, 2),
            'ventas_totales_ars' => round($ventas_totales_ars, 2),
            'cantidad_ventas' => $cantidad_ventas,
            'piezas_vendidas_mes' => $piezas_vendidas_mes,
            'ventas_mes_ars' => round($ventas_mes_ars, 2),
            'capital_inmovilizado_stock_ars' => round($capital_inmovilizado_stock_ars, 2),
            'valor_venta_stock_ars' => round($valor_venta_stock_ars, 2),
            'capital_filamento_ars' => round($capital_filamento_ars, 2),
            'total_bobinas' => $total_bobinas,
            'gramos_totales_disponibles' => $gramos_totales_disponibles,
            'alertas_stock_critico' => count($filamentos_criticos)
        ],
        'filamentos_criticos' => $filamentos_criticos,
        'estados_piezas' => $estados_piezas,
        'materiales_mas_usados' => $materiales_distribucion,
        'historico_ventas' => $historico_ventas
    ];
}

$datos = obtener_metricas_dashboard($pdo);
echo json_encode($datos, JSON_UNESCAPED_UNICODE);
