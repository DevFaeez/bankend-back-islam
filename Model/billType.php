<?php
namespace Model;

class BillType {
    private ?int $billTypeId;
    private ?string $name;

    public function __construct() {
        // No initialization
    }

    public function getBillTypeId(): ?int {
        return $this->billTypeId;
    }

    public function setBillTypeId(int $billTypeId): void {
        $this->billTypeId = $billTypeId;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(?string $name): void {
        $this->name = $name;
    }
}
