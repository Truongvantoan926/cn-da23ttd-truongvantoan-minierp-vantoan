<?php if (isset($_SESSION['user_id'])): ?>
    <?php
    $hasPermission = static fn (string $permission): bool =>
        \App\Core\AuthMiddleware::hasPermission($permission);
    $canViewHr = $hasPermission('hr.view');
    $canViewInventory = $hasPermission('inventory.view');
    $canViewCrm = $hasPermission('crm.view');
    $canViewFinance = $hasPermission('finance.view');
    $canViewAudit = $hasPermission('audit.view');
    $canManageUsers = $hasPermission('auth.manage_users');
    ?>
    <aside
        class="offcanvas-md offcanvas-start bg-white border-end flex-shrink-0"
        tabindex="-1"
        id="mainSidebar"
        aria-labelledby="mainSidebarLabel"
        style="width: 240px; max-width: 85vw;"
    >
        <div class="offcanvas-header d-md-none border-bottom">
            <h2 class="offcanvas-title h5 mb-0" id="mainSidebarLabel">Điều hướng</h2>
            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="offcanvas"
                data-bs-target="#mainSidebar"
                aria-label="Đóng menu điều hướng"
            ></button>
        </div>
        <div class="offcanvas-body p-3">
            <nav class="nav nav-pills flex-column gap-1" aria-label="Điều hướng chính">
                <a class="nav-link" href="<?= BASE_URL ?>/dashboard/index">
                    <i class="fa-solid fa-chart-line me-2" aria-hidden="true"></i>
                    Dashboard
                </a>
                <?php if ($canViewHr): ?>
                    <div class="mt-3 px-3 text-uppercase text-secondary small fw-semibold">Nhân sự</div>
                    <a class="nav-link" href="<?= BASE_URL ?>/department/index">
                        <i class="fa-solid fa-building me-2" aria-hidden="true"></i>
                        Phòng ban
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>/employee/index">
                        <i class="fa-solid fa-user-group me-2" aria-hidden="true"></i>
                        Danh sách Nhân viên
                    </a>
                <?php endif; ?>
                <?php if ($canViewInventory): ?>
                    <div class="mt-4 px-3 text-uppercase text-secondary small fw-semibold">Kho hàng</div>
                    <a class="nav-link" href="<?= BASE_URL ?>/category/index">
                        <i class="fa-solid fa-tags me-2" aria-hidden="true"></i>
                        Danh mục
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>/product/index">
                        <i class="fa-solid fa-box-open me-2" aria-hidden="true"></i>
                        Sản phẩm
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>/receipt/index">
                        <i class="fa-solid fa-arrow-right-arrow-left me-2" aria-hidden="true"></i>
                        Giao dịch kho
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>/warranty/index">
                        <i class="fa-solid fa-shield-halved me-2" aria-hidden="true"></i>
                        Bảo hành &amp; IMEI
                    </a>
                <?php endif; ?>
                <?php if ($canViewCrm): ?>
                    <div class="mt-4 px-3 text-uppercase text-secondary small fw-semibold">Đối tác (CRM)</div>
                    <a class="nav-link" href="<?= BASE_URL ?>/customer/index">
                        <i class="fa-solid fa-users me-2" aria-hidden="true"></i>
                        Khách hàng
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>/supplier/index">
                        <i class="fa-solid fa-truck-field me-2" aria-hidden="true"></i>
                        Nhà cung cấp
                    </a>
                <?php endif; ?>
                <?php if ($canViewFinance): ?>
                    <div class="mt-4 px-3 text-uppercase text-secondary small fw-semibold">Kế toán nội bộ</div>
                    <a class="nav-link" href="<?= BASE_URL ?>/finance/index">
                        <i class="fa-solid fa-money-bill-transfer me-2" aria-hidden="true"></i>
                        Sổ quỹ Thu/Chi
                    </a>
                <?php endif; ?>
                <?php if ($canViewAudit || $canManageUsers): ?>
                    <div class="mt-4 px-3 text-uppercase text-secondary small fw-semibold">Quản trị</div>
                    <?php if ($canManageUsers): ?>
                        <a class="nav-link" href="<?= BASE_URL ?>/user/index">
                            <i class="fa-solid fa-user-shield me-2" aria-hidden="true"></i>
                            Quản lý tài khoản
                        </a>
                    <?php endif; ?>
                    <?php if ($canViewAudit): ?>
                        <a class="nav-link" href="<?= BASE_URL ?>/log/index">
                            <i class="fa-solid fa-clipboard-list me-2" aria-hidden="true"></i>
                            Nhật ký hệ thống
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>
        </div>
    </aside>
<?php endif; ?>
