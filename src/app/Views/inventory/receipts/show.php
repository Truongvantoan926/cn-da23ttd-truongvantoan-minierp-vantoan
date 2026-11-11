<main class="container flex-grow-1 py-4 py-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <a class="text-decoration-none" href="<?= BASE_URL ?>/receipt/index">
            <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>
            Danh sách giao dịch kho
        </a>
        <button class="btn btn-outline-secondary" type="button" onclick="window.print()">
            <i class="fa-solid fa-print me-2" aria-hidden="true"></i>
            In phiếu
        </button>
    </div>

    <?php $isInbound = $receipt['type'] === 'IN'; ?>
    <section class="card border-0 shadow-sm mx-auto" style="max-width: 1000px;">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 border-bottom pb-4 mb-4">
                <div>
                    <p class="text-uppercase text-secondary small fw-semibold mb-2">Chứng từ kho</p>
                    <h1 class="h3 mb-2"><?= $isInbound ? 'Phiếu nhập kho' : 'Phiếu xuất kho' ?></h1>
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
                                <td class="text-end text-nowrap"><?= number_format((float) $item['price'], 2, ',', '.') ?> ₫</td>
                                <td class="text-end text-nowrap"><?= number_format((float) $item['total'], 2, ',', '.') ?> ₫</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-end">Tổng cộng</th>
                            <th class="text-end text-nowrap">
                                <?= number_format((float) $receipt['total_amount'], 2, ',', '.') ?> ₫
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
        </div>
    </section>
</main>
