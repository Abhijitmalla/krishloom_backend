<?php

require_once __DIR__ . '/../config/database.php';

class MasterType
{
    private $conn;
    private $table = "master_types";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Get All
    public function getAll()
    {
        $query = "SELECT * FROM {$this->table} ORDER BY id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Add
    public function add($name, $image)
    {
        $query = "INSERT INTO {$this->table}
                  (name, image)
                  VALUES (?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $name, $image);

        return $stmt->execute();
    }

    // Update
    public function update($id, $name, $image)
    {
        $query = "UPDATE {$this->table}
                  SET name = ?, image = ?
                  WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssi", $name, $image, $id);

        return $stmt->execute();
    }

    // Delete
    public function delete($id)
    {
        $query = "DELETE FROM {$this->table}
                  WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    // Get By Id
    public function getById($id)
    {
        $query = "SELECT *
                  FROM {$this->table}
                  WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }
}
?>