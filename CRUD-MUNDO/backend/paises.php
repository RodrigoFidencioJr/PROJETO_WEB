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

const SELECT_BASE = "SELECT p.*, c.nome AS continente_nome, g.nome AS governante_nome
                      FROM paises p
                      JOIN continentes c ON c.id = p.continente_id
                      LEFT JOIN governantes g ON g.id = p.governante_id";

function listar($db) {
    if (isset($_GET['id'])) {
        $stmt = $db->prepare(SELECT_BASE . " WHERE p.id = ?");
        $stmt->execute([$_GET['id']]);
        $dado = $stmt->fetch();
        if (!$dado) {
            http_response_code(404);
            echo json_encode(['erro' => 'País não encontrado']);
            return;
        }
        echo json_encode($dado);
        return;
    }

    if (isset($_GET['continente_id'])) {
        $stmt = $db->prepare(SELECT_BASE . " WHERE p.continente_id = ? ORDER BY p.nome");
        $stmt->execute([$_GET['continente_id']]);
        echo json_encode($stmt->fetchAll());
        return;
    }

    $stmt = $db->query(SELECT_BASE . " ORDER BY p.nome");
    echo json_encode($stmt->fetchAll());
}

function criar($db) {
    $dados = json_decode(file_get_contents('php://input'), true);

    if (empty($dados['nome']) || empty($dados['continente_id'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'nome e continente_id são obrigatórios']);
        return;
    }

    if (!continenteExiste($db, $dados['continente_id'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'continente_id informado não existe']);
        return;
    }

    $stmt = $db->prepare("INSERT INTO paises
        (nome, continente_id, populacao, area_km2, idioma, governante_id, clima, regime_politico, moeda)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $dados['nome'],
        $dados['continente_id'],
        $dados['populacao'] ?? 0,
        $dados['area_km2'] ?? 0,
        $dados['idioma'] ?? null,
        $dados['governante_id'] ?? null,
        $dados['clima'] ?? null,
        $dados['regime_politico'] ?? null,
        $dados['moeda'] ?? null,
    ]);

    http_response_code(201);
    echo json_encode(['id' => $db->lastInsertId()]);
}

function atualizar($db) {
    $dados = json_decode(file_get_contents('php://input'), true);

    if (empty($dados['id']) || empty($dados['nome']) || empty($dados['continente_id'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'id, nome e continente_id são obrigatórios']);
        return;
    }

    if (!continenteExiste($db, $dados['continente_id'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'continente_id informado não existe']);
        return;
    }

    $stmt = $db->prepare("UPDATE paises SET
        nome = ?, continente_id = ?, populacao = ?, area_km2 = ?, idioma = ?,
        governante_id = ?, clima = ?, regime_politico = ?, moeda = ?
        WHERE id = ?");
    $stmt->execute([
        $dados['nome'],
        $dados['continente_id'],
        $dados['populacao'] ?? 0,
        $dados['area_km2'] ?? 0,
        $dados['idioma'] ?? null,
        $dados['governante_id'] ?? null,
        $dados['clima'] ?? null,
        $dados['regime_politico'] ?? null,
        $dados['moeda'] ?? null,
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
        $stmt = $db->prepare("DELETE FROM paises WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(['sucesso' => true]);
    } catch (PDOException $e) {
        http_response_code(409);
        echo json_encode(['erro' => 'Não é possível excluir: existem cidades vinculadas a este país']);
    }
}

function continenteExiste($db, $id) {
    $stmt = $db->prepare("SELECT id FROM continentes WHERE id = ?");
    $stmt->execute([$id]);
    return (bool) $stmt->fetch();
}