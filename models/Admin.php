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
}