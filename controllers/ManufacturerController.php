<?php

require_once __DIR__ . '/../models/Manufacturer.php';

class ManufacturerController
{
    private $manufacturer;

    public function __construct($db)
    {
        $this->manufacturer = new Manufacturer($db);
    }

    // Get All
    public function getManufacturer()
    {
        $data = $this->manufacturer->getAll();

        echo json_encode([
            "status" => true,
            "data" => $data
        ]);
    }

    // Add
    public function addManufacturer()
    {
        $name = trim($_POST['name'] ?? '');
        $pin_code = trim($_POST['pin_code'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (
            empty($name) ||
            empty($pin_code) ||
            empty($address)
        ) {
            echo json_encode([
                "status" => false,
                "message" => "All fields are required."
            ]);
            return;
        }

        $result = $this->manufacturer->add(
            $name,
            $pin_code,
            $address
        );

        echo json_encode([
            "status" => $result,
            "message" => $result
                ? "Manufacturer added successfully."
                : "Failed to add manufacturer."
        ]);
    }

    // Update
    public function updateManufacturer()
    {
        $id = $_POST['id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $pin_code = trim($_POST['pin_code'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (
            empty($id) ||
            empty($name) ||
            empty($pin_code) ||
            empty($address)
        ) {
            echo json_encode([
                "status" => false,
                "message" => "All fields are required."
            ]);
            return;
        }

        $result = $this->manufacturer->update(
            $id,
            $name,
            $pin_code,
            $address
        );

        echo json_encode([
            "status" => $result,
            "message" => $result
                ? "Manufacturer updated successfully."
                : "Failed to update manufacturer."
        ]);
    }

    // Delete
    public function deleteManufacturer()
    {
        $id = $_POST['id'] ?? '';

        if (empty($id)) {
            echo json_encode([
                "status" => false,
                "message" => "Manufacturer ID is required."
            ]);
            return;
        }

        $result = $this->manufacturer->delete($id);

        echo json_encode([
            "status" => $result,
            "message" => $result
                ? "Manufacturer deleted successfully."
                : "Failed to delete manufacturer."
        ]);
    }
}