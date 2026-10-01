<?php
/* [LOCK-MODULE: CORE-DB] - NO MODIFICAR ESTE BLOQUE A MENOS QUE EL USUARIO LO ESPECIFIQUE */

declare(strict_types=1);

/**
 * Gestor de Conexión a Base de Datos (Singleton PDO)
 * Provee conexión transaccional ACID, manejo estricto de excepciones y seguridad en consultas.
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
        // Constructor privado para patrón Singleton
    }

    private function __clone()
    {
        // Prevenir clonación
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_PORT') ?: '3306';
            $dbname = getenv('DB_NAME') ?: 'farm_3d';
            $user = getenv('DB_USER') ?: 'root';
            $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
            $charset = 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE utf8mb4_unicode_ci",
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Si MySQL no está activo o accesible, inicializar SQLite local para operatividad inmediata
                error_log('[DB-NOTICE] MySQL no disponible (' . $e->getMessage() . '). Activando modo SQLite local persistente con soporte ACID.');
                
                $sqlitePath = __DIR__ . '/../database/farm_3d.sqlite';
                self::$instance = new PDO("sqlite:{$sqlitePath}", null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
                self::$instance->exec('PRAGMA journal_mode = WAL;');
                self::$instance->exec('PRAGMA foreign_keys = ON;');
                self::initSqliteTables(self::$instance);
            }
        }

        return self::$instance;
    }

    private static function initSqliteTables(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS usuarios (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                usuario TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                nombre TEXT NOT NULL,
                email TEXT DEFAULT NULL,
                activo INTEGER DEFAULT 1,
                ultimo_acceso TIMESTAMP DEFAULT NULL,
                creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS configuracion_granja (
                id INTEGER PRIMARY KEY,
                tarifa_kwh REAL NOT NULL DEFAULT 120.00,
                potencia_promedio_w INTEGER NOT NULL DEFAULT 150,
                margen_ganancia_defecto REAL DEFAULT 40.00,
                costo_hora_mano_obra REAL DEFAULT 0.00,
                tasa_falla_estimada REAL DEFAULT 5.00,
                actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS filamentos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                marca TEXT NOT NULL,
                material TEXT NOT NULL,
                color TEXT NOT NULL,
                peso_inicial_g INTEGER NOT NULL,
                peso_actual_g INTEGER NOT NULL,
                costo_rollo_ars REAL NOT NULL,
                costo_por_gramo_ars REAL DEFAULT 0,
                alerta_stock_minimo_g INTEGER DEFAULT 150,
                activo INTEGER DEFAULT 1,
                creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS movimientos_filamento (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                filamento_id INTEGER NOT NULL,
                tipo TEXT NOT NULL,
                gramos INTEGER NOT NULL,
                peso_anterior_g INTEGER NOT NULL,
                peso_nuevo_g INTEGER NOT NULL,
                nota TEXT,
                creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS piezas_stock (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT NOT NULL,
                filamento_id INTEGER NOT NULL,
                peso_g INTEGER NOT NULL,
                horas_impresion REAL NOT NULL,
                costo_produccion_ars REAL NOT NULL,
                precio_venta_ars REAL NOT NULL,
                estado TEXT DEFAULT 'en_stock',
                creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS ventas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                pieza_id INTEGER NOT NULL,
                cantidad INTEGER NOT NULL DEFAULT 1,
                precio_unitario_ars REAL NOT NULL,
                costo_unitario_ars REAL NOT NULL,
                ganancia_neta_ars REAL DEFAULT 0,
                fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Sembrar usuario admin si no existe
        $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE usuario = 'admin'");
        if ($stmt->fetchColumn() == 0) {
            $hash = password_hash('admin123', PASSWORD_BCRYPT);
            $ins = $pdo->prepare("INSERT INTO usuarios (usuario, password_hash, nombre, email, activo) VALUES ('admin', :hash, 'Operador Granja 3D', 'admin@farm3d.local', 1)");
            $ins->execute([':hash' => $hash]);
        }

        // Sembrar bobinas iniciales si no existen
        $stmtF = $pdo->query("SELECT COUNT(*) FROM filamentos");
        if ($stmtF->fetchColumn() == 0) {
            $pdo->exec("
                INSERT INTO filamentos (marca, material, color, peso_inicial_g, peso_actual_g, costo_rollo_ars, costo_por_gramo_ars, alerta_stock_minimo_g) VALUES
                ('Printalot', 'PLA', 'Negro', 1000, 880, 22000.00, 22.00, 150),
                ('Grilon3', 'PETG', 'Cristal', 1000, 650, 25500.00, 25.50, 150),
                ('GST3D', 'PLA+', 'Blanco', 1000, 120, 19500.00, 19.50, 150),
                ('eSun', 'ABS+', 'Gris', 1000, 1000, 31000.00, 31.00, 150);

                INSERT INTO piezas_stock (nombre, filamento_id, peso_g, horas_impresion, costo_produccion_ars, precio_venta_ars, estado) VALUES
                ('Soporte de Cámara Articulado', 1, 80, 4.0, 1923.60, 3206.00, 'en_stock'),
                ('Engranaje Reductor Helicoidal', 2, 110, 5.5, 2800.00, 4700.00, 'vendida'),
                ('Guía Lineal MGN12 Adaptador', 1, 45, 2.2, 1100.00, 2100.00, 'en_stock');

                INSERT INTO ventas (pieza_id, cantidad, precio_unitario_ars, costo_unitario_ars, ganancia_neta_ars) VALUES
                (2, 1, 4700.00, 2800.00, 1900.00);
            ");
        }
    }
}

