<?php

require_once __DIR__ . '/../models/Wishlist.php';

class WishlistController
{
    private $wishlist;

    public function __construct()
    {
        $this->wishlist = new Wishlist();
    }

    // ===========================
    // ADD TO WISHLIST
    // ===========================
    public function addWishlist()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input) {
            echo json_encode([
                "status" => false,
                "message" => "Invalid JSON"
            ]);
            return;
        }

        $userId = $input['user_id'] ?? 0;
        $productId = $input['product_id'] ?? 0;

        if (!$userId || !$productId) {
            echo json_encode([
                "status" => false,
                "message" => "user_id and product_id are required"
            ]);
            return;
        }

        if ($this->wishlist->isWishlist($userId, $productId)) {

            echo json_encode([
                "status" => false,
                "message" => "Product already in wishlist"
            ]);
            return;
        }

        if ($this->wishlist->addWishlist($userId, $productId)) {

            echo json_encode([
                "status" => true,
                "message" => "Product added to wishlist"
            ]);

        } else {

            echo json_encode([
                "status" => false,
                "message" => "Failed to add wishlist"
            ]);
        }
    }

    // ===========================
    // GET WISHLIST
    // ===========================
    public function getWishlist()
    {
        $userId = $_GET['user_id'] ?? 0;

        if (!$userId) {

            echo json_encode([
                "status" => false,
                "message" => "User ID is required"
            ]);

            return;
        }

        $wishlist = $this->wishlist->getWishlist($userId);

        echo json_encode([
            "status" => true,
            "data" => $wishlist
        ]);
    }

    // ===========================
    // REMOVE WISHLIST
    // ===========================
    public function removeWishlist()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input) {

            echo json_encode([
                "status" => false,
                "message" => "Invalid JSON"
            ]);

            return;
        }

        $userId = $input['user_id'] ?? 0;
        $productId = $input['product_id'] ?? 0;

        if (!$userId || !$productId) {

            echo json_encode([
                "status" => false,
                "message" => "user_id and product_id are required"
            ]);

            return;
        }

        if ($this->wishlist->removeWishlist($userId, $productId)) {

            echo json_encode([
                "status" => true,
                "message" => "Product removed from wishlist"
            ]);

        } else {

            echo json_encode([
                "status" => false,
                "message" => "Failed to remove wishlist"
            ]);
        }
    }

    // ===========================
    // CHECK WISHLIST
    // ===========================
    public function isWishlist()
    {
        $userId = $_GET['user_id'] ?? 0;
        $productId = $_GET['product_id'] ?? 0;

        $exists = $this->wishlist->isWishlist($userId, $productId);

        echo json_encode([
            "status" => true,
            "wishlist" => $exists
        ]);
    }
}