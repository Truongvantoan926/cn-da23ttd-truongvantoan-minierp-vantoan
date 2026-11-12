<main class="container flex-grow-1 py-4 py-lg-5">
    <div class="mx-auto" style="max-width: 900px;">
        <div class="mb-4">
            <a class="text-decoration-none" href="<?= BASE_URL ?>/product/index">
                <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>
                Danh sách sản phẩm
            </a>
            <h1 class="h3 mt-3 mb-1">Sửa sản phẩm</h1>
            <p class="text-secondary mb-0">Cập nhật thông tin cơ bản của sản phẩm.</p>
        </div>

        <?php if (isset($error) && is_string($error) && $error !== ''): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form
            class="card border-0 shadow-sm"
            action="<?= BASE_URL ?>/product/update/<?= rawurlencode((string) $product['id']) ?>"
            method="POST"
            enctype="multipart/form-data"
        >
            <div class="card-body p-4">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                >

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control"
                            id="sku"
                            name="sku"
                            value="<?= htmlspecialchars($values['sku'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            maxlength="60"
                            pattern="[A-Za-z0-9][A-Za-z0-9._-]{0,59}"
                            required
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="name" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
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
                        <label for="category_id" class="form-label">Danh mục</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Không phân loại</option>
                            <?php foreach ($categories as $category): ?>
                                <option
                                    value="<?= htmlspecialchars((string) $category['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    <?= $values['category_id'] === (string) $category['id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars((string) $category['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="product_images" class="form-label">Thêm ảnh sản phẩm</label>
                        <input
                            type="file"
                            class="form-control"
                            id="product_images"
                            name="product_images[]"
                            accept="image/*"
                            multiple
                        >
                        <div class="form-text">Tối đa 8 ảnh mỗi lần tải lên, mỗi ảnh không quá 5 MB.</div>
                        <div class="mt-3">
                            <div class="form-label">Ảnh hiện tại</div>
                            <?php if ($images === []): ?>
                                <p class="text-secondary small mb-0">Sản phẩm chưa có ảnh.</p>
                            <?php else: ?>
                                <div class="d-flex flex-wrap gap-3 mt-2">
                                    <?php foreach ($images as $image): ?>
                                        <div class="position-relative d-inline-block">
                                            <img
                                                class="img-thumbnail"
                                                src="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/' . ltrim((string) $image['image_path'], '/'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                alt="Ảnh sản phẩm"
                                                style="width: 80px; height: 80px; object-fit: cover;"
                                            >
                                            <form
                                                class="d-inline"
                                                action="<?= BASE_URL ?>/product/deleteImage/<?= rawurlencode((string) $image['id']) ?>"
                                                method="POST"
                                                onsubmit="return confirm('Xóa ảnh này?');"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                >
                                                <button
                                                    class="btn btn-danger btn-sm position-absolute"
                                                    type="submit"
                                                    aria-label="Xóa ảnh sản phẩm"
                                                    style="top: -5px; right: -5px; padding: 2px 6px; font-size: 10px; border-radius: 50%; line-height: 1;"
                                                >
                                                    X
                                                </button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="unit" class="form-label">Đơn vị tính <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control"
                            id="unit"
                            name="unit"
                            value="<?= htmlspecialchars($values['unit'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            maxlength="30"
                            required
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="cost_price" class="form-label">Giá vốn (VND) <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            class="form-control"
                            id="cost_price"
                            name="cost_price"
                            value="<?= htmlspecialchars(\App\Core\CurrencyFormatter::formatInput((string) $values['cost_price']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            min="0"
                            step="0.01"
                            required
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="sale_price" class="form-label">Giá bán (VND) <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            class="form-control"
                            id="sale_price"
                            name="sale_price"
                            value="<?= htmlspecialchars(\App\Core\CurrencyFormatter::formatInput((string) $values['sale_price']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            min="0"
                            step="0.01"
                            required
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="min_stock" class="form-label">Tồn kho tối thiểu <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            class="form-control"
                            id="min_stock"
                            name="min_stock"
                            value="<?= htmlspecialchars($values['min_stock'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            min="0"
                            step="0.001"
                            required
                        >
                        <div class="form-text">Số lượng tồn thực tế được cập nhật qua nghiệp vụ nhập/xuất kho.</div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-0 px-4 pb-4">
                <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/product/index">Hủy</a>
                <button type="submit" class="btn btn-primary ms-2">
                    <i class="fa-solid fa-floppy-disk me-2" aria-hidden="true"></i>
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</main>
