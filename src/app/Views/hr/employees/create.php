<main class="container flex-grow-1 py-4 py-lg-5">
    <div class="mx-auto" style="max-width: 900px;">
        <div class="mb-4">
            <a class="text-decoration-none" href="<?= BASE_URL ?>/employee/index">
                <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>
                Danh sách nhân viên
            </a>
            <h1 class="h3 mt-3 mb-1">Thêm nhân viên</h1>
            <p class="text-secondary mb-0">Nhập thông tin hồ sơ nhân viên.</p>
        </div>

        <?php if (isset($error) && is_string($error) && $error !== ''): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form class="card border-0 shadow-sm" action="<?= BASE_URL ?>/employee/store" method="POST">
            <div class="card-body p-4">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                >

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="code" class="form-label">Mã nhân viên <span class="text-danger">*</span></label>
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
                        <label for="full_name" class="form-label">Họ tên <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control"
                            id="full_name"
                            name="full_name"
                            value="<?= htmlspecialchars($values['full_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            maxlength="150"
                            required
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="dob" class="form-label">Ngày sinh</label>
                        <input
                            type="date"
                            class="form-control"
                            id="dob"
                            name="dob"
                            value="<?= htmlspecialchars($values['dob'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
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
                        <label for="department_id" class="form-label">Phòng ban</label>
                        <select class="form-select" id="department_id" name="department_id">
                            <option value="">Chưa phân công</option>
                            <?php foreach ($departments as $department): ?>
                                <option
                                    value="<?= htmlspecialchars((string) $department['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    <?= $values['department_id'] === (string) $department['id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars((string) $department['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="position" class="form-label">Chức vụ</label>
                        <input
                            type="text"
                            class="form-control"
                            id="position"
                            name="position"
                            value="<?= htmlspecialchars($values['position'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            maxlength="100"
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="base_salary" class="form-label">Lương cơ bản (VND) <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            class="form-control"
                            id="base_salary"
                            name="base_salary"
                            value="<?= htmlspecialchars(\App\Core\CurrencyFormatter::formatInput((string) $values['base_salary']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            min="0"
                            step="0.01"
                            required
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="hire_date" class="form-label">Ngày vào làm</label>
                        <input
                            type="date"
                            class="form-control"
                            id="hire_date"
                            name="hire_date"
                            value="<?= htmlspecialchars($values['hire_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active" <?= $values['status'] === 'active' ? 'selected' : '' ?>>Đang làm việc</option>
                            <option value="inactive" <?= $values['status'] === 'inactive' ? 'selected' : '' ?>>Đã nghỉ</option>
                            <option value="on_leave" <?= $values['status'] === 'on_leave' ? 'selected' : '' ?>>Nghỉ phép</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-0 px-4 pb-4">
                <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/employee/index">Hủy</a>
                <button type="submit" class="btn btn-primary ms-2">
                    <i class="fa-solid fa-floppy-disk me-2" aria-hidden="true"></i>
                    Lưu nhân viên
                </button>
            </div>
        </form>
    </div>
</main>
