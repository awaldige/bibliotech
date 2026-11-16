<?php
header("Content-Type: application/json; charset=utf-8");

require 'config.php';

$sql = "
    SELECT * FROM (
        /* 1. LIVROS */
        SELECT
            titulo AS item,
            'livro' AS tipo,
            data_cadastro AS data
        FROM livros

        UNION ALL

        /* 2. LEITORES */
        SELECT
            nome AS item,
            'leitor' AS tipo,
            data_cadastro AS data
        FROM usuarios

        UNION ALL

        /* 3. RESERVAS */
        SELECT
            COALESCE(l.titulo, 'Reserva de Livro') AS item,
            'reserva' AS tipo,
            r.data_reserva AS data
        FROM reservas r
        LEFT JOIN livros l
            ON l.id = r.id_livro

        UNION ALL

        /* 4. EMPRÉSTIMOS */
        SELECT
            COALESCE(l.titulo, 'Livro Emprestado') AS item,
            'emprestimo' AS tipo,
            e.data_emprestimo AS data
        FROM emprestimos e
        LEFT JOIN livros l
            ON l.id = e.id_livro

        UNION ALL

        /* 5. DEVOLUÇÕES */
        SELECT
            COALESCE(l.titulo, 'Livro Devolvido') AS item,
            'devolucao' AS tipo,
            e.data_devolucao_real AS data
        FROM emprestimos e
        LEFT JOIN livros l
            ON l.id = e.id_livro
        WHERE e.status = 'devolvido'
          AND e.data_devolucao_real IS NOT NULL

    ) AS todas

    ORDER BY data DESC
    LIMIT 20
";

$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "erro" => "Erro ao consultar o histórico de atividades."
    ], JSON_UNESCAPED_UNICODE);

    $conn->close();
    exit;
}

$atividades = [];

while ($row = $result->fetch_assoc()) {
    $atividades[] = $row;
}

echo json_encode($atividades, JSON_UNESCAPED_UNICODE);

$conn->close();