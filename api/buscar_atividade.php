<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';

try {
    $sql = "
        SELECT * FROM (
            /* 1. LIVROS */
            (SELECT titulo as item, 'livro' as tipo, data_cadastro as data FROM livros)
            UNION ALL
            /* 2. LEITORES */
            (SELECT nome as item, 'leitor' as tipo, data_cadastro as data FROM usuarios)
            UNION ALL
            /* 3. RESERVAS - Usando LEFT JOIN para garantir que o título venha */
            (SELECT COALESCE(l.titulo, 'Reserva de Livro') as item, 'reserva' as tipo, r.data_reserva as data 
             FROM reservas r LEFT JOIN livros l ON r.id_livro = l.id)
            UNION ALL
            /* 4. EMPRÉSTIMOS */
            (SELECT COALESCE(l.titulo, 'Livro Emprestado') as item, 'emprestimo' as tipo, e.data_emprestimo as data 
             FROM emprestimos e LEFT JOIN livros l ON e.id_livro = l.id)
            UNION ALL
            /* 5. DEVOLUÇÕES */
            (SELECT COALESCE(l.titulo, 'Livro Devolvido') as item, 'devolucao' as tipo, e.data_devolucao_real as data 
             FROM emprestimos e LEFT JOIN livros l ON e.id_livro = l.id 
             WHERE e.status = 'devolvido' AND e.data_devolucao_real IS NOT NULL)
            UNION ALL
            /* 6. AUTORES/EDITORAS */
            (SELECT nome as item, 'autor' as tipo, NOW() as data FROM autores)
            UNION ALL
            (SELECT nome as item, 'editora' as tipo, NOW() as data FROM editoras)
        ) AS todas
        ORDER BY data DESC LIMIT 20";

    $result = $conn->query($sql);
    $atividades = [];
    while($row = $result->fetch_assoc()) { $atividades[] = $row; }
    echo json_encode($atividades);
} catch (Exception $e) {
    echo json_encode(["erro" => $e->getMessage()]);
}
