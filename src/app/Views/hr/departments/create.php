<main class="container flex-grow-1 py-4 py-lg-5">
    <div class="mx-auto" style="max-width: 720px;">
        <div class="mb-4">
            <a class="btn btn-secondary btn-sm" href="<?= BASE_URL ?>/department/index">Quay lại danh sách</a>
            <h1 class="h3 mt-3 mb-1">Thêm phòng ban</h1>
            <p class="text-secondary mb-0">Nhập mã và tên cho phòng ban mới.</p>
        </div>

        <?php if (isset($error) && is_string($error) && $error !== ''): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form class="card border-0 shadow-sm" action="<?= BASE_URL ?>/department/store" method="POST">
            <div class="card-body p-4">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                >

                <div class="mb-3">
                    <label for="code" class="form-label">Mã phòng ban</label>
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
                    <div class="form-text">Dùng chữ không dấu, số, dấu gạch ngang hoặc gạch dưới.</div>
                </div>

                <div class="mb-0">
                    <label for="name" class="form-label">Tên phòng ban</label>
                    <input
                        type="text"
                        class="form-control"
                        id="name"
                        name="name"
                        value="<?= htmlspecialchars($values['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                        maxlength="120"
                        required
                    >
                </div>
            </div>
            <div class="card-footer bg-white border-0 px-4 pb-4">
                <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/department/index">Hủy</a>
                <button type="submit" class="btn btn-primary ms-2">
                    <i class="fa-solid fa-floppy-disk me-2" aria-hidden="true"></i>
                    Lưu phòng ban
                </button>
            </div>
        </form>
    </div>
</main>
