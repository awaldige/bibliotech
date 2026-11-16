<?php

$arquivoLocal = __DIR__ . '/config.local.php';

if (file_exists($arquivoLocal)) {
    require $arquivoLocal;
} else {
    $host = "127.0.0.1";
    $user = "root";
    $pass = "";
    $db   = "biblioteca";
    $port = 3308;

    $conn = new mysqli($host, $user, $pass, $db, $port);

    if ($conn->connect_error) {
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode([
            "sucesso" => false,
            "erro" => "Falha na conexão: " . $conn->connect_error
        ]));
    }

    $conn->set_charset("utf8mb4");
}

?>
