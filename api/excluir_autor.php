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
$id = filter_var(
    $_POST['id'] ?? null,
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

if ($id === false || $id === null) {
    responder(false, "ID do autor inválido.", 400);
}

$transacaoIniciada = false;

try {
    $conn->begin_transaction();
    $transacaoIniciada = true;

    // Bloqueia o autor durante a verificação.
    $stmt = $conn->prepare(
        "SELECT id FROM autores WHERE id = ? FOR UPDATE"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $autor = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$autor) {
        $conn->rollback();
        $transacaoIniciada = false;

        responder(false, "Autor não encontrado.", 404);
    }

    // Preserva todos os livros vinculados ao autor.
    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS total FROM livros WHERE id_autor = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $resultado = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ((int) $resultado['total'] > 0) {
        $conn->rollback();
        $transacaoIniciada = false;

        responder(
            false,
            "Este autor não pode ser excluído porque possui livros vinculados.",
            409
        );
    }

    // Exclui somente autores sem livros vinculados.
    $stmt = $conn->prepare(
        "DELETE FROM autores WHERE id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows !== 1) {
        throw new Exception("Falha ao excluir autor.");
    }

    $stmt->close();

    $conn->commit();
    $transacaoIniciada = false;
    $conn->close();

    responder(true);

} catch (Throwable $e) {
    if ($transacaoIniciada) {
        $conn->rollback();
    }

    error_log("Erro ao excluir autor: " . $e->getMessage());

    $conn->close();

    responder(false, "Não foi possível excluir o autor.", 500);
}
?>