<style>
.print-document {
    width: min(100%, 210mm);
    min-height: 297mm;
    margin: 0 auto;
    padding: 18mm 20mm;
    color: #111;
    background: #fff;
}

.print-document table {
    border-color: #6c757d;
}

.qr-code-image {
    display: block;
    width: 180px;
    height: 180px;
    object-fit: contain;
}

@media print {
    @page {
        size: A4;
        margin: 0;
    }

    html,
    body {
        min-height: 0 !important;
        color: #000 !important;
        background: #fff !important;
    }

    header.navbar,
    aside,
    footer,
    .no-print {
        display: none !important;
    }

    body,
    .d-flex.flex-grow-1,
    main {
        display: block !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .print-document {
        width: 210mm;
        min-height: 297mm;
        margin: 0;
        padding: 18mm 20mm !important;
        box-shadow: none !important;
    }

    .table-responsive {
        overflow: visible !important;
    }

    .qr-code-image {
        width: 180px !important;
        height: 180px !important;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
}
</style>

<?php
$escape = static fn (mixed $value): string => htmlspecialchars(
    (string) ($value ?? '—'),
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
$isInbound = $receipt['type'] === 'IN';
$paymentBank = $isInbound ? ($receipt['partner_bank_name'] ?? null) : 'Vietcombank';
$paymentAccount = $isInbound ? ($receipt['partner_bank_account'] ?? null) : '0123456789';
$paymentHolder = $isInbound ? ($receipt['partner_account_holder'] ?? null) : 'CONG TY MINI ERP';
$sandboxPaymentUrl = isset($sandboxPaymentUrl) && is_string($sandboxPaymentUrl)
    ? $sandboxPaymentUrl
    : null;
$isPending = $receipt['payment_status'] === 'pending';
$bankName = trim((string) ($paymentBank ?? ''));
$bankAliases = [
    'vietcombank' => 'VCB',
    'techcombank' => 'TCB',
    'mbbank' => 'MB',
    'mb' => 'MB',
    'bidv' => 'BIDV',
    'vietinbank' => 'ICB',
    'icb' => 'ICB',
    'acb' => 'ACB',
    'vpbank' => 'VPB',
    'tpbank' => 'TPB',
    'agribank' => 'VBA',
    'sacombank' => 'STB',
];
$bankKey = strtolower((string) preg_replace('/[^a-z0-9]/i', '', $bankName));
$bankCode = $bankAliases[$bankKey] ?? 'VCB';
$paymentAccount = trim((string) ($paymentAccount ?? ''));
$paymentHolder = trim((string) ($paymentHolder ?? ''));
$amount = (int) $receipt['total_amount'];
$addInfo = 'Thanh toan hop dong ' . (string) $receipt['code'];
$vietQrUrl = 'https://img.vietqr.io/image/'
    . rawurlencode($bankCode) . '-' . rawurlencode($paymentAccount)
    . '-compact2.png?amount=' . $amount
    . '&addInfo=' . rawurlencode($addInfo)
    . '&accountName=' . rawurlencode($paymentHolder);
$fallbackQrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data='
    . rawurlencode($vietQrUrl);
$sandboxQrUrl = $sandboxPaymentUrl === null
    ? null
    : 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data='
        . rawurlencode($sandboxPaymentUrl);
$qrImageUrl = $isPending && $sandboxQrUrl !== null ? $sandboxQrUrl : $vietQrUrl;
$qrErrorHandler = $isPending && $sandboxQrUrl !== null
    ? 'this.onerror=null;this.style.display="none";'
    : "this.onerror=null;this.src='" . $fallbackQrUrl . "';";
$canGenerateQr = $isPending
    ? $sandboxQrUrl !== null
    : $paymentAccount !== '' && $paymentHolder !== '';
$statusUrl = rtrim(BASE_URL, '/') . '/receipt/checkPaymentStatus/'
    . rawurlencode((string) $receipt['id']);
?>

<main class="container-fluid flex-grow-1 py-4">
    <div class="no-print d-flex justify-content-between align-items-center gap-3 mb-3">
        <a class="btn btn-link text-decoration-none" href="<?= BASE_URL ?>/receipt/index">Quay lại danh sách</a>
        <button class="btn btn-primary" type="button" onclick="window.print()">In hóa đơn</button>
    </div>

    <article class="print-document shadow-sm mt-4 pt-2">
        <header class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
            <div>
                <div class="h5 fw-bold mb-1"><?= $isInbound ? 'NHÀ CUNG CẤP' : 'CÔNG TY MINI ERP' ?></div>
                <div class="text-secondary"><?= $isInbound ? $escape($receipt['partner_name']) : 'Hóa đơn bán hàng / Phiếu thanh toán' ?></div>
            </div>
            <div class="text-end">
                <h1 class="h3 fw-bold mb-1">HÓA ĐƠN THANH TOÁN</h1>
                <div>Mã hóa đơn: HD-<?= $escape($receipt['code']) ?></div>
                <div>Ngày lập: <?= $escape($receipt['created_at']) ?></div>
            </div>
        </header>

        <div class="mb-4">
            <span class="fw-bold">Trạng thái thanh toán:</span>
            <?php if ($receipt['payment_status'] === 'paid'): ?>
                <span id="payment-status-badge" class="badge text-bg-success">ĐÃ THANH TOÁN</span>
                <?php if (!empty($receipt['paid_at'])): ?>
                    <span class="ms-2">Thời gian: <?= $escape($receipt['paid_at']) ?></span>
                <?php endif; ?>
            <?php else: ?>
                <span id="payment-status-badge" class="badge text-bg-warning">CHỜ CHUYỂN KHOẢN</span>
                <span id="payment-paid-at" class="ms-2 d-none"></span>
            <?php endif; ?>
        </div>

        <section class="row g-3 mb-4">
            <div class="col-md-6">
                <h2 class="h6 fw-bold"><?= $isInbound ? 'Bên nhận thanh toán' : 'Bên thanh toán' ?></h2>
                <div class="fw-semibold"><?= $escape($receipt['partner_name']) ?></div>
                <div>Điện thoại: <?= $escape($receipt['partner_phone']) ?></div>
                <div>Địa chỉ: <?= $escape($receipt['partner_address']) ?></div>
                <div>Email: <?= $escape($receipt['partner_email']) ?></div>
            </div>
            <div class="col-md-6">
                <h2 class="h6 fw-bold">Thông tin phiếu</h2>
                <div>Loại phiếu: <?= $isInbound ? 'Nhập kho (IN)' : 'Xuất kho (OUT)' ?></div>
                <div>Mã phiếu: <?= $escape($receipt['code']) ?></div>
                <div>Ngày lập phiếu: <?= $escape($receipt['created_at']) ?></div>
            </div>
        </section>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>STT</th>
                        <th>Mã hàng</th>
                        <th>Sản phẩm</th>
                        <th>ĐVT</th>
                        <th class="text-end">SL</th>
                        <th class="text-end">Đơn giá</th>
                        <th class="text-end">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= $escape($item['sku']) ?></td>
                            <td><?= $escape($item['product_name']) ?></td>
                            <td><?= $escape($item['unit']) ?></td>
                            <td class="text-end"><?= number_format((int) $item['qty'], 0, ',', '.') ?></td>
                            <td class="text-end text-nowrap"><?= \App\Core\CurrencyFormatter::format($item['price']) ?> ₫</td>
                            <td class="text-end text-nowrap"><?= \App\Core\CurrencyFormatter::format($item['total']) ?> ₫</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-end">Tổng tiền thanh toán</th>
                        <th class="text-end text-nowrap"><?= \App\Core\CurrencyFormatter::format($receipt['total_amount']) ?> ₫</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <section class="mt-4 pt-2">
            <h2 class="h6 fw-bold">Thông tin chuyển khoản</h2>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <div>Ngân hàng: <?= $escape($paymentBank) ?></div>
                    <div>Số tài khoản: <?= $escape($paymentAccount) ?></div>
                    <div>Chủ tài khoản: <?= $escape($paymentHolder) ?></div>
                    <div>Nội dung chuyển khoản: <?= $escape($addInfo) ?></div>
                </div>
                <div class="text-center">
                    <?php if ($canGenerateQr): ?>
                        <img
                            class="qr-code-image border rounded p-1"
                            id="payment-qr"
                            src="<?= $escape($qrImageUrl) ?>"
                            onerror="<?= $escape($qrErrorHandler) ?>"
                            width="180"
                            height="180"
                            alt="<?= $isPending ? 'Mã QR Sandbox thanh toán' : 'Mã QR thanh toán' ?> <?= $escape($receipt['code']) ?>"
                        >
                        <div class="small text-secondary mt-1">Quét mã QR để chuyển khoản nhanh</div>
                        <?php if ($isPending && $sandboxPaymentUrl !== null): ?>
                            <a
                                id="sandbox-pay-link"
                                class="btn btn-outline-success btn-sm mt-2"
                                href="<?= $escape($sandboxPaymentUrl) ?>"
                            >Mở trang thanh toán Sandbox QR</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="border rounded p-3 text-secondary">
                            <?= $isPending
                                ? 'Không thể tạo mã QR Sandbox cho máy chủ hiện tại.'
                                : 'Chưa có đủ thông tin tài khoản để tạo mã QR.' ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php if (!empty($receipt['note'])): ?>
            <section class="mt-4">
                <h2 class="h6 fw-bold">Ghi chú</h2>
                <div><?= nl2br($escape($receipt['note'])) ?></div>
            </section>
        <?php endif; ?>

        <div class="text-end mt-5">
            <div>Ngày lập hóa đơn: <?= $escape(date('d/m/Y', strtotime((string) $receipt['created_at']))) ?></div>
            <div class="fw-bold mt-3">Người lập hóa đơn</div>
            <div class="text-secondary small">(Ký, ghi rõ họ tên)</div>
            <div style="height: 75px;"></div>
        </div>
    </article>
</main>

<?php if ($isPending && $sandboxPaymentUrl !== null): ?>
    <script>
        (() => {
            const statusUrl = <?= json_encode(
                $statusUrl,
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
            ) ?>;
            const badge = document.getElementById('payment-status-badge');
            const paidAt = document.getElementById('payment-paid-at');
            const qr = document.getElementById('payment-qr');
            const sandboxLink = document.getElementById('sandbox-pay-link');

            const checkPaymentStatus = async () => {
                try {
                    const response = await fetch(statusUrl, {
                        cache: 'no-store',
                        headers: { Accept: 'application/json' }
                    });
                    if (!response.ok) {
                        return;
                    }

                    const result = await response.json();
                    if (result.payment_status !== 'paid') {
                        return;
                    }

                    badge.className = 'badge text-bg-success';
                    badge.textContent = 'ĐÃ THANH TOÁN';
                    if (result.paid_at) {
                        paidAt.textContent = 'Thời gian: ' + result.paid_at;
                        paidAt.classList.remove('d-none');
                    }
                    if (qr) {
                        qr.classList.add('d-none');
                    }
                    if (sandboxLink) {
                        sandboxLink.classList.add('d-none');
                    }
                    window.clearInterval(pollingTimer);
                } catch (error) {
                    console.error('Không thể kiểm tra trạng thái thanh toán.', error);
                }
            };

            const pollingTimer = window.setInterval(checkPaymentStatus, 3000);
            checkPaymentStatus();
        })();
    </script>
<?php endif; ?>
