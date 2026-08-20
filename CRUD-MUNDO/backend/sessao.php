<?php
require_once __DIR__ . '/Auth.php';

header('Content-Type: application/json; charset=utf8');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/Database.php';
$db = (new Database())->connect();

const MAX_TENTATIVAS = 3;

$metodo = $_SERVER['REQUEST_METHOD'];
$dados = json_decode(file_get_contents('php://input'), true) ?? [];
$acao = $_GET['acao'] ?? ($dados['acao'] ?? null);

if ($metodo === 'GET' && $acao === 'me') {
    quemSouEu();
} elseif ($metodo === 'POST' && $acao === 'login') {
    login($db, $dados);
} elseif ($metodo === 'POST' && $acao === 'logout') {
    logout();
} elseif ($metodo === 'POST' && $acao === 'trocar-senha') {
    trocarSenha($db, $dados);
} else {
    http_response_code(404);
    echo json_encode(['erro' => 'Ação inválida']);
}

function login($db, $dados) {
    $username = trim($dados['username'] ?? '');
    $senha = $dados['senha'] ?? '';

    if (!$username || !$senha) {
        http_response_code(400);
        echo json_encode(['erro' => 'Informe usuário e senha.']);
        return;
    }

    $stmt = $db->prepare("SELECT * FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        http_response_code(401);
        echo json_encode(['erro' => 'Usuário ou senha inválidos.']);
        return;
    }

    if ($usuario['status'] === 'B') {
        registrarLog($db, $username, 'Tentativa de login em usuário bloqueado');
        http_response_code(403);
        echo json_encode(['erro' => 'Usuário bloqueado. Fale com o administrador.']);
        return;
    }

    if ($usuario['status'] === 'I') {
        registrarLog($db, $username, 'Tentativa de login em usuário inativo');
        http_response_code(403);
        echo json_encode(['erro' => 'Usuário inativo.']);
        return;
    }

    if (!password_verify($senha, $usuario['senha'])) {
        $tentativas = $usuario['qtde_acesso'] + 1;
        $bloqueou = $tentativas >= MAX_TENTATIVAS;
        $novoStatus = $bloqueou ? 'B' : $usuario['status'];

        $stmt = $db->prepare("UPDATE usuarios SET qtde_acesso = ?, status = ? WHERE username = ?");
        $stmt->execute([$tentativas, $novoStatus, $username]);

        registrarLog($db, $username, $bloqueou
            ? 'Senha incorreta - usuário bloqueado após 3 tentativas'
            : "Senha incorreta - tentativa $tentativas de " . MAX_TENTATIVAS);

        http_response_code($bloqueou ? 403 : 401);
        echo json_encode(['erro' => $bloqueou
            ? 'Usuário bloqueado após 3 tentativas incorretas. Fale com o administrador.'
            : 'Usuário ou senha inválidos.']);
        return;
    }

    $stmt = $db->prepare("UPDATE usuarios SET qtde_acesso = 0 WHERE username = ?");
    $stmt->execute([$username]);

    $_SESSION['username'] = $usuario['username'];
    $_SESSION['nome'] = $usuario['nome'];
    $_SESSION['tipo'] = $usuario['tipo'];

    registrarLog($db, $username, 'Login efetuado com sucesso');

    echo json_encode([
        'sucesso' => true,
        'username' => $usuario['username'],
        'nome' => $usuario['nome'],
        'tipo' => $usuario['tipo'],
        'primeiro_acesso' => $usuario['primeiro_acesso'] === 'S',
    ]);
}

function logout() {
    $_SESSION = [];
    session_destroy();
    echo json_encode(['sucesso' => true]);
}

function quemSouEu() {
    if (!usuarioLogado()) {
        http_response_code(401);
        echo json_encode(['autenticado' => false]);
        return;
    }
    echo json_encode([
        'autenticado' => true,
        'username' => $_SESSION['username'],
        'nome' => $_SESSION['nome'],
        'tipo' => $_SESSION['tipo'],
    ]);
}

function trocarSenha($db, $dados) {
    exigirLogin();

    $senhaAtual = $dados['senha_atual'] ?? '';
    $senhaNova = $dados['senha_nova'] ?? '';

    if (!$senhaAtual || !$senhaNova) {
        http_response_code(400);
        echo json_encode(['erro' => 'Informe a senha atual e a nova senha.']);
        return;
    }

    if (strlen($senhaNova) < 6) {
        http_response_code(400);
        echo json_encode(['erro' => 'A nova senha deve ter pelo menos 6 caracteres.']);
        return;
    }

    $stmt = $db->prepare("SELECT senha FROM usuarios WHERE username = ?");
    $stmt->execute([$_SESSION['username']]);
    $usuario = $stmt->fetch();

    if (!password_verify($senhaAtual, $usuario['senha'])) {
        http_response_code(401);
        echo json_encode(['erro' => 'Senha atual incorreta.']);
        return;
    }

    $hash = password_hash($senhaNova, PASSWORD_BCRYPT);
    $stmt = $db->prepare("UPDATE usuarios SET senha = ?, primeiro_acesso = 'N' WHERE username = ?");
    $stmt->execute([$hash, $_SESSION['username']]);

    echo json_encode(['sucesso' => true]);
}

function registrarLog($db, $username, $descricao) {
    $stmt = $db->prepare("SELECT username FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    if (!$stmt->fetch()) return;

    $stmt = $db->prepare("INSERT INTO logs (descricao, username) VALUES (?, ?)");
    $stmt->execute([$descricao, $username]);
}