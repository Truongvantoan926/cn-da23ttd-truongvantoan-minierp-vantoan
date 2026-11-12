<style>
@media print {
    @page {
        size: A4;
        margin: 14mm;
    }

    html,
    body {
        background: #fff !important;
        color: #000 !important;
    }

    body {
        display: block !important;
        min-height: 0 !important;
    }

    header.navbar,
    aside,
    .no-print {
        display: none !important;
    }

    .d-flex.flex-grow-1,
    main {
        display: block !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .card {
        border: 0 !important;
        box-shadow: none !important;
    }

    .card-body {
        padding: 0 !important;
    }

    .table-responsive {
        overflow: visible !important;
    }

    .signature-area {
        break-inside: avoid;
    }
}
</style>

<main class="container flex-grow-1 py-4 py-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
        <a class="btn btn-secondary btn-sm" href="<?= BASE_URL ?>/receipt/index">Quay lại danh sách</a>
        <button class="btn btn-outline-secondary" type="button" onclick="window.print()">
            <i class="fa-solid fa-print me-2" aria-hidden="true"></i>
            In phiếu kho
        </button>
    </div>

    <?php $isInbound = $receipt['type'] === 'IN'; ?>
    <section class="card border-0 shadow-sm mx-auto" style="max-width: 1000px;">
        <div class="card-body p-4 p-lg-5">
            <div class="text-center mb-4">
                <div class="fw-bold text-uppercase">Công ty / Doanh nghiệp</div>
                <h1 class="h4 text-uppercase mt-3 mb-1"><?= $isInbound ? 'Phiếu nhập kho' : 'Phiếu xuất kho' ?></h1>
                <div class="text-secondary">Mã phiếu: <?= htmlspecialchars((string) $receipt['code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 border-bottom pb-4 mb-4">
                <div>
                    <p class="text-uppercase text-secondary small fw-semibold mb-2">Chứng từ kho</p>
                    <h2 class="h5 mb-2"><?= $isInbound ? 'Phiếu nhập kho' : 'Phiếu xuất kho' ?></h2>
                    <span class="badge <?= $isInbound ? 'text-bg-success' : 'text-bg-danger' ?>">
                        <?= $isInbound ? 'IN · Nhập' : 'OUT · Xuất' ?>
                    </span>
                </div>
                <div class="text-lg-end">
                    <div class="text-secondary">Mã phiếu</div>
                    <div class="fs-5 fw-semibold">
                        <?= htmlspecialchars((string) $receipt['code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </div>
                    <div class="text-secondary mt-2">Ngày lập</div>
                    <div><?= htmlspecialchars((string) $receipt['created_at'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="text-secondary small"><?= $isInbound ? 'Nhà cung cấp' : 'Khách hàng' ?></div>
                    <div class="fw-semibold">
                        <?= htmlspecialchars((string) ($receipt['partner_name'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">Nhân viên lập phiếu</div>
                    <div class="fw-semibold">
                        <?= htmlspecialchars((string) ($receipt['employee_name'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">SKU</th>
                            <th scope="col">Sản phẩm</th>
                            <th scope="col">ĐVT</th>
                            <th scope="col" class="text-end">Số lượng</th>
                            <th scope="col" class="text-end">Đơn giá</th>
                            <th scope="col" class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars((string) $item['sku'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) $item['product_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) $item['unit'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td class="text-end"><?= number_format((float) $item['qty'], 0, ',', '.') ?></td>
                                <td class="text-end text-nowrap"><?= \App\Core\CurrencyFormatter::format($item['price']) ?>&nbsp;₫</td>
                                <td class="text-end text-nowrap"><?= \App\Core\CurrencyFormatter::format($item['total']) ?>&nbsp;₫</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-end">Tổng cộng</th>
                            <th class="text-end text-nowrap">
                                <?= \App\Core\CurrencyFormatter::format($receipt['total_amount']) ?>&nbsp;₫
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <?php if (!empty($receipt['note'])): ?>
                <div class="mt-4">
                    <div class="text-secondary small mb-1">Ghi chú</div>
                    <div class="border rounded p-3">
                        <?= nl2br(htmlspecialchars((string) $receipt['note'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row text-center mt-5 signature-area">
                <div class="col-4">
                    <div class="fw-semibold">Người lập phiếu</div>
                    <div class="text-secondary small">(Ký, ghi rõ họ tên)</div>
                    <div style="height: 90px;"></div>
                </div>
                <div class="col-4">
                    <div class="fw-semibold">Người giao hàng</div>
                    <div class="text-secondary small">(Ký, ghi rõ họ tên)</div>
                    <div style="height: 90px;"></div>
                </div>
                <div class="col-4">
                    <div class="fw-semibold">Thủ kho</div>
                    <div class="text-secondary small">(Ký, ghi rõ họ tên)</div>
                    <div style="height: 90px;"></div>
                </div>
            </div>
        </div>
    </section>
</main>
