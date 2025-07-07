<?php

use Model\Employee;

require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/Employee.php';
require_once __DIR__ . '/../Repository/AdminRepository.php';
require_once __DIR__ . '/../Config/Database.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

use Repository\AdminRepositoryImpl;
use Config\Database;

$connection = Database::getConnection();
$adminRepo = new AdminRepositoryImpl($connection);
 
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'fetchAdmin':
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $employeeId = $_GET['employeeId'] ?? 0;
        $result = $adminRepo->fetchAdmin((int)$employeeId);
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    break;

    case 'fetchAllAdmin':
     if ($_SERVER['REQUEST_METHOD'] === 'GET') {
          $result = $adminRepo->fetchAllAdmin();

         header('Content-Type: application/json');
         echo json_encode($result);
     } else {
         http_response_code(405); // Method Not Allowed
         echo json_encode(["result" => "fail", "message" => "Method not allowed"]);
      }
     break;

    case 'adminLogin':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $result = $adminRepo->adminLogin($username, $password);
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    break;
    case 'registerAdmin':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validate required fields
        if (
            !isset($data['username'], $data['email'], $data['password'],
                     $data['fullName'], $data['role'], $data['status'])
        ) {
            echo json_encode(["result" => "fail", "message" => "Missing required fields"]);
            exit;
        }

        // Create Employee object
        $employee = new Employee();
        $employee->setUsername($data['username']);
        $employee->setEmail($data['email']);
        $employee->setPassword($data['password']);
        $employee->setFullName($data['fullName']);
        $employee->setRole($data['role']);
        $employee->setStatus($data['status']);
        $employee->setManagerId($data['managerId'] ?? null); // Optional

        // Call repository
        $result = $adminRepo->registerAdmin($employee);

        // Output
        header('Content-Type: application/json');
        echo json_encode($result);
    } else {
        http_response_code(405);
        echo json_encode(["result" => "fail", "message" => "Method not allowed"]);
    }
    break;

    case 'updateAdmin':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['employeeId'])) {
            echo json_encode(["result" => "fail", "message" => "employeeId is required"]);
            exit;
        }

        $user = new Employee();
        $user->setFullName($data['fullName'] ?? '');
        $user->setUsername($data['username'] ?? '');
        $user->setEmail($data['email'] ?? '');
        $user->setRole($data['role'] ?? '');
        $user->setStatus($data['status'] ?? '');

        $result = $adminRepo->updateAdminProfile($user, (int)$data['employeeId']);

        header('Content-Type: application/json');
        echo json_encode($result);
    } else {
        http_response_code(405); // Method Not Allowed
        echo json_encode(["result" => "fail", "message" => "Method not allowed"]);
    }
    break;
    case 'changeAdminPassword':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['employeeId'], $data['currentPassword'], $data['newPassword'])) {
            echo json_encode(["result" => "fail", "message" => "Missing required fields"]);
            exit;
        }

        $employeeId = (int)$data['employeeId'];
        $currentPassword = $data['currentPassword'];
        $newPassword = $data['newPassword'];

        $result = $adminRepo->changeAdminPassword($employeeId, $currentPassword, $newPassword);

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
    