<?php
namespace Model;

class TransferTransaction {
    private ?string $transferMode;
    private ?string $transferType;

    public function __construct() {
        // No initialization
    }

    public function getTransferMode(): ?string {
        return $this->transferMode;
    }

    public function setTransferMode(?string $transferMode): void {
        $this->transferMode = $transferMode;
    }

    public function getTransferType(): ?string {
        return $this->transferType;
    }

    public function setTransferType(?string $transferType): void {
        $this->transferType = $transferType;
    }

}
