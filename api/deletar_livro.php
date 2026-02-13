<?php
header("Content-Type: application/json");
require 'config.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $conn->prepare("DELETE FROM livros WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(["sucesso" => true]);
    } else {
        echo json_encode(["sucesso" => false, "erro" => $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["sucesso" => false, "erro" => "ID não fornecido"]);
}

$conn->close();