<?php

require_once __DIR__ . '/../models/MasterType.php';

class MasterTypeController
{
    private $masterType;

    public function __construct()
    {
        $this->masterType = new MasterType();
    }

    // Get All
    public function getMasterTypes()
    {
        $data = $this->masterType->getAll();

        echo json_encode([
            "status" => true,
            "data" => $data
        ]);
    }

    // Get Single
    public function getMasterType()
    {
        $id = $_GET['id'] ?? 0;

        $data = $this->masterType->getById($id);

        echo json_encode([
            "status" => true,
            "data" => $data
        ]);
    }

    // Add
    public function addMasterType()
    {
        $name = $_POST['name'] ?? '';

        if (empty($name)) {
            echo json_encode([
                "status" => false,
                "message" => "Name is required"
            ]);
            return;
        }

        $imageName = '';

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] === 0
        ) {

            $uploadDir = __DIR__ . '/../uploads/master-types/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $imageName =
                time() . '_' .
                basename($_FILES['image']['name']);

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                $uploadDir . $imageName
            );
        }

        $result = $this->masterType->add(
            $name,
            $imageName
        );

        echo json_encode([
            "status" => $result,
            "message" => $result
                ? "Master Type added successfully"
                : "Failed to add Master Type"
        ]);
    }

    // Update
    public function updateMasterType()
    {
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $oldImage = $_POST['old_image'] ?? '';

        $imageName = $oldImage;

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] === 0
        ) {

            $uploadDir = __DIR__ . '/../uploads/master-types/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $imageName =
                time() . '_' .
                basename($_FILES['image']['name']);

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                $uploadDir . $imageName
            );
        }

        $result = $this->masterType->update(
            $id,
            $name,
            $imageName
        );

        echo json_encode([
            "status" => $result,
            "message" => $result
                ? "Master Type updated successfully"
                : "Failed to update Master Type"
        ]);
    }

    // Delete
    public function deleteMasterType()
    {
        $id = $_POST['id'] ?? 0;

        $result = $this->masterType->delete($id);

        echo json_encode([
            "status" => $result,
            "message" => $result
                ? "Master Type deleted successfully"
                : "Failed to delete Master Type"
        ]);
    }
}
?>