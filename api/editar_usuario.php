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

// Valida ID.
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id < 1) {
    responder(false, "ID do leitor inválido.", 400);
}

// Recebe os dados.
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
    $conn->begin_transaction();

    // Bloqueia o leitor durante a atualização.
    $stmt = $conn->prepare(
        "SELECT id FROM usuarios WHERE id = ? FOR UPDATE"
    );

    if (!$stmt) {
        throw new Exception("Falha ao preparar consulta.");
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $leitor = $resultado->fetch_assoc();

    $stmt->close();

    if (!$leitor) {
        $conn->rollback();
        responder(false, "Leitor não encontrado.", 404);
    }

    // Atualiza os dados.
    $stmt = $conn->prepare(
        "UPDATE usuarios
         SET nome = ?, email = ?, telefone = ?
         WHERE id = ?"
    );

    if (!$stmt) {
        throw new Exception("Falha ao preparar atualização.");
    }

    $stmt->bind_param("sssi", $nome, $email, $telefone, $id);

    if (!$stmt->execute()) {
        throw new Exception("Falha ao executar atualização.");
    }

    $stmt->close();

    $conn->commit();
    $conn->close();

    responder(true);

} catch (Throwable $e) {
    $conn->rollback();

    error_log("Erro ao editar leitor: " . $e->getMessage());

    $conn->close();

    responder(false, "Não foi possível atualizar o leitor.", 500);
}