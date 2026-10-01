<?php
/* [LOCK-MODULE: API-INSIGHTS] - NO MODIFICAR ESTE BLOQUE A MENOS QUE EL USUARIO LO ESPECIFIQUE */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_authenticated()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Acceso no autorizado al API de insights.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $action;
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

switch ($action) {
    case 'dashboard_data':
        manejar_dashboard_data($pdo);
        break;
    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "Acción no válida: '{$action}'"], JSON_UNESCAPED_UNICODE);
        break;
}

function manejar_dashboard_data(PDO $pdo): void {
    try {
        // Obtenemos todas las ventas junto con la información de la pieza
        $stmt = $pdo->query('
            SELECT 
                p.nombre, 
                v.cantidad, 
                v.ganancia_neta_ars, 
                p.peso_g, 
                p.horas_impresion 
            FROM ventas v
            JOIN piezas_stock p ON v.pieza_id = p.id
        ');
        $ventas_crudas = $stmt->fetchAll();

        // Limpiar sufijos (xN) y agrupar
        $productos = [];
        foreach ($ventas_crudas as $v) {
            $nombre_base = $v['nombre'];
            if (preg_match('/^(.*?)\s*\(x\d+\)$/i', $v['nombre'], $matches)) {
                $nombre_base = trim($matches[1]);
            }

            if (!isset($productos[$nombre_base])) {
                $productos[$nombre_base] = [
                    'nombre' => $nombre_base,
                    'total_vendido' => 0,
                    'ganancia_total' => 0.0,
                    'peso_total_g' => 0,
                    'horas_totales' => 0.0
                ];
            }

            $productos[$nombre_base]['total_vendido'] += (int)$v['cantidad'];
            $productos[$nombre_base]['ganancia_total'] += (float)$v['ganancia_neta_ars'];
            $productos[$nombre_base]['peso_total_g'] += (int)$v['peso_g'];
            $productos[$nombre_base]['horas_totales'] += (float)$v['horas_impresion'];
        }

        // Calcular métricas
        foreach ($productos as $key => &$prod) {
            // Rentabilidad por Hora (Ganancia / Horas)
            $prod['rentabilidad_por_hora'] = $prod['horas_totales'] > 0 
                ? round($prod['ganancia_total'] / $prod['horas_totales'], 2) 
                : 0;

            // Rentabilidad por Gramo (Ganancia / Gramos)
            $prod['rentabilidad_por_gramo'] = $prod['peso_total_g'] > 0 
                ? round($prod['ganancia_total'] / $prod['peso_total_g'], 2) 
                : 0;
        }
        unset($prod);

        // Convertir asociativo a numérico
        $lista = array_values($productos);

        // Ordenar: Top Ventas (Volumen)
        $top_volumen = $lista;
        usort($top_volumen, function($a, $b) {
            return $b['total_vendido'] <=> $a['total_vendido'];
        });

        // Ordenar: Top Rentabilidad por Hora
        $top_hora = $lista;
        usort($top_hora, function($a, $b) {
            return $b['rentabilidad_por_hora'] <=> $a['rentabilidad_por_hora'];
        });

        // Ordenar: Top Rentabilidad por Gramo
        $top_gramo = $lista;
        usort($top_gramo, function($a, $b) {
            return $b['rentabilidad_por_gramo'] <=> $a['rentabilidad_por_gramo'];
        });

        echo json_encode([
            'status' => 'success',
            'data' => [
                'top_volumen' => $top_volumen,
                'top_hora' => $top_hora,
                'top_gramo' => $top_gramo
            ]
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        error_log("[API-INSIGHTS-ERR] " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al procesar insights.'], JSON_UNESCAPED_UNICODE);
    }
}
