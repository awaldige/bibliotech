<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';

$sql = "SELECT * FROM autores ORDER BY nome ASC";
$result = $conn->query($sql);

$autores = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $autores[] = $row;
    }
}

echo json_encode($autores);
$conn->close();
?>