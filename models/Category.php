<?php

require_once __DIR__ . '/../config/database.php';

class Category
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Add
    public function addCategory($data)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO categories (name, type_id, image)
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param(
            "sis",
            $data['name'],
            $data['type_id'],
            $data['image']
        );

        return $stmt->execute();
    }

    // Get All
    public function getCategories()
    {
        $sql = "
            SELECT
                c.id,
                c.name,
                c.type_id,
                c.image,
                mt.name AS type_name
            FROM categories c
            LEFT JOIN master_types mt
                ON c.type_id = mt.id
            ORDER BY c.id DESC
        ";

        $result = $this->conn->query($sql);

        $categories = [];

        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }

        return $categories;
    }

    // Update
    public function updateCategory($data)
    {
        if (!empty($data['image'])) {

            $stmt = $this->conn->prepare("
                UPDATE categories
                SET
                    name = ?,
                    type_id = ?,
                    image = ?
                WHERE id = ?
            ");

            $stmt->bind_param(
                "sisi",
                $data['name'],
                $data['type_id'],
                $data['image'],
                $data['id']
            );
        } else {

            $stmt = $this->conn->prepare("
                UPDATE categories
                SET
                    name = ?,
                    type_id = ?
                WHERE id = ?
            ");

            $stmt->bind_param(
                "sii",
                $data['name'],
                $data['type_id'],
                $data['id']
            );
        }

        return $stmt->execute();
    }

    // Delete
    public function deleteCategory($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM categories WHERE id=?"
        );

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}