
<?php
header("Content-Type: application/json; charset=utf-8");

require __DIR__ . '/config.php';

date_default_timezone_set('America/Sao_Paulo');

function responder($sucesso, $mensagem = '', $statusHttp = 200) {
    http_response_code($statusHttp);

    echo json_encode([
        "sucesso" => $sucesso,
        "erro" => $sucesso ? null : $mensagem
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// Aceita somente POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, "Método não permitido.", 405);
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    responder(false, "ID do empréstimo inválido.", 400);
}

try {
    $conn->begin_transaction();

    // Bloqueia o empréstimo durante a devolução.
    $stmt = $conn->prepare(
        "SELECT id, status
         FROM emprestimos
         WHERE id = ?
         FOR UPDATE"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $emprestimo = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$emprestimo) {
        throw new Exception("Empréstimo não encontrado.");
    }

    if (!in_array($emprestimo['status'], ['ativo', 'atrasado'], true)) {
        throw new Exception("Este empréstimo já foi devolvido ou não está ativo.");
    }

    // Registra a devolução sem alterar o total de exemplares.
    $stmt = $conn->prepare(
        "UPDATE emprestimos
         SET status = 'devolvido',
             data_devolucao_real = NOW()
         WHERE id = ?"
    );

    $stmt->bind_param("i", $id);

    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
        throw new Exception("Não foi possível registrar a devolução.");
    }

    $stmt->close();

    $conn->commit();

    responder(true);

} catch (Exception $e) {

    $conn->rollback();

    responder(false, $e->getMessage(), 400);
}
