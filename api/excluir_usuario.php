
<?php
header("Content-Type: application/json; charset=utf-8");

require 'config.php';

function responder($sucesso, $erro = null, $status = 200) {
    http_response_code($status);

    echo json_encode([
        "sucesso" => $sucesso,
        "erro" => $erro
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// Aceita somente POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Allow: POST");
    responder(false, "Método não permitido.", 405);
}

// Valida o ID.
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id <= 0) {
    responder(false, "ID do leitor inválido.", 400);
}

try {
    $conn->begin_transaction();

    // Bloqueia o leitor durante a operação.
    $stmtLeitor = $conn->prepare(
        "SELECT id FROM usuarios WHERE id = ? FOR UPDATE"
    );

    if (!$stmtLeitor) {
        throw new Exception("Falha ao preparar consulta do leitor.");
    }

    $stmtLeitor->bind_param("i", $id);

    if (!$stmtLeitor->execute()) {
        throw new Exception("Falha ao consultar leitor.");
    }

    $resultado = $stmtLeitor->get_result();
    $leitor = $resultado->fetch_assoc();
    $stmtLeitor->close();

    if (!$leitor) {
        $conn->rollback();
        responder(false, "Leitor não encontrado.", 404);
    }

    // Verifica qualquer histórico de empréstimos ou reservas.
    $sqlHistorico = "
        SELECT
            (SELECT COUNT(*) FROM emprestimos WHERE id_usuario = ?) AS emprestimos,
            (SELECT COUNT(*) FROM reservas WHERE id_usuario = ?) AS reservas
    ";

    $stmtHistorico = $conn->prepare($sqlHistorico);

    if (!$stmtHistorico) {
        throw new Exception("Falha ao preparar consulta do histórico.");
    }

    $stmtHistorico->bind_param("ii", $id, $id);

    if (!$stmtHistorico->execute()) {
        throw new Exception("Falha ao consultar histórico.");
    }

    $resultadoHistorico = $stmtHistorico->get_result();
    $historico = $resultadoHistorico->fetch_assoc();
    $stmtHistorico->close();

    $totalEmprestimos = (int) $historico['emprestimos'];
    $totalReservas = (int) $historico['reservas'];

    if ($totalEmprestimos > 0 || $totalReservas > 0) {
        $conn->rollback();

        responder(
            false,
            "Este leitor não pode ser excluído porque possui histórico de empréstimos ou reservas.",
            409
        );
    }

    // Exclui somente leitores sem histórico.
    $stmtExcluir = $conn->prepare(
        "DELETE FROM usuarios WHERE id = ?"
    );

    if (!$stmtExcluir) {
        throw new Exception("Falha ao preparar exclusão.");
    }

    $stmtExcluir->bind_param("i", $id);

    if (!$stmtExcluir->execute()) {
        throw new Exception("Falha ao excluir leitor.");
    }

    if ($stmtExcluir->affected_rows !== 1) {
        $stmtExcluir->close();
        throw new Exception("Não foi possível confirmar a exclusão.");
    }

    $stmtExcluir->close();

    $conn->commit();
    $conn->close();

    responder(true);

} catch (Throwable $e) {
    $conn->rollback();

    error_log("Erro ao excluir leitor: " . $e->getMessage());

    $conn->close();

    responder(false, "Não foi possível excluir o leitor.", 500);
}
