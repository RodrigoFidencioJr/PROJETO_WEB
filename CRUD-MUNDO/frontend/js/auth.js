const AUTH_BASE = '../backend/sessao.php';

async function verificarSessao() {
    const resposta = await fetch(`${AUTH_BASE}?acao=me`, { credentials: 'include' });
    if (resposta.status === 401) return null;
    return resposta.json();
}

async function protegerPagina({ apenasAdmin = false } = {}) {
    const sessao = await verificarSessao();

    if (!sessao || !sessao.autenticado) {
        window.location.href = 'login.html';
        return null;
    }

    if (apenasAdmin && sessao.tipo !== 'A') {
        window.location.href = 'index.html';
        return null;
    }

    montarNavUsuario(sessao);
    return sessao;
}

function montarNavUsuario(sessao) {
    const infoEl = document.getElementById('usuario-logado');
    if (infoEl) {
        infoEl.textContent = `${sessao.nome} · ${sessao.tipo === 'A' ? 'administrador' : 'usuário'}`;
    }

    if (sessao.tipo !== 'A') {
        document.querySelectorAll('.somente-admin').forEach(el => el.style.display = 'none');
    }
}

async function fazerLogout() {
    await fetch(`${AUTH_BASE}?acao=logout`, { method: 'POST', credentials: 'include' });
    window.location.href = 'login.html';
}

document.addEventListener('DOMContentLoaded', () => {
    const btnLogout = document.getElementById('btn-logout');
    if (btnLogout) btnLogout.addEventListener('click', fazerLogout);
});