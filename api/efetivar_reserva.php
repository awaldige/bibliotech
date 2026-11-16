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

// Valida os identificadores.
$idReserva = filter_input(INPUT_POST, 'id_reserva', FILTER_VALIDATE_INT);
$idLivro = filter_input(INPUT_POST, 'id_livro', FILTER_VALIDATE_INT);
$idUsuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);

if (!$idReserva || $idReserva <= 0 ||
    !$idLivro || $idLivro <= 0 ||
    !$idUsuario || $idUsuario <= 0) {
    responder(false, "Dados incompletos ou identificadores inválidos.", 400);
}

try {
    $conn->begin_transaction();

    // Bloqueia a reserva para impedir efetivação duplicada.
    $stmt = $conn->prepare(
        "SELECT id, id_livro, id_usuario, status
         FROM reservas
         WHERE id = ?
         FOR UPDATE"
    );

    $stmt->bind_param("i", $idReserva);
    $stmt->execute();

    $reserva = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$reserva) {
        throw new Exception("Reserva não encontrada.");
    }

    if ($reserva['status'] !== 'pendente') {
        throw new Exception("Somente reservas pendentes podem ser efetivadas.");
    }

    // Confere se o livro e o leitor correspondem à reserva.
    if ((int) $reserva['id_livro'] !== $idLivro ||
        (int) $reserva['id_usuario'] !== $idUsuario) {
        throw new Exception("Os dados enviados não correspondem à reserva.");
    }

    // Define a devolução para daqui a 7 dias.
    $dataPrevista = date('Y-m-d', strtotime('+7 days'));

    // Cria o empréstimo sem alterar a quantidade do livro.
    $stmt = $conn->prepare(
        "INSERT INTO emprestimos
         (id_livro, id_usuario, data_devolucao_prevista, status)
         VALUES (?, ?, ?, 'ativo')"
    );

    $stmt->bind_param("iis", $idLivro, $idUsuario, $dataPrevista);

    if (!$stmt->execute()) {
        throw new Exception("Não foi possível registrar o empréstimo.");
    }

    $stmt->close();

    // Converte a reserva em concluída.
    // O exemplar continua ocupado pelo empréstimo.
    $stmt = $conn->prepare(
        "UPDATE reservas
         SET status = 'concluida'
         WHERE id = ? AND status = 'pendente'"
    );

    $stmt->bind_param("i", $idReserva);

    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
        throw new Exception("Não foi possível concluir a reserva.");
    }

    $stmt->close();

    $conn->commit();

    responder(true);

} catch (Throwable $e) {

    $conn->rollback();

    responder(false, $e->getMessage(), 400);
}
