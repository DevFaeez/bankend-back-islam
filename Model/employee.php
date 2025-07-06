<?php
namespace Model;

class Employee {
    private ?int $employeeId;
    private ?string $username;
    private ?string $email;
    private ?string $password;
    private ?string $fullName;
    private ?string $role;
    private ?string $status;
    private ?int $managerId;

    public function __construct() {
        // No initialization
    }

    public function getEmployeeId(): ?int {
        return $this->employeeId;
    }

    public function setEmployeeId(int $employeeId): void {
        $this->employeeId = $employeeId;
    }

    public function getUsername(): ?string {
        return $this->username;
    }

    public function setUsername(?string $username): void {
        $this->username = $username;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(?string $email): void {
        $this->email = $email;
    }

    public function getPassword(): ?string {
        return $this->password;
    }

    public function setPassword(?string $password): void {
        $this->password = $password;
    }

    public function getFullName(): ?string {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): void {
        $this->fullName = $fullName;
    }

    public function getRole(): ?string {
        return $this->role;
    }

    public function setRole(?string $role): void {
        $this->role = $role;
    }

    public function getStatus(): ?string {
        return $this->status;
    }

    public function setStatus(?string $status): void {
        $this->status = $status;
    }

    public function getManagerId(): ?int {
        return $this->managerId;
    }

    public function setManagerId(?int $managerId): void {
        $this->managerId = $managerId;
    }
}
