<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // CORS preflight request
    http_response_code(200);
    exit;
}

try {
    if (!isset($_GET['cep'])) {
        http_response_code(400);
        echo json_encode(["error" => "CEP não informado."]);
        exit;
    }

    $cep = preg_replace('/[^0-9]/', '', $_GET['cep']);

    if (!preg_match('/^[0-9]{8}$/', $cep)) {
        http_response_code(400);
        echo json_encode(["error" => "CEP inválido. Use apenas 8 números."]);
        exit;
    }

    $url = "https://viacep.com.br/ws/$cep/json/";
    $response = file_get_contents($url);
    error_log("Resposta ViaCEP: " . $response);

    if ($response === FALSE) {
        http_response_code(500);
        echo json_encode(["error" => "Erro ao acessar o serviço ViaCEP."]);
        exit;
    }

    $data = json_decode($response, true);

    if (isset($data['erro']) && $data['erro'] === true) {
        http_response_code(404);
        echo json_encode(["error" => "CEP não encontrado."]);
    } else {
        http_response_code(200);
        echo json_encode($data);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erro interno: " . $e->getMessage()]);
}
