<?php

require_once __DIR__ . '/../models/Slider.php';

class SliderController
{
    private $slider;

    public function __construct()
    {
        $this->slider = new Slider();
    }

    private function uploadImage($file)
    {
        if (!isset($file) || $file['error'] != 0) {
            return null;
        }

        $folder = "../uploads/sliders/";

        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        $fileName =
            time() . "_" .
            preg_replace('/\s+/', '_', basename($file['name']));

        $path = $folder . $fileName;

        move_uploaded_file(
            $file['tmp_name'],
            $path
        );

        return "uploads/sliders/" . $fileName;
    }

    // Add Slider
    public function addSlider()
    {
        $data = [
            'show_in' => $_POST['show_in'],
            'title' => $_POST['title'],
            'url' => $_POST['url'],
            'description' => $_POST['description'],
            'main_image' => $this->uploadImage($_FILES['main_image']),
            'mobile_image' => $this->uploadImage($_FILES['mobile_image'])
        ];

        $result = $this->slider->addSlider($data);

        if ($result) {
            echo json_encode([
                "status" => true,
                "message" => "Slider Added",
                "data" => $result
            ]);
        } else {
            echo json_encode([
                "status" => false,
                "message" => "Failed"
            ]);
        }
    }

    // Get Sliders
    public function getSliders()
    {
        echo json_encode([
            "status" => true,
            "data" => $this->slider->getSliders()
        ]);
    }

    // Update Slider
    public function updateSlider()
    {
        $old = $this->slider->getSlider($_POST['id']);

        $main =
            isset($_FILES['main_image'])
                ? $this->uploadImage($_FILES['main_image'])
                : $old['main_image'];

        $mobile =
            isset($_FILES['mobile_image'])
                ? $this->uploadImage($_FILES['mobile_image'])
                : $old['mobile_image'];

        $data = [
            'id' => $_POST['id'],
            'show_in' => $_POST['show_in'],
            'title' => $_POST['title'],
            'url' => $_POST['url'],
            'description' => $_POST['description'],
            'main_image' => $main,
            'mobile_image' => $mobile
        ];

        $success = $this->slider->updateSlider($data);

        echo json_encode([
            "status" => $success,
            "message" => $success
                ? "Slider Updated"
                : "Update Failed",
            "data" => $data
        ]);
    }

    // Delete Slider
    public function deleteSlider()
    {
        $success = $this->slider->deleteSlider($_POST['id']);

        echo json_encode([
            "status" => $success,
            "message" => $success
                ? "Slider Deleted"
                : "Delete Failed"
        ]);
    }
}