<?php

require_once __DIR__ . '/../config/database.php';

class DeliveryCharge
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Add Delivery Charge
    public function add($delivery_charge, $minimum_package)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO delivery_charges
            (delivery_charge, minimum_package)
            VALUES (?, ?)
        ");

        $stmt->bind_param(
            "dd",
            $delivery_charge,
            $minimum_package
        );

        if ($stmt->execute()) {
            return [
                "status" => true,
                "data" => [
                    "id" => $this->conn->insert_id
                ]
            ];
        }

        return [
            "status" => false,
            "message" => "Failed to add delivery charge."
        ];
    }

    // Get All Delivery Charges
    public function getAll()
    {
        $result = $this->conn->query("
            SELECT *
            FROM delivery_charges
            ORDER BY id DESC
        ");

        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return [
            "status" => true,
            "data" => $data
        ];
    }

    // Update Delivery Charge
    public function update($id, $delivery_charge, $minimum_package)
    {
        $stmt = $this->conn->prepare("
            UPDATE delivery_charges
            SET
                delivery_charge = ?,
                minimum_package = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "ddi",
            $delivery_charge,
            $minimum_package,
            $id
        );

        if ($stmt->execute()) {
            return [
                "status" => true,
                "message" => "Delivery charge updated successfully."
            ];
        }

        return [
            "status" => false,
            "message" => "Failed to update."
        ];
    }

    // Delete Delivery Charge
    public function delete($id)
    {
        $stmt = $this->conn->prepare("
            DELETE FROM delivery_charges
            WHERE id = ?
        ");

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            return [
                "status" => true,
                "message" => "Deleted successfully."
            ];
        }

        return [
            "status" => false,
            "message" => "Failed to delete."
        ];
    }
}
?>