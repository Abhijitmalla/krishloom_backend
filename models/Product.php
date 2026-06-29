<?php

require_once __DIR__ . '/../config/Database.php';

class Product
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function addProduct($data)
    {
        $sql = "INSERT INTO products (
                    product_code,
                    product_title,
                    product_type,
                    product_category,
                    manufacturer,
                    hsn_code,
                    mrp,
                    selling_price,
                    basic_price,
                    gst_percent,
                    gst_amount,
                    sizes,
                    colors,
                    main_image,
                    gallery_images,
                    features,
                    description,
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "ssssssdddddsssssss",
            $data['product_code'],
            $data['product_title'],
            $data['product_type'],
            $data['product_category'],
            $data['manufacturer'],
            $data['hsn_code'],
            $data['mrp'],
            $data['selling_price'],
            $data['basic_price'],
            $data['gst_percent'],
            $data['gst_amount'],
            $data['sizes'],
            $data['colors'],
            $data['main_image'],
            $data['gallery_images'],
            $data['features'],
            $data['description'],
            $data['status']
        );

        return $stmt->execute();
    }

    public function getProducts()
    {
        $sql = "SELECT * FROM products ORDER BY id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function getProduct($id)
    {
        $sql = "SELECT * FROM products WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function deleteProduct($id)
    {
        $sql = "DELETE FROM products WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}