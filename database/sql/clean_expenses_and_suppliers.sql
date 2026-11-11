-- =====================================================================
-- JAFFNA GOLD - LIVE SERVER DATABASE EXPENSE & SUPPLIER CLEANUP SCRIPT
-- =====================================================================
-- Description:
-- 1. Registers / updates all 10 Local Suppliers in `handmade_suppliers`.
-- 2. Normalizes all supplier expenses across `staff_expenses` & `expenses`.
-- 3. Synchronizes all historical supplier payments into `handmade_supplier_payments`.
-- 4. Normalizes common daily expenses (Tea, Food, Water, Petrol, etc.) to standard names.
-- 5. Cleans and resets `expense_categories` table to a clean, non-duplicated list.
-- =====================================================================

START TRANSACTION;

-- ---------------------------------------------------------------------
-- STEP 1: REGISTER / UPDATE ALL 11 LOCAL SUPPLIERS IN `handmade_suppliers`
-- ---------------------------------------------------------------------

-- Ensure supplier ID 1 is MANO ANNA
UPDATE `handmade_suppliers` 
SET `name` = 'MANO ANNA', `updated_at` = NOW() 
WHERE `id` = 1;

-- 1. MANO ANNA
INSERT INTO `handmade_suppliers` (`name`, `address`, `opening_payable_balance`, `is_active`, `created_at`, `updated_at`)
SELECT 'MANO ANNA', 'JAFFNA', 0.00, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `handmade_suppliers` WHERE `name` = 'MANO ANNA');

-- 2. KANIY MANO
INSERT INTO `handmade_suppliers` (`name`, `address`, `opening_payable_balance`, `is_active`, `created_at`, `updated_at`)
SELECT 'KANIY MANO', 'JAFFNA', 0.00, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `handmade_suppliers` WHERE `name` = 'KANIY MANO');

-- 3. MARAN THALAI
INSERT INTO `handmade_suppliers` (`name`, `address`, `opening_payable_balance`, `is_active`, `created_at`, `updated_at`)
SELECT 'MARAN THALAI', 'JAFFNA', 0.00, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `handmade_suppliers` WHERE `name` = 'MARAN THALAI');

-- 4. PRAKAS
INSERT INTO `handmade_suppliers` (`name`, `address`, `opening_payable_balance`, `is_active`, `created_at`, `updated_at`)
SELECT 'PRAKAS', 'JAFFNA', 0.00, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `handmade_suppliers` WHERE `name` = 'PRAKAS');

-- 5. SATHES DAD
INSERT INTO `handmade_suppliers` (`name`, `address`, `opening_payable_balance`, `is_active`, `created_at`, `updated_at`)
SELECT 'SATHES DAD', 'JAFFNA', 0.00, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `handmade_suppliers` WHERE `name` = 'SATHES DAD');

-- 6. SATHES ANNA
INSERT INTO `handmade_suppliers` (`name`, `address`, `opening_payable_balance`, `is_active`, `created_at`, `updated_at`)
SELECT 'SATHES ANNA', 'JAFFNA', 0.00, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `handmade_suppliers` WHERE `name` = 'SATHES ANNA');

-- 7. UDAYAN RING
INSERT INTO `handmade_suppliers` (`name`, `address`, `opening_payable_balance`, `is_active`, `created_at`, `updated_at`)
SELECT 'UDAYAN RING', 'JAFFNA', 0.00, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `handmade_suppliers` WHERE `name` = 'UDAYAN RING');

-- 8. SANKAR ANNA
INSERT INTO `handmade_suppliers` (`name`, `address`, `opening_payable_balance`, `is_active`, `created_at`, `updated_at`)
SELECT 'SANKAR ANNA', 'JAFFNA', 0.00, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `handmade_suppliers` WHERE `name` = 'SANKAR ANNA');

-- 9. SUTHA ANNA
INSERT INTO `handmade_suppliers` (`name`, `address`, `opening_payable_balance`, `is_active`, `created_at`, `updated_at`)
SELECT 'SUTHA ANNA', 'JAFFNA', 0.00, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `handmade_suppliers` WHERE `name` = 'SUTHA ANNA');

-- 10. ATHAAN THANGA THANNIY
INSERT INTO `handmade_suppliers` (`name`, `address`, `opening_payable_balance`, `is_active`, `created_at`, `updated_at`)
SELECT 'ATHAAN THANGA THANNIY', 'JAFFNA', 0.00, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `handmade_suppliers` WHERE `name` = 'ATHAAN THANGA THANNIY');

-- 11. RAMESH ANNA
INSERT INTO `handmade_suppliers` (`name`, `address`, `opening_payable_balance`, `is_active`, `created_at`, `updated_at`)
SELECT 'RAMESH ANNA', 'JAFFNA', 0.00, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `handmade_suppliers` WHERE `name` = 'RAMESH ANNA');

-- ---------------------------------------------------------------------
-- STEP 2: NORMALIZE LOCAL SUPPLIER EXPENSE NAMES IN `staff_expenses` & `expenses`
-- ---------------------------------------------------------------------

-- 1. KANIY MANO (KANIY MANO KO, KANIY MANO KOLAYA, MANO KOLAYA)
UPDATE `staff_expenses` SET `expense_type` = 'KANIY MANO' 
WHERE LOWER(`expense_type`) LIKE '%kolaya%'
   OR LOWER(`expense_type`) LIKE '%kaniy%'
   OR `id` IN (2871, 2939, 2968, 2995, 3010, 3026, 3045, 3088, 3124, 3161, 3177, 3202, 3221, 3257, 3277, 3288);

UPDATE `expenses` SET `category` = 'Purchasing Expense - KANIY MANO' 
WHERE LOWER(`category`) LIKE '%kolaya%'
   OR LOWER(`category`) LIKE '%kaniy%'
   OR `id` IN (2872, 2941, 2970, 2997, 3012, 3028, 3047, 3090, 3126, 3162, 3163, 3179, 3204, 3223, 3259, 3279, 3290);

-- 2. MANO ANNA (Mano anna, MANO, mano, MANO ANNA, MANO ANNNA)
UPDATE `staff_expenses` SET `expense_type` = 'MANO ANNA' 
WHERE (LOWER(`expense_type`) LIKE '%mano%' OR `expense_type` = 'KANIY MANO')
  AND `id` NOT IN (2871, 2939, 2968, 2995, 3010, 3026, 3045, 3088, 3124, 3161, 3177, 3202, 3221, 3257, 3277, 3288)
  AND LOWER(`expense_type`) NOT LIKE '%kolaya%'
  AND LOWER(`expense_type`) NOT LIKE '%kaniy%';

UPDATE `expenses` SET `category` = 'Purchasing Expense - MANO ANNA' 
WHERE (LOWER(`category`) LIKE '%mano%' OR `category` = 'Purchasing Expense - KANIY MANO')
  AND `id` NOT IN (2872, 2941, 2970, 2997, 3012, 3028, 3047, 3090, 3126, 3162, 3163, 3179, 3204, 3223, 3259, 3279, 3290)
  AND LOWER(`category`) NOT LIKE '%kolaya%'
  AND LOWER(`category`) NOT LIKE '%kaniy%';

-- 2. MARAN THALAI (kombu thalliy, MULLAI THIU KOMBU THALLIY, MULLAITHIU KOMBU THALLIY)
UPDATE `staff_expenses` SET `expense_type` = 'MARAN THALAI' 
WHERE LOWER(`expense_type`) LIKE '%kombu%' 
   OR LOWER(`expense_type`) LIKE '%mullai%' 
   OR LOWER(`expense_type`) LIKE '%maran%';

UPDATE `expenses` SET `category` = 'Purchasing Expense - MARAN THALAI' 
WHERE LOWER(`category`) LIKE '%kombu%' 
   OR LOWER(`category`) LIKE '%mullai%' 
   OR LOWER(`category`) LIKE '%maran%';

-- 3. PRAKAS (PRAHAS ANNAN, PRAGAS ANNA, PIRAKASH ANNA, pirakash)
UPDATE `staff_expenses` SET `expense_type` = 'PRAKAS' 
WHERE LOWER(`expense_type`) LIKE '%prahas%' 
   OR LOWER(`expense_type`) LIKE '%pragas%' 
   OR LOWER(`expense_type`) LIKE '%prakas%' 
   OR LOWER(`expense_type`) LIKE '%pirakas%';

UPDATE `expenses` SET `category` = 'Purchasing Expense - PRAKAS' 
WHERE LOWER(`category`) LIKE '%prahas%' 
   OR LOWER(`category`) LIKE '%pragas%' 
   OR LOWER(`category`) LIKE '%prakas%' 
   OR LOWER(`category`) LIKE '%pirakas%';

-- 4. SATHES DAD
UPDATE `staff_expenses` SET `expense_type` = 'SATHES DAD' 
WHERE LOWER(`expense_type`) LIKE '%sathes dad%';

UPDATE `expenses` SET `category` = 'Purchasing Expense - SATHES DAD' 
WHERE LOWER(`category`) LIKE '%sathes dad%';

-- 5. SATHES ANNA
UPDATE `staff_expenses` SET `expense_type` = 'SATHES ANNA' 
WHERE LOWER(`expense_type`) LIKE '%sathes anna%';

UPDATE `expenses` SET `category` = 'Purchasing Expense - SATHES ANNA' 
WHERE LOWER(`category`) LIKE '%sathes anna%';

-- 6. UDAYAN RING (MOTHIRAM VANGINA KASU, mothiram konduvaraver, mothiram kodu varaver, mothiram, mothiram sairaver, MOTHIRAM UTHAYAN, MOTHGIRAM UTHAYAN)
UPDATE `staff_expenses` SET `expense_type` = 'UDAYAN RING' 
WHERE LOWER(`expense_type`) LIKE '%mothiram%' 
   OR LOWER(`expense_type`) LIKE '%mothgiram%' 
   OR LOWER(`expense_type`) LIKE '%mothiran%' 
   OR LOWER(`expense_type`) LIKE '%uthayan%' 
   OR LOWER(`expense_type`) LIKE '%udayan%';

UPDATE `expenses` SET `category` = 'Purchasing Expense - UDAYAN RING' 
WHERE LOWER(`category`) LIKE '%mothiram%' 
   OR LOWER(`category`) LIKE '%mothgiram%' 
   OR LOWER(`category`) LIKE '%mothiran%' 
   OR LOWER(`category`) LIKE '%uthayan%' 
   OR LOWER(`category`) LIKE '%udayan%';

-- 7. SANKAR ANNA (sanger anna, sanger, SANGAR, sangar anna, SANGER ANNA)
UPDATE `staff_expenses` SET `expense_type` = 'SANKAR ANNA' 
WHERE LOWER(`expense_type`) LIKE '%sanger%' 
   OR LOWER(`expense_type`) LIKE '%sankar%' 
   OR LOWER(`expense_type`) LIKE '%sangar%';

UPDATE `expenses` SET `category` = 'Purchasing Expense - SANKAR ANNA' 
WHERE LOWER(`category`) LIKE '%sanger%' 
   OR LOWER(`category`) LIKE '%sankar%' 
   OR LOWER(`category`) LIKE '%sangar%';

-- 8. SUTHA ANNA (sutha anna, SUTHA, suthu, SUTHU BROCH, SUTHU KAPPU)
UPDATE `staff_expenses` SET `expense_type` = 'SUTHA ANNA' 
WHERE LOWER(`expense_type`) LIKE '%sutha%' 
   OR LOWER(`expense_type`) LIKE '%suthu%';

UPDATE `expenses` SET `category` = 'Purchasing Expense - SUTHA ANNA' 
WHERE LOWER(`category`) LIKE '%sutha%' 
   OR LOWER(`category`) LIKE '%suthu%';

-- 9. ATHAAN THANGA THANNIY (athaan thanga thanniy kasu, aththaan thanga thanniy)
UPDATE `staff_expenses` SET `expense_type` = 'ATHAAN THANGA THANNIY' 
WHERE LOWER(`expense_type`) LIKE '%athaan%' 
   OR LOWER(`expense_type`) LIKE '%aththaan%';

UPDATE `expenses` SET `category` = 'Purchasing Expense - ATHAAN THANGA THANNIY' 
WHERE LOWER(`category`) LIKE '%athaan%' 
   OR LOWER(`category`) LIKE '%aththaan%';

-- 10. RAMESH ANNA (RAMESH ANNA, RAMESH ANNA DAD)
UPDATE `staff_expenses` SET `expense_type` = 'RAMESH ANNA' 
WHERE LOWER(`expense_type`) LIKE '%ramesh%';

UPDATE `expenses` SET `category` = 'Purchasing Expense - RAMESH ANNA' 
WHERE LOWER(`category`) LIKE '%ramesh%';

-- ---------------------------------------------------------------------
-- STEP 3: SYNC ALL HISTORICAL SUPPLIER PAYMENTS TO `handmade_supplier_payments`
-- ---------------------------------------------------------------------

-- Retrieve supplier IDs
SET @mano_anna_id = (SELECT `id` FROM `handmade_suppliers` WHERE `name` = 'MANO ANNA' ORDER BY `id` ASC LIMIT 1);
SET @kaniy_mano_id = (SELECT `id` FROM `handmade_suppliers` WHERE `name` = 'KANIY MANO' ORDER BY `id` ASC LIMIT 1);

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

-- 3. Insert missing payments from staff_expenses into handmade_supplier_payments
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
WHERE se.expense_type IN (
    'MANO ANNA', 'KANIY MANO', 'MARAN THALAI', 'PRAKAS', 'SATHES DAD', 'SATHES ANNA', 
    'UDAYAN RING', 'SANKAR ANNA', 'SUTHA ANNA', 'ATHAAN THANGA THANNIY', 'RAMESH ANNA'
)
AND NOT EXISTS (
    SELECT 1 FROM `handmade_supplier_payments` hsp
    WHERE hsp.supplier_id = s.id 
      AND hsp.payment_date = se.expense_date 
      AND hsp.amount = se.amount
);

-- ---------------------------------------------------------------------
-- STEP 4: NORMALIZE COMMON DAILY EXPENSES IN `staff_expenses` & `expenses`
-- ---------------------------------------------------------------------

-- 1. Salary Payouts
UPDATE `staff_expenses` SET `expense_type` = 'Salary Payout' 
WHERE LOWER(`expense_type`) LIKE '%salary%' 
   OR LOWER(`expense_type`) LIKE '%sallari%' 
   OR LOWER(`expense_type`) LIKE '%saleary%';

UPDATE `expenses` SET `category` = 'Salary Payout' 
WHERE LOWER(`category`) LIKE '%salary%' 
   OR LOWER(`category`) LIKE '%sallari%' 
   OR LOWER(`category`) LIKE '%saleary%';

-- 2. Tea & Refreshment
UPDATE `staff_expenses` SET `expense_type` = 'Tea & Refreshment' 
WHERE `expense_type` IN ('TEE', 'tea', 'TEA', 'shehan tee', 'tee saprin', 'tea saprin', 'TEA AND WATER', 'REFRESH MENT', 'soda', 'kastamear soda', 'soda costemer')
   OR LOWER(`expense_type`) LIKE 'tee %' 
   OR LOWER(`expense_type`) LIKE 'tea %' 
   OR LOWER(`expense_type`) LIKE '% tee' 
   OR LOWER(`expense_type`) LIKE '% tea';

UPDATE `expenses` SET `category` = 'Staff Expense - Tea & Refreshment' 
WHERE `category` LIKE '%TEE%' 
   OR `category` LIKE '%tea%' 
   OR `category` LIKE '%soda%';

-- 3. Staff Meals & Food
UPDATE `staff_expenses` SET `expense_type` = 'Staff Meals' 
WHERE LOWER(`expense_type`) LIKE '%sappadu%' 
   OR LOWER(`expense_type`) LIKE '%sapadu%' 
   OR LOWER(`expense_type`) LIKE '%saapadu%' 
   OR LOWER(`expense_type`) LIKE '%sapado%' 
   OR LOWER(`expense_type`) LIKE '%lunch%' 
   OR LOWER(`expense_type`) LIKE '%puttu%' 
   OR LOWER(`expense_type`) LIKE '%breakfast%';

UPDATE `expenses` SET `category` = 'Staff Expense - Staff Meals' 
WHERE LOWER(`category`) LIKE '%sappadu%' 
   OR LOWER(`category`) LIKE '%sapadu%' 
   OR LOWER(`category`) LIKE '%saapadu%' 
   OR LOWER(`category`) LIKE '%sapado%' 
   OR LOWER(`category`) LIKE '%lunch%' 
   OR LOWER(`category`) LIKE '%puttu%' 
   OR LOWER(`category`) LIKE '%breakfast%';

-- 4. Water
UPDATE `staff_expenses` SET `expense_type` = 'Water' 
WHERE LOWER(`expense_type`) LIKE '%thanniy%' 
   OR LOWER(`expense_type`) LIKE '%thaniiy%' 
   OR LOWER(`expense_type`) LIKE '%water%' 
   OR LOWER(`expense_type`) LIKE '%thaniy%';

UPDATE `expenses` SET `category` = 'Staff Expense - Water' 
WHERE LOWER(`category`) LIKE '%thanniy%' 
   OR LOWER(`category`) LIKE '%thaniiy%' 
   OR LOWER(`category`) LIKE '%water%' 
   OR LOWER(`category`) LIKE '%thaniy%';

-- 5. Transport & Fuel
UPDATE `staff_expenses` SET `expense_type` = 'Transport & Fuel' 
WHERE LOWER(`expense_type`) LIKE '%petrol%' 
   OR LOWER(`expense_type`) LIKE '%petrool%' 
   OR LOWER(`expense_type`) LIKE '%petros%' 
   OR LOWER(`expense_type`) LIKE '%bike%' 
   OR LOWER(`expense_type`) LIKE '%auto%';

UPDATE `expenses` SET `category` = 'Staff Expense - Transport & Fuel' 
WHERE LOWER(`category`) LIKE '%petrol%' 
   OR LOWER(`category`) LIKE '%petrool%' 
   OR LOWER(`category`) LIKE '%bike%' 
   OR LOWER(`category`) LIKE '%auto%';

-- 6. Packaging & Bags
UPDATE `staff_expenses` SET `expense_type` = 'Packaging' 
WHERE LOWER(`expense_type`) LIKE '%bag%' 
   OR LOWER(`expense_type`) LIKE '%pepar%' 
   OR LOWER(`expense_type`) LIKE '%paper%' 
   OR LOWER(`expense_type`) LIKE '%bille role%' 
   OR LOWER(`expense_type`) LIKE '%bille rolle%' 
   OR LOWER(`expense_type`) LIKE '%box%' 
   OR LOWER(`expense_type`) LIKE '%tissue%';

UPDATE `expenses` SET `category` = 'Staff Expense - Packaging' 
WHERE LOWER(`category`) LIKE '%bag%' 
   OR LOWER(`category`) LIKE '%pepar%' 
   OR LOWER(`category`) LIKE '%paper%' 
   OR LOWER(`category`) LIKE '%bille role%' 
   OR LOWER(`category`) LIKE '%box%';

-- 7. Courier & Delivery
UPDATE `staff_expenses` SET `expense_type` = 'Courier & Delivery' 
WHERE LOWER(`expense_type`) LIKE '%dhl%' 
   OR LOWER(`expense_type`) LIKE '%domix%' 
   OR LOWER(`expense_type`) LIKE '%parcel%' 
   OR LOWER(`expense_type`) LIKE '%parcal%' 
   OR LOWER(`expense_type`) LIKE '%parsel%';

UPDATE `expenses` SET `category` = 'Staff Expense - Courier & Delivery' 
WHERE LOWER(`category`) LIKE '%dhl%' 
   OR LOWER(`category`) LIKE '%domix%' 
   OR LOWER(`category`) LIKE '%parcel%';

-- 8. Sadhaka & Charity
UPDATE `staff_expenses` SET `expense_type` = 'Sadhaka' 
WHERE LOWER(`expense_type`) LIKE '%sadhaka%' 
   OR LOWER(`expense_type`) LIKE '%sathaka%' 
   OR LOWER(`expense_type`) LIKE '%donet%' 
   OR LOWER(`expense_type`) LIKE '%jamath%' 
   OR LOWER(`expense_type`) LIKE '%palliy%';

UPDATE `expenses` SET `category` = 'Staff Expense - Sadhaka' 
WHERE LOWER(`category`) LIKE '%sadhaka%' 
   OR LOWER(`category`) LIKE '%sathaka%' 
   OR LOWER(`category`) LIKE '%donet%' 
   OR LOWER(`category`) LIKE '%palliy%';

-- 9. Cleaning & Maintenance
UPDATE `staff_expenses` SET `expense_type` = 'Cleaning & Maintenance' 
WHERE LOWER(`expense_type`) LIKE '%clener%' 
   OR LOWER(`expense_type`) LIKE '%cleener%' 
   OR LOWER(`expense_type`) LIKE '%pancher%' 
   OR LOWER(`expense_type`) LIKE '%repar%';

UPDATE `expenses` SET `category` = 'Staff Expense - Cleaning & Maintenance' 
WHERE LOWER(`category`) LIKE '%clener%' 
   OR LOWER(`category`) LIKE '%cleener%' 
   OR LOWER(`category`) LIKE '%pancher%' 
   OR LOWER(`category`) LIKE '%repar%';

-- ---------------------------------------------------------------------
-- STEP 5: CLEAN AND RESET `expense_categories` TABLE
-- ---------------------------------------------------------------------

-- Remove all old cluttered / duplicated categories
DELETE FROM `expense_categories`;

-- Insert clean, standardized categories
INSERT INTO `expense_categories` (`expense_category`, `type`, `created_at`, `updated_at`) VALUES
-- Daily Expenses (Standard categories for shop daily costs)
('Daily Expenses', 'Tea & Refreshment', NOW(), NOW()),
('Daily Expenses', 'Staff Meals', NOW(), NOW()),
('Daily Expenses', 'Water & Amenities', NOW(), NOW()),
('Daily Expenses', 'Transport & Fuel', NOW(), NOW()),
('Daily Expenses', 'Packaging & Bags', NOW(), NOW()),
('Daily Expenses', 'Stationery & Printing', NOW(), NOW()),
('Daily Expenses', 'Cleaning & Maintenance', NOW(), NOW()),
('Daily Expenses', 'Courier & Delivery', NOW(), NOW()),
('Daily Expenses', 'Sadhaka & Charity', NOW(), NOW()),
('Daily Expenses', 'Utilities (Phone/WiFi/Power)', NOW(), NOW()),
('Daily Expenses', 'Daily Wages & Overtime', NOW(), NOW()),
('Daily Expenses', 'Customer Hospitality', NOW(), NOW()),
('Daily Expenses', 'Other Daily Expenses', NOW(), NOW()),

-- Monthly Expenses (Regular fixed recurring business costs)
('Monthly Expenses', 'Electricity Bill', NOW(), NOW()),
('Monthly Expenses', 'Shop Rent', NOW(), NOW()),
('Monthly Expenses', 'SLT / Internet Bill', NOW(), NOW()),
('Monthly Expenses', 'Telephone Bill', NOW(), NOW()),
('Monthly Expenses', 'Water Bill', NOW(), NOW()),
('Monthly Expenses', 'Accounting & Audit Fees', NOW(), NOW()),
('Monthly Expenses', 'Income Tax & Levies', NOW(), NOW()),
('Monthly Expenses', 'Staff Monthly Salaries', NOW(), NOW()),
('Monthly Expenses', 'Other Monthly Expenses', NOW(), NOW());

COMMIT;
