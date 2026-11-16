<?php
header("Content-Type: application/json; charset=utf-8");

require 'config.php';

$sql = "SELECT
            e.id,
            l.titulo AS livro_titulo,
            u.nome AS leitor_nome,
            e.data_emprestimo,
            e.data_devolucao_prevista,
            e.status
        FROM emprestimos e
        INNER JOIN livros l
            ON e.id_livro = l.id
        INNER JOIN usuarios u
            ON e.id_usuario = u.id
        WHERE e.status IN ('ativo', 'atrasado')
        ORDER BY e.data_emprestimo DESC";

$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "erro" => "Erro ao consultar os empréstimos."
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