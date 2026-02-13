<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';

$id = $_POST['id'] ?? null;
$nome = $_POST['nome'] ?? null;
$nacionalidade = $_POST['nacionalidade'] ?? null;

try {
    $stmt = $conn->prepare("UPDATE autores SET nome = ?, nacionalidade = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nome, $nacionalidade, $id);
    echo json_encode(["sucesso" => $stmt->execute()]);
} catch (Exception $e) {
    echo json_encode(["sucesso" => false, "erro" => $e->getMessage()]);
}
$conn->close();