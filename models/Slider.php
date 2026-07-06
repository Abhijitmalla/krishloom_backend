<?php

require_once __DIR__ . '/../config/database.php';

class Slider
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Add Slider
    public function addSlider($data)
    {
        $sql = "INSERT INTO sliders
                (show_in,title,url,description,main_image,mobile_image)
                VALUES (?,?,?,?,?,?)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "ssssss",
            $data['show_in'],
            $data['title'],
            $data['url'],
            $data['description'],
            $data['main_image'],
            $data['mobile_image']
        );

        if ($stmt->execute()) {
            $data['id'] = $this->conn->insert_id;
            return $data;
        }

        return false;
    }

    // Get All Sliders
    public function getSliders()
    {
        $sql = "SELECT * FROM sliders ORDER BY id DESC";

        $result = $this->conn->query($sql);

        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    // Update Slider
    public function updateSlider($data)
    {
        $sql = "UPDATE sliders
                SET
                    show_in=?,
                    title=?,
                    url=?,
                    description=?,
                    main_image=?,
                    mobile_image=?
                WHERE id=?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->bind_param(
            "ssssssi",
            $data['show_in'],
            $data['title'],
            $data['url'],
            $data['description'],
            $data['main_image'],
            $data['mobile_image'],
            $data['id']
        ) && $stmt->execute();
    }

    // Delete Slider
    public function deleteSlider($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM sliders WHERE id=?"
        );

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    // Get Single Slider
    public function getSlider($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM sliders WHERE id=?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }
}