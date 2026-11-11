<main class="container flex-grow-1 py-4 py-lg-5">
    <div class="mx-auto" style="max-width: 1000px;">
        <div class="mb-4">
            <a class="text-decoration-none" href="<?= BASE_URL ?>/receipt/index">
                <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>
                Danh sách giao dịch kho
            </a>
            <h1 class="h3 mt-3 mb-1">Tạo phiếu nhập / xuất kho</h1>
            <p class="text-secondary mb-0">Tồn kho được cập nhật tự động sau khi lưu phiếu.</p>
        </div>

        <?php if (isset($error) && is_string($error) && $error !== ''): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form class="card border-0 shadow-sm" action="<?= BASE_URL ?>/receipt/store" method="POST">
            <div class="card-body p-4">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                >

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="type" class="form-label">Loại phiếu <span class="text-danger">*</span></label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="IN" <?= $values['type'] === 'IN' ? 'selected' : '' ?>>Nhập kho</option>
                            <option value="OUT" <?= $values['type'] === 'OUT' ? 'selected' : '' ?>>Xuất kho</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="partner_id" class="form-label">Đối tác <span class="text-danger">*</span></label>
                        <select class="form-select" id="partner_id" name="partner_id" required>
                            <option value="">-- Chọn đối tác --</option>
                            <?php if ($suppliers !== []): ?>
                            <optgroup label="Nhà cung cấp" data-type="IN">
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option
                                        value="<?= htmlspecialchars((string) $supplier['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                        <?= $values['type'] === 'IN' && $values['partner_id'] === (string) $supplier['id'] ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars((string) $supplier['code'] . ' - ' . (string) $supplier['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endif; ?>
                            <?php if ($customers !== []): ?>
                            <optgroup label="Khách hàng" data-type="OUT">
                                <?php foreach ($customers as $customer): ?>
                                    <option
                                        value="<?= htmlspecialchars((string) $customer['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                        <?= $values['type'] === 'OUT' && $values['partner_id'] === (string) $customer['id'] ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars((string) $customer['code'] . ' - ' . (string) $customer['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endif; ?>
                        </select>
                        <div class="form-text" id="partner-help">Phiếu nhập chọn nhà cung cấp; phiếu xuất chọn khách hàng.</div>
                        <?php if ($suppliers === [] && $customers === []): ?>
                            <div class="form-text text-warning">Chưa có nhà cung cấp hoặc khách hàng để chọn.</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label for="employee_id" class="form-label">Nhân viên lập phiếu</label>
                        <select class="form-select" id="employee_id" name="employee_id">
                            <option value="">Không chọn</option>
                            <?php foreach ($employees as $employee): ?>
                                <option
                                    value="<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    <?= $values['employee_id'] === (string) $employee['id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars((string) $employee['full_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h2 class="h5 mb-0">Sản phẩm trong phiếu</h2>
                    <button class="btn btn-sm btn-outline-primary" type="button" id="add-item">
                        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>
                        Thêm dòng sản phẩm
                    </button>
                </div>

                <div id="receipt-items" class="vstack gap-3">
                    <?php foreach ($values['items'] as $index => $item): ?>
                        <div class="row g-2 align-items-end border rounded p-3 mx-0" data-item-row>
                            <div class="col-lg-5">
                                <label class="form-label">Sản phẩm <span class="text-danger">*</span></label>
                                <select
                                    class="form-select"
                                    name="items[<?= (int) $index ?>][product_id]"
                                    data-product-select
                                    required
                                >
                                    <option value="">Chọn sản phẩm</option>
                                    <?php foreach ($products as $product): ?>
                                        <option
                                            value="<?= htmlspecialchars((string) $product['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                            data-cost-price="<?= htmlspecialchars((string) $product['cost_price'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                            data-sale-price="<?= htmlspecialchars((string) $product['sale_price'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                            <?= $item['product_id'] === (string) $product['id'] ? 'selected' : '' ?>
                                        >
                                            <?= htmlspecialchars((string) $product['sku'] . ' — ' . (string) $product['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-sm-4 col-lg-2">
                                <label class="form-label">Số lượng</label>
                                <input
                                    class="form-control"
                                    type="number"
                                    name="items[<?= (int) $index ?>][qty]"
                                    value="<?= htmlspecialchars($item['qty'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    min="1"
                                    step="1"
                                    required
                                >
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <label class="form-label">Đơn giá (VND)</label>
                                <input
                                    class="form-control"
                                    type="number"
                                    name="items[<?= (int) $index ?>][price]"
                                    value="<?= htmlspecialchars($item['price'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    min="0"
                                    step="0.01"
                                    data-item-price
                                    required
                                >
                            </div>
                            <div class="col-sm-2 col-lg-2">
                                <button
                                    class="btn btn-outline-danger w-100"
                                    type="button"
                                    data-remove-item
                                    aria-label="Xóa dòng sản phẩm"
                                >
                                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                    <span class="d-lg-none ms-1">Xóa</span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <template id="receipt-item-template">
                    <div class="row g-2 align-items-end border rounded p-3 mx-0" data-item-row>
                        <div class="col-lg-5">
                            <label class="form-label">Sản phẩm <span class="text-danger">*</span></label>
                            <select
                                class="form-select"
                                name="items[__INDEX__][product_id]"
                                data-product-select
                                required
                            >
                                <option value="">Chọn sản phẩm</option>
                                <?php foreach ($products as $product): ?>
                                    <option
                                        value="<?= htmlspecialchars((string) $product['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                        data-cost-price="<?= htmlspecialchars((string) $product['cost_price'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                        data-sale-price="<?= htmlspecialchars((string) $product['sale_price'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    >
                                        <?= htmlspecialchars((string) $product['sku'] . ' — ' . (string) $product['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-4 col-lg-2">
                            <label class="form-label">Số lượng</label>
                            <input
                                class="form-control"
                                type="number"
                                name="items[__INDEX__][qty]"
                                min="1"
                                step="1"
                                required
                            >
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <label class="form-label">Đơn giá (VND)</label>
                            <input
                                class="form-control"
                                type="number"
                                name="items[__INDEX__][price]"
                                min="0"
                                step="0.01"
                                data-item-price
                                required
                            >
                        </div>
                        <div class="col-sm-2 col-lg-2">
                            <button
                                class="btn btn-outline-danger w-100"
                                type="button"
                                data-remove-item
                                aria-label="Xóa dòng sản phẩm"
                            >
                                <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                <span class="d-lg-none ms-1">Xóa</span>
                            </button>
                        </div>
                    </div>
                </template>

                <div class="mt-4">
                    <label for="note" class="form-label">Ghi chú</label>
                    <textarea
                        class="form-control"
                        id="note"
                        name="note"
                        rows="3"
                        maxlength="5000"
                    ><?= htmlspecialchars($values['note'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
                </div>
            </div>
            <div class="card-footer bg-white border-0 px-4 pb-4">
                <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/receipt/index">Hủy</a>
                <button type="submit" class="btn btn-primary ms-2">
                    <i class="fa-solid fa-floppy-disk me-2" aria-hidden="true"></i>
                    Lưu phiếu và cập nhật tồn kho
                </button>
            </div>
        </form>
    </div>
</main>

<script>
(() => {
    const typeSelect = document.getElementById('type');
    const partnerSelect = document.getElementById('partner_id');
    const itemContainer = document.getElementById('receipt-items');
    const itemTemplate = document.getElementById('receipt-item-template');
    const addItemButton = document.getElementById('add-item');
    let nextItemIndex = <?= count($values['items']) ?>;

    const filterPartners = (resetSelection) => {
        Array.from(partnerSelect.querySelectorAll('optgroup[data-type]')).forEach((group) => {
            group.hidden = group.dataset.type !== typeSelect.value;
        });

        const selectedOption = partnerSelect.selectedOptions[0];
        if (
            resetSelection
            || (selectedOption && selectedOption.parentElement.dataset.type !== typeSelect.value)
        ) {
            partnerSelect.value = '';
        }
    };

    const setSuggestedPrice = (row) => {
        const productSelect = row.querySelector('[data-product-select]');
        const priceInput = row.querySelector('[data-item-price]');
        const option = productSelect.selectedOptions[0];
        if (!option || !option.value) {
            return;
        }

        priceInput.value = typeSelect.value === 'IN'
            ? option.dataset.costPrice
            : option.dataset.salePrice;
    };

    typeSelect.addEventListener('change', () => {
        filterPartners(true);
        itemContainer.querySelectorAll('[data-item-row]').forEach(setSuggestedPrice);
    });

    itemContainer.addEventListener('change', (event) => {
        if (event.target.matches('[data-product-select]')) {
            setSuggestedPrice(event.target.closest('[data-item-row]'));
        }
    });

    itemContainer.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-remove-item]');
        if (!removeButton) {
            return;
        }

        const rows = itemContainer.querySelectorAll('[data-item-row]');
        const row = removeButton.closest('[data-item-row]');
        if (rows.length === 1) {
            row.querySelector('[data-product-select]').value = '';
            row.querySelector('[name$=\"[qty]\"]').value = '';
            row.querySelector('[data-item-price]').value = '';
            return;
        }

        row.remove();
    });

    addItemButton.addEventListener('click', () => {
        const html = itemTemplate.innerHTML.replace(/__INDEX__/g, String(nextItemIndex++));
        itemContainer.insertAdjacentHTML('beforeend', html);
    });

    filterPartners(false);
})();
</script>
