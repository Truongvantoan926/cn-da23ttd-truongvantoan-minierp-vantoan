SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS modules (
    code VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_enabled TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (code),
    CONSTRAINT chk_modules_is_enabled CHECK (is_enabled IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS roles (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    level TINYINT UNSIGNED NOT NULL,
    data_scope ENUM('own', 'department', 'all') NOT NULL DEFAULT 'own',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_name (name),
    KEY idx_roles_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
    code VARCHAR(100) NOT NULL,
    module_code VARCHAR(50) NOT NULL,
    name VARCHAR(120) NOT NULL,
    PRIMARY KEY (code),
    KEY idx_permissions_module (module_code),
    CONSTRAINT fk_permissions_module
        FOREIGN KEY (module_code) REFERENCES modules (code)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_code VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (role_id, permission_code),
    KEY idx_role_permissions_permission (permission_code),
    CONSTRAINT fk_role_permissions_role
        FOREIGN KEY (role_id) REFERENCES roles (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_role_permissions_permission
        FOREIGN KEY (permission_code) REFERENCES permissions (code)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS departments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(30) NOT NULL,
    name VARCHAR(120) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_departments_code (code),
    UNIQUE KEY uq_departments_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS employees (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(30) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    dob DATE NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(190) NULL,
    department_id BIGINT UNSIGNED NULL,
    position VARCHAR(100) NULL,
    base_salary DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    hire_date DATE NULL,
    status ENUM('active', 'inactive', 'on_leave') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_employees_code (code),
    UNIQUE KEY uq_employees_email (email),
    KEY idx_employees_department_status (department_id, status),
    KEY idx_employees_full_name (full_name),
    CONSTRAINT fk_employees_department
        FOREIGN KEY (department_id) REFERENCES departments (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_employees_base_salary CHECK (base_salary >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(80) NOT NULL,
    full_name VARCHAR(150) NULL,
    phone VARCHAR(30) NULL,
    password_hash VARCHAR(255) NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    employee_id BIGINT UNSIGNED NULL,
    status ENUM('active', 'inactive', 'locked') NOT NULL DEFAULT 'active',
    last_login DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_employee (employee_id),
    KEY idx_users_role_status (role_id, status),
    CONSTRAINT fk_users_role
        FOREIGN KEY (role_id) REFERENCES roles (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_users_employee
        FOREIGN KEY (employee_id) REFERENCES employees (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100) NOT NULL,
    record_id VARCHAR(100) NULL,
    old_data JSON NULL,
    new_data JSON NULL,
    ip VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_logs_user_created (user_id, created_at),
    KEY idx_audit_logs_table_record (table_name, record_id),
    KEY idx_audit_logs_created (created_at),
    CONSTRAINT fk_audit_logs_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_activity_logs_user_created (user_id, created_at),
    KEY idx_activity_logs_created (created_at, id),
    CONSTRAINT fk_activity_logs_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,
    role_id BIGINT UNSIGNED NULL,
    type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_notifications_user_read_created (user_id, is_read, created_at),
    KEY idx_notifications_role_read_created (role_id, is_read, created_at),
    CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_notifications_role
        FOREIGN KEY (role_id) REFERENCES roles (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT chk_notifications_is_read CHECK (is_read IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attendance (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    work_date DATE NOT NULL,
    check_in DATETIME NULL,
    check_out DATETIME NULL,
    status ENUM('present', 'absent', 'late', 'half_day', 'leave') NOT NULL DEFAULT 'present',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_attendance_employee_date (employee_id, work_date),
    KEY idx_attendance_work_date_status (work_date, status),
    CONSTRAINT fk_attendance_employee
        FOREIGN KEY (employee_id) REFERENCES employees (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_attendance_time_order CHECK (
        check_in IS NULL OR check_out IS NULL OR check_out >= check_in
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leaves (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    from_date DATE NOT NULL,
    to_date DATE NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    approved_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_leaves_employee_dates (employee_id, from_date, to_date),
    KEY idx_leaves_status_dates (status, from_date, to_date),
    KEY idx_leaves_approved_by (approved_by),
    CONSTRAINT fk_leaves_employee
        FOREIGN KEY (employee_id) REFERENCES employees (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_leaves_approved_by
        FOREIGN KEY (approved_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_leaves_date_order CHECK (to_date >= from_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payrolls (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    `month` DATE NOT NULL,
    base DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    allowance DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    deduction DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    net DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status ENUM('draft', 'approved', 'paid', 'cancelled') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_payrolls_employee_month (employee_id, `month`),
    KEY idx_payrolls_month_status (`month`, status),
    CONSTRAINT fk_payrolls_employee
        FOREIGN KEY (employee_id) REFERENCES employees (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_payrolls_amounts CHECK (
        base >= 0 AND allowance >= 0 AND deduction >= 0 AND net >= 0
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    description TEXT NULL,
    parent_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_name_parent (name, parent_id),
    KEY idx_categories_parent (parent_id),
    CONSTRAINT fk_categories_parent
        FOREIGN KEY (parent_id) REFERENCES categories (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    sku VARCHAR(60) NOT NULL,
    name VARCHAR(180) NOT NULL,
    category_id BIGINT UNSIGNED NULL,
    unit VARCHAR(30) NOT NULL,
    cost_price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    sale_price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    min_stock DECIMAL(15,3) NOT NULL DEFAULT 0.000,
    stock_qty DECIMAL(15,3) NOT NULL DEFAULT 0.000,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_products_sku (sku),
    KEY idx_products_category_status (category_id, status),
    KEY idx_products_name (name),
    KEY idx_products_stock_qty (stock_qty),
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_products_prices CHECK (cost_price >= 0 AND sale_price >= 0),
    CONSTRAINT chk_products_stock CHECK (min_stock >= 0 AND stock_qty >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_images (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_main TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_product_images_product_main (product_id, is_main, id),
    CONSTRAINT fk_product_images_product
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT chk_product_images_is_main CHECK (is_main IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS suppliers (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(30) NOT NULL,
    name VARCHAR(180) NOT NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(190) NULL,
    bank_name VARCHAR(120) NULL,
    bank_account VARCHAR(50) NULL,
    account_holder VARCHAR(180) NULL,
    address VARCHAR(255) NULL,
    tax_code VARCHAR(40) NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_suppliers_code (code),
    KEY idx_suppliers_name (name),
    KEY idx_suppliers_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS purchase_receipts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(40) NOT NULL,
    supplier_id BIGINT UNSIGNED NOT NULL,
    total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    paid_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status ENUM('draft', 'received', 'cancelled') NOT NULL DEFAULT 'draft',
    received_at DATETIME NULL,
    created_by BIGINT UNSIGNED NULL,
    note TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_purchase_receipts_code (code),
    KEY idx_purchase_receipts_supplier_date (supplier_id, created_at),
    KEY idx_purchase_receipts_status_date (status, created_at),
    KEY idx_purchase_receipts_created_by (created_by),
    CONSTRAINT fk_purchase_receipts_supplier
        FOREIGN KEY (supplier_id) REFERENCES suppliers (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_purchase_receipts_created_by
        FOREIGN KEY (created_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_purchase_receipts_amounts CHECK (
        total >= 0 AND paid_amount >= 0 AND paid_amount <= total
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS purchase_receipt_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    purchase_receipt_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    qty DECIMAL(15,3) NOT NULL,
    unit_cost DECIMAL(15,2) NOT NULL,
    line_total DECIMAL(15,2) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_purchase_receipt_items_product (purchase_receipt_id, product_id),
    KEY idx_purchase_receipt_items_product (product_id),
    CONSTRAINT fk_purchase_receipt_items_receipt
        FOREIGN KEY (purchase_receipt_id) REFERENCES purchase_receipts (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_purchase_receipt_items_product
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_purchase_receipt_items_amounts CHECK (
        qty > 0 AND unit_cost >= 0 AND line_total >= 0
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stock_movements (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    type ENUM('IN', 'OUT') NOT NULL,
    qty DECIMAL(15,3) NOT NULL,
    ref_type VARCHAR(50) NULL,
    ref_id BIGINT UNSIGNED NULL,
    note TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_stock_movements_product_created (product_id, created_at),
    KEY idx_stock_movements_type_created (type, created_at),
    KEY idx_stock_movements_reference (ref_type, ref_id),
    KEY idx_stock_movements_created_by (created_by),
    CONSTRAINT fk_stock_movements_product
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_stock_movements_created_by
        FOREIGN KEY (created_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_stock_movements_qty CHECK (qty > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customers (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(30) NOT NULL,
    name VARCHAR(180) NOT NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(190) NULL,
    bank_name VARCHAR(120) NULL,
    bank_account VARCHAR(50) NULL,
    account_holder VARCHAR(180) NULL,
    address VARCHAR(255) NULL,
    type ENUM('individual', 'business') NOT NULL DEFAULT 'business',
    assigned_to BIGINT UNSIGNED NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_customers_code (code),
    KEY idx_customers_assigned_status (assigned_to, status),
    KEY idx_customers_name (name),
    KEY idx_customers_phone (phone),
    KEY idx_customers_email (email),
    CONSTRAINT fk_customers_assigned_to
        FOREIGN KEY (assigned_to) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventory_receipts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(20) NOT NULL,
    type ENUM('IN', 'OUT') NOT NULL,
    partner_id BIGINT UNSIGNED NULL,
    employee_id BIGINT UNSIGNED NULL,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    note TEXT NULL,
    payment_status ENUM('pending', 'paid') NOT NULL DEFAULT 'pending',
    paid_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_inventory_receipts_code (code),
    KEY idx_inventory_receipts_type_date (type, created_at),
    KEY idx_inventory_receipts_payment_status (payment_status, created_at),
    KEY idx_inventory_receipts_partner (type, partner_id),
    KEY idx_inventory_receipts_employee (employee_id),
    CONSTRAINT fk_inventory_receipts_employee
        FOREIGN KEY (employee_id) REFERENCES employees (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_inventory_receipts_total_amount CHECK (total_amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventory_receipt_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    receipt_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    qty INT UNSIGNED NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    total DECIMAL(15,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_inventory_receipt_items_receipt (receipt_id),
    KEY idx_inventory_receipt_items_product (product_id),
    CONSTRAINT fk_inventory_receipt_items_receipt
        FOREIGN KEY (receipt_id) REFERENCES inventory_receipts (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_inventory_receipt_items_product
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_inventory_receipt_items_values CHECK (
        qty > 0 AND price >= 0 AND total >= 0
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_serials (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    serial_number VARCHAR(100) NOT NULL,
    receipt_in_id BIGINT UNSIGNED NULL,
    receipt_out_id BIGINT UNSIGNED NULL,
    status ENUM('in_stock', 'sold', 'warranty') NOT NULL DEFAULT 'in_stock',
    warranty_months INT UNSIGNED NOT NULL DEFAULT 12,
    sold_date DATE NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_product_serials_serial_number (serial_number),
    KEY idx_product_serials_product_status (product_id, status),
    KEY idx_product_serials_receipt_in (receipt_in_id),
    KEY idx_product_serials_receipt_out (receipt_out_id),
    CONSTRAINT fk_product_serials_product
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_product_serials_receipt_in
        FOREIGN KEY (receipt_in_id) REFERENCES inventory_receipts (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_product_serials_receipt_out
        FOREIGN KEY (receipt_out_id) REFERENCES inventory_receipts (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_interactions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    type ENUM('call', 'email', 'meeting', 'note', 'other') NOT NULL DEFAULT 'note',
    subject VARCHAR(180) NULL,
    content TEXT NOT NULL,
    interacted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_customer_interactions_customer_date (customer_id, interacted_at),
    KEY idx_customer_interactions_user_date (user_id, interacted_at),
    CONSTRAINT fk_customer_interactions_customer
        FOREIGN KEY (customer_id) REFERENCES customers (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_customer_interactions_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS opportunities (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    value DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    stage ENUM('lead', 'qualified', 'proposal', 'negotiation', 'won', 'lost') NOT NULL DEFAULT 'lead',
    owner_id BIGINT UNSIGNED NULL,
    expected_close_date DATE NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_opportunities_customer (customer_id),
    KEY idx_opportunities_owner_stage (owner_id, stage),
    KEY idx_opportunities_stage_close (stage, expected_close_date),
    CONSTRAINT fk_opportunities_customer
        FOREIGN KEY (customer_id) REFERENCES customers (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_opportunities_owner
        FOREIGN KEY (owner_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_opportunities_value CHECK (value >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(40) NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    paid_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status ENUM('draft', 'confirmed', 'cancelled') NOT NULL DEFAULT 'draft',
    created_by BIGINT UNSIGNED NULL,
    order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    note TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_orders_code (code),
    KEY idx_orders_customer_date (customer_id, order_date),
    KEY idx_orders_status_date (status, order_date),
    KEY idx_orders_created_by (created_by),
    CONSTRAINT fk_orders_customer
        FOREIGN KEY (customer_id) REFERENCES customers (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_orders_created_by
        FOREIGN KEY (created_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_orders_amounts CHECK (
        total >= 0 AND paid_amount >= 0 AND paid_amount <= total
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    qty DECIMAL(15,3) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    line_total DECIMAL(15,2) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_order_items_product (order_id, product_id),
    KEY idx_order_items_product (product_id),
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_order_items_amounts CHECK (
        qty > 0 AND unit_price >= 0 AND line_total >= 0
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS transactions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(20) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    category VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    amount DECIMAL(15,2) NOT NULL,
    ref_type VARCHAR(50) NULL,
    ref_id BIGINT UNSIGNED NULL,
    payment_method VARCHAR(50) NOT NULL DEFAULT 'Chuyển khoản',
    note TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    trans_date DATE NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_transactions_code (code),
    KEY idx_transactions_type_date (type, trans_date),
    KEY idx_transactions_category_date (category, trans_date),
    KEY idx_transactions_reference (ref_type, ref_id),
    KEY idx_transactions_created_by (created_by),
    CONSTRAINT fk_transactions_created_by
        FOREIGN KEY (created_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_transactions_amount CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO modules (code, name, is_enabled) VALUES
    ('auth', 'Tài khoản và phân quyền', 1),
    ('hr', 'Nhân sự', 1),
    ('inventory', 'Kho hàng và chuỗi cung ứng', 1),
    ('finance', 'Kế toán nội bộ', 1),
    ('crm', 'Quản lý khách hàng', 1),
    ('dashboard', 'Bảng điều khiển', 1);

INSERT IGNORE INTO roles (name, level, data_scope) VALUES
    ('admin', 5, 'all'),
    ('manager', 4, 'department'),
    ('accountant', 3, 'all'),
    ('warehouse', 3, 'all'),
    ('hr', 3, 'department'),
    ('sales', 2, 'own');

INSERT IGNORE INTO permissions (code, module_code, name) VALUES
    ('auth.manage_users', 'auth', 'Quản lý tài khoản'),
    ('auth.manage_roles', 'auth', 'Quản lý vai trò và quyền'),
    ('hr.view', 'hr', 'Xem dữ liệu nhân sự'),
    ('hr.manage', 'hr', 'Quản lý nhân sự và chấm công'),
    ('hr.manage_payroll', 'hr', 'Quản lý bảng lương'),
    ('inventory.view', 'inventory', 'Xem kho hàng'),
    ('inventory.manage_products', 'inventory', 'Quản lý sản phẩm'),
    ('inventory.manage_receipts', 'inventory', 'Quản lý phiếu nhập'),
    ('inventory.manage_stock', 'inventory', 'Quản lý biến động kho'),
    ('finance.view', 'finance', 'Xem kế toán nội bộ'),
    ('finance.manage', 'finance', 'Quản lý giao dịch kế toán'),
    ('crm.view', 'crm', 'Xem dữ liệu khách hàng'),
    ('crm.manage_customers', 'crm', 'Quản lý khách hàng'),
    ('crm.manage_opportunities', 'crm', 'Quản lý cơ hội bán hàng'),
    ('crm.manage_orders', 'crm', 'Quản lý đơn hàng'),
    ('dashboard.view', 'dashboard', 'Xem bảng điều khiển');

INSERT IGNORE INTO role_permissions (role_id, permission_code)
SELECT r.id, p.code
FROM roles AS r
CROSS JOIN permissions AS p
WHERE r.name = 'admin';

INSERT IGNORE INTO role_permissions (role_id, permission_code)
SELECT r.id, p.code
FROM roles AS r
CROSS JOIN permissions AS p
WHERE r.name = 'manager'
  AND p.code IN (
      'hr.view', 'inventory.view', 'finance.view', 'crm.view', 'dashboard.view'
  );

INSERT IGNORE INTO role_permissions (role_id, permission_code)
SELECT r.id, p.code
FROM roles AS r
CROSS JOIN permissions AS p
WHERE r.name = 'accountant'
  AND p.code IN ('finance.view', 'finance.manage', 'dashboard.view');

INSERT IGNORE INTO role_permissions (role_id, permission_code)
SELECT r.id, p.code
FROM roles AS r
CROSS JOIN permissions AS p
WHERE r.name = 'warehouse'
  AND p.code IN (
      'inventory.view', 'inventory.manage_products',
      'inventory.manage_receipts', 'inventory.manage_stock', 'dashboard.view'
  );

INSERT IGNORE INTO role_permissions (role_id, permission_code)
SELECT r.id, p.code
FROM roles AS r
CROSS JOIN permissions AS p
WHERE r.name = 'hr'
  AND p.code IN ('hr.view', 'hr.manage', 'hr.manage_payroll', 'dashboard.view');

INSERT IGNORE INTO role_permissions (role_id, permission_code)
SELECT r.id, p.code
FROM roles AS r
CROSS JOIN permissions AS p
WHERE r.name = 'sales'
  AND p.code IN (
      'crm.view', 'crm.manage_customers', 'crm.manage_opportunities',
      'crm.manage_orders', 'dashboard.view'
  );