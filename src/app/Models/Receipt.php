<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use DomainException;
use PDO;
use Throwable;

final class Receipt extends Model
{
    protected string $table = 'inventory_receipts';

    public function getAllWithDetails(): array
    {
        $statement = $this->db->query(
            $this->detailsSelect()
            . ' ORDER BY inventory_receipts.created_at DESC, inventory_receipts.id DESC'
        );

        return $statement->fetchAll();
    }

    public function getPaginatedWithDetails(int $page, int $perPage): array
    {
        return $this->paginate(
            $this->detailsSelect()
                . ' ORDER BY inventory_receipts.created_at DESC, inventory_receipts.id DESC',
            'SELECT COUNT(*) FROM inventory_receipts',
            [],
            $page,
            $perPage
        );
    }

    public function getRecent(): array
    {
        $statement = $this->db->query(
            $this->detailsSelect()
            . ' ORDER BY inventory_receipts.created_at DESC, inventory_receipts.id DESC LIMIT 5'
        );

        return $statement->fetchAll();
    }

    public function getByIdWithDetails(int|string $receiptId): ?array
    {
        $statement = $this->db->prepare(
            $this->detailsSelect() . ' WHERE inventory_receipts.id = :receipt_id LIMIT 1'
        );
        $statement->execute(['receipt_id' => $receiptId]);
        $receipt = $statement->fetch();

        return $receipt === false ? null : $receipt;
    }

    public function confirmPayment(int|string $receiptId, int $userId): ?bool
    {
        $this->db->beginTransaction();

        try {
            $statement = $this->db->prepare(
                'SELECT inventory_receipts.id, inventory_receipts.code, inventory_receipts.type, '
                . 'inventory_receipts.partner_id, '
                . 'inventory_receipts.total_amount, inventory_receipts.payment_status, '
                . 'COALESCE(suppliers.name, customers.name) AS partner_name '
                . 'FROM inventory_receipts '
                . 'LEFT JOIN suppliers ON inventory_receipts.type = \'IN\' '
                . 'AND suppliers.id = inventory_receipts.partner_id '
                . 'LEFT JOIN customers ON inventory_receipts.type = \'OUT\' '
                . 'AND customers.id = inventory_receipts.partner_id '
                . 'WHERE inventory_receipts.id = :receipt_id FOR UPDATE'
            );
            $statement->execute(['receipt_id' => $receiptId]);
            $receipt = $statement->fetch(PDO::FETCH_ASSOC);

            if ($receipt === false) {
                $this->db->commit();
                return null;
            }

            if ($receipt['type'] !== 'OUT') {
                throw new DomainException('Chỉ phiếu xuất kho mới có thể xác nhận thanh toán.');
            }

            if ($receipt['payment_status'] === 'paid') {
                $this->db->commit();
                return false;
            }

            if ((float) $receipt['total_amount'] <= 0) {
                throw new DomainException('Phiếu có tổng tiền bằng 0 nên không thể xác nhận chuyển khoản.');
            }

            $this->insertTransaction($receipt, 'income', 'Xuất kho', $userId);

            $update = $this->db->prepare(
                "UPDATE inventory_receipts SET payment_status = 'paid', paid_at = NOW() "
                . "WHERE id = :receipt_id AND payment_status = 'pending'"
            );
            $update->execute(['receipt_id' => $receipt['id']]);
            if ($update->rowCount() !== 1) {
                throw new DomainException('Trạng thái thanh toán phiếu đã thay đổi.');
            }

            (new ActivityLog())->log(
                'receipt.payment.confirm',
                sprintf(
                    'Xác nhận thanh toán phiếu %s (#%d), số tiền %s.',
                    $receipt['code'],
                    $receipt['id'],
                    $receipt['total_amount']
                )
            );

            $this->db->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    public function recordInboundPayment(int|string $receiptId, int $userId): void
    {
        if (!$this->db->inTransaction()) {
            throw new DomainException('Ghi nhận thanh toán phiếu nhập phải nằm trong transaction tạo phiếu.');
        }

        $statement = $this->db->prepare(
            'SELECT inventory_receipts.id, inventory_receipts.code, inventory_receipts.type, '
            . 'inventory_receipts.partner_id, '
            . 'inventory_receipts.total_amount, inventory_receipts.payment_status, '
            . 'COALESCE(suppliers.name, customers.name) AS partner_name '
            . 'FROM inventory_receipts '
            . 'LEFT JOIN suppliers ON inventory_receipts.type = \'IN\' '
            . 'AND suppliers.id = inventory_receipts.partner_id '
            . 'LEFT JOIN customers ON inventory_receipts.type = \'OUT\' '
            . 'AND customers.id = inventory_receipts.partner_id '
            . 'WHERE inventory_receipts.id = :receipt_id FOR UPDATE'
        );
        $statement->execute(['receipt_id' => $receiptId]);
        $receipt = $statement->fetch(PDO::FETCH_ASSOC);

        if (
            $receipt === false
            || $receipt['type'] !== 'IN'
            || $receipt['payment_status'] !== 'paid'
        ) {
            throw new DomainException('Không thể tự động ghi nhận thanh toán cho phiếu nhập kho.');
        }

        if ((float) $receipt['total_amount'] <= 0) {
            throw new DomainException('Tổng tiền phiếu nhập phải lớn hơn 0 để ghi vào sổ quỹ.');
        }

        $this->insertTransaction($receipt, 'expense', 'Nhập kho', $userId);
        (new ActivityLog())->log(
            'receipt.payment.auto',
            sprintf(
                'Tự động ghi nhận thanh toán phiếu nhập %s (#%d), số tiền %s.',
                $receipt['code'],
                $receipt['id'],
                $receipt['total_amount']
            )
        );
    }

    private function insertTransaction(array $receipt, string $type, string $category, int $userId): void
    {
        $transactionInsert = $this->db->prepare(
            'INSERT INTO transactions '
            . '(code, type, category, description, amount, ref_type, ref_id, payment_method, note, created_by, trans_date) '
            . 'VALUES (:code, :type, :category, :description, :amount, :ref_type, :ref_id, :payment_method, '
            . ':note, :created_by, CURRENT_DATE)'
        );
        $receiptAction = $receipt['type'] === 'IN' ? 'nhập kho' : 'xuất kho';
        $partnerName = trim((string) ($receipt['partner_name'] ?? ''));
        if ($partnerName === '') {
            $partnerName = 'Đối tác #' . (string) ($receipt['partner_id'] ?? '');
        }
        $transactionInsert->execute([
            'code' => 'TC-' . strtoupper(bin2hex(random_bytes(8))),
            'type' => $type,
            'category' => $category,
            'description' => sprintf(
                'Thanh toán %s %s - %s',
                $receiptAction,
                $receipt['code'],
                $partnerName
            ),
            'amount' => $receipt['total_amount'],
            'ref_type' => 'inventory_receipt',
            'ref_id' => $receipt['id'],
            'payment_method' => 'Chuyển khoản',
            'note' => 'Thanh toán phiếu ' . $receipt['code'],
            'created_by' => $userId,
        ]);
    }

    private function detailsSelect(): string
    {
        return 'SELECT inventory_receipts.*, '
            . 'COALESCE((SELECT SUM(receipt_items.qty) '
            . 'FROM inventory_receipt_items AS receipt_items '
            . 'WHERE receipt_items.receipt_id = inventory_receipts.id), 0) AS total_quantity, '
            . 'COALESCE(suppliers.name, customers.name) AS partner_name, '
            . 'COALESCE(suppliers.phone, customers.phone) AS partner_phone, '
            . 'COALESCE(suppliers.address, customers.address) AS partner_address, '
            . 'COALESCE(suppliers.email, customers.email) AS partner_email, '
            . 'COALESCE(suppliers.bank_name, customers.bank_name) AS partner_bank_name, '
            . 'COALESCE(suppliers.bank_account, customers.bank_account) AS partner_bank_account, '
            . 'COALESCE(suppliers.account_holder, customers.account_holder) AS partner_account_holder, '
            . 'employees.full_name AS employee_name '
            . 'FROM inventory_receipts '
            . 'LEFT JOIN suppliers '
            . 'ON inventory_receipts.type = \'IN\' AND suppliers.id = inventory_receipts.partner_id '
            . 'LEFT JOIN customers '
            . 'ON inventory_receipts.type = \'OUT\' AND customers.id = inventory_receipts.partner_id '
            . 'LEFT JOIN employees ON employees.id = inventory_receipts.employee_id';
    }
}
