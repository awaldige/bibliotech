<?php
header("Content-Type: application/json; charset=utf-8");

require __DIR__ . '/config.php';

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

// Valida o identificador.
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    responder(false, "ID da reserva inválido.", 400);
}

try {
    $conn->begin_transaction();

    // Bloqueia a reserva durante o cancelamento.
    $stmt = $conn->prepare(
        "SELECT id, status
         FROM reservas
         WHERE id = ?
         FOR UPDATE"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $reserva = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$reserva) {
        throw new Exception("Reserva não encontrada.");
    }

    if ($reserva['status'] !== 'pendente') {
        throw new Exception("Somente reservas pendentes podem ser canceladas.");
    }

    // Preserva o registro e altera apenas o status.
    $stmt = $conn->prepare(
        "UPDATE reservas
         SET status = 'cancelada'
         WHERE id = ? AND status = 'pendente'"
    );

    $stmt->bind_param("i", $id);

    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
        throw new Exception("Não foi possível cancelar a reserva.");
    }

    $stmt->close();

    $conn->commit();

    responder(true);

} catch (Exception $e) {

    $conn->rollback();

    responder(false, $e->getMessage(), 400);
}
