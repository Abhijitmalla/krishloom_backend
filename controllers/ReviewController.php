<?php

require_once __DIR__ . '/../models/Review.php';

class ReviewController
{
    private $review;

    public function __construct($db)
    {
        $this->review = new Review($db);
    }

    // Add Review
    public function addReview()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (
            empty($data['product_id']) ||
            empty($data['user_id']) ||
            empty($data['rating'])
        ) {
            echo json_encode([
                "success" => false,
                "message" => "Required fields missing"
            ]);
            return;
        }

        $result = $this->review->addReview($data);

        echo json_encode([
            "success" => $result,
            "message" => $result
                ? "Review added successfully"
                : "Failed to add review"
        ]);
    }

    // Get Reviews
    public function getReviews()
    {
        $productId = $_GET['product_id'] ?? 0;

        if (!$productId) {
            echo json_encode([
                "success" => false,
                "message" => "Product ID required"
            ]);
            return;
        }

        $reviews = $this->review
            ->getReviewsByProduct($productId);

        echo json_encode([
            "success" => true,
            "reviews" => $reviews
        ]);
    }

    // Delete Review
    public function deleteReview()
    {
        $id = $_GET['id'] ?? 0;

        $result = $this->review->deleteReview($id);

        echo json_encode([
            "success" => $result,
            "message" => $result
                ? "Review deleted successfully"
                : "Failed to delete review"
        ]);
    }
}