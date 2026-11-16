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

// Recebe e normaliza os dados.
$nome = trim($_POST['nome'] ?? '');
$nacionalidade = trim($_POST['nacionalidade'] ?? '');

// Valida o nome.
if ($nome === '') {
    responder(false, "O nome do autor é obrigatório.", 400);
}

if (mb_strlen($nome, 'UTF-8') > 255) {
    responder(false, "O nome deve ter no máximo 255 caracteres.", 400);
}

// Valida a nacionalidade opcional.
if (mb_strlen($nacionalidade, 'UTF-8') > 100) {
    responder(false, "A nacionalidade deve ter no máximo 100 caracteres.", 400);
}

// Permite salvar nacionalidade vazia como NULL.
$nacionalidade = ($nacionalidade === '') ? null : $nacionalidade;

$transacaoIniciada = false;

try {
    $conn->begin_transaction();
    $transacaoIniciada = true;

    // Verifica a existência e bloqueia o autor durante a edição.
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

    // Atualiza os dados sem alterar os livros vinculados.
    $stmt = $conn->prepare(
        "UPDATE autores SET nome = ?, nacionalidade = ? WHERE id = ?"
    );

    $stmt->bind_param("ssi", $nome, $nacionalidade, $id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    $transacaoIniciada = false;
    $conn->close();

    responder(true);

} catch (Throwable $e) {
    if ($transacaoIniciada) {
        $conn->rollback();
    }

    error_log("Erro ao editar autor: " . $e->getMessage());

    $conn->close();

    responder(false, "Não foi possível atualizar o autor.", 500);
}
?>