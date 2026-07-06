<?php

require_once __DIR__ . '/../config/Database.php';

class Affiliate
{
    private $conn;
    private $table = "affiliates";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Get All Affiliates
    public function getAll()
    {
        $query = "SELECT * FROM {$this->table} ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $affiliates = [];
        while ($row = $result->fetch_assoc()) {
            $affiliates[] = $row;
        }
        
        return $affiliates;
    }

    // Register Affiliate
    public function register($data)
    {
        $query = "
            INSERT INTO {$this->table}
            (
                membership_id,
                name,
                mobile_no,
                email,
                state,
                city,
                pin_code,
                password
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->bind_param(
            "ssisssis",
            $data['membership_id'],
            $data['name'],
            $data['mobile_no'],
            $data['email'],
            $data['state'],
            $data['city'],
            $data['pin_code'],
            $data['password']
        );

        return $stmt->execute();
    }

    // Find by Membership ID or Mobile Number
    public function findByLogin($login)
    {
        $query = "
            SELECT *
            FROM {$this->table}
            WHERE membership_id = ?
            OR mobile_no = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->bind_param("ss", $login, $login);

        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    // Check if Membership ID already exists
    public function membershipExists($membershipId)
    {
        $query = "
            SELECT id
            FROM {$this->table}
            WHERE membership_id = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $membershipId);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    // Check if Mobile Number already exists
    public function mobileExists($mobile)
    {
        $query = "
            SELECT id
            FROM {$this->table}
            WHERE mobile_no = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $mobile);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    // Check if Email already exists
    public function emailExists($email)
    {
        $query = "
            SELECT id
            FROM {$this->table}
            WHERE email = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }
}