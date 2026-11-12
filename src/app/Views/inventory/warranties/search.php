<main class="container flex-grow-1 py-4 py-lg-5">
    <div class="mx-auto" style="max-width: 860px;">
        <div class="mb-4">
            <a class="btn btn-secondary btn-sm" href="<?= BASE_URL ?>/warranty/index">Quay lại danh sách</a>
            <h1 class="h3 mt-3 mb-1">Tra cứu bảo hành</h1>
            <p class="text-secondary mb-0">Nhập số Serial/IMEI để xem tình trạng sản phẩm và hạn bảo hành.</p>
        </div>

        <form class="card border-0 shadow-sm mb-4" action="<?= BASE_URL ?>/warranty/search" method="GET">
            <div class="card-body p-4">
                <label for="serial_number" class="form-label">Số Serial/IMEI</label>
                <div class="input-group">
                    <input
                        type="search"
                        class="form-control"
                        id="serial_number"
                        name="serial_number"
                        value="<?= htmlspecialchars($serialNumber, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                        maxlength="100"
                        required
                        autofocus
                    >
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-solid fa-magnifying-glass me-2" aria-hidden="true"></i>
                        Tra cứu
                    </button>
                </div>
            </div>
        </form>

        <?php if (isset($error) && is_string($error) && $error !== ''): ?>
            <div class="alert alert-warning" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($serial !== null): ?>
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
            $daysRemaining = $serial['warranty_days_remaining'];
            ?>
            <section class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Thông tin bảo hành</h2>
                    <span class="badge <?= $statusClasses[$status] ?? 'text-bg-dark' ?>">
                        <?= htmlspecialchars($statusLabels[$status] ?? $status, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </span>
                </div>
                <div class="card-body p-4">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-secondary">Serial/IMEI</dt>
                        <dd class="col-sm-8 fw-semibold"><?= htmlspecialchars((string) $serial['serial_number'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>

                        <dt class="col-sm-4 text-secondary">Sản phẩm</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars((string) $serial['product_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>

                        <dt class="col-sm-4 text-secondary">SKU</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars((string) $serial['sku'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>

                        <dt class="col-sm-4 text-secondary">Ngày bán</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars((string) ($serial['sold_date'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>

                        <dt class="col-sm-4 text-secondary">Thời hạn bảo hành</dt>
                        <dd class="col-sm-8"><?= number_format((int) $serial['warranty_months']) ?> tháng</dd>

                        <dt class="col-sm-4 text-secondary">Ngày hết hạn</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars((string) ($serial['warranty_end_date'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></dd>

                        <dt class="col-sm-4 text-secondary">Thời hạn còn lại</dt>
                        <dd class="col-sm-8">
                            <?php if ($serial['warranty_end_date'] === null): ?>
                                Chưa bắt đầu
                            <?php elseif ((int) $daysRemaining >= 0): ?>
                                Còn <?= number_format((int) $daysRemaining) ?> ngày
                            <?php else: ?>
                                <span class="text-danger">Đã hết hạn</span>
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>
