document.getElementById('form-cidade').addEventListener('submit', salvarCidade);
document.getElementById('btn-cancelar').addEventListener('click', limparFormulario);

carregarSelects();
carregarCidades();

async function carregarSelects() {
    const [paises, governantes] = await Promise.all([
        apiGet('paises'),
        apiGet('governantes'),
    ]);

    const selectPais = document.getElementById('cidade-pais');
    selectPais.innerHTML = '<option value="">Selecione...</option>' +
        paises.map(p => `<option value="${p.id}">${p.nome}</option>`).join('');

    const selectGovernante = document.getElementById('cidade-governante');
    selectGovernante.innerHTML = '<option value="">Nenhum</option>' +
        governantes.map(g => `<option value="${g.id}">${g.nome}</option>`).join('');
}

async function carregarCidades() {
    const cidades = await apiGet('cidades');
    const corpo = document.getElementById('tabela-cidades');

    if (cidades.length === 0) {
        corpo.innerHTML = '<tr><td colspan="5" class="vazio">Nenhuma cidade cadastrada.</td></tr>';
        return;
    }

    corpo.innerHTML = cidades.map(c => `
        <tr>
            <td>${c.nome}</td>
            <td>${c.pais_nome}</td>
            <td>${c.governante_nome ?? '-'}</td>
            <td>${Number(c.populacao).toLocaleString('pt-BR')}</td>
            <td class="acoes">
                <button class="btn-secundario" onclick="editarCidade(${c.id})">Editar</button>
                <button class="btn-perigo" onclick="excluirCidade(${c.id})">Excluir</button>
            </td>
        </tr>
    `).join('');
}

async function salvarCidade(evento) {
    evento.preventDefault();

    const id = document.getElementById('cidade-id').value;
    const nome = document.getElementById('cidade-nome').value.trim();
    const pais_id = document.getElementById('cidade-pais').value;

    if (!nome || !pais_id) {
        mostrarMensagem('Nome e país são obrigatórios.', 'erro');
        return;
    }

    const dados = {
        nome,
        pais_id,
        governante_id: document.getElementById('cidade-governante').value || null,
        populacao: document.getElementById('cidade-populacao').value || 0,
        area_km2: document.getElementById('cidade-area').value || 0,
        clima: document.getElementById('cidade-clima').value.trim(),
        data_fundacao: document.getElementById('cidade-fundacao').value || null,
    };

    const resultado = id
        ? await apiPut('cidades', { ...dados, id })
        : await apiPost('cidades', dados);

    if (resultado.status >= 400) {
        mostrarMensagem(resultado.dados.erro || 'Erro ao salvar cidade.', 'erro');
        return;
    }

    mostrarMensagem('Cidade salva com sucesso.', 'sucesso');
    limparFormulario();
    carregarCidades();
}

async function editarCidade(id) {
    const c = await apiGet('cidades', { id });
    document.getElementById('cidade-id').value = c.id;
    document.getElementById('cidade-nome').value = c.nome;
    document.getElementById('cidade-pais').value = c.pais_id;
    document.getElementById('cidade-governante').value = c.governante_id ?? '';
    document.getElementById('cidade-populacao').value = c.populacao;
    document.getElementById('cidade-area').value = c.area_km2;
    document.getElementById('cidade-clima').value = c.clima ?? '';
    document.getElementById('cidade-fundacao').value = c.data_fundacao ?? '';
    document.getElementById('titulo-form').textContent = 'Editar cidade';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function excluirCidade(id) {
    if (!confirm('Excluir esta cidade? Essa ação não pode ser desfeita.')) return;

    const resultado = await apiDelete('cidades', id);
    if (resultado.status >= 400) {
        mostrarMensagem(resultado.dados.erro || 'Erro ao excluir cidade.', 'erro');
        return;
    }

    mostrarMensagem('Cidade excluída.', 'sucesso');
    carregarCidades();
}

function limparFormulario() {
    document.getElementById('form-cidade').reset();
    document.getElementById('cidade-id').value = '';
    document.getElementById('titulo-form').textContent = 'Nova cidade';
}
