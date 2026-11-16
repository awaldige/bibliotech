
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

// Validação dos campos.
$idLivro = filter_input(INPUT_POST, 'id_livro', FILTER_VALIDATE_INT);
$idLeitor = filter_input(INPUT_POST, 'id_leitor', FILTER_VALIDATE_INT);
$dataDev = $_POST['data_devolucao'] ?? '';

if (!$idLivro || $idLivro <= 0 || !$idLeitor || $idLeitor <= 0) {
    responder(false, "Selecione um livro e um leitor válidos.", 400);
}

// Validação da data no formato YYYY-MM-DD.
$dataObjeto = DateTime::createFromFormat('!Y-m-d', $dataDev);

if (
    !$dataObjeto ||
    $dataObjeto->format('Y-m-d') !== $dataDev ||
    $dataObjeto < new DateTime('today')
) {
    responder(false, "Informe uma data de devolução válida, igual ou posterior a hoje.", 400);
}

try {
    $conn->begin_transaction();

    // Bloqueia o registro do livro durante a verificação.
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

    // Confirma a existência do leitor.
    $stmt = $conn->prepare(
        "SELECT id FROM usuarios WHERE id = ?"
    );

    $stmt->bind_param("i", $idLeitor);
    $stmt->execute();

    $leitor = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$leitor) {
        throw new Exception("Leitor não encontrado.");
    }

    // Conta empréstimos que continuam ocupando exemplares.
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

    // Conta reservas que bloqueiam exemplares.
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

    $quantidadeTotal = (int) $livro['quantidade'];
    $ocupados = (int) $emprestimos['total'];
    $reservados = (int) $reservas['total'];

    $disponiveis = $quantidadeTotal - $ocupados - $reservados;

    if ($disponiveis <= 0) {
        throw new Exception("Não há exemplares disponíveis para empréstimo.");
    }

    // Registra o empréstimo sem alterar a quantidade total do livro.
    $stmt = $conn->prepare(
        "INSERT INTO emprestimos
         (id_livro, id_usuario, data_emprestimo,
          data_devolucao_prevista, status)
         VALUES (?, ?, NOW(), ?, 'ativo')"
    );

    $stmt->bind_param("iis", $idLivro, $idLeitor, $dataDev);

    if (!$stmt->execute()) {
        throw new Exception("Não foi possível registrar o empréstimo.");
    }

    $stmt->close();

    $conn->commit();

    responder(true);

} catch (Exception $e) {

    $conn->rollback();

    responder(false, $e->getMessage(), 400);
}
