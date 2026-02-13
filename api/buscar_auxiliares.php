<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';

// Array que vai conter as duas listas
$response = [
    "autores" => [],
    "editoras" => []
];

// 1. Busca todos os autores
$resAutores = $conn->query("SELECT id, nome FROM autores ORDER BY nome ASC");
if ($resAutores) {
    while($row = $resAutores->fetch_assoc()) {
        $response["autores"][] = $row;
    }
}

// 2. Busca todas as editoras
$resEditoras = $conn->query("SELECT id, nome FROM editoras ORDER BY nome ASC");
if ($resEditoras) {
    while($row = $resEditoras->fetch_assoc()) {
        $response["editoras"][] = $row;
    }
}

// Retorna o JSON completo
echo json_encode($response);

$conn->close();
?>