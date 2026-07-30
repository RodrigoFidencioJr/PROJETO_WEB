<?php
header('Content-Type: application/json; charset=utf8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/Database.php';

$db = (new Database())->connect();
$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':    listar($db); break;
    case 'POST':   criar($db); break;
    case 'PUT':    atualizar($db); break;
    case 'DELETE': excluir($db); break;
    default:
        http_response_code(405);
        echo json_encode(['erro' => 'Método não permitido']);
}

function listar($db) {
    if (isset($_GET['id'])) {
        $stmt = $db->prepare("SELECT * FROM continentes WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $dado = $stmt->fetch();
        if (!$dado) {
            http_response_code(404);
            echo json_encode(['erro' => 'Continente não encontrado']);
            return;
        }
        echo json_encode($dado);
        return;
    }
    $stmt = $db->query("SELECT * FROM continentes ORDER BY nome");
    echo json_encode($stmt->fetchAll());
}

function criar($db) {
    $dados = json_decode(file_get_contents('php://input'), true);

    if (empty($dados['nome'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'Nome é obrigatório']);
        return;
    }

    $stmt = $db->prepare("INSERT INTO continentes (nome, populacao, area_km2) VALUES (?, ?, ?)");
    $stmt->execute([
        $dados['nome'],
        $dados['populacao'] ?? 0,
        $dados['area_km2'] ?? 0,
    ]);

    http_response_code(201);
    echo json_encode(['id' => $db->lastInsertId()]);
}

function atualizar($db) {
    $dados = json_decode(file_get_contents('php://input'), true);

    if (empty($dados['id']) || empty($dados['nome'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'id e nome são obrigatórios']);
        return;
    }

    $stmt = $db->prepare("UPDATE continentes SET nome = ?, populacao = ?, area_km2 = ? WHERE id = ?");
    $stmt->execute([
        $dados['nome'],
        $dados['populacao'] ?? 0,
        $dados['area_km2'] ?? 0,
        $dados['id'],
    ]);

    echo json_encode(['sucesso' => true]);
}

function excluir($db) {
    if (empty($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'id é obrigatório']);
        return;
    }

    try {
        $stmt = $db->prepare("DELETE FROM continentes WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(['sucesso' => true]);
    } catch (PDOException $e) {
        http_response_code(409);
        echo json_encode(['erro' => 'Não é possível excluir: existem países vinculados a este continente']);
    }
}