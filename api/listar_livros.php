<?php
header("Content-Type: application/json; charset=utf-8");

require 'config.php';

$sql = "SELECT
            l.id,
            l.titulo,
            l.isbn,
            l.ano_publicacao,
            l.quantidade,
            a.nome AS nome_autor,
            e.nome AS nome_editora,

            COALESCE(emp.emprestados, 0) AS emprestados,
            COALESCE(res.reservados, 0) AS reservados,

            l.quantidade
                - COALESCE(emp.emprestados, 0)
                - COALESCE(res.reservados, 0) AS disponiveis

        FROM livros l

        LEFT JOIN autores a
            ON l.id_autor = a.id

        LEFT JOIN editoras e
            ON l.id_editora = e.id

        LEFT JOIN (
            SELECT
                id_livro,
                COUNT(*) AS emprestados
            FROM emprestimos
            WHERE status IN ('ativo', 'atrasado')
            GROUP BY id_livro
        ) emp
            ON emp.id_livro = l.id

        LEFT JOIN (
            SELECT
                id_livro,
                COUNT(*) AS reservados
            FROM reservas
            WHERE status = 'pendente'
            GROUP BY id_livro
        ) res
            ON res.id_livro = l.id

        ORDER BY l.id DESC";

$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "erro" => "Erro ao consultar livros."
    ]);

    $conn->close();
    exit;
}

$livros = [];

while ($row = $result->fetch_assoc()) {
    $livros[] = $row;
}

echo json_encode($livros, JSON_UNESCAPED_UNICODE);

$conn->close();