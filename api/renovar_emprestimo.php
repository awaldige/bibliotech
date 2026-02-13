<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["sucesso" => false, "erro" => "ID não informado"]);
    exit;
}

try {
    // Definimos a nova data para +7 dias a partir de HOJE
    $novaData = date('Y-m-d', strtotime('+7 days'));

    $sql = "UPDATE emprestimos SET data_devolucao_prevista = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $novaData, $id);

    if ($stmt->execute()) {
        echo json_encode(["sucesso" => true, "nova_data" => date('d/m/Y', strtotime($novaData))]);
    } else {
        echo json_encode(["sucesso" => false, "erro" => $conn->error]);
    }
} catch (Exception $e) {
    echo json_encode(["sucesso" => false, "erro" => $e->getMessage()]);
}
$conn->close();