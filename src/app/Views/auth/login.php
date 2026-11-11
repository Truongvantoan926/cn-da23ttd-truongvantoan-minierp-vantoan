<main class="container d-flex flex-grow-1 align-items-center justify-content-center py-5">
    <div class="card border-0 shadow-sm w-100" style="max-width: 420px;">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="text-primary mb-3">
                    <i class="fa-solid fa-boxes-stacked fa-3x" aria-hidden="true"></i>
                </div>
                <h1 class="h3 mb-2">Đăng nhập</h1>
                <p class="text-secondary mb-0">Truy cập hệ thống Mini ERP</p>
            </div>

            <?php if (isset($error) && is_string($error) && $error !== ''): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/auth/login" method="POST">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                >

                <div class="mb-3">
                    <label for="username" class="form-label">Tên đăng nhập</label>
                    <input
                        type="text"
                        class="form-control"
                        id="username"
                        name="username"
                        autocomplete="username"
                        maxlength="80"
                        required
                        autofocus
                    >
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa-solid fa-right-to-bracket me-2" aria-hidden="true"></i>
                    Đăng nhập
                </button>
            </form>
        </div>
    </div>
</main>
