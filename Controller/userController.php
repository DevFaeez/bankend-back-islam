<?php

require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/Account.php';
require_once __DIR__ . '/../Repository/UserRepository.php';
require_once __DIR__ . '/../Config/Database.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

use Model\User;
use Model\Account;
use Repository\UserRepositoryImpl;
use Config\Database;

$connection = Database::getConnection();
$userRepo = new UserRepositoryImpl($connection);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);

            $user = new User();

            $account = new Account();

            $user -> setEmail($data['email']);
            $user -> setNricNumber($data['nricNumber']);
            $user -> setFullName($data['fullName']);

            $account -> setPassword($data['password']);
            $account -> setUsername($data['username']);

            $result = $userRepo->register($user, $account);
            header('Content-Type: application/json');
            echo json_encode($result);
        }
        break;

    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $userRepo->login($data['username'] ?? '', $data['password'] ?? '');
            header('Content-Type: application/json');
            echo json_encode($result);
        }
    break;

        case 'fetchUser':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $accountId = $_GET['accountId'] ?? null;

            if ($accountId) {
                $result = $userRepo->fetchUser((int)$accountId);
            } else {
                $result = [
                    "result" => "fail",
                    "message" => "accountId is required"
                ];
            }

            header('Content-Type: application/json');
            echo json_encode($result);
        }
        break;

         case 'fetchAllUser':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $result = $userRepo->fetchAllUser();

                header('Content-Type: application/json');
                echo json_encode($result);
            } else {
                http_response_code(405); // Method Not Allowed
                echo json_encode(["result" => "fail", "message" => "Method not allowed"]);
            }
            break;

      case 'updateUser':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['accountId'])) {
                echo json_encode(["result" => "fail", "message" => "accountId is required"]);
                exit;
            }

            $user = new User();
            $user->setFullName($data['fullName'] ?? '');
            $user->setEmail($data['email'] ?? '');
            $user->setPhoneNumber($data['phoneNumber'] ?? '');
            $user->setAddress($data['address'] ?? '');

            $result = $userRepo->updateUserProfile($user, (int)$data['accountId']);

            header('Content-Type: application/json');
            echo json_encode($result);
        } else {
            http_response_code(405);
            echo json_encode(["result" => "fail", "message" => "Method not allowed"]);
        }
        break;
        
        case 'updateUserPassword':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['accountId'], $data['currentPassword'], $data['newPassword'])) {
            echo json_encode(["result" => "fail", "message" => "Missing required fields"]);
            exit;
        }

        $currentPassword = $data['currentPassword'];
        $newPassword = $data['newPassword'];
        $accountId = (int)$data['accountId'];

        $result = $userRepo->updateUserPassword($currentPassword, $newPassword, $accountId);

        header('Content-Type: application/json');
        echo json_encode($result);
    } else {
        http_response_code(405);
        echo json_encode(["result" => "fail", "message" => "Method not allowed"]);
    }
    break;




    default:
        header('Content-Type: application/json');
        echo json_encode(["result" => "fail", "message" => "Invalid action"]);
    break;
}
    