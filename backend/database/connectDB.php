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
    die("Connection failed skullemoji");
}
else {
    echo "Connection established.";
}
?>
