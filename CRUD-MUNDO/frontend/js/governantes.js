document.getElementById('form-governante').addEventListener('submit', salvarGovernante);
document.getElementById('btn-cancelar').addEventListener('click', limparFormulario);

carregarGovernantes();

async function carregarGovernantes() {
    const governantes = await apiGet('governantes');
    const corpo = document.getElementById('tabela-governantes');

    if (governantes.length === 0) {
        corpo.innerHTML = '<tr><td colspan="5" class="vazio">Nenhum governante cadastrado.</td></tr>';
        return;
    }

    corpo.innerHTML = governantes.map(g => `
        <tr>
            <td>${g.nome}</td>
            <td>${g.partido_politico ?? '-'}</td>
            <td>${g.idade}</td>
            <td>${formatarData(g.data_inicio_mandato)} — ${g.data_fim_mandato ? formatarData(g.data_fim_mandato) : 'atual'}</td>
            <td class="acoes">
                <button class="btn-secundario" onclick="editarGovernante(${g.id})">Editar</button>
                <button class="btn-perigo" onclick="excluirGovernante(${g.id})">Excluir</button>
            </td>
        </tr>
    `).join('');
}

async function salvarGovernante(evento) {
    evento.preventDefault();

    const id = document.getElementById('governante-id').value;
    const nome = document.getElementById('governante-nome').value.trim();
    const data_nascimento = document.getElementById('governante-nascimento').value;
    const data_inicio_mandato = document.getElementById('governante-inicio').value;
    const data_fim_mandato = document.getElementById('governante-fim').value;

    if (!nome || !data_nascimento || !data_inicio_mandato) {
        mostrarMensagem('Nome, data de nascimento e início do mandato são obrigatórios.', 'erro');
        return;
    }

    if (data_fim_mandato && data_fim_mandato < data_inicio_mandato) {
        mostrarMensagem('A data de fim do mandato não pode ser anterior ao início.', 'erro');
        return;
    }

    const dados = {
        nome,
        partido_politico: document.getElementById('governante-partido').value.trim(),
        data_nascimento,
        data_inicio_mandato,
        data_fim_mandato: data_fim_mandato || null,
    };

    const resultado = id
        ? await apiPut('governantes', { ...dados, id })
        : await apiPost('governantes', dados);

    if (resultado.status >= 400) {
        mostrarMensagem(resultado.dados.erro || 'Erro ao salvar governante.', 'erro');
        return;
    }

    mostrarMensagem('Governante salvo com sucesso.', 'sucesso');
    limparFormulario();
    carregarGovernantes();
}

async function editarGovernante(id) {
    const g = await apiGet('governantes', { id });
    document.getElementById('governante-id').value = g.id;
    document.getElementById('governante-nome').value = g.nome;
    document.getElementById('governante-partido').value = g.partido_politico ?? '';
    document.getElementById('governante-nascimento').value = g.data_nascimento;
    document.getElementById('governante-inicio').value = g.data_inicio_mandato;
    document.getElementById('governante-fim').value = g.data_fim_mandato ?? '';
    document.getElementById('titulo-form').textContent = 'Editar governante';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function excluirGovernante(id) {
    if (!confirm('Excluir este governante? Países e cidades vinculados ficam sem governante.')) return;

    const resultado = await apiDelete('governantes', id);
    if (resultado.status >= 400) {
        mostrarMensagem(resultado.dados.erro || 'Erro ao excluir governante.', 'erro');
        return;
    }

    mostrarMensagem('Governante excluído.', 'sucesso');
    carregarGovernantes();
}

function limparFormulario() {
    document.getElementById('form-governante').reset();
    document.getElementById('governante-id').value = '';
    document.getElementById('titulo-form').textContent = 'Novo governante';
}

function formatarData(data) {
    const [ano, mes, dia] = data.split('-');
    return `${dia}/${mes}/${ano}`;
}
