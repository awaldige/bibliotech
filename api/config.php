<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "biblioteca";
$port = 3308; 

// Conecta passando a porta como 5º parâmetro
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    die(json_encode(["sucesso" => false, "erro" => "Falha na conexão: " . $conn->connect_error]));
}

$conn->set_charset("utf8mb4");
?>
