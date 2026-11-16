
<?php
header("Content-Type: application/json; charset=utf-8");

require __DIR__ . '/config.php';

date_default_timezone_set('America/Sao_Paulo');

function responder($sucesso, $mensagem = '', $statusHttp = 200) {
    http_response_code($statusHttp);

    echo json_encode([
        "sucesso" => $sucesso,
        "erro" => $sucesso ? null : $mensagem
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// Aceita somente POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, "Método não permitido.", 405);
}

// Valida os identificadores.
$idLivro = filter_input(INPUT_POST, 'id_livro', FILTER_VALIDATE_INT);
$idUsuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);

if (!$idLivro || $idLivro <= 0 || !$idUsuario || $idUsuario <= 0) {
    responder(false, "Selecione um livro e um leitor válidos.", 400);
}

try {
    $conn->begin_transaction();

    // Bloqueia o livro durante a verificação da disponibilidade.
    $stmt = $conn->prepare(
        "SELECT id, COALESCE(quantidade, 0) AS quantidade
         FROM livros
         WHERE id = ?
         FOR UPDATE"
    );

    $stmt->bind_param("i", $idLivro);
    $stmt->execute();

    $livro = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$livro) {
        throw new Exception("Livro não encontrado.");
    }

    // Confirma a existência do usuário.
    $stmt = $conn->prepare(
        "SELECT id FROM usuarios WHERE id = ?"
    );

    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();

    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$usuario) {
        throw new Exception("Leitor não encontrado.");
    }

    // Conta empréstimos que ocupam exemplares.
    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS total
         FROM emprestimos
         WHERE id_livro = ?
         AND status IN ('ativo', 'atrasado')"
    );

    $stmt->bind_param("i", $idLivro);
    $stmt->execute();

    $emprestimos = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Conta reservas pendentes.
    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS total
         FROM reservas
         WHERE id_livro = ?
         AND status = 'pendente'"
    );

    $stmt->bind_param("i", $idLivro);
    $stmt->execute();

    $reservas = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $total = (int) $livro['quantidade'];
    $ocupados = (int) $emprestimos['total'];
    $reservados = (int) $reservas['total'];

    $disponiveis = $total - $ocupados - $reservados;

    if ($disponiveis <= 0) {
        throw new Exception("Não há exemplares disponíveis para reserva.");
    }

    // Cria a reserva sem alterar a quantidade total.
    $stmt = $conn->prepare(
        "INSERT INTO reservas (id_livro, id_usuario, status)
         VALUES (?, ?, 'pendente')"
    );

    $stmt->bind_param("ii", $idLivro, $idUsuario);

    if (!$stmt->execute()) {
        throw new Exception("Não foi possível registrar a reserva.");
    }

    $stmt->close();

    $conn->commit();

    responder(true);

} catch (Exception $e) {

    $conn->rollback();

    responder(false, $e->getMessage(), 400);
}
