<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';

$id_livro   = $_POST['id_livro'] ?? null;
$id_usuario = $_POST['id_usuario'] ?? null; // Alinhado com o SQL

if (!$id_livro || !$id_usuario) {
    echo json_encode(["sucesso" => false, "erro" => "Dados incompletos"]);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO reservas (id_livro, id_usuario, status) VALUES (?, ?, 'pendente')");
    $stmt->bind_param("ii", $id_livro, $id_usuario);
    
    if ($stmt->execute()) {
        echo json_encode(["sucesso" => true]);
    } else {
        echo json_encode(["sucesso" => false, "erro" => $conn->error]);
    }
} catch (Exception $e) {
    echo json_encode(["sucesso" => false, "erro" => $e->getMessage()]);
}
$conn->close();