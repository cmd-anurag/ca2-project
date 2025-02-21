<?php
// THE PHP SCRIPT ATTEMPTS TO CONNECT TO REMOTE MYSQL DB
error_reporting(E_ALL);
ini_set('display_errors', 1);

$config = require_once  __DIR__ . "/../../config/db_config.php";

$conn = new mysqli(
    $config['DB_HOST'],
    $config['DB_USERNAME'],
    $config['DB_PASSWORD'],
    $config['DB_NAME'],
    $config['DB_PORT']
);

if($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    die();
}
else {

    // i need to fix logging oof.


    // $log_file = __DIR__ . '/../../logs/db_connection.log';
    // $log_dir = dirname($log_file);
    
    // // Create logs directory if it doesn't exist
    // if (!file_exists($log_dir)) {
    //     mkdir($log_dir, 0777, true);
    // }
    
    // // Write to custom log file
    // error_log("Connected to DB\n", 3, $log_file);
}
?>
