<?php

require_once __DIR__ . '/../models/TopScroll.php';

class TopScrollController
{
    private $topScroll;

    public function __construct()
    {
        $this->topScroll = new TopScroll();
    }

    // Get All
    public function getTopScroll()
    {
        $data = $this->topScroll->getAll();

        echo json_encode([
            "status" => true,
            "data" => $data
        ]);
    }

    // Add
    public function addTopScroll()
    {
        $name = trim($_POST['name'] ?? '');

        if (empty($name)) {
            echo json_encode([
                "status" => false,
                "message" => "Name is required."
            ]);
            return;
        }

        $result = $this->topScroll->add($name);

        echo json_encode([
            "status" => $result['status'],
            "message" => $result['status']
                ? "Top scroll added successfully."
                : $result['message'],
            "data" => [
                "id" => $result['id'] ?? null,
                "name" => $name
            ]
        ]);
    }

    // Update
    public function updateTopScroll()
    {
        $id = $_POST['id'] ?? '';
        $name = trim($_POST['name'] ?? '');

        if (empty($id) || empty($name)) {
            echo json_encode([
                "status" => false,
                "message" => "ID and Name are required."
            ]);
            return;
        }

        $status = $this->topScroll->update($id, $name);

        echo json_encode([
            "status" => $status,
            "message" => $status
                ? "Top scroll updated successfully."
                : "Failed to update top scroll."
        ]);
    }

    // Delete
    public function deleteTopScroll()
    {
        $id = $_POST['id'] ?? '';

        if (empty($id)) {
            echo json_encode([
                "status" => false,
                "message" => "ID is required."
            ]);
            return;
        }

        $status = $this->topScroll->delete($id);

        echo json_encode([
            "status" => $status,
            "message" => $status
                ? "Top scroll deleted successfully."
                : "Failed to delete top scroll."
        ]);
    }
}