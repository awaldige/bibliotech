
<?php
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header("Allow: GET");

    echo json_encode([
        "sucesso" => false,
        "erro" => "Método não permitido."
    ]);
    exit;
}

require 'config.php';

try {
    $sql = "SELECT id, nome, nacionalidade FROM autores ORDER BY nome ASC";
    $result = $conn->query($sql);

    if (!$result) {
        throw new RuntimeException("Falha ao consultar autores.");
    }

    $autores = [];

    while ($row = $result->fetch_assoc()) {
        $autores[] = $row;
    }

    echo json_encode(
        $autores,
        JSON_UNESCAPED_UNICODE
    );

    $result->free();
    $conn->close();

} catch (Throwable $e) {
    error_log("Erro ao listar autores: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "erro" => "Não foi possível listar os autores."
    ], JSON_UNESCAPED_UNICODE);

    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
