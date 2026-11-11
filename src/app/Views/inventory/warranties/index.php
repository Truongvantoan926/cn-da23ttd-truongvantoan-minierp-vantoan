<main class="container-fluid flex-grow-1 p-4 p-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Bảo hành &amp; Serial/IMEI</h1>
            <p class="text-secondary mb-0">Theo dõi serial sản phẩm, trạng thái và hạn bảo hành</p>
        </div>
        <a class="btn btn-primary" href="<?= BASE_URL ?>/warranty/search">
            <i class="fa-solid fa-magnifying-glass me-2" aria-hidden="true"></i>
            Tra cứu bảo hành
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-4">Serial/IMEI</th>
                        <th scope="col">SKU</th>
                        <th scope="col">Sản phẩm</th>
                        <th scope="col">Tình trạng</th>
                        <th scope="col">Ngày bán</th>
                        <th scope="col">Thời hạn</th>
                        <th scope="col" class="pe-4">Hạn bảo hành</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($serials === []): ?>
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-5">
                                Chưa có sản phẩm nào được gắn Serial/IMEI.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($serials as $serial): ?>
                            <?php
                            $statusLabels = [
                                'in_stock' => 'Trong kho',
                                'sold' => 'Đã bán',
                                'warranty' => 'Đang bảo hành',
                            ];
                            $statusClasses = [
                                'in_stock' => 'text-bg-primary',
                                'sold' => 'text-bg-secondary',
                                'warranty' => 'text-bg-warning',
                            ];
                            $status = (string) $serial['status'];
                            ?>
                            <tr>
                                <td class="ps-4 fw-semibold">
                                    <a
                                        href="<?= BASE_URL ?>/warranty/search?serial_number=<?= rawurlencode((string) $serial['serial_number']) ?>"
                                        class="text-decoration-none"
                                    >
                                        <?= htmlspecialchars((string) $serial['serial_number'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars((string) $serial['sku'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) $serial['product_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td>
                                    <span class="badge <?= $statusClasses[$status] ?? 'text-bg-dark' ?>">
                                        <?= htmlspecialchars($statusLabels[$status] ?? $status, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars((string) ($serial['sold_date'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= number_format((int) $serial['warranty_months']) ?> tháng</td>
                                <td class="pe-4"><?= htmlspecialchars((string) ($serial['warranty_end_date'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
