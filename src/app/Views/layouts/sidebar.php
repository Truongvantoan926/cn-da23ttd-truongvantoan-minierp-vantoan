<?php if (isset($_SESSION['user_id'])): ?>
    <aside class="bg-white border-end p-3 flex-shrink-0" style="width: 240px;">
        <nav class="nav nav-pills flex-column gap-1" aria-label="Điều hướng chính">
            <a class="nav-link" href="<?= BASE_URL ?>/dashboard/index">
                <i class="fa-solid fa-chart-line me-2" aria-hidden="true"></i>
                Dashboard
            </a>
            <div class="mt-3 px-3 text-uppercase text-secondary small fw-semibold">Nhân sự</div>
            <a class="nav-link" href="<?= BASE_URL ?>/department/index">
                <i class="fa-solid fa-building me-2" aria-hidden="true"></i>
                Phòng ban
            </a>
            <a class="nav-link" href="<?= BASE_URL ?>/employee/index">
                <i class="fa-solid fa-user-group me-2" aria-hidden="true"></i>
                Danh sách Nhân viên
            </a>
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
            <div class="mt-4 px-3 text-uppercase text-secondary small fw-semibold">Đối tác (CRM)</div>
            <a class="nav-link" href="<?= BASE_URL ?>/customer/index">
                <i class="fa-solid fa-users me-2" aria-hidden="true"></i>
                Khách hàng
            </a>
            <a class="nav-link" href="<?= BASE_URL ?>/supplier/index">
                <i class="fa-solid fa-truck-field me-2" aria-hidden="true"></i>
                Nhà cung cấp
            </a>
            <div class="mt-4 px-3 text-uppercase text-secondary small fw-semibold">Kế toán nội bộ</div>
            <a class="nav-link" href="<?= BASE_URL ?>/finance/index">
                <i class="fa-solid fa-money-bill-transfer me-2" aria-hidden="true"></i>
                Sổ quỹ Thu/Chi
            </a>
        </nav>
    </aside>
<?php endif; ?>
