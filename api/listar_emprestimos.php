<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';
date_default_timezone_set('America/Sao_Paulo');

try {
    // Usamos LEFT JOIN para garantir que o livro apareça.
    // Removi o WHERE status = 'emprestado' para testar se os dados "brotam" na tela.
    $sql = "SELECT 
                e.id, 
                COALESCE(u.nome, 'Leitor não identificado') AS leitor, 
                COALESCE(l.titulo, 'Livro não identificado') AS livro, 
                e.data_emprestimo, 
                e.data_devolucao_prevista 
            FROM emprestimos e
            LEFT JOIN usuarios u ON e.id_usuario = u.id
            LEFT JOIN livros l ON e.id_livro = l.id
            WHERE e.status != 'devolvido' 
            ORDER BY e.data_emprestimo DESC";

    $result = $conn->query($sql);
    $dados = [];
    while($row = $result->fetch_assoc()) {
        $dados[] = $row;
    }
    echo json_encode($dados);
} catch (Exception $e) {
    echo json_encode(["erro" => $e->getMessage()]);
}