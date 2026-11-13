ALTER TABLE suppliers
    ADD COLUMN bank_name VARCHAR(120) NULL AFTER email,
    ADD COLUMN bank_account VARCHAR(50) NULL AFTER bank_name,
    ADD COLUMN account_holder VARCHAR(180) NULL AFTER bank_account;

ALTER TABLE customers
    ADD COLUMN bank_name VARCHAR(120) NULL AFTER email,
    ADD COLUMN bank_account VARCHAR(50) NULL AFTER bank_name,
    ADD COLUMN account_holder VARCHAR(180) NULL AFTER bank_account;

ALTER TABLE inventory_receipts
    ADD COLUMN payment_status ENUM('pending', 'paid') NOT NULL DEFAULT 'pending' AFTER note,
    ADD COLUMN paid_at DATETIME NULL AFTER payment_status,
    ADD KEY idx_inventory_receipts_payment_status (payment_status, created_at);

UPDATE suppliers
SET email = COALESCE(NULLIF(email, ''), CONCAT('supplier', id, '@example.com')),
    bank_name = COALESCE(NULLIF(bank_name, ''), 'Vietcombank'),
    bank_account = COALESCE(NULLIF(bank_account, ''), CONCAT('000000', LPAD(id, 6, '0'))),
    account_holder = COALESCE(NULLIF(account_holder, ''), name);

UPDATE customers
SET email = COALESCE(NULLIF(email, ''), CONCAT('customer', id, '@example.com')),
    bank_name = COALESCE(NULLIF(bank_name, ''), 'Vietcombank'),
    bank_account = COALESCE(NULLIF(bank_account, ''), CONCAT('000000', LPAD(id, 6, '0'))),
    account_holder = COALESCE(NULLIF(account_holder, ''), name);
