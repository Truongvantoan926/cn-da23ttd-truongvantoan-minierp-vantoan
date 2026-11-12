<main class="container-fluid flex-grow-1 p-4 p-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Danh mục sản phẩm</h1>
            <p class="text-secondary mb-0">Quản lý các nhóm hàng trong kho</p>
        </div>
        <?php if ($canManage): ?>
            <a class="btn btn-primary" href="<?= BASE_URL ?>/category/create">
                <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                Thêm danh mục
            </a>
        <?php endif; ?>
    </div>

    <?php if (isset($error) && is_string($error) && $error !== ''): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form class="row g-2 mb-3" action="<?= BASE_URL ?>/category/index" method="GET">
        <div class="col-sm-8 col-md-6 col-lg-4">
            <label class="visually-hidden" for="category-search">Tìm danh mục theo tên</label>
            <input
                class="form-control"
                id="category-search"
                type="search"
                name="search"
                value="<?= htmlspecialchars($search, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                placeholder="Tìm danh mục theo tên"
            >
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-primary" type="submit">
                <i class="fa-solid fa-magnifying-glass me-2" aria-hidden="true"></i>
                Tìm kiếm
            </button>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-4">#</th>
                        <th scope="col">Tên danh mục</th>
                        <th scope="col">Mô tả</th>
                        <th scope="col" class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($categories === []): ?>
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-5">
                                <?= $search === '' ? 'Chưa có danh mục nào.' : 'Không tìm thấy danh mục phù hợp.' ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td class="ps-4 text-secondary">
                                    <?= htmlspecialchars((string) $category['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </td>
                                <td class="fw-semibold">
                                    <?= htmlspecialchars((string) $category['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars((string) ($category['description'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </td>
                                <td class="text-end pe-4 text-nowrap">
                                    <a
                                        class="btn btn-sm btn-outline-secondary me-1"
                                        href="<?= BASE_URL ?>/category/show/<?= rawurlencode((string) $category['id']) ?>"
                                        aria-label="Xem danh mục <?= htmlspecialchars((string) $category['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    >
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                        <span class="d-none d-md-inline ms-1">Xem</span>
                                    </a>
                                    <?php if ($canManage): ?>
                                    <a
                                        class="btn btn-sm btn-outline-primary me-1"
                                        href="<?= BASE_URL ?>/category/edit/<?= rawurlencode((string) $category['id']) ?>"
                                        aria-label="Sửa danh mục <?= htmlspecialchars((string) $category['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    >
                                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                        <span class="d-none d-md-inline ms-1">Sửa</span>
                                    </a>
                                    <form
                                        class="d-inline"
                                        action="<?= BASE_URL ?>/category/delete/<?= rawurlencode((string) $category['id']) ?>"
                                        method="POST"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');"
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
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
