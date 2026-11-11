<main class="container-fluid flex-grow-1 p-4 p-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Phòng ban</h1>
            <p class="text-secondary mb-0">Quản lý danh mục phòng ban</p>
        </div>
        <a class="btn btn-primary" href="<?= BASE_URL ?>/department/create">
            <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
            Thêm mới
        </a>
    </div>

    <?php if (isset($error) && is_string($error) && $error !== ''): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-4">Mã phòng ban</th>
                        <th scope="col">Tên phòng ban</th>
                        <th scope="col" class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($departments === []): ?>
                        <tr>
                            <td colspan="3" class="text-center text-secondary py-5">
                                Chưa có phòng ban nào.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($departments as $department): ?>
                            <tr>
                                <td class="ps-4 fw-semibold">
                                    <?= htmlspecialchars((string) $department['code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </td>
                                <td><?= htmlspecialchars((string) $department['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td class="text-end pe-4">
                                    <a
                                        class="btn btn-sm btn-outline-primary me-1"
                                        href="<?= BASE_URL ?>/department/edit/<?= rawurlencode((string) $department['id']) ?>"
                                        aria-label="Sửa phòng ban <?= htmlspecialchars((string) $department['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    >
                                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                        <span class="d-none d-md-inline ms-1">Sửa</span>
                                    </a>
                                    <form
                                        class="d-inline"
                                        action="<?= BASE_URL ?>/department/delete/<?= rawurlencode((string) $department['id']) ?>"
                                        method="post"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa phòng ban này?');"
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
</main>
