document.getElementById('form-pais').addEventListener('submit', salvarPais);
document.getElementById('btn-cancelar').addEventListener('click', limparFormulario);

carregarSelects();
carregarPaises();

async function carregarSelects() {
    const [continentes, governantes] = await Promise.all([
        apiGet('continentes'),
        apiGet('governantes'),
    ]);

    const selectContinente = document.getElementById('pais-continente');
    selectContinente.innerHTML = '<option value="">Selecione...</option>' +
        continentes.map(c => `<option value="${c.id}">${c.nome}</option>`).join('');

    const selectGovernante = document.getElementById('pais-governante');
    selectGovernante.innerHTML = '<option value="">Nenhum</option>' +
        governantes.map(g => `<option value="${g.id}">${g.nome}</option>`).join('');
}

async function carregarPaises() {
    const paises = await apiGet('paises');
    const corpo = document.getElementById('tabela-paises');

    if (paises.length === 0) {
        corpo.innerHTML = '<tr><td colspan="5" class="vazio">Nenhum país cadastrado.</td></tr>';
        return;
    }

    corpo.innerHTML = paises.map(p => `
        <tr>
            <td>${p.nome}</td>
            <td>${p.continente_nome}</td>
            <td>${p.governante_nome ?? '-'}</td>
            <td>${Number(p.populacao).toLocaleString('pt-BR')}</td>
            <td class="acoes">
                <button class="btn-secundario" onclick="editarPais(${p.id})">Editar</button>
                <button class="btn-perigo" onclick="excluirPais(${p.id})">Excluir</button>
            </td>
        </tr>
    `).join('');
}

async function salvarPais(evento) {
    evento.preventDefault();

    const id = document.getElementById('pais-id').value;
    const nome = document.getElementById('pais-nome').value.trim();
    const continente_id = document.getElementById('pais-continente').value;

    if (!nome || !continente_id) {
        mostrarMensagem('Nome e continente são obrigatórios.', 'erro');
        return;
    }

    const dados = {
        nome,
        continente_id,
        governante_id: document.getElementById('pais-governante').value || null,
        populacao: document.getElementById('pais-populacao').value || 0,
        area_km2: document.getElementById('pais-area').value || 0,
        idioma: document.getElementById('pais-idioma').value.trim(),
        clima: document.getElementById('pais-clima').value.trim(),
        regime_politico: document.getElementById('pais-regime').value.trim(),
        moeda: document.getElementById('pais-moeda').value.trim(),
    };

    const resultado = id
        ? await apiPut('paises', { ...dados, id })
        : await apiPost('paises', dados);

    if (resultado.status >= 400) {
        mostrarMensagem(resultado.dados.erro || 'Erro ao salvar país.', 'erro');
        return;
    }

    mostrarMensagem('País salvo com sucesso.', 'sucesso');
    limparFormulario();
    carregarPaises();
}

async function editarPais(id) {
    const p = await apiGet('paises', { id });
    document.getElementById('pais-id').value = p.id;
    document.getElementById('pais-nome').value = p.nome;
    document.getElementById('pais-continente').value = p.continente_id;
    document.getElementById('pais-governante').value = p.governante_id ?? '';
    document.getElementById('pais-populacao').value = p.populacao;
    document.getElementById('pais-area').value = p.area_km2;
    document.getElementById('pais-idioma').value = p.idioma ?? '';
    document.getElementById('pais-clima').value = p.clima ?? '';
    document.getElementById('pais-regime').value = p.regime_politico ?? '';
    document.getElementById('pais-moeda').value = p.moeda ?? '';
    document.getElementById('titulo-form').textContent = 'Editar país';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function excluirPais(id) {
    if (!confirm('Excluir este país? Essa ação não pode ser desfeita.')) return;

    const resultado = await apiDelete('paises', id);
    if (resultado.status >= 400) {
        mostrarMensagem(resultado.dados.erro || 'Erro ao excluir país.', 'erro');
        return;
    }

    mostrarMensagem('País excluído.', 'sucesso');
    carregarPaises();
}

function limparFormulario() {
    document.getElementById('form-pais').reset();
    document.getElementById('pais-id').value = '';
    document.getElementById('titulo-form').textContent = 'Novo país';
}
