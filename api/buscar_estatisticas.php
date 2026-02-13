<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';

try {
    // 1. Contagem de Livros
    $resLivros = $conn->query("SELECT COUNT(*) as total FROM livros");
    $totalLivros = ($resLivros) ? $resLivros->fetch_assoc()['total'] : 0;

    // 2. Contagem de Leitores
    $resLeitores = $conn->query("SELECT COUNT(*) as total FROM usuarios");
    $totalLeitores = ($resLeitores) ? $resLeitores->fetch_assoc()['total'] : 0;
    
    // 3. Contagem de Empréstimos Ativos (Não devolvidos)
    $resEmprestimos = $conn->query("SELECT COUNT(*) as total FROM emprestimos WHERE status != 'devolvido'");
    $totalEmprestimos = ($resEmprestimos) ? $resEmprestimos->fetch_assoc()['total'] : 0;

    // 4. Contagem de Reservas Pendentes (Consulta Real)
    // Tentamos contar da tabela reservas. Se a tabela não existir, retorna 0.
    $totalReservas = 0;
    try {
        $resReservas = $conn->query("SELECT COUNT(*) as total FROM reservas WHERE status = 'pendente'");
        if ($resReservas) {
            $totalReservas = $resReservas->fetch_assoc()['total'];
        }
    } catch (Exception $e_reserva) {
        $totalReservas = 0; // Tabela pode não existir ainda
    }
    
    echo json_encode([
        "livros" => (int)$totalLivros,
        "leitores" => (int)$totalLeitores,
        "emprestimos" => (int)$totalEmprestimos,
        "reservas" => (int)$totalReservas
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erro" => $e->getMessage()]);
}

$conn->close();