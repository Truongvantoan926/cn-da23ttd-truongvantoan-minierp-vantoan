<main class="container-fluid flex-grow-1 p-4 p-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Sản phẩm</h1>
            <p class="text-secondary mb-0">Quản lý thông tin và tồn kho sản phẩm</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-success" href="<?= BASE_URL ?>/product/exportCsv">
                <i class="fa-solid fa-file-csv me-2" aria-hidden="true"></i>
                Xuất Excel/CSV
            </a>
            <?php if ($canManage): ?>
            <a class="btn btn-primary" href="<?= BASE_URL ?>/product/create">
                <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                Thêm sản phẩm
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($error) && is_string($error) && $error !== ''): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form class="row g-2 mb-3" action="<?= BASE_URL ?>/product/index" method="GET">
        <div class="col-sm-8 col-md-6 col-lg-4">
            <label class="visually-hidden" for="product-search">Tìm theo tên hoặc SKU</label>
            <input
                class="form-control"
                id="product-search"
                type="search"
                name="search"
                value="<?= htmlspecialchars($search, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                placeholder="Tìm theo tên hoặc SKU"
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
                        <th scope="col" class="ps-4">SKU</th>
                        <th scope="col">Tên sản phẩm</th>
                        <th scope="col">Danh mục</th>
                        <th scope="col">ĐVT</th>
                        <th scope="col" class="text-end">Giá vốn</th>
                        <th scope="col" class="text-end">Giá bán</th>
                        <th scope="col" class="text-end">Tồn tối thiểu</th>
                        <th scope="col" class="text-end">Tồn thực tế</th>
                        <th scope="col" class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products === []): ?>
                        <tr>
                            <td colspan="9" class="text-center text-secondary py-5">
                                <?= $search === '' ? 'Chưa có sản phẩm nào.' : 'Không tìm thấy sản phẩm phù hợp.' ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <?php $isLowStock = (float) $product['stock_qty'] <= (float) $product['min_stock']; ?>
                            <tr class="<?= $isLowStock ? 'table-warning' : '' ?>">
                                <td class="ps-4 fw-semibold">
                                    <?= htmlspecialchars((string) $product['sku'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </td>
                                <td><?= htmlspecialchars((string) $product['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($product['category_name'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) $product['unit'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td class="text-end text-nowrap">
                                    <?= \App\Core\CurrencyFormatter::format($product['cost_price']) ?>&nbsp;₫
                                </td>
                                <td class="text-end text-nowrap">
                                    <?= \App\Core\CurrencyFormatter::format($product['sale_price']) ?>&nbsp;₫
                                </td>
                                <td class="text-end">
                                    <?= number_format((float) $product['min_stock'], 3, ',', '.') ?>
                                </td>
                                <td class="text-end">
                                    <span class="<?= $isLowStock ? 'badge text-bg-danger' : '' ?>">
                                        <?= number_format((float) $product['stock_qty'], 3, ',', '.') ?>
                                    </span>
                                    <?php if ($isLowStock): ?>
                                        <span class="badge text-bg-danger ms-1">Cần nhập thêm</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 text-nowrap">
                                    <a
                                        class="btn btn-sm btn-outline-secondary me-1"
                                        href="<?= BASE_URL ?>/product/show/<?= rawurlencode((string) $product['id']) ?>"
                                        aria-label="Xem chi tiết sản phẩm <?= htmlspecialchars((string) $product['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    >
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                        <span class="d-none d-md-inline ms-1">Xem</span>
                                    </a>
                                    <?php if ($canManage): ?>
                                    <a
                                        class="btn btn-sm btn-outline-primary me-1"
                                        href="<?= BASE_URL ?>/product/edit/<?= rawurlencode((string) $product['id']) ?>"
                                        aria-label="Sửa sản phẩm <?= htmlspecialchars((string) $product['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    >
                                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                        <span class="d-none d-md-inline ms-1">Sửa</span>
                                    </a>
                                    <form
                                        class="d-inline"
                                        action="<?= BASE_URL ?>/product/delete/<?= rawurlencode((string) $product['id']) ?>"
                                        method="POST"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');"
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
