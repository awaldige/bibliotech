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

// Recebe e normaliza o nome.
$nome = trim($_POST['nome'] ?? '');

// Valida o nome.
if ($nome === '') {
    responder(false, "O nome do autor é obrigatório.", 400);
}

if (mb_strlen($nome, 'UTF-8') > 255) {
    responder(false, "O nome deve ter no máximo 255 caracteres.", 400);
}

try {
    $stmt = $conn->prepare(
        "INSERT INTO autores (nome) VALUES (?)"
    );

    if (!$stmt) {
        throw new Exception("Falha ao preparar cadastro.");
    }

    $stmt->bind_param("s", $nome);

    if (!$stmt->execute()) {
        throw new Exception("Falha ao executar cadastro.");
    }

    $stmt->close();
    $conn->close();

    responder(true);

} catch (Throwable $e) {
    error_log("Erro ao cadastrar autor: " . $e->getMessage());

    $conn->close();

    responder(false, "Não foi possível cadastrar o autor.", 500);
}
?>