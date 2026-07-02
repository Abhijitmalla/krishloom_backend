<?php

require_once __DIR__ . '/../config/Database.php';

class Address
{
    private $conn;
    private $table = "user_addresses";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function addAddress($data)
    {
        $sql = "INSERT INTO {$this->table}
                (user_id, full_name, phone, address,
                 city, state, pincode, country, is_default)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            return false;
        }

        $stmt->bind_param(
            "isssssssi",
            $data['user_id'],
            $data['full_name'],
            $data['phone'],
            $data['address'],
            $data['city'],
            $data['state'],
            $data['pincode'],
            $data['country'],
            $data['is_default']
        );

        return $stmt->execute();
    }

    public function getAddresses($userId)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM {$this->table}
             WHERE user_id = ?
             ORDER BY is_default DESC, id DESC"
        );
        if ($stmt === false) {
            return [];
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

   public function updateAddress($id, $data)
{
    // If only setting default address
    if (
        isset($data['is_default']) &&
        $data['is_default'] == 1 &&
        !isset($data['full_name'])
    ) {

        // Remove default from all addresses of this user
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table}
             SET is_default = 0
             WHERE user_id = ?"
        );

        if ($stmt === false) {
            return false;
        }

        $stmt->bind_param("i", $data['user_id']);
        $stmt->execute();

        // Set selected address as default
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table}
             SET is_default = 1
             WHERE id = ? AND user_id = ?"
        );

        if ($stmt === false) {
            return false;
        }

        $stmt->bind_param(
            "ii",
            $id,
            $data['user_id']
        );

        return $stmt->execute();
    }

    // Normal address update
    $sql = "UPDATE {$this->table}
            SET
                full_name = ?,
                phone = ?,
                address = ?,
                city = ?,
                state = ?,
                pincode = ?,
                country = ?,
                is_default = ?
            WHERE id = ? AND user_id = ?";

    $stmt = $this->conn->prepare($sql);

    if ($stmt === false) {
        return false;
    }

    $stmt->bind_param(
        "sssssssiii",
        $data['full_name'],
        $data['phone'],
        $data['address'],
        $data['city'],
        $data['state'],
        $data['pincode'],
        $data['country'],
        $data['is_default'],
        $id,
        $data['user_id']
    );

    return $stmt->execute();
}

    public function deleteAddress($id, $userId = null)
    {
        if ($userId !== null) {
            $sql = "DELETE FROM {$this->table} WHERE id = ? AND user_id = ?";
        } else {
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
        }

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            return false;
        }

        if ($userId !== null) {
            $stmt->bind_param("ii", $id, $userId);
        } else {
            $stmt->bind_param("i", $id);
        }

        return $stmt->execute();
    }
}