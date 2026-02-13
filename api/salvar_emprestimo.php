<?php
header("Content-Type: application/json");
require 'config.php';

$id_livro = $_POST['id_livro'];
$id_leitor = $_POST['id_leitor'];
$data_prevista = $_POST['data_devolucao'];
$hoje = date('Y-m-d H:i:s');

try {
    // 1. Grava o empréstimo com status 'emprestado'
    $stmt = $conn->prepare("INSERT INTO emprestimos (id_livro, id_usuario, data_emprestimo, data_devolucao_prevista, status) VALUES (?, ?, ?, ?, 'emprestado')");
    $stmt->bind_param("iiss", $id_livro, $id_leitor, $hoje, $data_prevista);
    
    if ($stmt->execute()) {
        echo json_encode(["sucesso" => true]);
    } else {
        throw new Exception("Erro ao gravar no banco.");
    }
} catch (Exception $e) {
    echo json_encode(["sucesso" => false, "erro" => $e->getMessage()]);
}