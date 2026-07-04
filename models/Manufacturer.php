<?php

class Manufacturer
{
    private $conn;
    private $table = "manufacturers";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Get All Manufacturers
    public function getAll()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC";
        $result = $this->conn->query($sql);

        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    // Add Manufacturer
    public function add($name, $pin_code, $address)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table}
            (name, pin_code, address)
            VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "sss",
            $name,
            $pin_code,
            $address
        );

        return $stmt->execute();
    }

    // Update Manufacturer
    public function update($id, $name, $pin_code, $address)
    {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table}
            SET name = ?, pin_code = ?, address = ?
            WHERE id = ?"
        );

        $stmt->bind_param(
            "sssi",
            $name,
            $pin_code,
            $address,
            $id
        );

        return $stmt->execute();
    }

    // Delete Manufacturer
    public function delete($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM {$this->table}
            WHERE id = ?"
        );

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}