<?php

require_once __DIR__ . '/../config/Database.php';

class User
{
    private $conn;
    private $lastError = '';

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function findByEmailOrPhone($email, $phone)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM users
             WHERE email = ?
             OR phone = ?"
        );

        $stmt->bind_param(
            "ss",
            $email,
            $phone
        );

        $stmt->execute();

        return $stmt->get_result();
    }

    public function findByLogin($login)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM users
             WHERE email = ?
             OR phone = ?"
        );

        $stmt->bind_param(
            "ss",
            $login,
            $login
        );

        $stmt->execute();

        return $stmt->get_result();
    }

    public function create(
        $name,
        $email,
        $phone,
        $password
    ) {
        $stmt = $this->conn->prepare(
            "INSERT INTO users
            (name,email,phone,password)
            VALUES(?,?,?,?)"
        );

        $stmt->bind_param(
            "ssss",
            $name,
            $email,
            $phone,
            $password
        );

        $result = $stmt->execute();

        if ($result === false) {
            $this->lastError = $stmt->error;
        }

        return $result;
    }

 public function getUserById($id)
{
    $sql = "SELECT
                id,
                name,
                email,
                phone,
                dob,
                gender,
                address,
                profile_image
            FROM users
            WHERE id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    return $stmt->get_result();
}
public function updateProfile($id, $dob, $gender, $address)
{
    $stmt = $this->conn->prepare(
        "UPDATE users
         SET dob = ?, gender = ?, address = ?
         WHERE id = ?"
    );

    $stmt->bind_param(
        "sssi",
        $dob,
        $gender,
        $address,
        $id
    );

    return $stmt->execute();
}

    public function updateProfileImage($id, $image)
    {
        $stmt = $this->conn->prepare(
            "UPDATE users
             SET profile_image = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "si",
            $image,
            $id
        );

        return $stmt->execute();
    }

    public function getAllUsers()
    {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $result = $this->conn->query($sql);
        
        $users = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        return $users;
    }
}