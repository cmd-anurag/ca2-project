<?php

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Bad Request Method"]);
    die();
}
if(!isset($_POST["userinput"])) {
    http_response_code(400);
    echo json_encode(["sucess" => false, "message" => "missing body"]);
    die();
}

// LOAD FROM .ENV
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$apikey = $_ENV["API_KEY"];


$userInput = $_POST["userinput"];


// Gemini api's json structure is nasty bruh
$requestBody = json_encode([
    "contents" => [
        [
            "role" => "user",
            "parts" => [["text" => "You are a helpful healthcare assistant. Avoid very long responses. Do not format your responses and answer in plaintext."]]
        ],
        [
            "role" => "user",
            "parts" => [["text" => $userInput]]
        ]
    ]
]);


$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$apikey";
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);


echo $response;