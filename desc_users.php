<?php
require_once __DIR__ . '/config/Database.php';
$db = new Database();
$conn = $db->connect();
$result = $conn->query("DESCRIBE users");
while($row = $result->fetch_assoc()) {
    print_r($row);
}
