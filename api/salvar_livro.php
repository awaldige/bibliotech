<?php
header("Content-Type: application/json");
require 'config.php';

$titulo = $_POST['titulo'];
$isbn = $_POST['isbn'];
$id_autor = $_POST['id_autor'];
$id_editora = $_POST['id_editora'];
$ano = $_POST['ano_publicacao'];
$qtd = $_POST['quantidade'];

$sql = "INSERT INTO livros (titulo, isbn, id_autor, id_editora, ano_publicacao, quantidade) 
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssiiii", $titulo, $isbn, $id_autor, $id_editora, $ano, $qtd);

if($stmt->execute()) {
    echo json_encode(["sucesso" => true]);
} else {
    echo json_encode(["sucesso" => false, "erro" => $conn->error]);
}

$stmt->close();
$conn->close();