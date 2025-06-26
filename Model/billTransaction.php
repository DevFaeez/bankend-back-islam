<?php
namespace Model;

class BillTransaction {
    private ?int $transactionId;
    private ?int $billId;

    public function __construct() {
        // No initialization
    }

    public function getTransactionId(): ?int {
        return $this->transactionId;
    }

    public function setTransactionId(int $transactionId): void {
        $this->transactionId = $transactionId;
    }

    public function getBillId(): ?int {
        return $this->billId;
    }

    public function setBillId(?int $billId): void {
        $this->billId = $billId;
    }
}
