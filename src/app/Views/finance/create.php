<main class="container flex-grow-1 py-4 py-lg-5">
    <div class="mx-auto" style="max-width: 760px;">
        <div class="mb-4">
            <a class="text-decoration-none" href="<?= BASE_URL ?>/finance/index">
                <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>
                Sổ quỹ Thu/Chi
            </a>
            <h1 class="h3 mt-3 mb-1">Lập phiếu Thu/Chi</h1>
            <p class="text-secondary mb-0">Nhập thông tin giao dịch nội bộ.</p>
        </div>

        <?php if (isset($error) && is_string($error) && $error !== ''): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form class="card border-0 shadow-sm" action="<?= BASE_URL ?>/finance/store" method="POST">
            <div class="card-body p-4">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                >

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="type" class="form-label">Loại giao dịch <span class="text-danger">*</span></label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="income" <?= $values['type'] === 'income' ? 'selected' : '' ?>>Thu</option>
                            <option value="expense" <?= $values['type'] === 'expense' ? 'selected' : '' ?>>Chi</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="category" class="form-label">Danh mục <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control"
                            id="category"
                            name="category"
                            value="<?= htmlspecialchars($values['category'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            maxlength="100"
                            required
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="amount" class="form-label">Số tiền (VND) <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            class="form-control"
                            id="amount"
                            name="amount"
                            value="<?= htmlspecialchars($values['amount'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            min="0.01"
                            max="9999999999999.99"
                            step="0.01"
                            required
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="payment_method" class="form-label">Hình thức thanh toán <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <?php foreach ($paymentMethods as $paymentMethod): ?>
                                <option
                                    value="<?= htmlspecialchars($paymentMethod, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    <?= $values['payment_method'] === $paymentMethod ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($paymentMethod, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="trans_date" class="form-label">Ngày giao dịch <span class="text-danger">*</span></label>
                        <input
                            type="date"
                            class="form-control"
                            id="trans_date"
                            name="trans_date"
                            value="<?= htmlspecialchars($values['trans_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            required
                        >
                    </div>
                    <div class="col-12">
                        <label for="note" class="form-label">Ghi chú</label>
                        <textarea
                            class="form-control"
                            id="note"
                            name="note"
                            rows="4"
                            maxlength="5000"
                        ><?= htmlspecialchars($values['note'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-0 px-4 pb-4">
                <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/finance/index">Hủy</a>
                <button type="submit" class="btn btn-primary ms-2">
                    <i class="fa-solid fa-floppy-disk me-2" aria-hidden="true"></i>
                    Lưu giao dịch
                </button>
            </div>
        </form>
    </div>
</main>
