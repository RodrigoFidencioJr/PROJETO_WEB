protegerPagina({ apenasAdmin: true });

carregarLogs();

async function carregarLogs() {
    const logs = await apiGet('logs');
    const corpo = document.getElementById('tabela-logs');

    if (!Array.isArray(logs) || logs.length === 0) {
        corpo.innerHTML = '<tr><td colspan="3" class="vazio">Nenhum log registrado.</td></tr>';
        return;
    }

    corpo.innerHTML = logs.map(log => `
        <tr>
            <td>${formatarDataHora(log.data_acesso)}</td>
            <td>${log.username}</td>
            <td>${log.descricao ?? '-'}</td>
        </tr>
    `).join('');
}

function formatarDataHora(valor) {
    if (!valor) return '-';
    const data = new Date(valor.replace(' ', 'T'));
    return Number.isNaN(data.getTime()) ? valor : data.toLocaleString('pt-BR');
}
