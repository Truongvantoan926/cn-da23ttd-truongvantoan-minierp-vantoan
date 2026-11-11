<main class="container-fluid flex-grow-1 p-4 p-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Sổ quỹ Thu/Chi</h1>
            <p class="text-secondary mb-0">Theo dõi các giao dịch và số dư quỹ</p>
        </div>
        <a class="btn btn-primary" href="<?= BASE_URL ?>/finance/create">
            <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
            Lập phiếu Thu/Chi
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success-subtle text-success p-3">
                        <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
                    </div>
                    <div>
                        <div class="text-secondary">Tổng Thu</div>
                        <div class="fs-4 fw-semibold text-success">
                            <?= number_format((float) $summary['total_income'], 2, ',', '.') ?> ₫
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-danger-subtle text-danger p-3">
                        <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
                    </div>
                    <div>
                        <div class="text-secondary">Tổng Chi</div>
                        <div class="fs-4 fw-semibold text-danger">
                            <?= number_format((float) $summary['total_expense'], 2, ',', '.') ?> ₫
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary-subtle text-primary p-3">
                        <i class="fa-solid fa-wallet" aria-hidden="true"></i>
                    </div>
                    <div>
                        <div class="text-secondary">Dư quỹ</div>
                        <div class="fs-4 fw-semibold text-primary">
                            <?= number_format((float) $summary['balance'], 2, ',', '.') ?> ₫
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h2 class="h5 mb-0">Danh sách giao dịch</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-4">Mã phiếu</th>
                        <th scope="col">Loại</th>
                        <th scope="col">Danh mục</th>
                        <th scope="col" class="text-end">Số tiền</th>
                        <th scope="col">Phương thức</th>
                        <th scope="col">Ngày GD</th>
                        <th scope="col" class="pe-4">Người lập</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($transactions === []): ?>
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-5">
                                Chưa có giao dịch thu/chi nào.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $transaction): ?>
                            <?php $isIncome = $transaction['type'] === 'income'; ?>
                            <tr>
                                <td class="ps-4 fw-semibold">
                                    <?= htmlspecialchars((string) $transaction['code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </td>
                                <td>
                                    <span class="badge <?= $isIncome ? 'text-bg-success' : 'text-bg-danger' ?>">
                                        <?= $isIncome ? 'Thu' : 'Chi' ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars((string) $transaction['category'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td class="text-end text-nowrap <?= $isIncome ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format((float) $transaction['amount'], 2, ',', '.') ?> ₫
                                </td>
                                <td><?= htmlspecialchars((string) $transaction['payment_method'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) $transaction['trans_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td class="pe-4">
                                    <?= htmlspecialchars((string) ($transaction['created_by_name'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
