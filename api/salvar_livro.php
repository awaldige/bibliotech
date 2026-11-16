
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

// Recebe e normaliza os campos.
$titulo = trim($_POST['titulo'] ?? '');
$isbn = trim($_POST['isbn'] ?? '');
$id_autor = filter_var($_POST['id_autor'] ?? null, FILTER_VALIDATE_INT);
$id_editora = filter_var($_POST['id_editora'] ?? null, FILTER_VALIDATE_INT);
$ano = trim($_POST['ano_publicacao'] ?? '');
$qtd = filter_var($_POST['quantidade'] ?? null, FILTER_VALIDATE_INT);

// Valida título.
if ($titulo === '') {
    responder(false, "Informe o título do livro.", 400);
}

if (mb_strlen($titulo, 'UTF-8') > 255) {
    responder(false, "O título deve ter no máximo 255 caracteres.", 400);
}

// Valida ISBN.
if (mb_strlen($isbn, 'UTF-8') > 20) {
    responder(false, "O ISBN deve ter no máximo 20 caracteres.", 400);
}

// Valida autor e editora.
if ($id_autor === false || $id_autor === null || $id_autor <= 0) {
    responder(false, "Selecione um autor válido.", 400);
}

if ($id_editora === false || $id_editora === null || $id_editora <= 0) {
    responder(false, "Selecione uma editora válida.", 400);
}

// Valida ano de publicação.
if ($ano === '') {
    $ano = null;
} else {
    $ano = filter_var($ano, FILTER_VALIDATE_INT);

    if ($ano === false || $ano < 1500 || $ano > (int) date('Y')) {
        responder(false, "Informe um ano de publicação válido.", 400);
    }
}

// Valida quantidade.
if ($qtd === false || $qtd === null || $qtd < 1) {
    responder(false, "A quantidade deve ser um número inteiro maior que zero.", 400);
}

// Confirma a existência do autor e da editora.
$sqlAuxiliar = "SELECT
                    EXISTS(SELECT 1 FROM autores WHERE id = ?) AS autor_existe,
                    EXISTS(SELECT 1 FROM editoras WHERE id = ?) AS editora_existe";

$stmtAuxiliar = $conn->prepare($sqlAuxiliar);

if (!$stmtAuxiliar) {
    responder(false, "Não foi possível validar os dados do cadastro.", 500);
}

$stmtAuxiliar->bind_param("ii", $id_autor, $id_editora);

if (!$stmtAuxiliar->execute()) {
    $stmtAuxiliar->close();
    responder(false, "Não foi possível validar os dados do cadastro.", 500);
}

$resultadoAuxiliar = $stmtAuxiliar->get_result();
$auxiliares = $resultadoAuxiliar->fetch_assoc();
$stmtAuxiliar->close();

if (!$auxiliares['autor_existe']) {
    responder(false, "O autor selecionado não foi encontrado.", 400);
}

if (!$auxiliares['editora_existe']) {
    responder(false, "A editora selecionada não foi encontrada.", 400);
}

// Insere o livro.
$sql = "INSERT INTO livros
        (titulo, isbn, id_autor, id_editora, ano_publicacao, quantidade)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    responder(false, "Não foi possível preparar o cadastro do livro.", 500);
}

// Campos opcionais vazios são gravados como NULL.
$isbnBanco = ($isbn === '') ? null : $isbn;

$stmt->bind_param(
    "ssiisi",
    $titulo,
    $isbnBanco,
    $id_autor,
    $id_editora,
    $ano,
    $qtd
);

if (!$stmt->execute()) {
    $stmt->close();
    responder(false, "Não foi possível cadastrar o livro.", 500);
}

$stmt->close();
$conn->close();

responder(true);
