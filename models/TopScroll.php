<?php

require_once __DIR__ . '/../config/database.php';

class TopScroll
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Get All
    public function getAll()
    {
        $sql = "SELECT * FROM top_scroll ORDER BY id DESC";
        $result = $this->conn->query($sql);

        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    // Add
    public function add($name)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO top_scroll (name) VALUES (?)"
        );

        $stmt->bind_param("s", $name);

        if ($stmt->execute()) {
            return [
                "status" => true,
                "id" => $stmt->insert_id
            ];
        }

        return [
            "status" => false,
            "message" => "Failed to add top scroll."
        ];
    }

    // Update
    public function update($id, $name)
    {
        $stmt = $this->conn->prepare(
            "UPDATE top_scroll SET name = ? WHERE id = ?"
        );

        $stmt->bind_param("si", $name, $id);

        return $stmt->execute();
    }

    // Delete
    public function delete($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM top_scroll WHERE id = ?"
        );

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}