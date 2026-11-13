<main class="container-fluid flex-grow-1 p-4 p-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Giao dịch kho</h1>
            <p class="text-secondary mb-0">Theo dõi phiếu nhập và phiếu xuất</p>
        </div>
        <?php if ($canManage): ?>
            <a class="btn btn-primary" href="<?= BASE_URL ?>/receipt/create">
                <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                Tạo phiếu kho
            </a>
        <?php endif; ?>
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
                        <th scope="col" class="ps-4">Mã phiếu</th>
                        <th scope="col">Loại</th>
                        <th scope="col">Ngày lập</th>
                        <th scope="col">Đối tác</th>
                        <th scope="col">Người lập</th>
                        <th scope="col" class="text-end">Tổng tiền</th>
                        <th scope="col">Thanh toán</th>
                        <th scope="col" class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($receipts === []): ?>
                        <tr>
                            <td colspan="8" class="text-center text-secondary py-5">
                                Chưa có giao dịch kho nào.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($receipts as $receipt): ?>
                            <?php $isInbound = $receipt['type'] === 'IN'; ?>
                            <?php $isLargeOutbound = !$isInbound
                                && (
                                    (int) $receipt['total_quantity'] >= 10
                                    || (float) $receipt['total_amount'] >= 50000000
                                ); ?>
                            <tr>
                                <td class="ps-4 fw-semibold">
                                    <?= htmlspecialchars((string) $receipt['code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </td>
                                <td>
                                    <span class="badge <?= $isInbound ? 'text-bg-success' : 'text-bg-danger' ?>">
                                        <?= $isInbound ? 'IN · Nhập' : 'OUT · Xuất' ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars((string) $receipt['created_at'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($receipt['partner_name'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($receipt['employee_name'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td class="text-end text-nowrap">
                                    <?= \App\Core\CurrencyFormatter::format($receipt['total_amount']) ?>&nbsp;₫
                                </td>
                                <td>
                                    <span class="badge <?= $receipt['payment_status'] === 'paid' ? 'text-bg-success' : 'text-bg-warning' ?>">
                                        <?= $receipt['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chờ chuyển khoản' ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex flex-nowrap gap-1 justify-content-end">
                                        <?php if ($isLargeOutbound): ?>
                                            <a
                                                href="<?= BASE_URL ?>/receipt/contract/<?= rawurlencode((string) $receipt['id']) ?>"
                                            class="btn btn-outline-secondary btn-sm text-nowrap"
                                            >Hợp đồng</a>
                                        <?php endif; ?>
                                        <a
                                            href="<?= BASE_URL ?>/receipt/invoice/<?= rawurlencode((string) $receipt['id']) ?>"
                                        class="btn btn-outline-primary btn-sm text-nowrap"
                                        >Hóa đơn</a>
                                        <a
                                            href="<?= BASE_URL ?>/receipt/show/<?= rawurlencode((string) $receipt['id']) ?>"
                                        class="btn btn-outline-dark btn-sm text-nowrap"
                                        >Xem</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php require $paginationViewPath; ?>
</main>
