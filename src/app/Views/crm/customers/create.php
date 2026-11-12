<main class="container flex-grow-1 py-4 py-lg-5">
    <div class="mx-auto" style="max-width: 900px;">
        <div class="mb-4">
            <a class="btn btn-secondary btn-sm" href="<?= BASE_URL ?>/customer/index">Quay lại danh sách</a>
            <h1 class="h3 mt-3 mb-1">Thêm khách hàng</h1>
            <p class="text-secondary mb-0">Nhập thông tin khách hàng.</p>
        </div>

        <?php if (isset($error) && is_string($error) && $error !== ''): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form class="card border-0 shadow-sm" action="<?= BASE_URL ?>/customer/store" method="POST">
            <div class="card-body p-4">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                >

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="code" class="form-label">Mã khách hàng <span class="text-danger">*</span></label>
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
                        <label for="name" class="form-label">Tên khách hàng <span class="text-danger">*</span></label>
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
                        <label for="type" class="form-label">Phân loại <span class="text-danger">*</span></label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="individual" <?= $values['type'] === 'individual' ? 'selected' : '' ?>>Cá nhân</option>
                            <option value="business" <?= $values['type'] === 'business' ? 'selected' : '' ?>>Doanh nghiệp</option>
                        </select>
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
                </div>
            </div>
            <div class="card-footer bg-white border-0 px-4 pb-4">
                <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/customer/index">Hủy</a>
                <button type="submit" class="btn btn-primary ms-2">
                    <i class="fa-solid fa-floppy-disk me-2" aria-hidden="true"></i>
                    Lưu khách hàng
                </button>
            </div>
        </form>
    </div>
</main>
