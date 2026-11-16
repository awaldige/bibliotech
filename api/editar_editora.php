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

// Recebe JSON.
$json = file_get_contents('php://input');
$dados = json_decode($json, true);

if (!is_array($dados)) {
    responder(false, "Dados JSON inválidos.", 400);
}

// Valida o ID.
$id = filter_var(
    $dados['id'] ?? null,
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

if ($id === false || $id === null) {
    responder(false, "ID da editora inválido.", 400);
}

// Recebe e normaliza os dados.
$nome = trim($dados['nome'] ?? '');
$localizacao = trim($dados['localizacao'] ?? '');

// Valida o nome.
if ($nome === '') {
    responder(false, "O nome da editora é obrigatório.", 400);
}

if (mb_strlen($nome, 'UTF-8') > 255) {
    responder(false, "O nome deve ter no máximo 255 caracteres.", 400);
}

// Valida a localização opcional.
if (mb_strlen($localizacao, 'UTF-8') > 255) {
    responder(false, "A localização deve ter no máximo 255 caracteres.", 400);
}

// Permite salvar localização vazia como NULL.
$localizacao = ($localizacao === '') ? null : $localizacao;

$transacaoIniciada = false;

try {
    $conn->begin_transaction();
    $transacaoIniciada = true;

    // Verifica a existência e bloqueia a editora durante a edição.
    $stmt = $conn->prepare(
        "SELECT id FROM editoras WHERE id = ? FOR UPDATE"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $editora = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$editora) {
        $conn->rollback();
        $transacaoIniciada = false;

        responder(false, "Editora não encontrada.", 404);
    }

    // Atualiza os dados sem alterar os livros vinculados.
    $stmt = $conn->prepare(
        "UPDATE editoras SET nome = ?, localizacao = ? WHERE id = ?"
    );

    $stmt->bind_param("ssi", $nome, $localizacao, $id);
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

    error_log("Erro ao editar editora: " . $e->getMessage());

    $conn->close();

    responder(false, "Não foi possível atualizar a editora.", 500);
}
?>