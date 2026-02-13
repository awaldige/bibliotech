<?php
header("Content-Type: application/json; charset=utf-8");
require 'config.php';

// IMPORTANTE: Como o JS envia JSON, precisamos ler o corpo da requisição
$json = file_get_contents('php://input');
$dados = json_decode($json, true);

// Pegamos os valores de dentro do array decodificado
$id = $dados['id'] ?? null;
$nome = $dados['nome'] ?? null;
$localizacao = $dados['localizacao'] ?? null;

// Verificação de segurança
if (!$id || !$nome) {
    echo json_encode(["sucesso" => false, "erro" => "Dados incompletos no servidor"]);
    exit;
}

try {
    // Preparamos a atualização (incluindo a localização/cidade)
    $stmt = $conn->prepare("UPDATE editoras SET nome = ?, localizacao = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nome, $localizacao, $id);

    if ($stmt->execute()) {
        echo json_encode(["sucesso" => true]);
    } else {
        echo json_encode(["sucesso" => false, "erro" => $conn->error]);
    }
} catch (Exception $e) {
    echo json_encode(["sucesso" => false, "erro" => $e->getMessage()]);
}

$conn->close();