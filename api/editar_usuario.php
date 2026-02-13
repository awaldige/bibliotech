<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';

$id = $_POST['id'];
$nome = $_POST['nome'];
$email = $_POST['email'];
$telefone = $_POST['telefone']; // Recebendo o telefone

$stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ? WHERE id = ?");
$stmt->bind_param("sssi", $nome, $email, $telefone, $id);

echo json_encode(["sucesso" => $stmt->execute()]);
$conn->close();