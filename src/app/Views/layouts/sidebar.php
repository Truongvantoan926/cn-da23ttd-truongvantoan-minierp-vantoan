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

    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $requestPath = is_string($requestPath) ? $requestPath : '/';
    $basePath = parse_url(BASE_URL, PHP_URL_PATH);
    if (
        is_string($basePath)
        && $basePath !== ''
        && str_starts_with($requestPath, rtrim($basePath, '/') . '/')
    ) {
        $requestPath = substr($requestPath, strlen(rtrim($basePath, '/')));
    }
    $currentModule = explode('/', trim($requestPath, '/'))[0] ?? 'dashboard';
    $isActive = static fn (string $module): bool => $currentModule === $module;
    ?>
    <aside
        class="offcanvas-md offcanvas-start app-sidebar flex-shrink-0"
        tabindex="-1"
        id="mainSidebar"
        aria-labelledby="mainSidebarLabel"
        style="width: 260px; max-width: 85vw;"
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
        <div class="offcanvas-body">
            <nav class="nav flex-column" aria-label="Điều hướng chính">
                <section class="sidebar-group">
                    <h2 class="sidebar-section-title">TỔNG QUAN</h2>
                    <a
                        class="nav-link<?= $isActive('dashboard') ? ' active' : '' ?>"
                        href="<?= BASE_URL ?>/dashboard/index"
                        <?= $isActive('dashboard') ? 'aria-current="page"' : '' ?>
                    >
                        <i class="fa-solid fa-chart-line me-2" aria-hidden="true"></i>
                        Dashboard
                    </a>
                </section>

                <?php if ($canViewInventory): ?>
                    <section class="sidebar-group">
                        <h2 class="sidebar-section-title">KHO HÀNG &amp; SẢN PHẨM</h2>
                        <a
                            class="nav-link<?= $isActive('category') ? ' active' : '' ?>"
                            href="<?= BASE_URL ?>/category/index"
                            <?= $isActive('category') ? 'aria-current="page"' : '' ?>
                        >
                            <i class="fa-solid fa-tags me-2" aria-hidden="true"></i>
                            Danh mục
                        </a>
                        <a
                            class="nav-link<?= $isActive('product') ? ' active' : '' ?>"
                            href="<?= BASE_URL ?>/product/index"
                            <?= $isActive('product') ? 'aria-current="page"' : '' ?>
                        >
                            <i class="fa-solid fa-box-open me-2" aria-hidden="true"></i>
                            Sản phẩm
                        </a>
                        <a
                            class="nav-link<?= $isActive('receipt') ? ' active' : '' ?>"
                            href="<?= BASE_URL ?>/receipt/index"
                            <?= $isActive('receipt') ? 'aria-current="page"' : '' ?>
                        >
                            <i class="fa-solid fa-arrow-right-arrow-left me-2" aria-hidden="true"></i>
                            Giao dịch kho
                        </a>
                    </section>
                <?php endif; ?>

                <?php if ($canViewCrm): ?>
                    <section class="sidebar-group">
                        <h2 class="sidebar-section-title">ĐỐI TÁC (CRM)</h2>
                        <a
                            class="nav-link<?= $isActive('customer') ? ' active' : '' ?>"
                            href="<?= BASE_URL ?>/customer/index"
                            <?= $isActive('customer') ? 'aria-current="page"' : '' ?>
                        >
                            <i class="fa-solid fa-users me-2" aria-hidden="true"></i>
                            Khách hàng
                        </a>
                        <a
                            class="nav-link<?= $isActive('supplier') ? ' active' : '' ?>"
                            href="<?= BASE_URL ?>/supplier/index"
                            <?= $isActive('supplier') ? 'aria-current="page"' : '' ?>
                        >
                            <i class="fa-solid fa-truck-field me-2" aria-hidden="true"></i>
                            Nhà cung cấp
                        </a>
                    </section>
                <?php endif; ?>

                <?php if ($canViewFinance): ?>
                    <section class="sidebar-group">
                        <h2 class="sidebar-section-title">KẾ TOÁN &amp; TÀI CHÍNH</h2>
                        <a
                            class="nav-link<?= $isActive('finance') ? ' active' : '' ?>"
                            href="<?= BASE_URL ?>/finance/index"
                            <?= $isActive('finance') ? 'aria-current="page"' : '' ?>
                        >
                            <i class="fa-solid fa-money-bill-transfer me-2" aria-hidden="true"></i>
                            Sổ quỹ Thu/Chi
                        </a>
                    </section>
                <?php endif; ?>

                <?php if ($canViewHr || $canManageUsers || $canViewAudit): ?>
                    <section class="sidebar-group">
                        <h2 class="sidebar-section-title">NHÂN SỰ &amp; QUẢN TRỊ</h2>
                        <?php if ($canViewHr): ?>
                            <a
                                class="nav-link<?= $isActive('department') ? ' active' : '' ?>"
                                href="<?= BASE_URL ?>/department/index"
                                <?= $isActive('department') ? 'aria-current="page"' : '' ?>
                            >
                                <i class="fa-solid fa-building me-2" aria-hidden="true"></i>
                                Phòng ban
                            </a>
                            <a
                                class="nav-link<?= $isActive('employee') ? ' active' : '' ?>"
                                href="<?= BASE_URL ?>/employee/index"
                                <?= $isActive('employee') ? 'aria-current="page"' : '' ?>
                            >
                                <i class="fa-solid fa-user-group me-2" aria-hidden="true"></i>
                                Danh sách Nhân viên
                            </a>
                        <?php endif; ?>
                        <?php if ($canManageUsers): ?>
                            <a
                                class="nav-link<?= $isActive('user') ? ' active' : '' ?>"
                                href="<?= BASE_URL ?>/user/index"
                                <?= $isActive('user') ? 'aria-current="page"' : '' ?>
                            >
                                <i class="fa-solid fa-user-shield me-2" aria-hidden="true"></i>
                                Quản lý tài khoản
                            </a>
                        <?php endif; ?>
                        <?php if ($canViewAudit): ?>
                            <a
                                class="nav-link<?= $isActive('log') ? ' active' : '' ?>"
                                href="<?= BASE_URL ?>/log/index"
                                <?= $isActive('log') ? 'aria-current="page"' : '' ?>
                            >
                                <i class="fa-solid fa-clipboard-list me-2" aria-hidden="true"></i>
                                Nhật ký hệ thống
                            </a>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
            </nav>
        </div>
    </aside>
<?php endif; ?>
