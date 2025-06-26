<?php
namespace Model;

class LoanPaymentTransaction {
    private ?int $transactionId;
    private ?int $loanId;

    public function __construct() {
        // No initialization
    }

    public function getTransactionId(): ?int {
        return $this->transactionId;
    }

    public function setTransactionId(int $transactionId): void {
        $this->transactionId = $transactionId;
    }

    public function getLoanId(): ?int {
        return $this->loanId;
    }

    public function setLoanId(?int $loanId): void {
        $this->loanId = $loanId;
    }
}
