<?php
header("Content-Type: application/json");
require 'config.php';

// Recebe os dados do JavaScript
$id_reserva = $_POST['id_reserva'] ?? null;
$id_livro   = $_POST['id_livro'] ?? null;
$id_usuario = $_POST['id_usuario'] ?? null;

// Define a data de devolução para daqui a 7 dias
$data_prevista = date('Y-m-d', strtotime('+7 days'));

if (!$id_reserva || !$id_livro || !$id_usuario) {
    echo json_encode(["sucesso" => false, "erro" => "Dados incompletos"]);
    exit;
}

$conn->begin_transaction();

try {
    // 1. Cria o empréstimo usando o nome correto: data_devolucao_prevista
    $stmt1 = $conn->prepare("INSERT INTO emprestimos (id_livro, id_usuario, data_devolucao_prevista, status) VALUES (?, ?, ?, 'ativo')");
    $stmt1->bind_param("iis", $id_livro, $id_usuario, $data_prevista);
    $stmt1->execute();

    // 2. Marca a reserva como concluída para ela sumir da lista de pendentes
    $stmt2 = $conn->prepare("UPDATE reservas SET status = 'concluida' WHERE id = ?");
    $stmt2->bind_param("i", $id_reserva);
    $stmt2->execute();

    $conn->commit();
    echo json_encode(["sucesso" => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["sucesso" => false, "erro" => "Erro no banco: " . $e->getMessage()]);
}

$conn->close();
?>