<?php
header("Content-Type: application/json");
require 'config.php';
$result = $conn->query("SELECT * FROM usuarios ORDER BY nome ASC");
echo json_encode($result->fetch_all(MYSQLI_ASSOC));