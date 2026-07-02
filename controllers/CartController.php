<?php

require_once __DIR__.'/../models/Cart.php';

class CartController
{
    private $cart;

    public function __construct()
    {
        $this->cart=new Cart();
    }

    // Add Cart
    public function addCart()
    {
        $input=json_decode(file_get_contents("php://input"),true);

        if(!$input){
            echo json_encode(["status"=>false,"message"=>"Invalid JSON"]);
            return;
        }

        $this->cart->addCart(
            $input['user_id'],
            $input['product_id'],
            $input['quantity']
        );

        echo json_encode([
            "status"=>true,
            "message"=>"Added to cart"
        ]);
    }

    // Get Cart
    public function getCart()
    {
        $userId=$_GET['user_id'];

        $data=$this->cart->getCart($userId);

        echo json_encode([
            "status"=>true,
            "data"=>$data
        ]);
    }

    // Update Quantity
    public function updateQuantity()
    {
        $input=json_decode(file_get_contents("php://input"),true);

        $this->cart->updateQuantity(
            $input['user_id'],
            $input['product_id'],
            $input['quantity']
        );

        echo json_encode([
            "status"=>true,
            "message"=>"Quantity Updated"
        ]);
    }

    // Remove Item
    public function removeCart()
    {
        $input=json_decode(file_get_contents("php://input"),true);

        $this->cart->removeCart(
            $input['user_id'],
            $input['product_id']
        );

        echo json_encode([
            "status"=>true,
            "message"=>"Removed Successfully"
        ]);
    }

    // Clear Cart
    public function clearCart()
    {
        $input=json_decode(file_get_contents("php://input"),true);

        $this->cart->clearCart(
            $input['user_id']
        );

        echo json_encode([
            "status"=>true,
            "message"=>"Cart Cleared"
        ]);
    }
}