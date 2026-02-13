<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';

// Recebe os dados do formulário
$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$telefone = $_POST['telefone'] ?? '';

if (!empty($nome)) {
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, telefone) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nome, $email, $telefone);
    
    if ($stmt->execute()) {
        echo json_encode(["sucesso" => true]);
    } else {
        echo json_encode(["sucesso" => false, "erro" => $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["sucesso" => false, "erro" => "Nome é obrigatório"]);
}
$conn->close();