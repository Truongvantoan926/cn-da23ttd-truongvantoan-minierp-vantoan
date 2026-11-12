<main class="container-fluid flex-grow-1 p-4 p-lg-5">
    <?php
    $roleLabels = [
        'admin' => 'Quản trị viên',
        'manager' => 'Quản lý',
        'accountant' => 'Kế toán',
        'warehouse' => 'Thủ kho',
        'hr' => 'Nhân sự',
        'sales' => 'Kinh doanh',
    ];
    ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Quản lý tài khoản</h1>
            <p class="text-secondary mb-0">Cập nhật vai trò truy cập của người dùng</p>
        </div>
    </div>

    <?php if (isset($error) && is_string($error) && $error !== ''): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (isset($success) && is_string($success) && $success !== ''): ?>
        <div class="alert alert-success" role="status">
            <?= htmlspecialchars($success, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-4">Tên đăng nhập</th>
                        <th scope="col">Họ tên</th>
                        <th scope="col">Vai trò hiện tại</th>
                        <th scope="col" class="pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users === []): ?>
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-5">
                                Chưa có tài khoản nào.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <?php $isCurrentUser = (int) $user['id'] === (int) ($_SESSION['user_id'] ?? 0); ?>
                            <tr>
                                <td class="ps-4">
                                    <?= htmlspecialchars((string) $user['username'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars((string) ($user['full_name'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </td>
                                <td>
                                    <span class="badge text-bg-light border">
                                        <?= htmlspecialchars($roleLabels[$user['role_name']] ?? (string) $user['role_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="pe-4">
                                    <div class="d-flex align-items-center gap-2 flex-nowrap">
                                        <form
                                            class="d-flex align-items-center gap-2 flex-nowrap mb-0"
                                            action="<?= BASE_URL ?>/user/update/<?= rawurlencode((string) $user['id']) ?>"
                                            method="POST"
                                        >
                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                            >
                                            <label class="visually-hidden" for="role-<?= (int) $user['id'] ?>">
                                                Vai trò của <?= htmlspecialchars((string) $user['username'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                            </label>
                                            <select
                                                class="form-select form-select-sm w-auto"
                                                id="role-<?= (int) $user['id'] ?>"
                                                name="role_id"
                                                style="min-width: 9rem;"
                                                <?= $isCurrentUser ? 'disabled' : '' ?>
                                                required
                                            >
                                                <?php foreach ($roles as $role): ?>
                                                    <option
                                                        value="<?= (int) $role['id'] ?>"
                                                        <?= (int) $user['role_id'] === (int) $role['id'] ? 'selected' : '' ?>
                                                    >
                                                        <?= htmlspecialchars($roleLabels[$role['name']] ?? (string) $role['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button
                                                type="submit"
                                                class="btn btn-primary btn-sm"
                                                <?= $isCurrentUser ? 'disabled' : '' ?>
                                            >
                                                Lưu
                                            </button>
                                        </form>
                                        <form
                                            class="mb-0"
                                            action="<?= BASE_URL ?>/user/delete/<?= (int) $user['id'] ?>"
                                            method="POST"
                                        >
                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                            >
                                            <button
                                                type="submit"
                                                class="btn btn-outline-danger btn-sm"
                                                onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này?');"
                                                <?= $isCurrentUser ? 'disabled title="Không thể xóa tài khoản đang đăng nhập."' : '' ?>
                                            >
                                                Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
