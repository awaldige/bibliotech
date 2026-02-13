<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';

$id = $_POST['id'] ?? '';
$titulo = $_POST['titulo'] ?? '';
$quantidade = $_POST['quantidade'] ?? '';

if (empty($id) || empty($titulo)) {
    echo json_encode(["sucesso" => false, "erro" => "Dados incompletos."]);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE livros SET titulo = ?, quantidade = ? WHERE id = ?");
    $stmt->bind_param("sii", $titulo, $quantidade, $id);

    if ($stmt->execute()) {
        echo json_encode(["sucesso" => true]);
    } else {
        echo json_encode(["sucesso" => false, "erro" => $conn->error]);
    }
} catch (Exception $e) {
    echo json_encode(["sucesso" => false, "erro" => $e->getMessage()]);
}

$conn->close();