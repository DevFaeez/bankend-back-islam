<?php
namespace Repository;

use Model\User;

interface UserRepository {
    function register(User $user): array;
    function login(string $email, string $password): array;
}

class UserRepositoryImpl implements UserRepository {

    private \PDO $connection;

    public function __construct(\PDO $connection) {
        $this->connection = $connection;
    }

    public function register(User $user): array {
        try {
            // Check if user already exists by email
            $stmt = $this->connection->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$user->getEmail()]);
            if ($stmt->fetch()) {
                return ["result" => "fail", "message" => "Email already registered"];
            }

            // Hash the password
            $hashedPassword = password_hash($user->getPassword(), PASSWORD_DEFAULT);

            // Insert username, email, password only, user_id is auto_increment
            $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $user->getUsername(),
                $user->getEmail(),
                $hashedPassword
            ]);

            return ["result" => "success"];
        } catch (\Throwable $th) {
            return ["result" => "fail", "message" => $th->getMessage()];
        }
    }

    public function login(string $email, string $password): array {
        try {
            $sql = "SELECT * FROM users WHERE email = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$email]);

            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($user && password_verify($password, $user['password'])) {
                return [
                    "result" => "success",
                    "user" => [
                        "user_id" => $user['user_id'],
                        "username" => $user['username'],
                        "email" => $user['email']
                    ]
                ];
            } else {
                return ["result" => "fail", "message" => "Invalid email or password"];
            }
        } catch (\Throwable $th) {
            return ["result" => "fail", "message" => $th->getMessage()];
        }
    }
}
