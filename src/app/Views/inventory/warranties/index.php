<main class="container-fluid flex-grow-1 p-4 p-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Bảo hành &amp; Serial/IMEI</h1>
            <p class="text-secondary mb-0">Theo dõi serial sản phẩm, trạng thái và hạn bảo hành</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php if ($canManage): ?>
                <button
                    class="btn btn-success"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#addSerialModal"
                >
                    Thêm Serial/IMEI
                </button>
            <?php endif; ?>
            <a class="btn btn-primary" href="<?= BASE_URL ?>/warranty/search">
                <i class="fa-solid fa-magnifying-glass me-2" aria-hidden="true"></i>
                Tra cứu bảo hành
            </a>
        </div>
    </div>

    <?php if (isset($error) && is_string($error) && $error !== ''): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (isset($success) && is_string($success) && $success !== ''): ?>
        <div class="alert alert-success" role="status">
            <?= htmlspecialchars($success, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-4">Serial/IMEI</th>
                        <th scope="col">SKU</th>
                        <th scope="col">Sản phẩm</th>
                        <th scope="col">Tình trạng</th>
                        <th scope="col">Ngày bán</th>
                        <th scope="col">Thời hạn</th>
                        <th scope="col" class="pe-4">Hạn bảo hành</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($serials === []): ?>
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-5">
                                Chưa có sản phẩm nào được gắn Serial/IMEI.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($serials as $serial): ?>
                            <?php
                            $statusLabels = [
                                'in_stock' => 'Trong kho',
                                'sold' => 'Đã bán',
                                'warranty' => 'Đang bảo hành',
                            ];
                            $statusClasses = [
                                'in_stock' => 'text-bg-primary',
                                'sold' => 'text-bg-secondary',
                                'warranty' => 'text-bg-warning',
                            ];
                            $status = (string) $serial['status'];
                            ?>
                            <tr>
                                <td class="ps-4 fw-semibold">
                                    <a
                                        href="<?= BASE_URL ?>/warranty/search?serial_number=<?= rawurlencode((string) $serial['serial_number']) ?>"
                                        class="text-decoration-none"
                                    >
                                        <?= htmlspecialchars((string) $serial['serial_number'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars((string) $serial['sku'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) $serial['product_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td>
                                    <span class="badge <?= $statusClasses[$status] ?? 'text-bg-dark' ?>">
                                        <?= htmlspecialchars($statusLabels[$status] ?? $status, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars((string) ($serial['sold_date'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= number_format((int) $serial['warranty_months']) ?> tháng</td>
                                <td class="pe-4"><?= htmlspecialchars((string) ($serial['warranty_end_date'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php require $paginationViewPath; ?>

<?php if ($canManage): ?>
    <div
        class="modal fade"
        id="addSerialModal"
        tabindex="-1"
        aria-labelledby="addSerialModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="<?= BASE_URL ?>/warranty/store" method="POST">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="addSerialModalLabel">Thêm Serial/IMEI vào kho</h2>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Đóng"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                        >

                        <div class="mb-3">
                            <label for="serial-product-id" class="form-label">Sản phẩm</label>
                            <input
                                type="text"
                                id="searchProductSerial"
                                class="form-control mb-2"
                                placeholder="Nhập mã SKU hoặc tên sản phẩm để tìm nhanh..."
                                aria-label="Tìm sản phẩm theo mã SKU hoặc tên"
                                autocomplete="off"
                            >
                            <select
                                class="form-select"
                                id="serial-product-id"
                                name="product_id"
                                required
                            >
                                <option value="">-- Chọn sản phẩm --</option>
                                <?php foreach ($products as $product): ?>
                                    <option
                                        value="<?= (int) $product['id'] ?>"
                                        <?= (string) $addSerialValues['product_id'] === (string) $product['id'] ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars((string) $product['sku'] . ' — ' . (string) $product['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="serial-numbers" class="form-label">Danh sách Serial/IMEI</label>
                            <textarea
                                class="form-control"
                                id="serial-numbers"
                                name="serial_numbers"
                                rows="6"
                                maxlength="100000"
                                required
                                aria-describedby="serial-numbers-help"
                            ><?= htmlspecialchars($addSerialValues['serial_numbers'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
                            <div class="form-text" id="serial-numbers-help">
                                Nhập mỗi mã trên một dòng, tối đa 1000 mã mỗi lần.
                            </div>
                        </div>

                        <div>
                            <label for="warranty-months" class="form-label">Thời hạn bảo hành (tháng)</label>
                            <input
                                class="form-control"
                                id="warranty-months"
                                name="warranty_months"
                                type="number"
                                min="1"
                                max="120"
                                step="1"
                                value="<?= htmlspecialchars($addSerialValues['warranty_months'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                required
                            >
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Hủy
                        </button>
                        <button type="submit" class="btn btn-primary">Lưu vào kho</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        (() => {
            const modal = document.getElementById('addSerialModal');
            const searchInput = document.getElementById('searchProductSerial');
            const productSelect = document.getElementById('serial-product-id');
            const productOptions = Array.from(productSelect.options)
                .filter((option) => option.value !== '');

            const resetSearch = () => {
                searchInput.value = '';
                productOptions.forEach((option) => {
                    option.hidden = false;
                });
            };

            searchInput.addEventListener('input', () => {
                const keyword = searchInput.value.trim().toLowerCase();
                if (keyword === '') {
                    resetSearch();
                    productSelect.value = '';
                    return;
                }

                let firstMatch = null;
                productOptions.forEach((option) => {
                    const matches = option.textContent.toLowerCase().includes(keyword);
                    option.hidden = !matches;
                    if (matches && firstMatch === null) {
                        firstMatch = option;
                    }
                });

                productSelect.value = firstMatch === null ? '' : firstMatch.value;
            });

            modal.addEventListener('show.bs.modal', resetSearch);
        })();
    </script>
    <?php if ($openAddSerialModal): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('addSerialModal')).show();
            });
        </script>
    <?php endif; ?>
<?php endif; ?>
</main>
