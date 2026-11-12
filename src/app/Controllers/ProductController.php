<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Controller;
use App\Core\Database;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSerial;
use PDOException;
use Throwable;

final class ProductController extends Controller
{
    private const MAX_PRODUCT_IMAGES = 8;
    private const MAX_PRODUCT_IMAGE_SIZE = 5242880;
    private const MAX_PRODUCT_IMAGE_PIXELS = 20000000;

    public function __construct()
    {
        AuthMiddleware::checkPermission('inventory.view');
    }

    public function index(): void
    {
        $search = $_GET['search'] ?? '';
        $search = is_string($search) ? trim($search) : '';
        $this->renderIndex(null, $search, $this->requestedPage());
    }

    public function show(string $id): void
    {
        $product = (new Product())->findByIdWithCategory($id);
        if ($product === null) {
            $this->showNotFound();
            return;
        }

        $serialResult = (new ProductSerial())->getPaginatedByProductId(
            $id,
            $this->requestedPage(),
            self::ITEMS_PER_PAGE
        );
        $this->render('inventory/products/show', [
            'images' => (new ProductImage())->getImagesByProductId($id),
            'pageTitle' => (string) $product['name'] . ' - Mini ERP',
            'pagination' => $serialResult['pagination'],
            'product' => $product,
            'serials' => $serialResult['items'],
        ]);
    }

    public function exportCsv(): void
    {
        AuthMiddleware::checkPermission('inventory.view');

        $products = (new Product())->getAllWithCategory();
        $output = fopen('php://output', 'wb');
        if ($output === false) {
            throw new \RuntimeException('Không thể khởi tạo tệp CSV.');
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="danh-sach-san-pham.csv"');
        header('X-Content-Type-Options: nosniff');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['SKU', 'Tên sản phẩm', 'Danh mục', 'Đơn vị tính', 'Giá vốn', 'Giá bán', 'Tồn kho'], ',', '"', '');

        foreach ($products as $product) {
            fputcsv($output, [
                $this->csvText($product['sku']),
                $this->csvText($product['name']),
                $this->csvText($product['category_name'] ?? ''),
                $this->csvText($product['unit']),
                (string) $product['cost_price'],
                (string) $product['sale_price'],
                (string) $product['stock_qty'],
            ], ',', '"', '');
        }

        fclose($output);
        exit;
    }

    public function create(): void
    {
        AuthMiddleware::checkPermission('inventory.manage_products');
        $this->renderForm('create', $this->emptyValues());
    }

    public function store(): void
    {
        AuthMiddleware::checkPermission('inventory.manage_products');
        if (!$this->isPostRequest()) {
            http_response_code(405);
            header('Allow: POST');
            $this->renderForm('create', $this->emptyValues(), 'Phương thức gửi yêu cầu không hợp lệ.');
            return;
        }

        $values = $this->postedFields();
        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderForm('create', $values, 'Yêu cầu không hợp lệ hoặc phiên đã hết hạn.');
            return;
        }

        $error = $this->validateFields($values);
        if ($error !== null) {
            $this->renderForm('create', $values, $error);
            return;
        }

        try {
            $uploadedImages = $this->validatedProductImages();
        } catch (\InvalidArgumentException $exception) {
            $this->renderForm('create', $values, $exception->getMessage());
            return;
        }

        $pdo = Database::getInstance()->getConnection();
        $storedImagePaths = [];
        try {
            $pdo->beginTransaction();
            $productId = (new Product())->insert($this->databaseValues($values));
            $this->storeProductImages($productId, $uploadedImages, false, $storedImagePaths);
            (new ActivityLog())->log(
                'product.create',
                sprintf('Tạo sản phẩm #%s (SKU: %s).', $productId, $values['sku'])
            );
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $this->removeStoredProductImages($storedImagePaths);

            if ($exception instanceof PDOException && $exception->getCode() === '23000') {
                $this->renderForm('create', $values, 'SKU đã tồn tại hoặc danh mục không còn hợp lệ.');
                return;
            }

            throw $exception;
        }

        $this->redirect('/product/index');
    }

    public function edit(string $id): void
    {
        AuthMiddleware::checkPermission('inventory.manage_products');
        $product = (new Product())->findById($id);
        if ($product === null) {
            $this->showNotFound();
            return;
        }

        $images = (new ProductImage())->getImagesByProductId($id);
        $this->renderForm('edit', $this->formValues($product), null, $product, $images);
    }

    public function update(string $id): void
    {
        AuthMiddleware::checkPermission('inventory.manage_products');
        $model = new Product();
        $product = $model->findById($id);
        if ($product === null) {
            $this->showNotFound();
            return;
        }

        if (!$this->isPostRequest()) {
            http_response_code(405);
            header('Allow: POST');
            $this->renderForm(
                'edit',
                $this->formValues($product),
                'Phương thức gửi yêu cầu không hợp lệ.',
                $product
            );
            return;
        }

        $values = $this->postedFields();
        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderForm('edit', $values, 'Yêu cầu không hợp lệ hoặc phiên đã hết hạn.', $product);
            return;
        }

        $error = $this->validateFields($values);
        if ($error !== null) {
            $this->renderForm('edit', $values, $error, $product);
            return;
        }

        try {
            $uploadedImages = $this->validatedProductImages();
        } catch (\InvalidArgumentException $exception) {
            $this->renderForm('edit', $values, $exception->getMessage(), $product);
            return;
        }

        $pdo = Database::getInstance()->getConnection();
        $storedImagePaths = [];
        try {
            $pdo->beginTransaction();
            $model->update($id, $this->databaseValues($values));
            if ($uploadedImages !== []) {
                $hasMainImage = false;
                foreach ((new ProductImage())->getImagesByProductId($id) as $existingImage) {
                    if ((int) $existingImage['is_main'] === 1) {
                        $hasMainImage = true;
                        break;
                    }
                }
                $this->storeProductImages($id, $uploadedImages, $hasMainImage, $storedImagePaths);
            }
            (new ActivityLog())->log(
                'product.update',
                sprintf('Cập nhật sản phẩm #%s (SKU: %s).', $id, $values['sku'])
            );
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $this->removeStoredProductImages($storedImagePaths);

            if ($exception instanceof PDOException && $exception->getCode() === '23000') {
                $this->renderForm('edit', $values, 'SKU đã tồn tại hoặc danh mục không còn hợp lệ.', $product);
                return;
            }

            throw $exception;
        }

        $this->redirect('/product/index');
    }

    public function delete(string $id): void
    {
        AuthMiddleware::checkPermission('inventory.manage_products');
        if (!$this->isPostRequest()) {
            http_response_code(405);
            header('Allow: POST');
            $this->renderIndex('Phương thức gửi yêu cầu không hợp lệ.');
            return;
        }

        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderIndex('Yêu cầu không hợp lệ hoặc phiên đã hết hạn.');
            return;
        }

        try {
            (new Product())->delete($id);
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            $this->renderIndex('Không thể xóa sản phẩm vì đã có giao dịch kho hoặc đơn hàng liên quan.');
            return;
        }

        $this->redirect('/product/index');
    }

    public function deleteImage(string $imageId): void
    {
        AuthMiddleware::checkPermission('inventory.manage_products');
        if (!$this->isPostRequest()) {
            http_response_code(405);
            header('Allow: POST');
            $this->renderIndex('Phương thức gửi yêu cầu không hợp lệ.');
            return;
        }

        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderIndex('Yêu cầu không hợp lệ hoặc phiên đã hết hạn.');
            return;
        }

        $imageModel = new ProductImage();
        $image = $imageModel->findById($imageId);
        if ($image === null) {
            http_response_code(404);
            $this->renderIndex('Không tìm thấy ảnh sản phẩm được yêu cầu.');
            return;
        }

        $storedImagePath = $this->productImageAbsolutePath($image);
        if (is_file($storedImagePath) && !unlink($storedImagePath)) {
            throw new \RuntimeException('Không thể xóa tệp ảnh sản phẩm trên máy chủ.');
        }

        $productId = (string) $image['product_id'];
        $remainingImages = array_values(array_filter(
            $imageModel->getImagesByProductId($productId),
            static fn (array $candidate): bool => (string) $candidate['id'] !== $imageId
        ));
        $hasMainImage = false;
        foreach ($remainingImages as $remainingImage) {
            if ((int) $remainingImage['is_main'] === 1) {
                $hasMainImage = true;
                break;
            }
        }
        $promoteImageId = !$hasMainImage && $remainingImages !== []
            ? $remainingImages[0]['id']
            : null;

        $pdo = Database::getInstance()->getConnection();
        $pdo->beginTransaction();
        try {
            if ($imageModel->delete($imageId) === 0) {
                throw new \RuntimeException('Không thể xóa ảnh sản phẩm khỏi cơ sở dữ liệu.');
            }
            if ($promoteImageId !== null) {
                $imageModel->update($promoteImageId, ['is_main' => 1]);
            }
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }

        $this->redirect('/product/edit/' . rawurlencode($productId));
    }

    private function renderIndex(?string $error = null, string $search = '', int $page = 1): void
    {
        $result = (new Product())->getPaginatedWithCategory($search, $page, self::ITEMS_PER_PAGE);
        $this->render('inventory/products/index', [
            'canManage' => AuthMiddleware::hasRole(1)
                || AuthMiddleware::hasRole(2)
                || AuthMiddleware::hasRole(4),
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
            'pageTitle' => 'Sản phẩm - Mini ERP',
            'pagination' => $result['pagination'],
            'products' => $result['items'],
            'search' => $search,
        ]);
    }

    private function validatedProductImages(): array
    {
        $upload = $_FILES['product_images'] ?? null;
        if ($upload === null) {
            return [];
        }

        if (!is_array($upload)) {
            throw new \InvalidArgumentException('Dữ liệu ảnh tải lên không hợp lệ.');
        }

        foreach (['name', 'tmp_name', 'error', 'size'] as $field) {
            if (!isset($upload[$field]) || !is_array($upload[$field])) {
                throw new \InvalidArgumentException('Dữ liệu ảnh tải lên không hợp lệ.');
            }
        }

        $fileCount = count($upload['name']);
        if (
            count($upload['tmp_name']) !== $fileCount
            || count($upload['error']) !== $fileCount
            || count($upload['size']) !== $fileCount
        ) {
            throw new \InvalidArgumentException('Dữ liệu ảnh tải lên không hợp lệ.');
        }

        $mimeExtensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];
        $selectedCount = 0;
        foreach ($upload['error'] as $error) {
            if (!is_int($error)) {
                throw new \InvalidArgumentException('Dữ liệu ảnh tải lên không hợp lệ.');
            }
            if ($error !== UPLOAD_ERR_NO_FILE) {
                $selectedCount++;
            }
        }
        if ($selectedCount > self::MAX_PRODUCT_IMAGES) {
            throw new \InvalidArgumentException(
                'Mỗi lần chỉ có thể tải lên tối đa ' . self::MAX_PRODUCT_IMAGES . ' ảnh.'
            );
        }
        if ($selectedCount === 0) {
            return [];
        }

        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($fileInfo === false) {
            throw new \RuntimeException('Không thể xác minh định dạng ảnh trên máy chủ.');
        }

        $validatedImages = [];
        try {
            foreach ($upload['error'] as $index => $error) {
                if ($error === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                if ($error !== UPLOAD_ERR_OK) {
                    $message = match ($error) {
                        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ảnh tải lên vượt quá giới hạn cho phép.',
                        UPLOAD_ERR_PARTIAL => 'Ảnh chỉ được tải lên một phần, vui lòng thử lại.',
                        UPLOAD_ERR_NO_TMP_DIR => 'Máy chủ chưa cấu hình thư mục tạm để tải ảnh.',
                        UPLOAD_ERR_CANT_WRITE => 'Máy chủ không thể ghi ảnh tải lên.',
                        UPLOAD_ERR_EXTENSION => 'Một tiện ích mở rộng của máy chủ đã dừng tải ảnh.',
                        default => 'Không thể tải ảnh lên.',
                    };
                    throw new \InvalidArgumentException($message);
                }

                $temporaryPath = $upload['tmp_name'][$index] ?? null;
                if (!is_string($temporaryPath) || !is_uploaded_file($temporaryPath)) {
                    throw new \InvalidArgumentException('Tệp ảnh tải lên không hợp lệ.');
                }

                $fileSize = filesize($temporaryPath);
                if ($fileSize === false) {
                    throw new \RuntimeException('Không thể đọc kích thước ảnh tải lên.');
                }
                if ($fileSize < 1 || $fileSize > self::MAX_PRODUCT_IMAGE_SIZE) {
                    throw new \InvalidArgumentException('Mỗi ảnh phải có dung lượng không quá 5 MB.');
                }

                $mimeType = finfo_file($fileInfo, $temporaryPath);
                if (!is_string($mimeType) || !isset($mimeExtensions[$mimeType])) {
                    throw new \InvalidArgumentException('Chỉ chấp nhận ảnh JPEG, PNG, GIF hoặc WebP.');
                }

                $imageInfo = @getimagesize($temporaryPath);
                if (!is_array($imageInfo) || ($imageInfo['mime'] ?? null) !== $mimeType) {
                    throw new \InvalidArgumentException('Tệp tải lên không phải ảnh hợp lệ.');
                }

                $width = (int) ($imageInfo[0] ?? 0);
                $height = (int) ($imageInfo[1] ?? 0);
                if (
                    $width < 1
                    || $height < 1
                    || $width * $height > self::MAX_PRODUCT_IMAGE_PIXELS
                ) {
                    throw new \InvalidArgumentException('Kích thước ảnh vượt quá giới hạn cho phép.');
                }

                $validatedImages[] = [
                    'extension' => $mimeExtensions[$mimeType],
                    'temporary_path' => $temporaryPath,
                ];
            }
        } finally {
            finfo_close($fileInfo);
        }

        return $validatedImages;
    }

    private function storeProductImages(
        int|string $productId,
        array $images,
        bool $hasMainImage,
        array &$storedImagePaths
    ): void {
        if ($images === []) {
            return;
        }

        $directory = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'public'
            . DIRECTORY_SEPARATOR . 'uploads'
            . DIRECTORY_SEPARATOR . 'products';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('Không thể tạo thư mục lưu ảnh sản phẩm.');
        }
        if (!is_writable($directory)) {
            throw new \RuntimeException('Thư mục lưu ảnh sản phẩm không có quyền ghi.');
        }

        $imageModel = new ProductImage();
        foreach ($images as $image) {
            do {
                $filename = bin2hex(random_bytes(16)) . '.' . $image['extension'];
                $destination = $directory . DIRECTORY_SEPARATOR . $filename;
            } while (file_exists($destination));

            if (!move_uploaded_file($image['temporary_path'], $destination)) {
                throw new \RuntimeException('Không thể lưu ảnh sản phẩm lên máy chủ.');
            }
            $storedImagePaths[] = $destination;

            $imageModel->insert([
                'product_id' => $productId,
                'image_path' => 'uploads/products/' . $filename,
                'is_main' => $hasMainImage ? 0 : 1,
            ]);
            $hasMainImage = true;
        }
    }

    private function removeStoredProductImages(array $paths): void
    {
        $failedToRemove = false;
        foreach ($paths as $path) {
            if (is_file($path) && !unlink($path)) {
                $failedToRemove = true;
            }
        }

        if ($failedToRemove) {
            throw new \RuntimeException('Không thể dọn dẹp ảnh sau khi lưu sản phẩm thất bại.');
        }
    }

    private function productImageAbsolutePath(array $image): string
    {
        $relativePath = str_replace('\\', '/', (string) ($image['image_path'] ?? ''));
        $prefix = 'uploads/products/';
        $filename = str_starts_with($relativePath, $prefix)
            ? substr($relativePath, strlen($prefix))
            : '';
        if (!preg_match('/\A[a-f0-9]{32}\.(?:jpg|png|gif|webp)\z/i', $filename)) {
            throw new \RuntimeException('Đường dẫn ảnh sản phẩm không hợp lệ.');
        }

        return dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'public'
            . DIRECTORY_SEPARATOR . 'uploads'
            . DIRECTORY_SEPARATOR . 'products'
            . DIRECTORY_SEPARATOR . $filename;
    }

    private function csvText(mixed $value): string
    {
        $text = (string) ($value ?? '');

        return preg_match('/\A[\x00-\x20]*[=+\-@]/', $text) === 1
            ? "'" . $text
            : $text;
    }

    private function renderForm(
        string $form,
        array $values,
        ?string $error = null,
        ?array $product = null,
        ?array $images = null
    ): void {
        $this->render('inventory/products/' . $form, [
            'categories' => (new Category())->findAll(),
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
            'images' => $form === 'edit' && $product !== null
                ? ($images ?? (new ProductImage())->getImagesByProductId($product['id']))
                : [],
            'pageTitle' => ($form === 'edit' ? 'Sửa' : 'Thêm') . ' sản phẩm - Mini ERP',
            'product' => $product,
            'values' => $values,
        ]);
    }

    private function emptyValues(): array
    {
        return [
            'sku' => '',
            'name' => '',
            'category_id' => '',
            'unit' => '',
            'cost_price' => '0.00',
            'sale_price' => '0.00',
            'min_stock' => '0.000',
        ];
    }

    private function postedFields(): array
    {
        $values = [];
        foreach (array_keys($this->emptyValues()) as $field) {
            $value = $_POST[$field] ?? '';
            $values[$field] = is_string($value) ? trim($value) : '';
        }

        return $values;
    }

    private function formValues(array $product): array
    {
        $values = [];
        foreach (array_keys($this->emptyValues()) as $field) {
            $value = $product[$field] ?? '';
            $values[$field] = $value === null ? '' : (string) $value;
        }

        return $values;
    }

    private function validateFields(array $values): ?string
    {
        if (!preg_match('/\A[A-Za-z0-9][A-Za-z0-9._-]{0,59}\z/', $values['sku'])) {
            return 'SKU phải có từ 1 đến 60 ký tự chữ, số, dấu chấm, gạch ngang hoặc gạch dưới.';
        }

        if (!$this->hasTextLength($values['name'], 180)) {
            return 'Tên sản phẩm là bắt buộc và không được vượt quá 180 ký tự.';
        }

        if ($values['category_id'] !== '') {
            if (!preg_match('/\A[1-9][0-9]*\z/', $values['category_id'])) {
                return 'Danh mục được chọn không hợp lệ.';
            }

            if ((new Category())->findById($values['category_id']) === null) {
                return 'Danh mục được chọn không còn tồn tại.';
            }
        }

        if (!$this->hasTextLength($values['unit'], 30)) {
            return 'Đơn vị tính là bắt buộc và không được vượt quá 30 ký tự.';
        }

        if (!$this->isValidDecimal($values['cost_price'], 13, 2)) {
            return 'Giá vốn phải là số không âm, tối đa 2 chữ số thập phân.';
        }

        if (!$this->isValidDecimal($values['sale_price'], 13, 2)) {
            return 'Giá bán phải là số không âm, tối đa 2 chữ số thập phân.';
        }

        if ($this->compareDecimals($values['sale_price'], $values['cost_price'], 2) < 0) {
            return 'Giá bán phải lớn hơn hoặc bằng giá vốn.';
        }

        if (!$this->isValidDecimal($values['min_stock'], 12, 3)) {
            return 'Tồn kho tối thiểu phải là số không âm, tối đa 3 chữ số thập phân.';
        }

        return null;
    }

    private function databaseValues(array $values): array
    {
        return [
            'sku' => $values['sku'],
            'name' => $values['name'],
            'category_id' => $values['category_id'] !== '' ? (int) $values['category_id'] : null,
            'unit' => $values['unit'],
            'cost_price' => $values['cost_price'],
            'sale_price' => $values['sale_price'],
            'min_stock' => $values['min_stock'],
        ];
    }

    private function hasTextLength(string $value, int $maxLength): bool
    {
        return preg_match('/\A[^\r\n]{1,' . $maxLength . '}\z/u', $value) === 1;
    }

    private function isValidDecimal(string $value, int $maxIntegerDigits, int $scale): bool
    {
        return preg_match(
            '/\A[0-9]{1,' . $maxIntegerDigits . '}(?:\.[0-9]{1,' . $scale . '})?\z/',
            $value
        ) === 1;
    }

    private function compareDecimals(string $left, string $right, int $scale): int
    {
        $leftMinor = $this->toMinorUnits($left, $scale);
        $rightMinor = $this->toMinorUnits($right, $scale);

        if (strlen($leftMinor) !== strlen($rightMinor)) {
            return strlen($leftMinor) < strlen($rightMinor) ? -1 : 1;
        }

        return strcmp($leftMinor, $rightMinor);
    }

    private function toMinorUnits(string $value, int $scale): string
    {
        [$integer, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $integer = ltrim($integer, '0');
        $integer = $integer === '' ? '0' : $integer;
        $minorUnits = ltrim($integer . str_pad($fraction, $scale, '0'), '0');

        return $minorUnits === '' ? '0' : $minorUnits;
    }

    private function isPostRequest(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    private function hasValidCsrfToken(): bool
    {
        $sessionToken = $_SESSION['csrf_token'] ?? null;
        $submittedToken = $_POST['csrf_token'] ?? null;

        return is_string($sessionToken)
            && is_string($submittedToken)
            && $sessionToken !== ''
            && hash_equals($sessionToken, $submittedToken);
    }

    private function csrfToken(): string
    {
        $token = $_SESSION['csrf_token'] ?? null;
        if (!is_string($token) || !preg_match('/\A[a-f0-9]{64}\z/', $token)) {
            $token = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $token;
        }

        return $token;
    }

    private function showNotFound(): void
    {
        http_response_code(404);
        $this->renderIndex('Không tìm thấy sản phẩm được yêu cầu.');
    }
}
