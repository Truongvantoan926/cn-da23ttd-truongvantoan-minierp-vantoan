<main class="container-fluid flex-grow-1 p-4 p-lg-5">
    <div class="mb-4">
        <h1 class="h3 mb-2">
            Xin chào, <?= htmlspecialchars($username, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>!
        </h1>
        <p class="text-secondary mb-0">Tổng quan hoạt động doanh nghiệp</p>
    </div>
    <?php if (isset($flashError) && is_string($flashError) && $flashError !== ''): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($flashError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div class="flex-grow-1 me-3">
                        <p class="text-secondary mb-2">Tổng nhân viên</p>
                        <p class="h3 mb-0"><?= number_format($employeeCount, 0, ',', '.') ?></p>
                    </div>
                    <div
                        class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 bg-primary-subtle text-primary"
                        style="width: 50px; height: 50px;"
                    >
                        <i class="fa-solid fa-users fa-lg" aria-hidden="true"></i>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div class="flex-grow-1 me-3">
                        <p class="text-secondary mb-2">Tổng sản phẩm</p>
                        <p class="h3 mb-0"><?= number_format($productCount, 0, ',', '.') ?></p>
                    </div>
                    <div
                        class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 bg-success-subtle text-success"
                        style="width: 50px; height: 50px;"
                    >
                        <i class="fa-solid fa-boxes-stacked fa-lg" aria-hidden="true"></i>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div class="flex-grow-1 me-3">
                        <p class="text-secondary mb-2">Phiếu giao dịch kho</p>
                        <p class="h3 mb-0"><?= number_format($receiptCount, 0, ',', '.') ?></p>
                    </div>
                    <div
                        class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 bg-warning-subtle text-warning"
                        style="width: 50px; height: 50px;"
                    >
                        <i class="fa-solid fa-arrow-right-arrow-left fa-lg" aria-hidden="true"></i>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div class="flex-grow-1 me-3">
                        <p class="text-secondary mb-2">Dư quỹ hiện tại</p>
                        <p class="h3 mb-0">
                            <?= \App\Core\CurrencyFormatter::format($financeSummary['balance']) ?>&nbsp;₫
                        </p>
                    </div>
                    <div
                        class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 bg-info-subtle text-info"
                        style="width: 50px; height: 50px;"
                    >
                        <i class="fa-solid fa-wallet fa-lg" aria-hidden="true"></i>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <section class="card border-0 shadow-sm h-100 border-start border-4 border-danger">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div class="flex-grow-1 me-3">
                        <p class="text-secondary mb-2">Cảnh báo tồn kho</p>
                        <p class="h3 mb-0 text-danger"><?= number_format(count($lowStockProducts), 0, ',', '.') ?></p>
                        <p class="small text-secondary mb-0">Sản phẩm cần nhập thêm</p>
                    </div>
                    <div
                        class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 bg-danger-subtle text-danger"
                        style="width: 50px; height: 50px;"
                    >
                        <i class="fa-solid fa-triangle-exclamation fa-lg" aria-hidden="true"></i>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12 col-xl-6">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h5 mb-1">Tổng Thu và Tổng Chi</h2>
                    <p class="text-secondary small mb-3">Tổng hợp từ sổ quỹ kế toán nội bộ</p>
                    <div style="height: 300px;">
                        <canvas id="finance-summary-chart" aria-label="Biểu đồ so sánh tổng thu và tổng chi" role="img"></canvas>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-6">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h5 mb-1">5 giao dịch kho gần đây nhất</h2>
                    <p class="text-secondary small mb-3">Các phiếu nhập và xuất mới được tạo</p>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Mã phiếu</th>
                                    <th scope="col">Loại</th>
                                    <th scope="col">Ngày tạo</th>
                                    <th scope="col" class="text-end">Tổng tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recentReceipts === []): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-4">
                                            Chưa có giao dịch kho nào.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentReceipts as $receipt): ?>
                                        <?php $isInbound = $receipt['type'] === 'IN'; ?>
                                        <tr>
                                            <td class="fw-semibold">
                                                <?= htmlspecialchars((string) $receipt['code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= $isInbound ? 'text-bg-success' : 'text-bg-danger' ?>">
                                                    <?= $isInbound ? 'IN' : 'OUT' ?>
                                                </span>
                                            </td>
                                            <td class="text-nowrap">
                                                <?= htmlspecialchars((string) $receipt['created_at'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                            </td>
                                            <td class="text-end text-nowrap">
                                                <?= \App\Core\CurrencyFormatter::format($receipt['total_amount']) ?>&nbsp;₫
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h2 class="h5 mb-1">Sản phẩm chạm ngưỡng tồn tối thiểu</h2>
                    <p class="text-secondary small mb-0">Tồn thực tế nhỏ hơn hoặc bằng tồn tối thiểu</p>
                </div>
                <span class="badge text-bg-danger">Cần nhập thêm: <?= number_format(count($lowStockProducts), 0, ',', '.') ?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">SKU</th>
                            <th scope="col">Sản phẩm</th>
                            <th scope="col">Danh mục</th>
                            <th scope="col" class="text-end">Tồn thực tế</th>
                            <th scope="col" class="text-end">Tồn tối thiểu</th>
                            <th scope="col" class="text-end">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($lowStockProducts === []): ?>
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    Không có sản phẩm nào cần nhập thêm.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lowStockProducts as $product): ?>
                                <tr class="table-warning">
                                    <td class="fw-semibold">
                                        <?= htmlspecialchars((string) $product['sku'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </td>
                                    <td><?= htmlspecialchars((string) $product['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($product['category_name'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                    <td class="text-end"><?= number_format((float) $product['stock_qty'], 3, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format((float) $product['min_stock'], 3, ',', '.') ?></td>
                                    <td class="text-end"><span class="badge text-bg-danger">Cần nhập thêm</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
(() => {
    const canvas = document.getElementById('finance-summary-chart');
    const totals = <?= json_encode(
        [
            'income' => (float) $financeSummary['total_income'],
            'expense' => (float) $financeSummary['total_expense'],
        ],
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
    ) ?>;

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: ['Tổng Thu', 'Tổng Chi'],
            datasets: [{
                label: 'Số tiền (₫)',
                data: [totals.income, totals.expense],
                backgroundColor: ['rgba(25, 135, 84, 0.75)', 'rgba(220, 53, 69, 0.75)'],
                borderColor: ['rgb(25, 135, 84)', 'rgb(220, 53, 69)'],
                borderWidth: 1,
                borderRadius: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => new Intl.NumberFormat('vi-VN').format(value),
                    },
                },
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => new Intl.NumberFormat('vi-VN', {
                            style: 'currency',
                            currency: 'VND',
                            maximumFractionDigits: 0,
                        }).format(context.parsed.y),
                    },
                },
            },
        },
    });
})();
</script>
