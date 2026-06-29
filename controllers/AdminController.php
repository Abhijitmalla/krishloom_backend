<?php

require_once __DIR__ . '/../models/Admin.php';

class AdminController
{
    private $admin;

    public function __construct()
    {
        $this->admin = new Admin();
    }

    public function login()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        // Validation
        if (empty($email) || empty($password)) {
            echo json_encode([
                "status" => false,
                "message" => "Email and Password are required"
            ]);
            return;
        }

        // Get admin details
        $admin = $this->admin->login($email);

        // Email not found
        if (!$admin) {
            echo json_encode([
                "status" => false,
                "message" => "Invalid email"
            ]);
            return;
        }

        // Plain text password check
        if ($password !== $admin['password']) {
            echo json_encode([
                "status" => false,
                "message" => "Invalid password"
            ]);
            return;
        }

        // Login success
        echo json_encode([
            "status" => true,
            "message" => "Admin login successful",
            "data" => [
                "id" => $admin['id'],
                "email" => $admin['email']
            ]
        ]);
    }
}