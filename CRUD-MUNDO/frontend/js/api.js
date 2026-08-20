const API_BASE = '../backend';

async function apiGet(recurso, params = {}) {
    const query = new URLSearchParams(params).toString();
    const url = `${API_BASE}/${recurso}.php${query ? '?' + query : ''}`;
    const resposta = await fetch(url);
    return resposta.json();
}

async function apiPost(recurso, dados) {
    const resposta = await fetch(`${API_BASE}/${recurso}.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados),
    });
    return { status: resposta.status, dados: await resposta.json() };
}

async function apiPut(recurso, dados) {
    const resposta = await fetch(`${API_BASE}/${recurso}.php`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados),
    });
    return { status: resposta.status, dados: await resposta.json() };
}

async function apiDelete(recurso, id) {
    const resposta = await fetch(`${API_BASE}/${recurso}.php?id=${id}`, { method: 'DELETE' });
    return { status: resposta.status, dados: await resposta.json() };
}

function mostrarMensagem(texto, tipo) {
    const el = document.getElementById('mensagem');
    if (!el) return;
    el.textContent = texto;
    el.className = `mensagem ${tipo}`;
    clearTimeout(el._timeout);
    el._timeout = setTimeout(() => { el.className = 'mensagem'; }, 4000);
}
async function apiGet(recurso, params = {}) {
    const query = new URLSearchParams(params).toString();
    const url = `${API_BASE}/${recurso}.php${query ? '?' + query : ''}`;
    const resposta = await fetch(url, { credentials: 'include' });
    return resposta.json();
}

async function apiPost(recurso, dados) {
    const resposta = await fetch(`${API_BASE}/${recurso}.php`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados),
    });
    return { status: resposta.status, dados: await resposta.json() };
}

async function apiPut(recurso, dados) {
    const resposta = await fetch(`${API_BASE}/${recurso}.php`, {
        method: 'PUT',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados),
    });
    return { status: resposta.status, dados: await resposta.json() };
}

async function apiDelete(recurso, params) {
    const query = typeof params === 'object' ? new URLSearchParams(params).toString() : `id=${params}`;
    const resposta = await fetch(`${API_BASE}/${recurso}.php?${query}`, {
        method: 'DELETE',
        credentials: 'include',
    });
    return { status: resposta.status, dados: await resposta.json() };
}