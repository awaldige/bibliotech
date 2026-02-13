<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';

// SQL ajustado: Adicionamos r.id_livro e r.id_usuario na seleção
$sql = "SELECT 
            r.id, 
            r.id_livro, 
            r.id_usuario, 
            l.titulo as titulo_livro, 
            u.nome as nome_usuario, 
            r.data_reserva, 
            r.status
        FROM reservas r
        JOIN livros l ON r.id_livro = l.id
        JOIN usuarios u ON r.id_usuario = u.id
        WHERE r.status = 'pendente'
        ORDER BY r.data_reserva ASC";

$result = $conn->query($sql);
$dados = [];

if ($result) {
    while($row = $result->fetch_assoc()) {
        $dados[] = $row;
    }
}

// Retorna o JSON para o JavaScript com todos os IDs necessários
echo json_encode($dados);
$conn->close();