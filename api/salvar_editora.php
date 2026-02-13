<?php
header("Content-Type: application/json");
require 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

if(!empty($data['nome'])) {
    $stmt = $conn->prepare("INSERT INTO editoras (nome, localizacao) VALUES (?, ?)");
    $stmt->bind_param("ss", $data['nome'], $data['localizacao']);
    
    if($stmt->execute()) {
        echo json_encode(["sucesso" => true]);
    } else {
        echo json_encode(["sucesso" => false, "erro" => $conn->error]);
    }
}
$conn->close();
?>