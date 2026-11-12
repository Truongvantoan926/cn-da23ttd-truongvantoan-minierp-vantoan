<main class="container-fluid flex-grow-1 p-4 p-lg-5">
    <div class="mb-4">
        <a class="text-decoration-none" href="<?= BASE_URL ?>/department/index">
            <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>
            Danh sách phòng ban
        </a>
        <h1 class="h3 mt-3 mb-1">
            <?= htmlspecialchars((string) $department['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </h1>
        <p class="text-secondary mb-0">
            Mã phòng ban:
            <?= htmlspecialchars((string) $department['code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </p>
    </div>

    <section class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h2 class="h5 mb-0">Danh sách nhân sự</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-4">Mã NV</th>
                        <th scope="col">Tên nhân viên</th>
                        <th scope="col">Số điện thoại</th>
                        <th scope="col" class="pe-4">Vai trò (Role)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($employees === []): ?>
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-5">
                                Phòng ban chưa có nhân viên nào.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td class="ps-4 fw-semibold">
                                    <?= htmlspecialchars((string) $employee['code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </td>
                                <td><?= htmlspecialchars((string) $employee['full_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($employee['phone'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td class="pe-4">
                                    <?= htmlspecialchars((string) (($employee['role_name'] ?? null) ?: (($employee['position'] ?? null) ?: '—')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
