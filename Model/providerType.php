<?php
namespace Model;

class ProviderType {
    private ?string $providerTypeId;
    private ?string $name;
    private ?string $status;
    private int $billTypeId;

    public function __construct() {
        // No initialization
    }

    public function getProviderTypeId(): ?string {
        return $this->providerTypeId;
    }

    public function setProviderTypeId(string $providerTypeId): void {
        $this->providerTypeId = $providerTypeId;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(?string $name): void {
        $this->name = $name;
    }

    public function getStatus(): ?string {
        return $this->status;
    }

    public function setStatus(?string $status): void {
        $this->status = $status;
    }

    public function getBillTypeId(): int {
        return $this->billTypeId;
    }

    public function setBillTypeId(int $billTypeId): void {
        $this->billTypeId = $billTypeId;
    }
}
