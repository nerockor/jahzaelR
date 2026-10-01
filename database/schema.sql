-- =============================================================
-- 3D FARM MANAGER - SCHEMA DE BASE DE DATOS
-- Fase 1: Arquitectura Base, Base de Datos y Acceso Ofuscado
-- Motor: InnoDB / Charset: UTF8MB4
-- =============================================================

-- Tabla de Usuarios Administrativos del Hub
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(120) DEFAULT NULL,
    activo TINYINT(1) DEFAULT 1,
    ultimo_acceso TIMESTAMP NULL DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configuración energética y parámetros en Argentina (ARS)
CREATE TABLE IF NOT EXISTS configuracion_granja (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tarifa_kwh DECIMAL(10,2) NOT NULL DEFAULT 120.00, -- Costo en ARS por kWh
    potencia_promedio_w INT NOT NULL DEFAULT 150,     -- Watts consumidos por máquina
    margen_ganancia_defecto DECIMAL(5,2) DEFAULT 40.00,
    costo_hora_mano_obra DECIMAL(10,2) DEFAULT 0.00,
    tasa_falla_estimada DECIMAL(5,2) DEFAULT 5.00,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inventario de filamentos (Cards dinámicas)
CREATE TABLE IF NOT EXISTS filamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marca VARCHAR(50) NOT NULL,
    material VARCHAR(20) NOT NULL, -- PLA, PETG, TPU, ABS, PPS-CF, etc.
    color VARCHAR(30) NOT NULL,
    peso_inicial_g INT NOT NULL,  -- ej: 1000g
    peso_actual_g INT NOT NULL,   -- Se descuenta con cada pieza impresa
    costo_rollo_ars DECIMAL(12,2) NOT NULL,
    costo_por_gramo_ars DECIMAL(10,4) GENERATED ALWAYS AS (costo_rollo_ars / peso_inicial_g) STORED,
    alerta_stock_minimo_g INT DEFAULT 150,
    activo TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Auditoría de Movimientos de Filamento (Append-Only)
CREATE TABLE IF NOT EXISTS movimientos_filamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filamento_id INT NOT NULL,
    tipo ENUM('descuento_impresion', 'ajuste', 'recarga', 'scrap') NOT NULL,
    gramos INT NOT NULL,
    peso_anterior_g INT NOT NULL,
    peso_nuevo_g INT NOT NULL,
    nota VARCHAR(255) DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mov_filamento FOREIGN KEY (filamento_id) 
        REFERENCES filamentos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Piezas producidas / en stock para venta
CREATE TABLE IF NOT EXISTS piezas_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    filamento_id INT NOT NULL,
    peso_g INT NOT NULL,
    horas_impresion DECIMAL(6,2) NOT NULL,
    costo_produccion_ars DECIMAL(12,2) NOT NULL,
    precio_venta_ars DECIMAL(12,2) NOT NULL,
    estado ENUM('en_produccion', 'en_stock', 'vendida', 'dañada') DEFAULT 'en_stock',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_piezas_filamento FOREIGN KEY (filamento_id) 
        REFERENCES filamentos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registro de ventas (Histórico contable Append-Only)
CREATE TABLE IF NOT EXISTS ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pieza_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario_ars DECIMAL(12,2) NOT NULL,
    costo_unitario_ars DECIMAL(12,2) NOT NULL,
    ganancia_neta_ars DECIMAL(12,2) GENERATED ALWAYS AS (precio_unitario_ars - costo_unitario_ars) STORED,
    fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ventas_pieza FOREIGN KEY (pieza_id) 
        REFERENCES piezas_stock(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserción inicial de configuración de la granja (si no existe)
INSERT INTO configuracion_granja (id, tarifa_kwh, potencia_promedio_w, margen_ganancia_defecto, costo_hora_mano_obra, tasa_falla_estimada)
SELECT 1, 120.00, 150, 40.00, 2500.00, 5.00
WHERE NOT EXISTS (SELECT 1 FROM configuracion_granja WHERE id = 1);

-- Usuario administrador por defecto: admin / admin123
-- Hash generado mediante password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO usuarios (id, usuario, password_hash, nombre, email, activo)
SELECT 1, 'admin', '$2y$12$Nq9v7.nJgU6Msqz7zF7j1Obm4l0T8x9fO9k6Z3q1aM8b.uA2b3c4e', 'Operador Granja 3D', 'admin@farm3d.local', 1
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE usuario = 'admin');
