<?php if (isset($_SESSION['user_id'])): ?>
    <?php
    $roleId = filter_var($_SESSION['role_id'] ?? null, FILTER_VALIDATE_INT);
    $isAdminOrManager = in_array($roleId, [1, 2], true);
    $canAccessHr = $isAdminOrManager || $roleId === 5;
    $canAccessInventory = in_array($roleId, [1, 2, 3, 4, 6], true);
    $canManageInventory = in_array($roleId, [1, 2, 4], true);
    $canViewInventoryReceipts = in_array($roleId, [1, 2, 3, 4], true);
    $canViewProducts = in_array($roleId, [1, 2, 3, 4, 6], true);
    $canAccessCrm = in_array($roleId, [1, 2, 3, 4, 6], true);
    $canAccessFinance = in_array($roleId, [1, 2, 3], true);
    ?>
    <aside class="bg-white border-end p-3 flex-shrink-0" style="width: 240px;">
        <nav class="nav nav-pills flex-column gap-1" aria-label="Điều hướng chính">
            <a class="nav-link" href="<?= BASE_URL ?>/dashboard/index">
                <i class="fa-solid fa-chart-line me-2" aria-hidden="true"></i>
                Dashboard
            </a>
            <?php if ($canAccessHr): ?>
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
            <?php if ($canAccessInventory): ?>
            <div class="mt-4 px-3 text-uppercase text-secondary small fw-semibold">Kho hàng</div>
            <?php if ($canManageInventory || $roleId === 3): ?>
            <a class="nav-link" href="<?= BASE_URL ?>/category/index">
                <i class="fa-solid fa-tags me-2" aria-hidden="true"></i>
                Danh mục
            </a>
            <?php endif; ?>
            <?php if ($canViewProducts): ?>
            <a class="nav-link" href="<?= BASE_URL ?>/product/index">
                <i class="fa-solid fa-box-open me-2" aria-hidden="true"></i>
                Sản phẩm
            </a>
            <?php endif; ?>
            <?php if ($canViewInventoryReceipts): ?>
            <a class="nav-link" href="<?= BASE_URL ?>/receipt/index">
                <i class="fa-solid fa-arrow-right-arrow-left me-2" aria-hidden="true"></i>
                Giao dịch kho
            </a>
            <?php endif; ?>
            <?php endif; ?>
            <?php if ($canAccessCrm): ?>
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
            <?php if ($canAccessFinance): ?>
            <div class="mt-4 px-3 text-uppercase text-secondary small fw-semibold">Kế toán nội bộ</div>
            <a class="nav-link" href="<?= BASE_URL ?>/finance/index">
                <i class="fa-solid fa-money-bill-transfer me-2" aria-hidden="true"></i>
                Sổ quỹ Thu/Chi
            </a>
            <?php endif; ?>
        </nav>
    </aside>
<?php endif; ?>
