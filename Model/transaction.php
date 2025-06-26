<?php
namespace Model;

class Transaction {
    private ?int $transactionId;
    private ?string $type;
    private ?float $amount;
    private ?string $description;
    private ?string $transactionDate;
    private ?string $referenceNumber;
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

    public function getType(): ?string {
        return $this->type;
    }

    public function setType(?string $type): void {
        $this->type = $type;
    }

    public function getAmount(): ?float {
        return $this->amount;
    }

    public function setAmount(?float $amount): void {
        $this->amount = $amount;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function getTransactionDate(): ?string {
        return $this->transactionDate;
    }

    public function setTransactionDate(?string $transactionDate): void {
        $this->transactionDate = $transactionDate;
    }

    public function getReferenceNumber(): ?string {
        return $this->referenceNumber;
    }

    public function setReferenceNumber(?string $referenceNumber): void {
        $this->referenceNumber = $referenceNumber;
    }

    public function getAccountId(): ?int {
        return $this->accountId;
    }

    public function setAccountId(?int $accountId): void {
        $this->accountId = $accountId;
    }
}
