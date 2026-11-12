<main class="container-fluid flex-grow-1 p-4 p-lg-5">
    <div class="mb-4">
        <a class="text-decoration-none" href="<?= BASE_URL ?>/category/index">
            <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>
            Danh sách danh mục
        </a>
        <h1 class="h3 mt-3 mb-1">
            Sản phẩm thuộc danh mục
            <?= htmlspecialchars((string) $category['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </h1>
    </div>

    <section class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-4">SKU</th>
                        <th scope="col">Tên sản phẩm</th>
                        <th scope="col">ĐVT</th>
                        <th scope="col" class="text-end">Giá bán</th>
                        <th scope="col" class="text-end pe-4">Tồn kho thực tế</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products === []): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-5">
                                Danh mục chưa có sản phẩm nào.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="ps-4 fw-semibold">
                                    <?= htmlspecialchars((string) $product['sku'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </td>
                                <td><?= htmlspecialchars((string) $product['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) $product['unit'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td class="text-end text-nowrap">
                                    <?= \App\Core\CurrencyFormatter::format($product['sale_price']) ?>&nbsp;₫
                                </td>
                                <td class="text-end pe-4">
                                    <?= number_format((float) $product['stock_qty'], 3, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
