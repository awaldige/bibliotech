<?php
header("Content-Type: application/json; charset=utf-8");

require 'config.php';

$sql = "SELECT
            r.id,
            r.id_livro,
            r.id_usuario,
            l.titulo AS titulo_livro,
            u.nome AS nome_usuario,
            r.data_reserva,
            r.status
        FROM reservas r
        INNER JOIN livros l
            ON r.id_livro = l.id
        INNER JOIN usuarios u
            ON r.id_usuario = u.id
        WHERE r.status = 'pendente'
        ORDER BY r.data_reserva ASC";

$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "erro" => "Erro ao consultar as reservas."
    ], JSON_UNESCAPED_UNICODE);

    $conn->close();
    exit;
}

$dados = [];

while ($row = $result->fetch_assoc()) {
    $dados[] = $row;
}

echo json_encode($dados, JSON_UNESCAPED_UNICODE);

$conn->close();