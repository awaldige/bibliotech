<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';

// Ajustamos os nomes das colunas para id_autor e id_editora
$sql = "SELECT 
            l.id, 
            l.titulo, 
            l.isbn,
            l.ano_publicacao, 
            l.quantidade,
            a.nome AS nome_autor, 
            e.nome AS nome_editora 
        FROM livros l
        LEFT JOIN autores a ON l.id_autor = a.id
        LEFT JOIN editoras e ON l.id_editora = e.id
        ORDER BY l.id DESC";

$result = $conn->query($sql);
$livros = [];

if ($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $livros[] = $row;
        }
    }
    echo json_encode($livros);
} else {
    // Se houver erro no SQL, ele avisa o que é
    echo json_encode(["erro" => $conn->error]);
}

$conn->close();