<?php

require_once __DIR__ . '/../config/Database.php';

class Admin
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function login($email)
    {
        $query = "SELECT * FROM admins WHERE email = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }
    
    public function getById($id)
    {
        $query = "SELECT * FROM admins WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function updatePassword($id, $newPassword)
    {
        $query = "UPDATE admins SET password = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $newPassword, $id);
        return $stmt->execute();
    }
}