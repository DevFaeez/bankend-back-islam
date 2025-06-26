<?php
namespace Model;

class Bill {
    private ?int $billId;
    private ?int $billAccountNumber;
    private ?int $accountId;
    private ?string $providerTypeId;
    private ?string $billAmount;
    private ?string $billDesc;

    public function __construct() {
        // No initialization
    }

    public function getBillId(): ?int {
        return $this->billId;
    }

    public function setBillId(int $billId): void {
        $this->billId = $billId;
    }

    public function getBillDesc(): ?int {
        return $this->billDesc;
    }

    public function setBillDesc(int $billDesc): void {
        $this->billDesc = $billDesc;
    }
        public function getBillAmount(): ?int {
        return $this->billAmount;
    }

    public function setBillAmount(int $billAmount): void {
        $this->billAmount = $billAmount;
    }

    public function getBillAccountNumber(): ?int {
        return $this->billAccountNumber;
    }

    public function setBillAccountNumber(?int $billAccountNumber): void {
        $this->billAccountNumber = $billAccountNumber;
    }

    public function getAccountId(): ?int {
        return $this->accountId;
    }

    public function setAccountId(?int $accountId): void {
        $this->accountId = $accountId;
    }

    public function getProviderTypeId(): ?string {
        return $this->providerTypeId;
    }

    public function setProviderTypeId(?string $providerTypeId): void {
        $this->providerTypeId = $providerTypeId;
    }
}
