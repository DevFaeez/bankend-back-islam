<?php
namespace Model;


class Account {
    private ?int $accountId; 
    // private string $username; 
    private string $accountNumber; 
    private string $password;
    private int $balance;
    private string $status;
    private string $openedAt;
    private int $userId;
    private int $employeeId;


    
public function __construct() {
    // No initialization
}


    public function getAccountId(): ?int {
        return $this->accountId;
    }

    public function setAccountId(int $accountId): void {
        $this->accountId = $accountId;
    }
    // public function getUsername(): string {
    //     return $this->username;
    // }

    // public function setUsername(string $username): void {
    //     $this->username = $username;
    // }
    public function getAccountNumber(): string {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountNumber): void {
        $this->accountNumber = $accountNumber;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function setPassword(string $password): void {
        $this->password = $password;
    }

    public function getBalance(): int {
        return $this->balance;
    }

    public function setBalance(int $balance): void {
        $this->balance = $balance;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function setStatus(string $status): void {
        $this->status = $status;
    }

    
    public function getOpenedAt(): string {
        return $this->openedAt;
    }

    public function setOpenedAt(string $openedAt): void {
        $this->openedAt = $openedAt;
    }

     public function getUserId(): string {
        return $this->userId;
    }

    public function setUserId(int $userId): void {
        $this->userId = $userId;

    } public function getEmployeeId(): int {
        return $this->employeeId;
    }

    public function setEmployeeId(int $employeeId): void {
        $this->employeeId = $employeeId;
    }

}
