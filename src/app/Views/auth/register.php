<main class="container d-flex flex-grow-1 align-items-center justify-content-center py-5">
    <div class="card border-0 shadow-sm w-100" style="max-width: 480px;">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="text-primary mb-3">
                    <i class="fa-solid fa-user-plus fa-3x" aria-hidden="true"></i>
                </div>
                <h1 class="h3 mb-2">Đăng ký tài khoản</h1>
                <p class="text-secondary mb-0">Tạo tài khoản nhân viên Mini ERP</p>
            </div>

            <?php if (isset($error) && is_string($error) && $error !== ''): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/auth/register" method="POST">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                >

                <div class="mb-3">
                    <label for="full_name" class="form-label">Họ tên</label>
                    <input
                        type="text"
                        class="form-control"
                        id="full_name"
                        name="full_name"
                        value="<?= htmlspecialchars($values['full_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                        maxlength="150"
                        autocomplete="name"
                        required
                        autofocus
                    >
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Tên đăng nhập</label>
                    <input
                        type="text"
                        class="form-control"
                        id="username"
                        name="username"
                        value="<?= htmlspecialchars($values['username'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                        maxlength="80"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Số điện thoại</label>
                    <input
                        type="tel"
                        class="form-control"
                        id="phone"
                        name="phone"
                        value="<?= htmlspecialchars($values['phone'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                        maxlength="30"
                        autocomplete="tel"
                    >
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        minlength="8"
                        maxlength="72"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    Đăng ký
                </button>
                <a class="btn btn-outline-secondary w-100" href="<?= BASE_URL ?>/auth/index">
                    Đã có tài khoản? Đăng nhập
                </a>
            </form>
        </div>
    </div>
</main>
