-- =============================================================================
--  StoreCell — Stored Procedures (5) + Triggers (5)
--  Base de datos: MySQL 8+
--  Ejecutar con: mysql -u root -p storecell < database/sql/sp_triggers.sql
-- =============================================================================

DELIMITER $$

-- =============================================================================
--  STORED PROCEDURES
-- =============================================================================

-- ---------------------------------------------------------------------------
-- SP 1: sp_resumen_ventas_periodo
-- Devuelve totales de ventas entre dos fechas, agrupados por día y vendedor.
-- Uso: CALL sp_resumen_ventas_periodo('2025-11-01', '2025-11-30');
-- ---------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_resumen_ventas_periodo$$
CREATE PROCEDURE sp_resumen_ventas_periodo(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin    DATE
)
BEGIN
    -- Validar que el rango sea coherente
    IF p_fecha_inicio > p_fecha_fin THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La fecha de inicio no puede ser mayor que la fecha fin.';
    END IF;

    SELECT
        DATE(s.created_at)             AS fecha,
        u.name                         AS vendedor,
        COUNT(s.id)                    AS total_ventas,
        COALESCE(SUM(s.total), 0)      AS monto_total,
        COALESCE(SUM(s.payment_cash),    0) AS total_efectivo,
        COALESCE(SUM(s.payment_virtual), 0) AS total_virtual,
        ROUND(AVG(s.total), 2)         AS ticket_promedio
    FROM   sales s
    INNER  JOIN users u ON u.id = s.user_id
    WHERE  DATE(s.created_at) BETWEEN p_fecha_inicio AND p_fecha_fin
      AND  (s.pending_delete IS NULL OR s.pending_delete = 0)
    GROUP  BY DATE(s.created_at), s.user_id
    ORDER  BY fecha DESC, monto_total DESC;
END$$


-- ---------------------------------------------------------------------------
-- SP 2: sp_productos_bajo_stock
-- Lista productos cuyo stock es menor o igual al límite indicado.
-- Uso: CALL sp_productos_bajo_stock(5);
-- ---------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_productos_bajo_stock$$
CREATE PROCEDURE sp_productos_bajo_stock(
    IN p_limite INT
)
BEGIN
    IF p_limite < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El límite de stock no puede ser negativo.';
    END IF;

    SELECT
        p.id,
        c.name                  AS categoria,
        p.description           AS producto,
        p.barcode,
        p.stock_qty             AS stock_actual,
        p.sale_price,
        CASE
            WHEN p.stock_qty = 0 THEN 'SIN STOCK'
            ELSE 'STOCK BAJO'
        END                     AS estado_stock
    FROM   products p
    INNER  JOIN categories c ON c.id = p.category_id
    WHERE  p.stock_qty <= p_limite
      AND  p.active = 1
      AND  (p.barcode != 'RECHARGE' OR p.barcode IS NULL)
    ORDER  BY p.stock_qty ASC, c.name, p.description;
END$$


-- ---------------------------------------------------------------------------
-- SP 3: sp_cerrar_sesion_caja
-- Cierra la sesión de caja activa: registra el cierre y devuelve el resumen.
-- Uso: CALL sp_cerrar_sesion_caja(3, 1);
-- ---------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_cerrar_sesion_caja$$
CREATE PROCEDURE sp_cerrar_sesion_caja(
    IN  p_session_id INT,
    IN  p_user_id    INT
)
BEGIN
    DECLARE v_ya_cerrada  TINYINT DEFAULT 0;
    DECLARE v_base        DECIMAL(10,2) DEFAULT 0;
    DECLARE v_efectivo    DECIMAL(10,2) DEFAULT 0;
    DECLARE v_virtual     DECIMAL(10,2) DEFAULT 0;
    DECLARE v_ingresos    DECIMAL(10,2) DEFAULT 0;
    DECLARE v_egresos     DECIMAL(10,2) DEFAULT 0;

    -- Verificar que la sesión existe y está abierta
    SELECT COUNT(*) INTO v_ya_cerrada
    FROM   cash_sessions
    WHERE  id = p_session_id AND close_at IS NOT NULL;

    IF v_ya_cerrada > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La sesión de caja ya fue cerrada anteriormente.';
    END IF;

    -- Calcular totales de ventas de la sesión
    SELECT
        COALESCE(SUM(payment_cash),    0),
        COALESCE(SUM(payment_virtual), 0)
    INTO v_efectivo, v_virtual
    FROM sales
    WHERE cash_session_id = p_session_id
      AND (pending_delete IS NULL OR pending_delete = 0);

    -- Calcular movimientos de caja
    SELECT
        COALESCE(SUM(CASE WHEN type = 'ingreso' THEN amount END), 0),
        COALESCE(SUM(CASE WHEN type = 'egreso'  THEN amount END), 0)
    INTO v_ingresos, v_egresos
    FROM cash_movements
    WHERE cash_session_id = p_session_id;

    -- Obtener monto base
    SELECT base_amount INTO v_base
    FROM   cash_sessions
    WHERE  id = p_session_id;

    -- Cerrar la sesión
    UPDATE cash_sessions
    SET    close_at  = NOW(),
           closed_by = p_user_id
    WHERE  id = p_session_id;

    -- Devolver resumen del cierre
    SELECT
        p_session_id                          AS session_id,
        v_base                                AS monto_base,
        v_efectivo                            AS ventas_efectivo,
        v_virtual                             AS ventas_virtual,
        v_ingresos                            AS otros_ingresos,
        v_egresos                             AS egresos,
        (v_base + v_efectivo + v_ingresos - v_egresos) AS total_en_caja,
        NOW()                                 AS cerrada_en;
END$$


-- ---------------------------------------------------------------------------
-- SP 4: sp_reparaciones_tecnico
-- Lista reparaciones filtradas por técnico y/o estado.
-- Uso: CALL sp_reparaciones_tecnico(2, 'in_progress');
--      CALL sp_reparaciones_tecnico(NULL, 'pending');
-- ---------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_reparaciones_tecnico$$
CREATE PROCEDURE sp_reparaciones_tecnico(
    IN p_tecnico_id INT,
    IN p_estado     VARCHAR(20)
)
BEGIN
    SELECT
        r.id,
        r.customer_name,
        r.customer_phone,
        r.device_description,
        r.issue_description,
        r.repair_description,
        r.parts_cost,
        r.total_cost,
        r.status,
        r.is_warranty,
        r.delivered_at,
        r.created_at,
        CONCAT(rec.name)  AS recibido_por,
        CONCAT(tec.name)  AS tecnico
    FROM   repairs r
    LEFT   JOIN users rec ON rec.id = r.received_by
    LEFT   JOIN users tec ON tec.id = r.technician_id
    WHERE  (p_tecnico_id IS NULL OR r.technician_id = p_tecnico_id)
      AND  (p_estado     IS NULL OR r.status = p_estado)
    ORDER  BY
        FIELD(r.status, 'pending', 'in_progress', 'completed', 'delivered'),
        r.created_at DESC;
END$$


-- ---------------------------------------------------------------------------
-- SP 5: sp_top_productos_vendidos
-- Devuelve el ranking de los N productos más vendidos en un mes/año.
-- Uso: CALL sp_top_productos_vendidos(2025, 11, 10);
-- ---------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_top_productos_vendidos$$
CREATE PROCEDURE sp_top_productos_vendidos(
    IN p_anio     INT,
    IN p_mes      INT,
    IN p_cantidad INT
)
BEGIN
    IF p_cantidad <= 0 THEN SET p_cantidad = 10; END IF;

    SELECT
        p.id,
        c.name                              AS categoria,
        p.description                       AS producto,
        SUM(si.quantity)                    AS unidades_vendidas,
        SUM(si.subtotal)                    AS ingresos_total,
        ROUND(AVG(si.unit_price), 2)        AS precio_promedio_venta
    FROM   sale_items si
    INNER  JOIN products p ON p.id = si.product_id
    INNER  JOIN categories c ON c.id = p.category_id
    INNER  JOIN sales s ON s.id = si.sale_id
    WHERE  YEAR(s.created_at)  = p_anio
      AND  MONTH(s.created_at) = p_mes
      AND  (s.pending_delete IS NULL OR s.pending_delete = 0)
      AND  si.product_id IS NOT NULL
    GROUP  BY p.id, c.name, p.description
    ORDER  BY unidades_vendidas DESC
    LIMIT  p_cantidad;
END$$


-- =============================================================================
--  TRIGGERS
-- =============================================================================

-- ---------------------------------------------------------------------------
-- TRIGGER 1: trg_venta_item_descontar_stock
-- AFTER INSERT en sale_items → descuenta el stock del producto.
-- Garantiza que el inventario se actualice automáticamente en cada venta.
-- ---------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_venta_item_descontar_stock$$
CREATE TRIGGER trg_venta_item_descontar_stock
AFTER INSERT ON sale_items
FOR EACH ROW
BEGIN
    IF NEW.product_id IS NOT NULL THEN
        UPDATE products
        SET    stock_qty = stock_qty - NEW.quantity
        WHERE  id        = NEW.product_id;
    END IF;
END$$


-- ---------------------------------------------------------------------------
-- TRIGGER 2: trg_venta_item_reponer_stock
-- AFTER DELETE en sale_items → repone el stock al eliminar una venta.
-- Complemento del trigger anterior para mantener consistencia del inventario.
-- ---------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_venta_item_reponer_stock$$
CREATE TRIGGER trg_venta_item_reponer_stock
AFTER DELETE ON sale_items
FOR EACH ROW
BEGIN
    IF OLD.product_id IS NOT NULL THEN
        UPDATE products
        SET    stock_qty = stock_qty + OLD.quantity
        WHERE  id        = OLD.product_id;
    END IF;
END$$


-- ---------------------------------------------------------------------------
-- TRIGGER 3: trg_venta_numero_automatico
-- BEFORE INSERT en sales → genera el número de venta si no viene definido.
-- Formato: VTA-YYYYMMDD-NNNN  (ej: VTA-20251115-0042)
-- ---------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_venta_numero_automatico$$
CREATE TRIGGER trg_venta_numero_automatico
BEFORE INSERT ON sales
FOR EACH ROW
BEGIN
    DECLARE v_consecutivo INT DEFAULT 0;

    IF NEW.sale_number IS NULL OR NEW.sale_number = '' THEN
        -- Contar ventas del día para el consecutivo
        SELECT COUNT(*) + 1 INTO v_consecutivo
        FROM   sales
        WHERE  DATE(created_at) = DATE(NOW());

        SET NEW.sale_number = CONCAT(
            'VTA-',
            DATE_FORMAT(NOW(), '%Y%m%d'),
            '-',
            LPAD(v_consecutivo, 4, '0')
        );
    END IF;
END$$


-- ---------------------------------------------------------------------------
-- TRIGGER 4: trg_reparacion_fecha_entrega
-- BEFORE UPDATE en repairs → registra automáticamente delivered_at
-- cuando el estado cambia a 'delivered'.
-- ---------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_reparacion_fecha_entrega$$
CREATE TRIGGER trg_reparacion_fecha_entrega
BEFORE UPDATE ON repairs
FOR EACH ROW
BEGIN
    -- Solo actúa si el estado cambia a 'delivered' y no tenía fecha previa
    IF NEW.status = 'delivered'
       AND OLD.status <> 'delivered'
       AND NEW.delivered_at IS NULL
    THEN
        SET NEW.delivered_at = NOW();
    END IF;

    -- Si se revierte de 'delivered' a otro estado, limpiar la fecha
    IF OLD.status = 'delivered' AND NEW.status <> 'delivered' THEN
        SET NEW.delivered_at = NULL;
    END IF;
END$$


-- ---------------------------------------------------------------------------
-- TRIGGER 5: trg_movimiento_caja_validar
-- BEFORE INSERT en cash_movements → valida que el monto sea positivo
-- y que la sesión de caja esté abierta antes de registrar el movimiento.
-- ---------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_movimiento_caja_validar$$
CREATE TRIGGER trg_movimiento_caja_validar
BEFORE INSERT ON cash_movements
FOR EACH ROW
BEGIN
    DECLARE v_sesion_cerrada TINYINT DEFAULT 0;

    -- Validar monto positivo
    IF NEW.amount <= 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El monto del movimiento de caja debe ser mayor que cero.';
    END IF;

    -- Validar que la sesión de caja esté abierta
    SELECT COUNT(*) INTO v_sesion_cerrada
    FROM   cash_sessions
    WHERE  id       = NEW.cash_session_id
      AND  close_at IS NOT NULL;

    IF v_sesion_cerrada > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No se pueden registrar movimientos en una sesión de caja cerrada.';
    END IF;
END$$

DELIMITER ;

-- =============================================================================
--  VERIFICACIÓN: listar los objetos creados
-- =============================================================================
SELECT 'STORED PROCEDURES' AS tipo, ROUTINE_NAME AS nombre
FROM   INFORMATION_SCHEMA.ROUTINES
WHERE  ROUTINE_SCHEMA = DATABASE()
  AND  ROUTINE_TYPE   = 'PROCEDURE'
  AND  ROUTINE_NAME   LIKE 'sp_%'

UNION ALL

SELECT 'TRIGGER', TRIGGER_NAME
FROM   INFORMATION_SCHEMA.TRIGGERS
WHERE  TRIGGER_SCHEMA = DATABASE()
  AND  TRIGGER_NAME   LIKE 'trg_%'

ORDER  BY tipo, nombre;
