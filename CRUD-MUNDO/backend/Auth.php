<?php
session_start();

function usuarioLogado() {
    return $_SESSION['username'] ?? null;
}

function exigirLogin() {
    if (!usuarioLogado()) {
        http_response_code(401);
        echo json_encode(['erro' => 'Não autenticado. Faça login novamente.']);
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