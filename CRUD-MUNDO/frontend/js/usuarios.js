protegerPagina({ apenasAdmin: true });

document.getElementById('form-usuario').addEventListener('submit', salvarUsuario);
document.getElementById('btn-cancelar').addEventListener('click', limparFormulario);

carregarUsuarios();

async function carregarUsuarios() {
    const usuarios = await apiGet('usuarios');
    const corpo = document.getElementById('tabela-usuarios');

    if (!Array.isArray(usuarios) || usuarios.length === 0) {
        corpo.innerHTML = '<tr><td colspan="6" class="vazio">Nenhum usuário cadastrado.</td></tr>';
        return;
    }

    corpo.innerHTML = usuarios.map(u => `
        <tr>
            <td>${u.nome}</td>
            <td>${u.username}</td>
            <td>${u.tipo === 'A' ? 'Administrador' : 'Usuário'}</td>
            <td>${formatarStatus(u.status)}</td>
            <td>${u.qtde_acesso}</td>
            <td class="acoes">
                <button class="btn-secundario" onclick="editarUsuario('${escapeJs(u.username)}')">Editar</button>
                <button class="btn-perigo" onclick="excluirUsuario('${escapeJs(u.username)}')">Excluir</button>
            </td>
        </tr>
    `).join('');
}

async function salvarUsuario(evento) {
    evento.preventDefault();

    const original = document.getElementById('usuario-username-original').value;
    const username = document.getElementById('usuario-username').value.trim();
    const nome = document.getElementById('usuario-nome').value.trim();
    const senha = document.getElementById('usuario-senha').value;
    const tipo = document.getElementById('usuario-tipo').value;
    const status = document.getElementById('usuario-status').value;

    if (!username || !nome || (!original && !senha)) {
        mostrarMensagem('Username, nome e senha são obrigatórios para novo usuário.', 'erro');
        return;
    }

    if (senha && senha.length < 6) {
        mostrarMensagem('A senha deve ter pelo menos 6 caracteres.', 'erro');
        return;
    }

    const dados = { username, nome, tipo, status };
    if (senha) dados.senha = senha;

    const resultado = original
        ? await apiPut('usuarios', dados)
        : await apiPost('usuarios', dados);

    if (resultado.status >= 400) {
        mostrarMensagem(resultado.dados.erro || 'Erro ao salvar usuário.', 'erro');
        return;
    }

    mostrarMensagem('Usuário salvo com sucesso.', 'sucesso');
    limparFormulario();
    carregarUsuarios();
}

async function editarUsuario(username) {
    const usuarios = await apiGet('usuarios');
    const u = usuarios.find(item => item.username === username);
    if (!u) return;

    document.getElementById('usuario-username-original').value = u.username;
    document.getElementById('usuario-username').value = u.username;
    document.getElementById('usuario-username').readOnly = true;
    document.getElementById('usuario-nome').value = u.nome;
    document.getElementById('usuario-senha').value = '';
    document.getElementById('usuario-tipo').value = u.tipo;
    document.getElementById('usuario-status').value = u.status;
    document.getElementById('label-usuario-senha').textContent = 'Nova senha (opcional)';
    document.getElementById('titulo-form').textContent = 'Editar usuário';

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function excluirUsuario(username) {
    if (!confirm(`Excluir o usuário ${username}?`)) return;

    const resultado = await apiDelete('usuarios', { username });

    if (resultado.status >= 400) {
        mostrarMensagem(resultado.dados.erro || 'Erro ao excluir usuário.', 'erro');
        return;
    }

    mostrarMensagem('Usuário excluído.', 'sucesso');
    carregarUsuarios();
}

function limparFormulario() {
    document.getElementById('form-usuario').reset();
    document.getElementById('usuario-username-original').value = '';
    document.getElementById('usuario-username').readOnly = false;
    document.getElementById('usuario-status').value = 'A';
    document.getElementById('usuario-tipo').value = 'U';
    document.getElementById('label-usuario-senha').textContent = 'Senha *';
    document.getElementById('titulo-form').textContent = 'Novo usuário';
}

function formatarStatus(status) {
    if (status === 'A') return 'Ativo';
    if (status === 'I') return 'Inativo';
    return 'Bloqueado';
}

function escapeJs(valor) {
    return String(valor).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}
