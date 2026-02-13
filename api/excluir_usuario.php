<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["sucesso" => false, "erro" => "ID não fornecido."]);
    exit;
}

try {
    // Tentamos excluir o leitor
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["sucesso" => true]);
    } else {
        echo json_encode(["sucesso" => false, "erro" => "Não foi possível excluir o leitor."]);
    }
} catch (mysqli_sql_exception $e) {
    // Se o banco impedir (ex: leitor com empréstimo ativo), caímos aqui
    echo json_encode([
        "sucesso" => false, 
        "erro" => "Este leitor não pode ser removido pois possui empréstimos registrados."
    ]);
}

$conn->close();