<?php

require_once __DIR__ . '/../models/Category.php';

class CategoryController
{
    private $category;

    public function __construct()
    {
        $this->category = new Category();
    }

    // Add
    public function addCategory()
    {
        $imagePath = null;

        if (!empty($_FILES['image']['name'])) {

            $folder = "../uploads/categories/";

            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $fileName = time() . "_" . $_FILES['image']['name'];

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                $folder . $fileName
            );

            $imagePath = "uploads/categories/" . $fileName;
        }

        $data = [
            'name' => $_POST['name'],
            'type_id' => $_POST['type_id'],
            'image' => $imagePath
        ];

        $status = $this->category->addCategory($data);

        echo json_encode([
            'status' => $status,
            'message' => $status
                ? 'Category added successfully'
                : 'Failed to add category'
        ]);
    }

    // Get
    public function getCategory()
    {
        $categories = $this->category->getCategories();

        echo json_encode([
            'status' => true,
            'data' => $categories
        ]);
    }

    // Update
    public function updateCategory()
    {
        $imagePath = null;

        if (!empty($_FILES['image']['name'])) {

            $folder = "../uploads/categories/";

            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $fileName = time() . "_" . $_FILES['image']['name'];

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                $folder . $fileName
            );

            $imagePath = "uploads/categories/" . $fileName;
        }

        $data = [
            'id' => $_POST['id'],
            'name' => $_POST['name'],
            'type_id' => $_POST['type_id'],
            'image' => $imagePath
        ];

        $status = $this->category->updateCategory($data);

        echo json_encode([
            'status' => $status,
            'message' => $status
                ? 'Category updated successfully'
                : 'Failed to update category'
        ]);
    }

    // Delete
    public function deleteCategory()
    {
        $id = $_POST['id'];

        $status = $this->category->deleteCategory($id);

        echo json_encode([
            'status' => $status,
            'message' => $status
                ? 'Category deleted successfully'
                : 'Failed to delete category'
        ]);
    }
}