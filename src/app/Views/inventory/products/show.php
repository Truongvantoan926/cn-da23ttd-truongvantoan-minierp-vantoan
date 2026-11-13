<?php
$galleryImages = [];
if ($images === []) {
    $placeholderColors = ['#e9ecef', '#dbeafe', '#dcfce7', '#fef3c7'];
    foreach ($placeholderColors as $color) {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600">'
            . '<rect width="800" height="600" fill="' . $color . '"/>'
            . '<path d="M265 390l100-110 75 80 55-55 90 85H265z" fill="#94a3b8"/>'
            . '<circle cx="500" cy="210" r="42" fill="#cbd5e1"/>'
            . '<text x="400" y="475" text-anchor="middle" fill="#475569" '
            . 'font-family="Arial,sans-serif" font-size="32">Ảnh sản phẩm</text>'
            . '</svg>';
        $galleryImages[] = [
            'is_main' => count($galleryImages) === 0 ? 1 : 0,
            'src' => 'data:image/svg+xml,' . rawurlencode($svg),
        ];
    }
} else {
    foreach ($images as $image) {
        $galleryImages[] = [
            'is_main' => (int) $image['is_main'],
            'src' => rtrim(BASE_URL, '/') . '/' . ltrim((string) $image['image_path'], '/'),
        ];
    }
}
$mainImageSrc = $galleryImages[0]['src'];
foreach ($galleryImages as $galleryImage) {
    if ($galleryImage['is_main'] === 1) {
        $mainImageSrc = $galleryImage['src'];
        break;
    }
}
?>
<main class="container-fluid flex-grow-1 p-4 p-lg-5">
    <div class="mb-4">
        <a class="btn btn-secondary btn-sm" href="<?= BASE_URL ?>/product/index">Quay lại danh sách</a>
        <h1 class="h3 mt-3 mb-1">
            <?= htmlspecialchars((string) $product['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </h1>
    </div>

    <div class="row g-4">
        <section class="col-md-5" aria-label="Hình ảnh sản phẩm">
            <div class="card border-0 shadow-sm overflow-hidden">
                <img
                    id="main-image"
                    class="img-fluid rounded"
                    src="<?= htmlspecialchars($mainImageSrc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars((string) $product['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                    style="width: 100%; height: 400px; object-fit: contain; background-color: #f8f9fa; transition: opacity .2s ease;"
                >
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2" role="group" aria-label="Chọn ảnh sản phẩm">
                        <?php foreach ($galleryImages as $index => $image): ?>
                            <button
                                class="btn btn-light border p-1"
                                type="button"
                                data-product-thumbnail
                                data-image-src="<?= htmlspecialchars($image['src'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                aria-label="Xem ảnh sản phẩm <?= $index + 1 ?>"
                            >
                                <img
                                    src="<?= htmlspecialchars($image['src'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    alt=""
                                    style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;"
                                >
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="col-md-7" aria-label="Thông tin sản phẩm">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 mb-4">Thông tin sản phẩm</h2>
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-secondary">SKU</dt>
                        <dd class="col-sm-8 fw-semibold">
                            <?= htmlspecialchars((string) $product['sku'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </dd>

                        <dt class="col-sm-4 text-secondary">Danh mục</dt>
                        <dd class="col-sm-8">
                            <?= htmlspecialchars((string) ($product['category_name'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </dd>

                        <dt class="col-sm-4 text-secondary">Đơn vị tính</dt>
                        <dd class="col-sm-8">
                            <?= htmlspecialchars((string) $product['unit'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </dd>

                        <dt class="col-sm-4 text-secondary">Giá bán</dt>
                        <dd class="col-sm-8 fs-5 fw-semibold text-primary">
                            <?= \App\Core\CurrencyFormatter::format($product['sale_price']) ?>&nbsp;₫
                        </dd>

                        <dt class="col-sm-4 text-secondary">Giá vốn</dt>
                        <dd class="col-sm-8">
                            <?= \App\Core\CurrencyFormatter::format($product['cost_price']) ?>&nbsp;₫
                        </dd>

                        <dt class="col-sm-4 text-secondary">Tồn kho thực tế</dt>
                        <dd class="col-sm-8">
                            <?= number_format((int) $product['stock_qty'], 0, ',', '.') ?>
                            <?= htmlspecialchars((string) $product['unit'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </dd>

                        <dt class="col-sm-4 text-secondary">Tồn kho tối thiểu</dt>
                        <dd class="col-sm-8 mb-0">
                            <?= number_format((int) $product['min_stock'], 0, ',', '.') ?>
                            <?= htmlspecialchars((string) $product['unit'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </dd>
                    </dl>
                </div>
            </div>

        </section>
    </div>
</main>

<script>
document.querySelectorAll('[data-product-thumbnail]').forEach((thumbnail) => {
    thumbnail.addEventListener('click', () => {
        const mainImage = document.getElementById('main-image');
        if (!(mainImage instanceof HTMLImageElement)) {
            return;
        }

        mainImage.style.opacity = '0.5';
        mainImage.src = thumbnail.dataset.imageSrc;
        mainImage.onload = () => {
            mainImage.style.opacity = '1';
        };
    });
});
</script>
