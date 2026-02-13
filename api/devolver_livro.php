<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';

$id = $_GET['id'] ?? '';

if (empty($id)) {
    echo json_encode(["sucesso" => false, "erro" => "ID não informado."]);
    exit;
}

try {
    // Atualiza o status para 'devolvido' e registra a data atual
    $stmt = $conn->prepare("UPDATE emprestimos SET status = 'devolvido', data_devolucao_real = NOW() WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["sucesso" => true]);
    } else {
        echo json_encode(["sucesso" => false, "erro" => $conn->error]);
    }
} catch (Exception $e) {
    echo json_encode(["sucesso" => false, "erro" => $e->getMessage()]);
}

$conn->close();