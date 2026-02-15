<?php
// Dados do Servidor (InfinityFree)
$host = "sql313.infinityfree.com"; 
$user = "if0_41153277"; 
$pass = "Awaldige785143"; /
$db   = "if0_41153277_bibliotech"; 


$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    die(json_encode(["sucesso" => false, "erro" => "Falha na conexão: " . $conn->connect_error]));
}

$conn->set_charset("utf8mb4");
?>
