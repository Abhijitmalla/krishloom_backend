<?php

require_once __DIR__ . '/../config/database.php';

class Wishlist
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // ===========================
    // ADD TO WISHLIST
    // ===========================
    public function addWishlist($userId, $productId)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO wishlist (user_id, product_id)
            VALUES (?, ?)
        ");

        $stmt->bind_param("ii", $userId, $productId);

        return $stmt->execute();
    }

    // ===========================
    // GET USER WISHLIST
    // ===========================
    public function getWishlist($userId)
    {
        $stmt = $this->conn->prepare("
            SELECT
                w.id AS wishlist_id,
                p.id,
                p.product_code,
                p.product_title,
                p.product_type,
                p.product_category,
                p.manufacturer,
                p.hsn_code,
                p.mrp,
                p.selling_price,
                p.basic_price,
                p.gst_percent,
                p.gst_amount,
                p.sizes,
                p.colors,
                p.features,
                p.description,
                p.status,
                p.main_image
            FROM wishlist w
            INNER JOIN products p
                ON w.product_id = p.id
            WHERE w.user_id = ?
            AND p.image_type = 'main'
            ORDER BY w.id DESC
        ");

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ===========================
    // REMOVE FROM WISHLIST
    // ===========================
    public function removeWishlist($userId, $productId)
    {
        $stmt = $this->conn->prepare("
            DELETE FROM wishlist
            WHERE user_id = ?
            AND product_id = ?
        ");

        $stmt->bind_param("ii", $userId, $productId);

        return $stmt->execute();
    }

    // ===========================
    // CHECK WISHLIST
    // ===========================
    public function isWishlist($userId, $productId)
    {
        $stmt = $this->conn->prepare("
            SELECT id
            FROM wishlist
            WHERE user_id = ?
            AND product_id = ?
        ");

        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();

        $result = $stmt->get_result();

        return ($result->num_rows > 0);
    }
}