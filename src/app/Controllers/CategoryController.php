<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Controller;
use App\Models\Category;
use App\Models\Product;

final class CategoryController extends Controller
{
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
        $category = (new Category())->findById($id);
        if ($category === null) {
            $this->showNotFound();
            return;
        }

        $result = (new Product())->getPaginatedByCategoryId(
            $id,
            $this->requestedPage(),
            self::ITEMS_PER_PAGE
        );
        $this->render('inventory/categories/show', [
            'category' => $category,
            'pageTitle' => 'Sản phẩm thuộc danh mục ' . (string) $category['name'] . ' - Mini ERP',
            'pagination' => $result['pagination'],
            'products' => $result['items'],
        ]);
    }

    public function create(): void
    {
        AuthMiddleware::checkPermission('inventory.manage_categories');
        $this->renderForm('create', ['name' => '']);
    }

    public function store(): void
    {
        AuthMiddleware::checkPermission('inventory.manage_categories');
        if (!$this->isPostRequest()) {
            http_response_code(405);
            header('Allow: POST');
            $this->renderForm(
                'create',
                ['name' => ''],
                'Phương thức gửi yêu cầu không hợp lệ.'
            );
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

        (new Category())->insert(['name' => $values['name']]);
        $this->redirect('/category/index');
    }

    public function edit(string $id): void
    {
        AuthMiddleware::checkPermission('inventory.manage_categories');
        $category = (new Category())->findById($id);
        if ($category === null) {
            $this->showNotFound();
            return;
        }

        $this->renderForm('edit', ['name' => (string) $category['name']], null, $category);
    }

    public function update(string $id): void
    {
        AuthMiddleware::checkPermission('inventory.manage_categories');
        $model = new Category();
        $category = $model->findById($id);
        if ($category === null) {
            $this->showNotFound();
            return;
        }

        if (!$this->isPostRequest()) {
            http_response_code(405);
            header('Allow: POST');
            $this->renderForm(
                'edit',
                ['name' => (string) $category['name']],
                'Phương thức gửi yêu cầu không hợp lệ.',
                $category
            );
            return;
        }

        $values = $this->postedFields();
        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderForm('edit', $values, 'Yêu cầu không hợp lệ hoặc phiên đã hết hạn.', $category);
            return;
        }

        $error = $this->validateFields($values);
        if ($error !== null) {
            $this->renderForm('edit', $values, $error, $category);
            return;
        }

        $model->update($id, ['name' => $values['name']]);
        $this->redirect('/category/index');
    }

    public function delete(string $id): void
    {
        AuthMiddleware::checkPermission('inventory.manage_categories');
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

        (new Category())->delete($id);
        $this->redirect('/category/index');
    }

    private function renderIndex(?string $error = null, string $search = '', int $page = 1): void
    {
        $result = (new Category())->getPaginated($search, $page, self::ITEMS_PER_PAGE);
        $this->render('inventory/categories/index', [
            'categories' => $result['items'],
            'canManage' => AuthMiddleware::hasRole(1)
                || AuthMiddleware::hasRole(2)
                || AuthMiddleware::hasRole(4),
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
            'pageTitle' => 'Danh mục - Mini ERP',
            'pagination' => $result['pagination'],
            'search' => $search,
        ]);
    }

    private function renderForm(
        string $form,
        array $values,
        ?string $error = null,
        ?array $category = null
    ): void {
        $this->render('inventory/categories/' . $form, [
            'category' => $category,
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
            'pageTitle' => ($form === 'edit' ? 'Sửa' : 'Thêm') . ' danh mục - Mini ERP',
            'values' => $values,
        ]);
    }

    private function postedFields(): array
    {
        $name = $_POST['name'] ?? '';

        return ['name' => is_string($name) ? trim($name) : ''];
    }

    private function validateFields(array $values): ?string
    {
        if (!preg_match('/\A[^\r\n]{1,120}\z/u', $values['name'])) {
            return 'Tên danh mục là bắt buộc và không được vượt quá 120 ký tự.';
        }

        return null;
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
        $this->renderIndex('Không tìm thấy danh mục được yêu cầu.');
    }
}
