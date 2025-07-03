<?php
namespace Model;

class GoalAccount {
    private ?int $goalAccountId;
    private ?string $title;
    private ?float $balance;
    private ?float $goalAmount;
    private ?string $createdAt;
    private ?string $goalDate;
    private ?string $status;
    private ?string $description;
    private ?string $goalImage;
    private int $accountId;

    public function __construct() {
        // No initialization
    }

    public function getGoalAccountId(): ?int {
        return $this->goalAccountId;
    }

    public function setGoalAccountId(int $goalAccountId): void {
        $this->goalAccountId = $goalAccountId;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(?string $title): void {
        $this->title = $title;
    }

        public function getGoalDate(): ?string {
        return $this->goalDate;
    }

    public function setGoalDate(?string $goalDate): void {
        $this->goalDate = $goalDate;
    }

    public function getGoalImage(): ?string {
        return $this->goalImage;
    }

    public function setGoalImage(?string $goalImage): void {
        $this->goalImage = $goalImage;
    }


    public function getBalance(): ?float {
        return $this->balance;
    }

    public function setBalance(?float $balance): void {
        $this->balance = $balance;
    }

    public function getGoalAmount(): ?float {
        return $this->goalAmount;
    }

    public function setGoalAmount(?float $goalAmount): void {
        $this->goalAmount = $goalAmount;
    }

    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }

    public function setCreatedAt(?string $createdAt): void {
        $this->createdAt = $createdAt;
    }

    public function getStatus(): ?string {
        return $this->status;
    }

    public function setStatus(?string $status): void {
        $this->status = $status;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function getAccountId(): int {
        return $this->accountId;
    }

    public function setAccountId(int $accountId): void {
        $this->accountId = $accountId;
    }
}
