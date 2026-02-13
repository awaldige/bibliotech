<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';

// Como o JavaScript envia FormData, usamos o $_POST do PHP
$nome     = $_POST['nome'] ?? null;
$email    = $_POST['email'] ?? null;
$telefone = $_POST['telefone'] ?? null;

if(!empty($nome) && !empty($email)) {
    try {
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, telefone) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nome, $email, $telefone);
        
        if($stmt->execute()) {
            echo json_encode(["sucesso" => true]);
        } else {
            echo json_encode(["sucesso" => false, "erro" => $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(["sucesso" => false, "erro" => $e->getMessage()]);
    }
} else {
    echo json_encode(["sucesso" => false, "erro" => "Nome e Email são obrigatórios."]);
}

$conn->close();