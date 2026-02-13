<?php
header("Content-Type: application/json");
require 'config.php';

$sql = "SELECT id, nome, localizacao FROM editoras ORDER BY nome ASC";
$result = $conn->query($sql);
$editoras = [];

while($row = $result->fetch_assoc()) {
    $editoras[] = $row;
}

echo json_encode($editoras);
$conn->close();
?>