<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function usuarioLogado() {
    return $_SESSION['username'] ?? null;
}

function exigirLogin($permitirPrimeiroAcesso = false) {
    if (!usuarioLogado()) {
        http_response_code(401);
        echo json_encode(['erro' => 'Não autenticado. Faça login novamente.']);
        exit;
    }

    if (!$permitirPrimeiroAcesso && ($_SESSION['primeiro_acesso'] ?? false)) {
        http_response_code(428);
        echo json_encode([
            'erro' => 'Troca de senha obrigatória no primeiro acesso.',
            'trocar_senha' => true
        ]);
        exit;
    }
}

function exigirAdmin() {
    exigirLogin();

    if (($_SESSION['tipo'] ?? '') !== 'A') {
        http_response_code(403);
        echo json_encode(['erro' => 'Ação permitida apenas para administradores.']);
        exit;
    }
}
