<style>
.print-document {
    width: min(100%, 210mm);
    min-height: 297mm;
    margin: 0 auto;
    padding: 18mm 20mm;
    color: #111;
    background: #fff;
    font-family: "Times New Roman", serif;
    font-size: 15px;
    line-height: 1.45;
}

.print-document table {
    border-color: #444;
}

.signature-area {
    break-inside: avoid;
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
        padding: 18mm 20mm;
        box-shadow: none !important;
    }

    .table-responsive {
        overflow: visible !important;
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
$buyerName = $isInbound ? 'CÔNG TY MINI ERP' : ($receipt['partner_name'] ?? 'Khách hàng');
$sellerName = $isInbound ? ($receipt['partner_name'] ?? 'Nhà cung cấp') : 'CÔNG TY MINI ERP';
$paymentBank = $isInbound ? ($receipt['partner_bank_name'] ?? null) : 'Vietcombank';
$paymentAccount = $isInbound ? ($receipt['partner_bank_account'] ?? null) : '0123456789';
$paymentHolder = $isInbound ? ($receipt['partner_account_holder'] ?? null) : 'CONG TY MINI ERP';
?>

<main class="container-fluid flex-grow-1 py-4">
    <div class="no-print d-flex justify-content-between align-items-center gap-3 mb-3">
        <a class="btn btn-link text-decoration-none" href="<?= BASE_URL ?>/receipt/index">Quay lại danh sách</a>
        <button class="btn btn-primary" type="button" onclick="window.print()">In hợp đồng</button>
    </div>

    <article class="print-document shadow-sm">
        <header class="text-center mb-4">
            <div class="fw-bold">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
            <div class="fw-bold text-decoration-underline">Độc lập - Tự do - Hạnh phúc</div>
            <h1 class="h4 fw-bold mt-4 mb-1">HỢP ĐỒNG MUA BÁN HÀNG HÓA</h1>
            <div>Số: HĐ-<?= $escape($receipt['code']) ?></div>
        </header>

        <p>
            Hôm nay, ngày <?= $escape(date('d', strtotime((string) $receipt['created_at']))) ?>
            tháng <?= $escape(date('m', strtotime((string) $receipt['created_at']))) ?>
            năm <?= $escape(date('Y', strtotime((string) $receipt['created_at']))) ?>,
            các bên gồm:
        </p>

        <section class="mb-3">
            <h2 class="h6 fw-bold">BÊN A (BÊN MUA): <?= $escape($buyerName) ?></h2>
            <?php if (!$isInbound): ?>
                <div>Đại diện: Công ty Mini ERP</div>
            <?php else: ?>
                <div>Đại diện: Công ty Mini ERP</div>
            <?php endif; ?>
            <div>Địa chỉ: <?= $isInbound ? '—' : $escape($receipt['partner_address']) ?></div>
            <div>Điện thoại: <?= $isInbound ? '—' : $escape($receipt['partner_phone']) ?></div>
            <div>Email: <?= $isInbound ? '—' : $escape($receipt['partner_email']) ?></div>
        </section>

        <section class="mb-4">
            <h2 class="h6 fw-bold">BÊN B (BÊN BÁN): <?= $escape($sellerName) ?></h2>
            <div>Đại diện: <?= $isInbound ? $escape($receipt['partner_name']) : 'Công ty Mini ERP' ?></div>
            <div>Địa chỉ: <?= $isInbound ? $escape($receipt['partner_address']) : '—' ?></div>
            <div>Điện thoại: <?= $isInbound ? $escape($receipt['partner_phone']) : '—' ?></div>
            <div>Email: <?= $isInbound ? $escape($receipt['partner_email']) : '—' ?></div>
        </section>

        <section class="mb-4">
            <h2 class="h6 fw-bold">Điều 1. Hàng hóa và giá trị hợp đồng</h2>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Tên hàng hóa</th>
                            <th>Đơn vị tính</th>
                            <th class="text-end">Số lượng</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
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
                            <th colspan="5" class="text-end">Tổng giá trị hợp đồng</th>
                            <th class="text-end text-nowrap"><?= \App\Core\CurrencyFormatter::format($receipt['total_amount']) ?> ₫</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        <section class="mb-4">
            <h2 class="h6 fw-bold">Điều 2. Phương thức thanh toán và thông tin chuyển khoản</h2>
            <p class="mb-1">Thanh toán bằng hình thức chuyển khoản ngân hàng.</p>
            <div>Ngân hàng: <?= $escape($paymentBank) ?></div>
            <div>Số tài khoản: <?= $escape($paymentAccount) ?></div>
            <div>Chủ tài khoản: <?= $escape($paymentHolder) ?></div>
            <div>Nội dung chuyển khoản: Thanh toan hop dong <?= $escape($receipt['code']) ?></div>
        </section>

        <section class="mb-4">
            <h2 class="h6 fw-bold">Điều 3. Giao nhận, bảo hành và cam kết chung</h2>
            <p>
                Các bên thống nhất giao nhận hàng hóa theo danh mục tại Điều 1. Quyền lợi bảo hành
                được thực hiện theo chính sách bảo hành của sản phẩm và thỏa thuận giữa hai bên.
                Hai bên cam kết thực hiện đúng các nội dung hợp đồng; mọi sửa đổi, bổ sung phải
                được lập thành văn bản có xác nhận của cả hai bên.
            </p>
            <p>Hợp đồng được lập thành hai bản có giá trị như nhau, mỗi bên giữ một bản.</p>
        </section>

        <div class="row text-center mt-5 signature-area">
            <div class="col-6">
                <div class="fw-bold">ĐẠI DIỆN BÊN A</div>
                <div class="small">(Ký, ghi rõ họ tên và đóng dấu)</div>
                <div style="height: 100px;"></div>
            </div>
            <div class="col-6">
                <div class="fw-bold">ĐẠI DIỆN BÊN B</div>
                <div class="small">(Ký, ghi rõ họ tên và đóng dấu)</div>
                <div style="height: 100px;"></div>
            </div>
        </div>
    </article>
</main>
