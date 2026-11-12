<main class="container-fluid flex-grow-1 p-4 p-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Danh sách nhân viên</h1>
            <p class="text-secondary mb-0">Quản lý hồ sơ nhân viên</p>
        </div>
        <a class="btn btn-primary" href="<?= BASE_URL ?>/employee/create">
            <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
            Thêm nhân viên
        </a>
    </div>

    <?php if (isset($error) && is_string($error) && $error !== ''): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php
    $statusLabels = [
        'active' => 'Đang làm việc',
        'inactive' => 'Đã nghỉ',
        'on_leave' => 'Nghỉ phép',
    ];
    $statusClasses = [
        'active' => 'text-bg-success',
        'inactive' => 'text-bg-secondary',
        'on_leave' => 'text-bg-warning',
    ];
    ?>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-4">Mã NV</th>
                        <th scope="col">Họ tên</th>
                        <th scope="col">Phòng ban</th>
                        <th scope="col">Chức vụ</th>
                        <th scope="col">SĐT</th>
                        <th scope="col">Email</th>
                        <th scope="col">Ngày vào làm</th>
                        <th scope="col">Trạng thái</th>
                        <th scope="col" class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($employees === []): ?>
                        <tr>
                            <td colspan="9" class="text-center text-secondary py-5">
                                Chưa có nhân viên nào.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($employees as $employee): ?>
                            <?php
                            $status = (string) $employee['status'];
                            $statusLabel = $statusLabels[$status] ?? $status;
                            $statusClass = $statusClasses[$status] ?? 'text-bg-secondary';
                            ?>
                            <tr>
                                <td class="ps-4 fw-semibold">
                                    <?= htmlspecialchars((string) $employee['code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </td>
                                <td><?= htmlspecialchars((string) $employee['full_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($employee['department_name'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($employee['position'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($employee['phone'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($employee['email'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($employee['hire_date'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td>
                                    <span class="badge <?= htmlspecialchars($statusClass, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                        <?= htmlspecialchars($statusLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4 text-nowrap">
                                    <a
                                        class="btn btn-sm btn-outline-primary me-1"
                                        href="<?= BASE_URL ?>/employee/edit/<?= rawurlencode((string) $employee['id']) ?>"
                                        aria-label="Sửa nhân viên <?= htmlspecialchars((string) $employee['full_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    >
                                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                        <span class="d-none d-md-inline ms-1">Sửa</span>
                                    </a>
                                    <form
                                        class="d-inline"
                                        action="<?= BASE_URL ?>/employee/delete/<?= rawurlencode((string) $employee['id']) ?>"
                                        method="POST"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa nhân viên này?');"
                                    >
                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                        >
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                            <span class="d-none d-md-inline ms-1">Xóa</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php require $paginationViewPath; ?>
</main>
