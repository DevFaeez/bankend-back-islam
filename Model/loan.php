<?php
namespace Model;

class Loan {
    private ?int $loanId;
    private ?string $loanType;
    private ?float $interestRate;

    public function __construct() {
        // No initialization
    }

    public function getLoanId(): ?int {
        return $this->loanId;
    }

    public function setLoanId(int $loanId): void {
        $this->loanId = $loanId;
    }

    public function getLoanType(): ?string {
        return $this->loanType;
    }

    public function setLoanType(?string $loanType): void {
        $this->loanType = $loanType;
    }

    public function getInterestRate(): ?float {
        return $this->interestRate;
    }

    public function setInterestRate(?float $interestRate): void {
        $this->interestRate = $interestRate;
    }
}
