document.getElementById('form-continente').addEventListener('submit', salvarContinente);
document.getElementById('btn-cancelar').addEventListener('click', limparFormulario);

carregarContinentes();

async function carregarContinentes() {
    const continentes = await apiGet('continentes');
    const corpo = document.getElementById('tabela-continentes');

    if (continentes.length === 0) {
        corpo.innerHTML = '<tr><td colspan="5" class="vazio">Nenhum continente cadastrado.</td></tr>';
        return;
    }

    corpo.innerHTML = continentes.map(c => `
        <tr>
            <td>${c.nome}</td>
            <td>${Number(c.populacao).toLocaleString('pt-BR')}</td>
            <td>${Number(c.area_km2).toLocaleString('pt-BR')} km²</td>
            <td>${c.total_paises}</td>
            <td class="acoes">
                <button class="btn-secundario" onclick="editarContinente(${c.id})">Editar</button>
                <button class="btn-perigo" onclick="excluirContinente(${c.id})">Excluir</button>
            </td>
        </tr>
    `).join('');
}

async function salvarContinente(evento) {
    evento.preventDefault();

    const id = document.getElementById('continente-id').value;
    const nome = document.getElementById('continente-nome').value.trim();
    const populacao = document.getElementById('continente-populacao').value || 0;
    const area_km2 = document.getElementById('continente-area').value || 0;

    if (!nome) {
        mostrarMensagem('Informe o nome do continente.', 'erro');
        return;
    }

    const dados = { nome, populacao, area_km2 };
    const resultado = id
        ? await apiPut('continentes', { ...dados, id })
        : await apiPost('continentes', dados);

    if (resultado.status >= 400) {
        mostrarMensagem(resultado.dados.erro || 'Erro ao salvar continente.', 'erro');
        return;
    }

    mostrarMensagem('Continente salvo com sucesso.', 'sucesso');
    limparFormulario();
    carregarContinentes();
}

async function editarContinente(id) {
    const c = await apiGet('continentes', { id });
    document.getElementById('continente-id').value = c.id;
    document.getElementById('continente-nome').value = c.nome;
    document.getElementById('continente-populacao').value = c.populacao;
    document.getElementById('continente-area').value = c.area_km2;
    document.getElementById('titulo-form').textContent = 'Editar continente';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function excluirContinente(id) {
    if (!confirm('Excluir este continente? Essa ação não pode ser desfeita.')) return;

    const resultado = await apiDelete('continentes', id);
    if (resultado.status >= 400) {
        mostrarMensagem(resultado.dados.erro || 'Erro ao excluir continente.', 'erro');
        return;
    }

    mostrarMensagem('Continente excluído.', 'sucesso');
    carregarContinentes();
}

function limparFormulario() {
    document.getElementById('form-continente').reset();
    document.getElementById('continente-id').value = '';
    document.getElementById('titulo-form').textContent = 'Novo continente';
}
