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
        $stmt = $db->prepare("SELECT * FROM governantes WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $dado = $stmt->fetch();
        if (!$dado) {
            http_response_code(404);
            echo json_encode(['erro' => 'Governante não encontrado']);
            return;
        }
        echo json_encode($dado);
        return;
    }
    $stmt = $db->query("SELECT * FROM governantes ORDER BY nome");
    echo json_encode($stmt->fetchAll());
}

function criar($db) {
    $dados = json_decode(file_get_contents('php://input'), true);

    if (empty($dados['nome']) || empty($dados['data_nascimento']) || empty($dados['data_inicio_mandato'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'nome, data_nascimento e data_inicio_mandato são obrigatórios']);
        return;
    }

    $stmt = $db->prepare("INSERT INTO governantes (nome, partido_politico, data_nascimento, data_inicio_mandato, data_fim_mandato)
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $dados['nome'],
        $dados['partido_politico'] ?? null,
        $dados['data_nascimento'],
        $dados['data_inicio_mandato'],
        $dados['data_fim_mandato'] ?? null,
    ]);

    http_response_code(201);
    echo json_encode(['id' => $db->lastInsertId()]);
}

function atualizar($db) {
    $dados = json_decode(file_get_contents('php://input'), true);

    if (empty($dados['id']) || empty($dados['nome']) || empty($dados['data_nascimento']) || empty($dados['data_inicio_mandato'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'id, nome, data_nascimento e data_inicio_mandato são obrigatórios']);
        return;
    }

    $stmt = $db->prepare("UPDATE governantes
                           SET nome = ?, partido_politico = ?, data_nascimento = ?, data_inicio_mandato = ?, data_fim_mandato = ?
                           WHERE id = ?");
    $stmt->execute([
        $dados['nome'],
        $dados['partido_politico'] ?? null,
        $dados['data_nascimento'],
        $dados['data_inicio_mandato'],
        $dados['data_fim_mandato'] ?? null,
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

    $stmt = $db->prepare("DELETE FROM governantes WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    echo json_encode(['sucesso' => true]);
}