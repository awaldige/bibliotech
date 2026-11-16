
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

// Recebe e normaliza os dados.
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');

// Valida nome.
if ($nome === '') {
    responder(false, "Nome é obrigatório.", 400);
}

if (mb_strlen($nome, 'UTF-8') > 255) {
    responder(false, "O nome deve ter no máximo 255 caracteres.", 400);
}

// Valida e-mail.
if ($email === '') {
    responder(false, "E-mail é obrigatório.", 400);
}

if (mb_strlen($email, 'UTF-8') > 100) {
    responder(false, "O e-mail deve ter no máximo 100 caracteres.", 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responder(false, "Informe um endereço de e-mail válido.", 400);
}

// Valida telefone opcional.
if (mb_strlen($telefone, 'UTF-8') > 20) {
    responder(false, "O telefone deve ter no máximo 20 caracteres.", 400);
}

try {
    $stmt = $conn->prepare(
        "INSERT INTO usuarios (nome, email, telefone) VALUES (?, ?, ?)"
    );

    if (!$stmt) {
        throw new Exception("Falha ao preparar cadastro.");
    }

    $stmt->bind_param("sss", $nome, $email, $telefone);

    if (!$stmt->execute()) {
        throw new Exception("Falha ao executar cadastro.");
    }

    $stmt->close();
    $conn->close();

    responder(true);

} catch (Throwable $e) {
    error_log("Erro ao cadastrar leitor: " . $e->getMessage());

    $conn->close();

    responder(false, "Não foi possível cadastrar o leitor.", 500);
}
