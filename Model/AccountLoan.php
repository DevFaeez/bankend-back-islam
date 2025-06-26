<?php
namespace Model;

class AccountLoan {
    private ?int $accountLoanId;
    private ?string $icSlip;
    private ?string $paySlip;
    private ?string $purpose;
    private float $amount;
    private float $balance;
    private int $term;
    private string $createdAt;
    private ?string $paymentMethod;
    private ?int $loanId;
    private ?int $accountId;

    public function __construct() {
        // No initialization
    }

    public function getAccountLoanId(): ?int {
        return $this->accountLoanId;
    }

    public function setAccountLoanId(int $accountLoanId): void {
        $this->accountLoanId = $accountLoanId;
    }

    public function getIcSlip(): ?string {
        return $this->icSlip;
    }

    public function setIcSlip(?string $icSlip): void {
        $this->icSlip = $icSlip;
    }

    public function getPaySlip(): ?string {
        return $this->paySlip;
    }

    public function setPaySlip(?string $paySlip): void {
        $this->paySlip = $paySlip;
    }

    public function getPurpose(): ?string {
        return $this->purpose;
    }

    public function setPurpose(?string $purpose): void {
        $this->purpose = $purpose;
    }

    public function getAmount(): float {
        return $this->amount;
    }

    public function setAmount(float $amount): void {
        $this->amount = $amount;
    }

    public function getBalance(): float {
        return $this->balance;
    }

    public function setBalance(float $balance): void {
        $this->balance = $balance;
    }

    public function getTerm(): int {
        return $this->term;
    }

    public function setTerm(int $term): void {
        $this->term = $term;
    }

    public function getCreatedAt(): string {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): void {
        $this->createdAt = $createdAt;
    }

    public function getPaymentMethod(): ?string {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): void {
        $this->paymentMethod = $paymentMethod;
    }

    public function getLoanId(): ?int {
        return $this->loanId;
    }

    public function setLoanId(?int $loanId): void {
        $this->loanId = $loanId;
    }

    public function getAccountId(): ?int {
        return $this->accountId;
    }

    public function setAccountId(?int $accountId): void {
        $this->accountId = $accountId;
    }
}
