<?php
// THE PHP SCRIPT ATTEMPTS TO CONNECT TO REMOTE MYSQL DB

$config = require_once  __DIR__ . "/../../config/db_config.php";

$conn = new mysqli(
    $config['DB_HOST'],
    $config['DB_USERNAME'],
    $config['DB_PASSWORD'],
    $config['DB_NAME'],
    $config['DB_PORT']
);

if($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    die();
}
else {    
    // Write to custom log file
    $log_file = __DIR__ . '/../logs/dblog.log';
    $log_message = sprintf(
        "[%s] Database Connection: Success\nHost: %s\nDatabase: %s\nUser: %s\n\n",
        date('Y-m-d H:i:s'),
        $config['DB_HOST'],
        $config['DB_NAME'],
        $config['DB_USERNAME']
    );
    error_log($log_message, 3, $log_file);
}
?>
