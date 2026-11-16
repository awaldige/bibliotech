<?php
header("Content-Type: application/json; charset=utf-8");

require 'config.php';

function responder($dados, $status = 200) {
    http_response_code($status);

    echo json_encode($dados, JSON_UNESCAPED_UNICODE);

    exit;
}

// Aceita somente GET.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header("Allow: GET");
    responder([
        "sucesso" => false,
        "erro" => "Método não permitido."
    ], 405);
}

try {
    $sql = "SELECT id, nome, localizacao FROM editoras ORDER BY nome ASC";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Falha ao consultar editoras.");
    }

    $editoras = [];

    while ($row = $result->fetch_assoc()) {
        $editoras[] = $row;
    }

    $result->free();
    $conn->close();

    responder($editoras);

} catch (Throwable $e) {
    error_log("Erro ao listar editoras: " . $e->getMessage());

    $conn->close();

    responder([
        "sucesso" => false,
        "erro" => "Não foi possível carregar as editoras."
    ], 500);
}
?>