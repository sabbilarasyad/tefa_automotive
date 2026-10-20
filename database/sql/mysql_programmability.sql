-- ============================================================================
-- TEFA Automotive - MySQL Programmability (Triggers & Functions)
-- ============================================================================
-- This file contains all triggers and functions for the TEFA Automotive application.
-- It can be executed manually via phpMyAdmin, MySQL CLI, or any MySQL client
-- if the migration system cannot create them (e.g., hosting restrictions).
--
-- IMPORTANT: Run this AFTER all tables have been created by migrations.
-- ============================================================================

-- Use the correct database
USE `tefa_automotive`;

-- ============================================================================
-- DELIMITER SETUP
-- ============================================================================
-- Change delimiter to allow compound statements
DELIMITER $$

-- ============================================================================
-- FUNCTION: fn_calculate_pkb_total
-- Calculates the total amount for a PKB (services + spareparts)
-- ============================================================================
CREATE FUNCTION `fn_calculate_pkb_total`(
    p_pkb_id BIGINT UNSIGNED
) RETURNS DECIMAL(15,2)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_total DECIMAL(15,2) DEFAULT 0.00;

    -- Sum services subtotal
    SELECT COALESCE(SUM(`subtotal`), 0) INTO v_total
    FROM `pkb_services`
    WHERE `pkb_id` = p_pkb_id;

    -- Add spareparts subtotal
    SELECT v_total + COALESCE(SUM(`subtotal`), 0) INTO v_total
    FROM `pkb_spareparts`
    WHERE `pkb_id` = p_pkb_id;

    RETURN v_total;
END$$

-- ============================================================================
-- FUNCTION: fn_documentation_complete
-- Checks if all required documentation for a PKB is complete
-- Returns 1 (true) if complete, 0 (false) if incomplete
-- ============================================================================
CREATE FUNCTION `fn_documentation_complete`(
    p_pkb_id BIGINT UNSIGNED
) RETURNS TINYINT(1)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_services_complete TINYINT(1) DEFAULT 1;
    DECLARE v_spareparts_complete TINYINT(1) DEFAULT 1;

    -- Check if all required service documentation exists
    SELECT NOT EXISTS (
        SELECT 1
        FROM `pkb_services` ps
        LEFT JOIN `documentations` d ON d.`pkb_service_id` = ps.`id`
        WHERE ps.`pkb_id` = p_pkb_id
          AND ps.`documentation_required` = 1
          AND d.`id` IS NULL
    ) INTO v_services_complete;

    -- Check if all required sparepart documentation exists
    SELECT NOT EXISTS (
        SELECT 1
        FROM `pkb_spareparts` psp
        LEFT JOIN `documentations` d ON d.`pkb_sparepart_id` = psp.`id`
        WHERE psp.`pkb_id` = p_pkb_id
          AND psp.`documentation_required` = 1
          AND d.`id` IS NULL
    ) INTO v_spareparts_complete;

    RETURN v_services_complete AND v_spareparts_complete;
END$$

-- ============================================================================
-- TRIGGER: trg_pkb_status_history
-- Logs status changes for PKB records
-- ============================================================================
CREATE TRIGGER `trg_pkb_status_history`
AFTER UPDATE ON `pkb`
FOR EACH ROW
BEGIN
    IF OLD.`status` <> NEW.`status` THEN
        INSERT INTO `pkb_status_histories` (
            `pkb_id`,
            `from_status`,
            `to_status`,
            `changed_by`,
            `created_at`
        ) VALUES (
            NEW.`id`,
            OLD.`status`,
            NEW.`status`,
            @current_user_id,
            NOW()
        );
    END IF;
END$$

-- ============================================================================
-- TRIGGER: trg_prevent_negative_stock
-- Prevents sparepart stock from going negative
-- ============================================================================
CREATE TRIGGER `trg_prevent_negative_stock`
BEFORE UPDATE ON `spareparts`
FOR EACH ROW
BEGIN
    IF NEW.`stock_qty` < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Stok tidak boleh negatif';
    END IF;
END$$

-- ============================================================================
-- TRIGGER: trg_stock_movement_apply
-- Automatically updates sparepart stock when a stock movement is inserted
-- ============================================================================
CREATE TRIGGER `trg_stock_movement_apply`
AFTER INSERT ON `stock_movements`
FOR EACH ROW
BEGIN
    UPDATE `spareparts`
    SET `stock_qty` = NEW.`stock_after`
    WHERE `id` = NEW.`sparepart_id`;
END$$

-- ============================================================================
-- RESTORE DELIMITER
-- ============================================================================
DELIMITER ;

-- ============================================================================
-- VERIFICATION QUERIES
-- ============================================================================
-- Run these after executing the above to verify installation:

-- Check functions
-- SELECT ROUTINE_NAME, ROUTINE_TYPE FROM INFORMATION_SCHEMA.ROUTINES
-- WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = 'FUNCTION';

-- Check triggers
-- SELECT TRIGGER_NAME FROM INFORMATION_SCHEMA.TRIGGERS
-- WHERE TRIGGER_SCHEMA = DATABASE();

-- Test functions
-- SELECT fn_calculate_pkb_total(1) AS total_pkb_1;
-- SELECT fn_documentation_complete(1) AS doc_complete_pkb_1;

-- ============================================================================
-- NOTES FOR HOSTING ENVIRONMENTS
-- ============================================================================
-- 1. If your hosting provider doesn't allow SUPER privilege (required for
--    CREATE FUNCTION with DETERMINISTIC), you may need to:
--    a) Ask them to run this file for you
--    b) Or set log_bin_trust_function_creators = 1 in MySQL config
--    c) Or remove DETERMINISTIC keyword (may impact replication)
--
-- 2. If triggers already exist, you may see "Trigger already exists" errors.
--    Drop them first with: DROP TRIGGER IF EXISTS trigger_name;
--
-- 3. The @current_user_id variable is set by the application before
--    executing write operations. Ensure your application sets it:
--    SET @current_user_id = <user_id>;
--
-- 4. These objects are also created by the migration
--    2026_09_29_000000_create_workshop_database_programmability.php
--    This SQL file is provided as a fallback for environments where
--    migrations cannot create them directly.
-- ============================================================================