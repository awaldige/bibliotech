<?php
header("Content-Type: application/json");
require 'config.php';

$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM reservas WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["sucesso" => true]);
} else {
    echo json_encode(["sucesso" => false]);
}