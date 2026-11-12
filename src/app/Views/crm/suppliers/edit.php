<main class="container flex-grow-1 py-4 py-lg-5">
    <div class="mx-auto" style="max-width: 900px;">
        <div class="mb-4">
            <a class="btn btn-secondary btn-sm" href="<?= BASE_URL ?>/supplier/index">Quay lại danh sách</a>
            <h1 class="h3 mt-3 mb-1">Sửa nhà cung cấp</h1>
            <p class="text-secondary mb-0">Cập nhật thông tin nhà cung cấp.</p>
        </div>

        <?php if (isset($error) && is_string($error) && $error !== ''): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form
            class="card border-0 shadow-sm"
            action="<?= BASE_URL ?>/supplier/update/<?= rawurlencode((string) $supplier['id']) ?>"
            method="POST"
        >
            <div class="card-body p-4">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                >

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="code" class="form-label">Mã nhà cung cấp <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control"
                            id="code"
                            name="code"
                            value="<?= htmlspecialchars($values['code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            maxlength="30"
                            pattern="[A-Za-z0-9][A-Za-z0-9_-]{0,29}"
                            required
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="name" class="form-label">Tên nhà cung cấp <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control"
                            id="name"
                            name="name"
                            value="<?= htmlspecialchars($values['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            maxlength="180"
                            required
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input
                            type="tel"
                            class="form-control"
                            id="phone"
                            name="phone"
                            value="<?= htmlspecialchars($values['phone'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            maxlength="30"
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            value="<?= htmlspecialchars($values['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            maxlength="190"
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="address" class="form-label">Địa chỉ</label>
                        <input
                            type="text"
                            class="form-control"
                            id="address"
                            name="address"
                            value="<?= htmlspecialchars($values['address'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            maxlength="255"
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="tax_code" class="form-label">Mã số thuế</label>
                        <input
                            type="text"
                            class="form-control"
                            id="tax_code"
                            name="tax_code"
                            value="<?= htmlspecialchars($values['tax_code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            maxlength="40"
                        >
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-0 px-4 pb-4">
                <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/supplier/index">Hủy</a>
                <button type="submit" class="btn btn-primary ms-2">
                    <i class="fa-solid fa-floppy-disk me-2" aria-hidden="true"></i>
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</main>
