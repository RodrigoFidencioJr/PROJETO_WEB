<?php
require_once __DIR__ . '/Auth.php';

header('Content-Type: application/json; charset=utf8');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/Database.php';
$db = (new Database())->connect();

exigirAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

$stmt = $db->query("SELECT id_log, data_acesso, descricao, username FROM logs ORDER BY data_acesso DESC LIMIT 200");
echo json_encode($stmt->fetchAll());
