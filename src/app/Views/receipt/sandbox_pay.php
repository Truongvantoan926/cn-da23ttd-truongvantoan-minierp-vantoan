<style>
.sandbox-payment-page {
    width: min(100% - 2rem, 480px);
    margin: 2rem auto;
}

.sandbox-payment-card {
    border-radius: 1.25rem;
}

.sandbox-payment-amount {
    font-size: 2rem;
    font-weight: 700;
}
</style>

<?php
$escape = static fn (mixed $value): string => htmlspecialchars(
    (string) ($value ?? '—'),
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
?>

<main class="container flex-grow-1 py-4">
    <section class="sandbox-payment-page">
        <div class="text-center mb-3">
            <h1 class="h4 mb-1">Thanh toán chuyển khoản</h1>
            <div class="text-secondary">Cổng thanh toán mô phỏng Sandbox</div>
        </div>

        <div class="card border-0 shadow sandbox-payment-card">
            <div class="card-body p-4">
                <?php if (isset($error) && is_string($error) && $error !== ''): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $escape($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success text-center" role="status">
                        <div class="fw-bold">Giao dịch thành công</div>
                        <div><?= $escape($successMessage) ?></div>
                    </div>
                <?php endif; ?>

                <div class="text-center border-bottom pb-3 mb-3">
                    <div class="text-secondary">Số tiền thanh toán</div>
                    <div class="sandbox-payment-amount text-primary">
                        <?= \App\Core\CurrencyFormatter::format($receipt['total_amount']) ?>&nbsp;₫
                    </div>
                    <span class="badge <?= $receipt['payment_status'] === 'paid' ? 'text-bg-success' : 'text-bg-warning' ?>">
                        <?= $receipt['payment_status'] === 'paid' ? 'ĐÃ THANH TOÁN' : 'CHỜ CHUYỂN KHOẢN' ?>
                    </span>
                </div>

                <dl class="row mb-0">
                    <dt class="col-5">Ngân hàng</dt>
                    <dd class="col-7">Vietcombank</dd>
                    <dt class="col-5">Người nhận</dt>
                    <dd class="col-7">CONG TY MINI ERP</dd>
                    <dt class="col-5">Số tài khoản</dt>
                    <dd class="col-7">0123456789</dd>
                    <dt class="col-5">Mã hóa đơn</dt>
                    <dd class="col-7">HD-<?= $escape($receipt['code']) ?></dd>
                    <dt class="col-5">Nội dung</dt>
                    <dd class="col-7">Thanh toan hop dong <?= $escape($receipt['code']) ?></dd>
                </dl>

                <?php if ($receipt['payment_status'] === 'pending'): ?>
                    <form
                        class="mt-4"
                        action="<?= BASE_URL ?>/receipt/sandboxPay/<?= rawurlencode((string) $receipt['id']) ?>"
                        method="POST"
                    >
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= $escape($csrfToken) ?>"
                        >
                        <button type="submit" class="btn btn-success w-100">
                            Thanh toán thành công (Sandbox)
                        </button>
                    </form>
                <?php endif; ?>

                <a class="btn btn-link text-decoration-none w-100 mt-2" href="<?= BASE_URL ?>/receipt/invoice/<?= rawurlencode((string) $receipt['id']) ?>">
                    Quay lại hóa đơn
                </a>
            </div>
        </div>
    </section>
</main>
