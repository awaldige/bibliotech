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

// Aceita JSON e FormData.
$tipoConteudo = $_SERVER['CONTENT_TYPE'] ?? '';

if (stripos($tipoConteudo, 'application/json') !== false) {
    $json = file_get_contents('php://input');
    $dados = json_decode($json, true);

    if (!is_array($dados)) {
        responder(false, "Dados JSON inválidos.", 400);
    }
} else {
    $dados = $_POST;
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

// Salva localização vazia como NULL.
$localizacao = ($localizacao === '') ? null : $localizacao;

try {
    $stmt = $conn->prepare(
        "INSERT INTO editoras (nome, localizacao) VALUES (?, ?)"
    );

    if (!$stmt) {
        throw new Exception("Falha ao preparar cadastro.");
    }

    $stmt->bind_param("ss", $nome, $localizacao);

    if (!$stmt->execute()) {
        throw new Exception("Falha ao executar cadastro.");
    }

    $stmt->close();
    $conn->close();

    responder(true);

} catch (Throwable $e) {
    error_log("Erro ao cadastrar editora: " . $e->getMessage());

    $conn->close();

    responder(false, "Não foi possível cadastrar a editora.", 500);
}
?>