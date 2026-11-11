-- =====================================================================
-- JAFFNA GOLD - LIVE SERVER DATABASE FIX: SPLIT MANO ANNA & KANIY MANO
-- =====================================================================
-- Description:
-- Fixes the accidental merger of MANO ANNA and KANIY MANO.
-- 1. Restores supplier ID 1 to `MANO ANNA` in `handmade_suppliers`.
-- 2. Inserts `KANIY MANO` as its own distinct supplier.
-- 3. Separates `staff_expenses`:
--    - 16 KANIY MANO expenses (Kani/Kolaya).
--    - 65 MANO ANNA expenses.
-- 4. Separates `expenses`:
--    - 17 KANIY MANO expenses.
--    - 68 MANO ANNA expenses.
-- 5. Reassigns payments in `handmade_supplier_payments`:
--    - 16 payments moved to KANIY MANO.
--    - 67 payments retained under MANO ANNA.
-- =====================================================================

START TRANSACTION;

-- ---------------------------------------------------------------------
-- STEP 1: RESTORE MANO ANNA & CREATE KANIY MANO IN `handmade_suppliers`
-- ---------------------------------------------------------------------

-- 1. Restore supplier ID 1 name back to MANO ANNA
UPDATE `handmade_suppliers` 
SET `name` = 'MANO ANNA', `updated_at` = NOW() 
WHERE `id` = 1;

-- 2. Register KANIY MANO as a distinct supplier
INSERT INTO `handmade_suppliers` (`name`, `address`, `opening_payable_balance`, `is_active`, `created_at`, `updated_at`)
SELECT 'KANIY MANO', 'JAFFNA', 0.00, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `handmade_suppliers` WHERE `name` = 'KANIY MANO');

-- Retrieve IDs for variables
SET @mano_anna_id = (SELECT `id` FROM `handmade_suppliers` WHERE `name` = 'MANO ANNA' ORDER BY `id` ASC LIMIT 1);
SET @kaniy_mano_id = (SELECT `id` FROM `handmade_suppliers` WHERE `name` = 'KANIY MANO' ORDER BY `id` ASC LIMIT 1);

-- ---------------------------------------------------------------------
-- STEP 2: SEPARATE EXPENSES IN `staff_expenses`
-- ---------------------------------------------------------------------

-- Ensure the 16 Kaniy Mano records are set to KANIY MANO
UPDATE `staff_expenses` 
SET `expense_type` = 'KANIY MANO' 
WHERE `id` IN (2871, 2939, 2968, 2995, 3010, 3026, 3045, 3088, 3124, 3161, 3177, 3202, 3221, 3257, 3277, 3288)
   OR LOWER(`expense_type`) LIKE '%kolaya%'
   OR (LOWER(`expense_type`) LIKE '%kaniy%' AND `expense_type` != 'MANO ANNA');

-- Restore the 65 Mano Anna records back to MANO ANNA
UPDATE `staff_expenses` 
SET `expense_type` = 'MANO ANNA' 
WHERE `expense_type` = 'KANIY MANO' 
  AND `id` NOT IN (2871, 2939, 2968, 2995, 3010, 3026, 3045, 3088, 3124, 3161, 3177, 3202, 3221, 3257, 3277, 3288)
  AND LOWER(`expense_type`) NOT LIKE '%kolaya%';

-- ---------------------------------------------------------------------
-- STEP 3: SEPARATE EXPENSES IN `expenses`
-- ---------------------------------------------------------------------

-- Ensure the 17 Kaniy Mano records are set to Purchasing Expense - KANIY MANO
UPDATE `expenses` 
SET `category` = 'Purchasing Expense - KANIY MANO' 
WHERE `id` IN (2872, 2941, 2970, 2997, 3012, 3028, 3047, 3090, 3126, 3162, 3163, 3179, 3204, 3223, 3259, 3279, 3290)
   OR LOWER(`category`) LIKE '%kolaya%'
   OR (LOWER(`category`) LIKE '%kaniy%' AND `category` != 'Purchasing Expense - MANO ANNA');

-- Restore the 68 Mano Anna records back to Purchasing Expense - MANO ANNA
UPDATE `expenses` 
SET `category` = 'Purchasing Expense - MANO ANNA' 
WHERE `category` = 'Purchasing Expense - KANIY MANO' 
  AND `id` NOT IN (2872, 2941, 2970, 2997, 3012, 3028, 3047, 3090, 3126, 3162, 3163, 3179, 3204, 3223, 3259, 3279, 3290)
  AND LOWER(`category`) NOT LIKE '%kolaya%';

-- ---------------------------------------------------------------------
-- STEP 4: REASSIGN PAYMENTS IN `handmade_supplier_payments`
-- ---------------------------------------------------------------------

-- 1. Reassign the 16 payments for KANIY MANO to @kaniy_mano_id
UPDATE `handmade_supplier_payments` 
SET `supplier_id` = @kaniy_mano_id,
    `notes` = CASE 
        WHEN `id` = 7 THEN 'Purchasing Expense: Raw Gold / Poun Purchase'
        ELSE 'From Daily Expenses: KANIY MANO'
    END
WHERE `id` IN (2, 3, 7, 8, 11, 14, 18, 19, 23, 25, 27, 28, 30, 32, 34, 480)
   OR (`supplier_id` = @mano_anna_id AND (
       (`payment_date` = '2026-09-29' AND `amount` = 16500.00) OR
       (`payment_date` = '2026-10-02' AND `amount` = 2200.00) OR
       (`payment_date` = '2026-10-04' AND `amount` = 2900.00) OR
       (`payment_date` = '2026-10-05' AND `amount` = 115250.00) OR
       (`payment_date` = '2026-10-06' AND `amount` = 13500.00) OR
       (`payment_date` = '2026-10-07' AND `amount` = 10000.00) OR
       (`payment_date` = '2026-10-08' AND `amount` = 3000.00) OR
       (`payment_date` = '2026-10-10' AND `amount` = 34000.00) OR
       (`payment_date` = '2026-10-13' AND `amount` = 2300.00) OR
       (`payment_date` = '2026-10-14' AND `amount` = 10500.00) OR
       (`payment_date` = '2026-10-15' AND `amount` = 28000.00) OR
       (`payment_date` = '2026-10-16' AND `amount` = 1500.00) OR
       (`payment_date` = '2026-10-17' AND `amount` = 33000.00) OR
       (`payment_date` = '2026-10-19' AND `amount` = 10000.00) OR
       (`payment_date` = '2026-10-20' AND `amount` = 12300.00) OR
       (`payment_date` = '2026-10-21' AND `amount` = 25000.00)
   ));

-- 2. Restore MANO ANNA payment notes for all payments remaining under @mano_anna_id
UPDATE `handmade_supplier_payments` 
SET `notes` = CASE 
        WHEN `id` = 6 THEN 'Purchasing Expense: Raw Gold / Poun Purchase'
        ELSE 'From Daily Expenses: MANO ANNA'
    END
WHERE `supplier_id` = @mano_anna_id 
  AND (`notes` LIKE '%KANIY MANO%' OR `notes` LIKE '%mano%');

-- 3. Ensure any missing payments are properly synchronized
INSERT INTO `handmade_supplier_payments` (
    `supplier_id`, 
    `user_id`, 
    `amount`, 
    `payment_method`, 
    `payment_date`, 
    `notes`, 
    `created_at`, 
    `updated_at`
)
SELECT 
    s.id AS supplier_id,
    se.staff_id AS user_id,
    se.amount,
    'cash' AS payment_method,
    se.expense_date AS payment_date,
    CONCAT('From Daily Expenses: ', se.expense_type) AS notes,
    se.created_at,
    se.updated_at
FROM `staff_expenses` se
JOIN `handmade_suppliers` s ON s.name = se.expense_type
WHERE se.expense_type IN ('MANO ANNA', 'KANIY MANO')
AND NOT EXISTS (
    SELECT 1 FROM `handmade_supplier_payments` hsp
    WHERE hsp.supplier_id = s.id 
      AND hsp.payment_date = se.expense_date 
      AND hsp.amount = se.amount
);

COMMIT;

-- ---------------------------------------------------------------------
-- VERIFICATION QUERIES (Run these to confirm separation)
-- ---------------------------------------------------------------------
-- 1. Suppliers:
-- SELECT id, name, is_active FROM `handmade_suppliers` WHERE name IN ('MANO ANNA', 'KANIY MANO');

-- 2. Daily Expenses breakdown:
-- SELECT expense_type, COUNT(*) AS count, SUM(amount) AS total FROM `staff_expenses` WHERE expense_type IN ('MANO ANNA', 'KANIY MANO') GROUP BY expense_type;

-- 3. Main Expenses breakdown:
-- SELECT category, COUNT(*) AS count, SUM(amount) AS total FROM `expenses` WHERE category LIKE '%MANO%' GROUP BY category;

-- 4. Handmade Supplier Payments breakdown:
-- SELECT s.name, COUNT(p.id) AS payment_count, SUM(p.amount) AS total_paid FROM `handmade_suppliers` s JOIN `handmade_supplier_payments` p ON p.supplier_id = s.id WHERE s.name IN ('MANO ANNA', 'KANIY MANO') GROUP BY s.name;
