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

const SELECT_BASE = "SELECT c.*, p.nome AS pais_nome, g.nome AS governante_nome
                      FROM cidades c
                      JOIN paises p ON p.id = c.pais_id
                      LEFT JOIN governantes g ON g.id = c.governante_id";

function listar($db) {
    if (isset($_GET['id'])) {
        $stmt = $db->prepare(SELECT_BASE . " WHERE c.id = ?");
        $stmt->execute([$_GET['id']]);
        $dado = $stmt->fetch();
        if (!$dado) {
            http_response_code(404);
            echo json_encode(['erro' => 'Cidade não encontrada']);
            return;
        }
        echo json_encode($dado);
        return;
    }

    if (isset($_GET['pais_id'])) {
        $stmt = $db->prepare(SELECT_BASE . " WHERE c.pais_id = ? ORDER BY c.nome");
        $stmt->execute([$_GET['pais_id']]);
        echo json_encode($stmt->fetchAll());
        return;
    }

    $stmt = $db->query(SELECT_BASE . " ORDER BY c.nome");
    echo json_encode($stmt->fetchAll());
}

function criar($db) {
    $dados = json_decode(file_get_contents('php://input'), true);

    if (empty($dados['nome']) || empty($dados['pais_id'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'nome e pais_id são obrigatórios']);
        return;
    }

    if (!paisExiste($db, $dados['pais_id'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'pais_id informado não existe']);
        return;
    }

    $stmt = $db->prepare("INSERT INTO cidades
        (nome, pais_id, populacao, area_km2, clima, governante_id, data_fundacao)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $dados['nome'],
        $dados['pais_id'],
        $dados['populacao'] ?? 0,
        $dados['area_km2'] ?? 0,
        $dados['clima'] ?? null,
        $dados['governante_id'] ?? null,
        $dados['data_fundacao'] ?? null,
    ]);

    http_response_code(201);
    echo json_encode(['id' => $db->lastInsertId()]);
}

function atualizar($db) {
    $dados = json_decode(file_get_contents('php://input'), true);

    if (empty($dados['id']) || empty($dados['nome']) || empty($dados['pais_id'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'id, nome e pais_id são obrigatórios']);
        return;
    }

    if (!paisExiste($db, $dados['pais_id'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'pais_id informado não existe']);
        return;
    }

    $stmt = $db->prepare("UPDATE cidades SET
        nome = ?, pais_id = ?, populacao = ?, area_km2 = ?, clima = ?, governante_id = ?, data_fundacao = ?
        WHERE id = ?");
    $stmt->execute([
        $dados['nome'],
        $dados['pais_id'],
        $dados['populacao'] ?? 0,
        $dados['area_km2'] ?? 0,
        $dados['clima'] ?? null,
        $dados['governante_id'] ?? null,
        $dados['data_fundacao'] ?? null,
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

    $stmt = $db->prepare("DELETE FROM cidades WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    echo json_encode(['sucesso' => true]);
}

function paisExiste($db, $id) {
    $stmt = $db->prepare("SELECT id FROM paises WHERE id = ?");
    $stmt->execute([$id]);
    return (bool) $stmt->fetch();
}