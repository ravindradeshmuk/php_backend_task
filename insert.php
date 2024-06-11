<?php
// Set the appropriate headers for CORS
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
$servername = "localhost";
$username = "ravindra";
$password = "ravi123#";
$database = "formdata";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    $response = [
        "status" => "error",
        "message" => "Connection failed: " . $conn->connect_error
    ];
    echo json_encode($response);
    exit;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the raw POST data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Check if data is received correctly
    if (!$data) {
        $response = [
            "status" => "error",
            "message" => "Invalid request data"
        ];
        echo json_encode($response);
        exit;
    }

    // Prepare and bind the SQL statement
    $stmt = $conn->prepare("INSERT INTO webinardata (Name, Mail, Marketing, Company, SelectOption, Country) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $data['Name'], $data['Mail'], $data['Marketing'], $data['Company'], $data['SelectOption'], $data['Country']);

    if ($stmt->execute()) {
        $response = [
            "status" => "success",
            "message" => "Data submitted successfully"
        ];
    } else {
        $response = [
            "status" => "error",
            "message" => "Error: " . $stmt->error
        ];
    }

    $stmt->close();
} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Fetch data from the database
    $result = $conn->query("SELECT * FROM webinardata");

    if ($result->num_rows > 0) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $response = [
            "status" => "success",
            "data" => $data
        ];
    } else {
        $response = [
            "status" => "error",
            "message" => "No data found"
        ];
    }
} else {
    $response = [
        "status" => "error",
        "message" => "Invalid request method"
    ];
}

// Close connection
$conn->close();

// Output the response as JSON
echo json_encode($response);
?>
