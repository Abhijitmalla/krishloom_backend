<?php

require_once __DIR__ . '/../models/Product.php';

class ProductController
{
    private $product;

    public function __construct()
    {
        $this->product = new Product();
    }

    public function addProduct()
    {
        $data = [];

        if (!empty($_POST) || !empty($_FILES)) {
            $data = $_POST;

            $mainImage = $this->handleUploadedFile('main_image', 'image');
            if ($mainImage !== null) {
                $data['main_image'] = $mainImage;
            }

            $galleryImages = $this->handleUploadedFiles('gallery_images', 'gallery');
            if ($galleryImages !== null) {
                $data['gallery_images'] = is_array($galleryImages)
                    ? json_encode($galleryImages)
                    : $galleryImages;
            }
        } else {
            $rawBody = file_get_contents("php://input");
            $data = !empty($rawBody) ? json_decode($rawBody, true) : [];
            $data = is_array($data) ? $data : [];
        }

        $result = $this->product->addProduct($data);

        if ($result) {
            echo json_encode([
                "status" => true,
                "message" => "Product added successfully"
            ]);
        } else {
            echo json_encode([
                "status" => false,
                "message" => "Failed to add product"
            ]);
        }
    }

    private function handleUploadedFile(...$fieldNames)
    {
        foreach ($fieldNames as $fieldName) {
            if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
                continue;
            }

            $file = $_FILES[$fieldName];
            if (!is_array($file['name'])) {
                return $this->saveUploadedFile($file);
            }
        }

        return null;
    }

    private function handleUploadedFiles(...$fieldNames)
    {
        foreach ($fieldNames as $fieldName) {
            if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
                continue;
            }

            $files = $_FILES[$fieldName];
            if (!is_array($files['name'])) {
                $savedFile = $this->saveUploadedFile($files);
                return $savedFile !== null ? [$savedFile] : null;
            }

            $savedFiles = [];
            foreach ($files['name'] as $index => $name) {
                if ($files['error'][$index] !== UPLOAD_ERR_OK) {
                    continue;
                }

                $savedFile = $this->saveUploadedFile([
                    'name' => $name,
                    'tmp_name' => $files['tmp_name'][$index],
                    'error' => $files['error'][$index],
                    'size' => $files['size'][$index],
                ]);

                if ($savedFile !== null) {
                    $savedFiles[] = $savedFile;
                }
            }

            return !empty($savedFiles) ? $savedFiles : null;
        }

        return null;
    }

    private function saveUploadedFile($file)
    {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return null;
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return null;
        }

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed, true)) {
            return null;
        }

        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . uniqid() . '.' . $ext;
        $targetPath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return null;
        }

        return $fileName;
    }

  public function getProducts()
{
    $result = $this->product->getProducts();

    $products = [];

    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    echo json_encode([
        "status" => true,
        "data" => $products
    ]);
}

    public function getProduct()
    {
        $id = $_GET['id'] ?? 0;

        $product = $this->product->getProduct($id);

        echo json_encode([
            "status" => true,
            "data" => $product
        ]);
    }

    public function deleteProduct()
    {
        $id = $_GET['id'] ?? 0;

        $result = $this->product->deleteProduct($id);

        if ($result) {
            echo json_encode([
                "status" => true,
                "message" => "Product deleted successfully"
            ]);
        } else {
            echo json_encode([
                "status" => false,
                "message" => "Delete failed"
            ]);
        }
    }
}