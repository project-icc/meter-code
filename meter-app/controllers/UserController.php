<?php
require_once "../config/database.php";
require_once "../models/User.php";

class UserController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function createUser($name, $email, $password, $role = "Member") {
        $this->user->name = $name;
        $this->user->email = $email;
        $this->user->password = $password;
        $this->user->role = $role;

        if ($this->user->create()) {
            return "User created successfully!";
        } else {
            return "Failed to create user.";
        }
    }

    public function getUsers() {
        $stmt = $this->user->readAll();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $users;
    }
}

// ตัวอย่างการเรียกใช้
// $userController = new UserController();
// echo $userController->createUser("John Doe", "john@example.com", "password123");
// print_r($userController->getUsers());
?>
