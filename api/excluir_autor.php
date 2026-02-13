<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["sucesso" => false, "erro" => "ID não fornecido"]);
    exit;
}

try {
    // IMPORTANTE: Se o autor tiver livros cadastrados, o banco pode impedir a exclusão 
    // devido à chave estrangeira. Usamos um try/catch para avisar o usuário.
    $stmt = $conn->prepare("DELETE FROM autores WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["sucesso" => true]);
    } else {
        throw new Exception($conn->error);
    }

} catch (Exception $e) {
    // Caso o erro seja de chave estrangeira (autor com livros vinculados)
    $msg = (strpos($e->getMessage(), 'foreign key') !== false) 
           ? "Não é possível excluir: Este autor possui livros vinculados." 
           : $e->getMessage();
           
    echo json_encode(["sucesso" => false, "erro" => $msg]);
}

$conn->close();