<?php
namespace Model;



class User {
    private ?int $userId;  // nullable because when creating, we don't have it yet
    private string $email;
    private string $nricNumber;
    private string $fullName;
    private string $phoneNumber;
    private string $status;
    private string $address;



    // public function __construct(?int $userId = null, string $username = '', string $email = '', string $password = '', string $accountNum  = '') {
    //     $this->userId = $userId;
    //     $this->username = $username;
    //     $this->email = $email;
    //     $this->password = $password;
    //     $this->accountNum = $accountNum;
    // }

public function __construct() {
    // No initialization
}


    public function getUserId(): ?int {
        return $this->userId;
    }

    public function setUserId(int $userId): void {
        $this->userId = $userId;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function getNricNumber(): string {
        return $this->nricNumber;
    }

    public function setNricNumber(string $nricNumber): void {
        $this->nricNumber = $nricNumber;
    }

    
    public function getFullName(): string {
        return $this->fullName;
    }

    public function setFullName(string $fullName): void {
        $this->fullName = $fullName;
    }
    public function getPhoneNumber(): string {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): void {
        $this->phoneNumber = $phoneNumber;
    }
    public function getStatus(): string {
        return $this->status;
    }

    public function setStatus(string $status): void {
        $this->status = $status;
    }
    public function getAddress(): string {
        return $this->address;
    }

    public function setAddress(string $address): void {
        $this->address = $address;
    }
}
