const AUTH_BASE = '../backend/sessao.php';

async function verificarSessao() {
    try {
        const resposta = await fetch(`${AUTH_BASE}?acao=me`, {
            credentials: 'include'
        });

        if (!resposta.ok) return null;
        return await resposta.json();
    } catch (erro) {
        return null;
    }
}

async function protegerPagina({ apenasAdmin = false, paginaTrocaSenha = false } = {}) {
    const sessao = await verificarSessao();

    if (!sessao || !sessao.autenticado) {
        window.location.replace('login.html');
        return null;
    }

    // Primeiro acesso: nenhuma página do sistema é liberada antes da troca.
    if (sessao.primeiro_acesso && !paginaTrocaSenha) {
        window.location.replace('trocar_senha.html');
        return null;
    }

    // Se a troca obrigatória já foi concluída, não há motivo para permanecer nessa tela.
    if (!sessao.primeiro_acesso && paginaTrocaSenha) {
        window.location.replace('index.html');
        return null;
    }

    if (apenasAdmin && sessao.tipo !== 'A') {
        window.location.replace('index.html');
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
        document.querySelectorAll('.somente-admin').forEach(el => {
            el.style.display = 'none';
        });
    }
}

async function fazerLogout() {
    await fetch(`${AUTH_BASE}?acao=logout`, {
        method: 'POST',
        credentials: 'include'
    });
    window.location.replace('login.html');
}

document.addEventListener('DOMContentLoaded', () => {
    const btnLogout = document.getElementById('btn-logout');
    if (btnLogout) btnLogout.addEventListener('click', fazerLogout);
});
