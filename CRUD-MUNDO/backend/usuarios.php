<?php
require_once __DIR__ . '/Auth.php';

header('Content-Type: application/json; charset=utf8');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/Database.php';
$db = (new Database())->connect();

exigirAdmin();

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
    $stmt = $db->query("SELECT username, nome, status, tipo, qtde_acesso, primeiro_acesso FROM usuarios ORDER BY nome");
    echo json_encode($stmt->fetchAll());
}

function criar($db) {
    $dados = json_decode(file_get_contents('php://input'), true);

    if (empty($dados['username']) || empty($dados['senha']) || empty($dados['nome'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'username, senha e nome são obrigatórios']);
        return;
    }

    $tipo = ($dados['tipo'] ?? 'U') === 'A' ? 'A' : 'U';

    try {
        $stmt = $db->prepare("INSERT INTO usuarios (username, senha, nome, tipo) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $dados['username'],
            password_hash($dados['senha'], PASSWORD_BCRYPT),
            $dados['nome'],
            $tipo,
        ]);
    } catch (PDOException $e) {
        http_response_code(409);
        echo json_encode(['erro' => 'Já existe um usuário com esse username.']);
        return;
    }

    http_response_code(201);
    echo json_encode(['sucesso' => true]);
}

function atualizar($db) {
    $dados = json_decode(file_get_contents('php://input'), true);

    if (empty($dados['username']) || empty($dados['nome'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'username e nome são obrigatórios']);
        return;
    }

    $status = in_array($dados['status'] ?? '', ['A', 'I', 'B']) ? $dados['status'] : 'A';
    $tipo = ($dados['tipo'] ?? 'U') === 'A' ? 'A' : 'U';
    $qtdeAcesso = $status === 'B' ? ($dados['qtde_acesso'] ?? 0) : 0; // desbloquear zera as tentativas

    $sql = "UPDATE usuarios SET nome = ?, status = ?, tipo = ?, qtde_acesso = ?";
    $params = [$dados['nome'], $status, $tipo, $qtdeAcesso];

    if (!empty($dados['senha'])) {
        $sql .= ", senha = ?, primeiro_acesso = 'S'";
        $params[] = password_hash($dados['senha'], PASSWORD_BCRYPT);
    }

    $sql .= " WHERE username = ?";
    $params[] = $dados['username'];

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['sucesso' => true]);
}

function excluir($db) {
    if (empty($_GET['username'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'username é obrigatório']);
        return;
    }

    if ($_GET['username'] === $_SESSION['username']) {
        http_response_code(400);
        echo json_encode(['erro' => 'Você não pode excluir o próprio usuário logado.']);
        return;
    }

    $stmt = $db->prepare("DELETE FROM usuarios WHERE username = ?");
    $stmt->execute([$_GET['username']]);
    echo json_encode(['sucesso' => true]);
}
