<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';

try {
    // Buscamos os dados unindo as tabelas para pegar o Título do Livro e Nome do Leitor
    $sql = "SELECT 
                e.id, 
                l.titulo AS livro_titulo, 
                u.nome AS leitor_nome, 
                e.data_emprestimo, 
                e.data_devolucao_prevista, 
                e.status 
            FROM emprestimos e
            INNER JOIN livros l ON e.id_livro = l.id
            INNER JOIN usuarios u ON e.id_usuario = u.id
            WHERE e.status = 'emprestado' 
            ORDER BY e.data_emprestimo DESC";

    $result = $conn->query($sql);
    $dados = [];

    while($row = $result->fetch_assoc()) {
        $dados[] = $row;
    }

    echo json_encode($dados);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erro" => $e->getMessage()]);
}