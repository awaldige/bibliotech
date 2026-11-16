
<?php
header("Content-Type: application/json; charset=utf-8");

require 'config.php';

$sql = "
    SELECT
        (SELECT COUNT(*) FROM livros) AS livros,

        (SELECT COALESCE(SUM(quantidade), 0)
         FROM livros) AS exemplares,

        (SELECT COUNT(*)
         FROM usuarios) AS leitores,

        (SELECT COUNT(*)
         FROM emprestimos
         WHERE status IN ('ativo', 'atrasado')) AS emprestimos,

        (SELECT COUNT(*)
         FROM emprestimos
         WHERE status = 'atrasado') AS atrasados,

        (SELECT COUNT(*)
         FROM reservas
         WHERE status = 'pendente') AS reservas
";

$resultado = $conn->query($sql);

if (!$resultado) {
    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "erro" => "Não foi possível consultar as estatísticas da biblioteca."
    ], JSON_UNESCAPED_UNICODE);

    $conn->close();
    exit;
}

$linha = $resultado->fetch_assoc();

$estatisticas = [
    "livros" => (int) $linha["livros"],
    "exemplares" => (int) $linha["exemplares"],
    "leitores" => (int) $linha["leitores"],
    "emprestimos" => (int) $linha["emprestimos"],
    "atrasados" => (int) $linha["atrasados"],
    "reservas" => (int) $linha["reservas"]
];

$estatisticas["disponiveis"] =
    $estatisticas["exemplares"]
    - $estatisticas["emprestimos"]
    - $estatisticas["reservas"];

$resultado->free();

echo json_encode($estatisticas, JSON_UNESCAPED_UNICODE);

$conn->close();
