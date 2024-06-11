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

    if (isset($data['action'])) {
        if ($data['action'] == 'register') {
            // Handle user registration
            if (!isset($data['Name']) || !isset($data['Email']) || !isset($data['Password']) || !isset($data['Mobile'])) {
                $response = [
                    "status" => "error",
                    "message" => "Missing required fields"
                ];
                echo json_encode($response);
                exit;
            }

            $stmt = $conn->prepare("INSERT INTO registration_db(Name, Password, Email, Mobile) VALUES (?, ?, ?, ?)");
            $passwordHash = password_hash($data['Password'], PASSWORD_DEFAULT);
            $stmt->bind_param("ssss", $data['Email'], $passwordHash, $data['Name'], $data['Mobile']);

            if ($stmt->execute()) {
                $response = [
                    "status" => "success",
                    "message" => "User registered successfully"
                ];
            } else {
                $response = [
                    "status" => "error",
                    "message" => "Error: " . $stmt->error
                ];
            }
            $stmt->close();
        } elseif ($data['action'] == 'login') {
            // Handle user login
            if (!isset($data['Email']) || !isset($data['Password'])) {
                $response = [
                    "status" => "error",
                    "message" => "Missing required fields"
                ];
                echo json_encode($response);
                exit;
            }

            $stmt = $conn->prepare("SELECT password FROM registration_db WHERE username = ?");
            $stmt->bind_param("s", $data['Email']);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($passwordHash);
                $stmt->fetch();

                if (password_verify($data['Password'], $passwordHash)) {
                    $response = [
                        "status" => "success",
                        "message" => "Login successful"
                    ];
                } else {
                    $response = [
                        "status" => "error",
                        "message" => "Invalid username or password"
                    ];
                }
            } else {
                $response = [
                    "status" => "error",
                    "message" => "Invalid username or password"
                ];
            }
            $stmt->close();
        } else {
            $response = [
                "status" => "error",
                "message" => "Invalid action"
            ];
        }
    } else {
        $response = [
            "status" => "error",
            "message" => "No action specified"
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
