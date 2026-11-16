<?php
header("Content-Type: application/json; charset=utf-8");

require 'config.php';

function responder($sucesso, $erro = null, $dados = [])
{
    echo json_encode(
        array_merge([
            "sucesso" => $sucesso,
            "erro" => $erro
        ], $dados),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

// Aceita somente POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    responder(false, "Método não permitido.");
}

// Valida o ID
$id = $_POST['id'] ?? '';

if (!ctype_digit((string) $id) || (int) $id <= 0) {
    http_response_code(400);
    responder(false, "ID de empréstimo inválido.");
}

$id = (int) $id;

try {
    $conn->begin_transaction();

    // Localiza e bloqueia o empréstimo durante a operação
    $sql = "SELECT id, status
            FROM emprestimos
            WHERE id = ?
            FOR UPDATE";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $emprestimo = $resultado->fetch_assoc();
    $stmt->close();

    if (!$emprestimo) {
        $conn->rollback();
        http_response_code(404);
        responder(false, "Empréstimo não encontrado.");
    }

    // Somente empréstimos em aberto podem ser renovados
    if (!in_array($emprestimo['status'], ['ativo', 'atrasado'], true)) {
        $conn->rollback();
        http_response_code(409);
        responder(false, "Este empréstimo não pode ser renovado.");
    }

    // Mantém a regra atual: mais 7 dias a partir de hoje
    $novaData = date('Y-m-d', strtotime('+7 days'));

    $sql = "UPDATE emprestimos
            SET data_devolucao_prevista = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $novaData, $id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    responder(true, null, [
        "nova_data" => date('d/m/Y', strtotime($novaData))
    ]);

} catch (Throwable $e) {
    $conn->rollback();

    http_response_code(500);
    responder(false, "Erro ao renovar o empréstimo.");
}

$conn->close();