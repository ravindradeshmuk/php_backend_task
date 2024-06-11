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
//This script will handle login
session_start();

// check if the user is already logged in
if(isset($_SESSION['Name']))
{
    header("location: welcome.php");
    exit;
}
require_once "config.php";

$username = $password = "";
$err = "";

// if request method is post
if ($_SERVER['REQUEST_METHOD'] == "POST"){
    if(empty(trim($_POST['Name'])) || empty(trim($_POST['Password'])))
    {
        $err = "Please enter Name + Password";
    }
    else{
        $username = trim($_POST['Name']);
        $password = trim($_POST['Password']);
    }


if(empty($err))
{
    $sql = "SELECT id, Name, Password FROM users WHERE Name = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $param_Name);
    $param_Name = $Name;
    
    
    // Try to execute this statement
    if(mysqli_stmt_execute($stmt)){
        mysqli_stmt_store_result($stmt);
        if(mysqli_stmt_num_rows($stmt) == 1)
                {
                    mysqli_stmt_bind_result($stmt, $id, $Name, $hashed_Password);
                    if(mysqli_stmt_fetch($stmt))
                    {
                        if(password_verify($Password, $hashed_Password))
                        {
                            // this means the password is corrct. Allow user to login
                            session_start();
                            $_SESSION["Name"] = $Name;
                            $_SESSION["id"] = $id;
                            $_SESSION["loggedin"] = true;

                            //Redirect user to welcome page
                            header("location: welcome.php");
                            
                        }
                    }

                }

    }
}    


}


?>