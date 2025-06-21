<?php
namespace Model;

class User {
    private ?int $userId;  // nullable because when creating, we don't have it yet
    private string $username;
    private string $email;
    private string $password;

    public function __construct(?int $userId = null, string $username = '', string $email = '', string $password = '') {
        $this->userId = $userId;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
    }

    public function getUserId(): ?int {
        return $this->userId;
    }

    public function setUserId(int $userId): void {
        $this->userId = $userId;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function setUsername(string $username): void {
        $this->username = $username;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function setPassword(string $password): void {
        $this->password = $password;
    }
}
