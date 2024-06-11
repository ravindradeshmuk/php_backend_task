<?php
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit;
}
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$servername = "cloudletter.payflexsolutions.com";
$username = "ravindra";
$password = "ravi123#";
$database = "formdata";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
 
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
 
// Get the POST data
$postData = json_decode(file_get_contents('php://input'), true);
$email = $postData['email'];
$plainPassword = $postData['password'];
 
// Prepare and bind
$stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
 
// Execute the statement
$stmt->execute();
$stmt->bind_result($passwordHash);
$stmt->fetch();
 
// Check if the password matches
if (password_verify($plainPassword, $passwordHash)) {
    echo json_encode(array("status" => "success"));
} else {
    echo json_encode(array("status" => "failure"));
}
 
$stmt->close();
$conn->close();
?>