<?php
namespace Model;

class TransferTransaction {
    private ?int $transactionId;
    private ?string $reference;
    private ?string $transferType;
    private ?int $accountId;

    public function __construct() {
        // No initialization
    }

    public function getTransactionId(): ?int {
        return $this->transactionId;
    }

    public function setTransactionId(int $transactionId): void {
        $this->transactionId = $transactionId;
    }

    public function getReference(): ?string {
        return $this->reference;
    }

    public function setReference(?string $reference): void {
        $this->reference = $reference;
    }

    public function getTransferType(): ?string {
        return $this->transferType;
    }

    public function setTransferType(?string $transferType): void {
        $this->transferType = $transferType;
    }

    public function getAccountId(): ?int {
        return $this->accountId;
    }

    public function setAccountId(?int $accountId): void {
        $this->accountId = $accountId;
    }
}
