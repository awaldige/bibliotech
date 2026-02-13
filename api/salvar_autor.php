<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';

// Verificamos se o nome foi enviado via POST
$nome = $_POST['nome'] ?? '';

if (!empty($nome)) {
    // Usamos Prepared Statements para segurança contra SQL Injection
    $stmt = $conn->prepare("INSERT INTO autores (nome) VALUES (?)");
    $stmt->bind_param("s", $nome);
    
    if ($stmt->execute()) {
        echo json_encode(["sucesso" => true]);
    } else {
        echo json_encode(["sucesso" => false, "erro" => $conn->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(["sucesso" => false, "erro" => "O nome do autor não pode estar vazio."]);
}

$conn->close();
?>