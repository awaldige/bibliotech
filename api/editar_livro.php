
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

// Recebe e valida os campos.
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$titulo = trim($_POST['titulo'] ?? '');
$quantidade = filter_var($_POST['quantidade'] ?? null, FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id <= 0) {
    responder(false, "ID do livro inválido.", 400);
}

if ($titulo === '') {
    responder(false, "Informe o título do livro.", 400);
}

if (mb_strlen($titulo, 'UTF-8') > 255) {
    responder(false, "O título deve ter no máximo 255 caracteres.", 400);
}

if ($quantidade === false || $quantidade === null || $quantidade < 1) {
    responder(false, "A quantidade deve ser um número inteiro maior que zero.", 400);
}

try {
    $conn->begin_transaction();

    // Bloqueia o livro durante a atualização.
    $stmtLivro = $conn->prepare(
        "SELECT id FROM livros WHERE id = ? FOR UPDATE"
    );

    if (!$stmtLivro) {
        throw new Exception("Falha ao preparar consulta.");
    }

    $stmtLivro->bind_param("i", $id);

    if (!$stmtLivro->execute()) {
        throw new Exception("Falha ao consultar livro.");
    }

    $resultadoLivro = $stmtLivro->get_result();
    $livro = $resultadoLivro->fetch_assoc();
    $stmtLivro->close();

    if (!$livro) {
        $conn->rollback();
        responder(false, "Livro não encontrado.", 404);
    }

    // Calcula quantos exemplares estão ocupados.
    $sqlOcupacao = "
        SELECT
            (
                SELECT COUNT(*)
                FROM emprestimos
                WHERE id_livro = ?
                  AND status IN ('ativo', 'atrasado')
            ) +
            (
                SELECT COUNT(*)
                FROM reservas
                WHERE id_livro = ?
                  AND status = 'pendente'
            ) AS ocupados
    ";

    $stmtOcupacao = $conn->prepare($sqlOcupacao);

    if (!$stmtOcupacao) {
        throw new Exception("Falha ao preparar consulta de disponibilidade.");
    }

    $stmtOcupacao->bind_param("ii", $id, $id);

    if (!$stmtOcupacao->execute()) {
        throw new Exception("Falha ao consultar disponibilidade.");
    }

    $resultadoOcupacao = $stmtOcupacao->get_result();
    $dadosOcupacao = $resultadoOcupacao->fetch_assoc();
    $stmtOcupacao->close();

    $ocupados = (int) $dadosOcupacao['ocupados'];

    if ($quantidade < $ocupados) {
        $conn->rollback();

        responder(
            false,
            "A quantidade não pode ser menor que os exemplares emprestados ou reservados. Total ocupado: " . $ocupados . ".",
            409
        );
    }

    // Atualiza somente título e quantidade.
    $stmtAtualizar = $conn->prepare(
        "UPDATE livros SET titulo = ?, quantidade = ? WHERE id = ?"
    );

    if (!$stmtAtualizar) {
        throw new Exception("Falha ao preparar atualização.");
    }

    $stmtAtualizar->bind_param("sii", $titulo, $quantidade, $id);

    if (!$stmtAtualizar->execute()) {
        throw new Exception("Falha ao atualizar livro.");
    }

    $stmtAtualizar->close();

    $conn->commit();

    $conn->close();

    responder(true);

} catch (Throwable $e) {
    $conn->rollback();
    error_log("Erro ao editar livro: " . $e->getMessage());

    $conn->close();

    responder(false, "Não foi possível atualizar o livro.", 500);
}
