<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Controller;
use App\Models\Transaction;
use DateTimeImmutable;

final class FinanceController extends Controller
{
    private const TYPES = ['income', 'expense'];
    private const PAYMENT_METHODS = ['Tiền mặt', 'Chuyển khoản', 'Thẻ', 'Khác'];

    public function __construct()
    {
        AuthMiddleware::checkRole([1, 2, 3]);
    }

    public function index(): void
    {
        $model = new Transaction();
        $this->render('finance/index', [
            'pageTitle' => 'Sổ quỹ Thu/Chi - Mini ERP',
            'summary' => $model->getSummary(),
            'transactions' => $model->getAllWithUser(),
        ]);
    }

    public function create(): void
    {
        $this->renderForm($this->emptyValues());
    }

    public function store(): void
    {
        if (!$this->isPostRequest()) {
            http_response_code(405);
            header('Allow: POST');
            $this->renderForm($this->emptyValues(), 'Phương thức gửi yêu cầu không hợp lệ.');
            return;
        }

        $values = $this->postedFields();
        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderForm($values, 'Yêu cầu không hợp lệ hoặc phiên đã hết hạn.');
            return;
        }

        $error = $this->validateFields($values);
        if ($error !== null) {
            $this->renderForm($values, $error);
            return;
        }

        (new Transaction())->insert([
            'code' => 'TC-' . strtoupper(bin2hex(random_bytes(6))),
            'type' => $values['type'],
            'category' => $values['category'],
            'amount' => $values['amount'],
            'payment_method' => $values['payment_method'],
            'note' => $values['note'] !== '' ? $values['note'] : null,
            'created_by' => (int) $_SESSION['user_id'],
            'trans_date' => $values['trans_date'],
        ]);

        $this->redirect('/finance/index');
    }

    private function renderForm(array $values, ?string $error = null): void
    {
        $this->render('finance/create', [
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
            'pageTitle' => 'Lập phiếu Thu/Chi - Mini ERP',
            'paymentMethods' => self::PAYMENT_METHODS,
            'values' => $values,
        ]);
    }

    private function emptyValues(): array
    {
        return [
            'type' => 'income',
            'category' => '',
            'amount' => '',
            'payment_method' => 'Chuyển khoản',
            'trans_date' => date('Y-m-d'),
            'note' => '',
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

    private function validateFields(array $values): ?string
    {
        if (!in_array($values['type'], self::TYPES, true)) {
            return 'Loại giao dịch không hợp lệ.';
        }

        if (preg_match('/\A[^\r\n]{1,100}\z/u', $values['category']) !== 1) {
            return 'Danh mục là bắt buộc và không được vượt quá 100 ký tự.';
        }

        if (
            preg_match('/\A[0-9]{1,13}(?:\.[0-9]{1,2})?\z/', $values['amount']) !== 1
            || preg_match('/\A0(?:\.0{1,2})?\z/', $values['amount']) === 1
        ) {
            return 'Số tiền phải lớn hơn 0 và có tối đa 2 chữ số thập phân.';
        }

        if (!in_array($values['payment_method'], self::PAYMENT_METHODS, true)) {
            return 'Hình thức thanh toán không hợp lệ.';
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $values['trans_date']);
        $dateErrors = DateTimeImmutable::getLastErrors();
        if (
            $date === false
            || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
            || $date->format('Y-m-d') !== $values['trans_date']
        ) {
            return 'Ngày giao dịch không hợp lệ.';
        }

        if (preg_match('/\A.{0,5000}\z/us', $values['note']) !== 1) {
            return 'Ghi chú không được vượt quá 5000 ký tự.';
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
}
