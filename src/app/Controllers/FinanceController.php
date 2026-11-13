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
        AuthMiddleware::checkPermission('finance.view');
    }

    public function index(): void
    {
        [$filters, $filterError] = $this->queryFilters();
        if ($filterError !== null) {
            $filters = $this->defaultQueryFilters();
        }
        $model = new Transaction();
        $result = $model->getPaginatedWithUser(
            $this->requestedPage(),
            self::ITEMS_PER_PAGE,
            $filters
        );
        $this->render('finance/index', [
            'dailyTotals' => $model->getDailyTotals($filters),
            'error' => $filterError,
            'filters' => $filters,
            'pageTitle' => 'Sổ quỹ Thu/Chi - Mini ERP',
            'pagination' => $result['pagination'],
            'summary' => $model->getSummary($filters),
            'transactions' => $result['items'],
            'years' => $model->getAvailableYears(),
        ]);
    }

    public function exportCsv(): void
    {
        AuthMiddleware::checkPermission('finance.view');

        [$filters, $filterError] = $this->queryFilters();
        if ($filterError !== null) {
            http_response_code(400);
            header('Content-Type: text/plain; charset=UTF-8');
            echo $filterError;
            return;
        }

        $transactions = (new Transaction())->getAllWithUser($filters);
        $output = fopen('php://output', 'wb');
        if ($output === false) {
            throw new \RuntimeException('Không thể khởi tạo tệp CSV.');
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="so-quy-thu-chi.csv"');
        header('X-Content-Type-Options: nosniff');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv(
            $output,
            ['Mã phiếu', 'Loại', 'Danh mục', 'Nội dung / Diễn giải', 'Số tiền', 'Phương thức', 'Ngày giao dịch', 'Người lập', 'Ghi chú'],
            ',',
            '"',
            ''
        );

        foreach ($transactions as $transaction) {
            $description = trim((string) ($transaction['description'] ?? ''));
            if ($description === '') {
                $description = (string) ($transaction['note'] ?? '');
            }

            fputcsv($output, [
                $this->csvText($transaction['code']),
                $transaction['type'] === 'income' ? 'Thu' : 'Chi',
                $this->csvText($transaction['category']),
                $this->csvText($description),
                (string) $transaction['amount'],
                $this->csvText($transaction['payment_method']),
                (string) $transaction['trans_date'],
                $this->csvText($transaction['created_by_name'] ?? ''),
                $this->csvText($transaction['note'] ?? ''),
            ], ',', '"', '');
        }

        fclose($output);
        exit;
    }

    private function queryFilters(): array
    {
        $filters = $this->defaultQueryFilters();

        foreach (array_keys($filters) as $field) {
            if (!isset($_GET[$field])) {
                continue;
            }
            if (!is_string($_GET[$field])) {
                return [$filters, 'Tham số bộ lọc không hợp lệ.'];
            }
            $filters[$field] = trim($_GET[$field]);
        }

        if (strlen($filters['search']) > 255) {
            return [$filters, 'Từ khóa tìm kiếm không được vượt quá 255 ký tự.'];
        }
        if (!in_array($filters['type'], ['all', 'in', 'out'], true)) {
            return [$filters, 'Loại giao dịch được chọn không hợp lệ.'];
        }
        if ($filters['date'] !== '') {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $filters['date']);
            $dateErrors = DateTimeImmutable::getLastErrors();
            if (
                $date === false
                || ($dateErrors !== false && (
                    $dateErrors['warning_count'] > 0
                    || $dateErrors['error_count'] > 0
                ))
                || $date->format('Y-m-d') !== $filters['date']
            ) {
                return [$filters, 'Ngày giao dịch được chọn không hợp lệ.'];
            }

            $filters['month'] = '';
            $filters['year'] = '';
        }
        if (
            $filters['month'] !== ''
            && (
                !preg_match('/\A[0-9]{1,2}\z/', $filters['month'])
                || (int) $filters['month'] < 1
                || (int) $filters['month'] > 12
            )
        ) {
            return [$filters, 'Tháng được chọn không hợp lệ.'];
        }
        if (
            $filters['year'] !== ''
            && (
                !preg_match('/\A[0-9]{4}\z/', $filters['year'])
                || (int) $filters['year'] < 1900
            )
        ) {
            return [$filters, 'Năm được chọn không hợp lệ.'];
        }
        if ($filters['month'] !== '') {
            $filters['month'] = (string) (int) $filters['month'];
        }

        return [$filters, null];
    }

    private function defaultQueryFilters(): array
    {
        return [
            'search' => '',
            'type' => 'all',
            'date' => '',
            'month' => '',
            'year' => '',
        ];
    }

    public function create(): void
    {
        AuthMiddleware::checkPermission('finance.manage');
        $this->renderForm($this->emptyValues());
    }

    public function store(): void
    {
        AuthMiddleware::checkPermission('finance.manage');
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

    private function csvText(mixed $value): string
    {
        $text = (string) ($value ?? '');

        return preg_match('/\A[\x00-\x20]*[=+\-@]/', $text) === 1
            ? "'" . $text
            : $text;
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
