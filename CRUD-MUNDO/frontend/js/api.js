const API_BASE = '../backend';

async function tratarResposta(resposta) {
    let dados = {};

    try {
        dados = await resposta.json();
    } catch (erro) {
        dados = { erro: 'Resposta inválida do servidor.' };
    }

    if (resposta.status === 401) {
        window.location.replace('login.html');
    }

    if (resposta.status === 428 && dados.trocar_senha) {
        window.location.replace('trocar_senha.html');
    }

    return dados;
}

async function apiGet(recurso, params = {}) {
    const query = new URLSearchParams(params).toString();
    const url = `${API_BASE}/${recurso}.php${query ? '?' + query : ''}`;
    const resposta = await fetch(url, { credentials: 'include' });
    return tratarResposta(resposta);
}

async function apiPost(recurso, dados) {
    const resposta = await fetch(`${API_BASE}/${recurso}.php`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados),
    });

    return {
        status: resposta.status,
        dados: await tratarResposta(resposta)
    };
}

async function apiPut(recurso, dados) {
    const resposta = await fetch(`${API_BASE}/${recurso}.php`, {
        method: 'PUT',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados),
    });

    return {
        status: resposta.status,
        dados: await tratarResposta(resposta)
    };
}

async function apiDelete(recurso, params) {
    const query = typeof params === 'object'
        ? new URLSearchParams(params).toString()
        : `id=${encodeURIComponent(params)}`;

    const resposta = await fetch(`${API_BASE}/${recurso}.php?${query}`, {
        method: 'DELETE',
        credentials: 'include',
    });

    return {
        status: resposta.status,
        dados: await tratarResposta(resposta)
    };
}

function mostrarMensagem(texto, tipo) {
    const el = document.getElementById('mensagem');
    if (!el) return;

    el.textContent = texto;
    el.className = `mensagem ${tipo}`;

    clearTimeout(el._timeout);
    el._timeout = setTimeout(() => {
        el.className = 'mensagem';
    }, 4000);
}
