<main class="container flex-grow-1 py-4 py-lg-5">
    <div class="mx-auto" style="max-width: 1000px;">
        <div class="mb-4">
            <h1 class="h3 mb-1">Hồ sơ cá nhân</h1>
            <p class="text-secondary mb-0">
                Quản lý thông tin liên hệ và bảo mật tài khoản của bạn.
            </p>
        </div>

        <?php if (isset($error) && is_string($error) && $error !== ''): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        <?php if (isset($success) && is_string($success) && $success !== ''): ?>
            <div class="alert alert-success" role="alert">
                <?= htmlspecialchars($success, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-6">
                <section class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h2 class="h5 mb-0">
                            <i class="fa-solid fa-user-pen text-primary me-2" aria-hidden="true"></i>
                            Thông tin cá nhân
                        </h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-secondary">Tên đăng nhập</label>
                            <input
                                type="text"
                                class="form-control"
                                value="<?= htmlspecialchars((string) $profile['username'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                readonly
                            >
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary">Vai trò</label>
                            <input
                                type="text"
                                class="form-control"
                                value="<?= htmlspecialchars((string) $profile['role_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                readonly
                            >
                        </div>

                        <form action="<?= BASE_URL ?>/profile/update" method="POST">
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            >
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Họ tên <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="full_name"
                                    name="full_name"
                                    value="<?= htmlspecialchars((string) $profileValues['full_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    maxlength="150"
                                    autocomplete="name"
                                    required
                                >
                            </div>
                            <div class="mb-4">
                                <label for="phone" class="form-label">Số điện thoại</label>
                                <input
                                    type="tel"
                                    class="form-control"
                                    id="phone"
                                    name="phone"
                                    value="<?= htmlspecialchars((string) $profileValues['phone'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    maxlength="30"
                                    autocomplete="tel"
                                >
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-2" aria-hidden="true"></i>
                                Lưu thông tin
                            </button>
                        </form>
                    </div>
                </section>
            </div>

            <div class="col-lg-6">
                <section class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h2 class="h5 mb-0">
                            <i class="fa-solid fa-lock text-primary me-2" aria-hidden="true"></i>
                            Đổi mật khẩu
                        </h2>
                    </div>
                    <div class="card-body p-4">
                        <form action="<?= BASE_URL ?>/profile/change-password" method="POST">
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            >
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="current_password"
                                    name="current_password"
                                    autocomplete="current-password"
                                    required
                                >
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Mật khẩu mới</label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="new_password"
                                    name="new_password"
                                    minlength="8"
                                    maxlength="72"
                                    autocomplete="new-password"
                                    required
                                >
                                <div class="form-text">Mật khẩu dài từ 8 đến 72 byte.</div>
                            </div>
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Nhập lại mật khẩu mới</label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="confirm_password"
                                    name="confirm_password"
                                    minlength="8"
                                    maxlength="72"
                                    autocomplete="new-password"
                                    required
                                >
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-key me-2" aria-hidden="true"></i>
                                Cập nhật mật khẩu
                            </button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>
</main>
