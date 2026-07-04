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
            empty($data['rating']) ||
         empty($data['title'])

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

    // User - Accepted Reviews Only
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

        $reviews = $this->review->getReviews($productId);

        echo json_encode([
            "success" => true,
            "reviews" => $reviews
        ]);
    }

    // Admin - All Reviews
    public function getAllReviews()
    {
        $reviews = $this->review->getAllReviews();

        echo json_encode([
            "success" => true,
            "reviews" => $reviews
        ]);
    }

    // Delete Review
    public function deleteReview()
    {
        $id = $_GET['id'] ?? 0;

        if (!$id) {
            echo json_encode([
                "success" => false,
                "message" => "Review ID required"
            ]);
            return;
        }

        $result = $this->review->deleteReview($id);

        echo json_encode([
            "success" => $result,
            "message" => $result
                ? "Review deleted successfully"
                : "Failed to delete review"
        ]);
    }


    public function updateReviewStatus()
{
    $data = json_decode(file_get_contents("php://input"), true);

    if (
        empty($data['id']) ||
        empty($data['status'])
    ) {
        echo json_encode([
            "success" => false,
            "message" => "Review ID and status are required"
        ]);
        return;
    }

    $allowedStatuses = ['accepted', 'rejected'];

    if (!in_array($data['status'], $allowedStatuses)) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid status"
        ]);
        return;
    }

    $result = $this->review->updateReviewStatus(
        $data['id'],
        $data['status']
    );

    echo json_encode([
        "success" => $result,
        "message" => $result
            ? "Review status updated successfully"
            : "Failed to update review status"
    ]);
}
}