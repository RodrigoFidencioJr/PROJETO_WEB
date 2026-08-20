protegerPagina();

document.getElementById('form-trocar-senha').addEventListener('submit', async (evento) => {
    evento.preventDefault();

    const senhaAtual = document.getElementById('senha-atual').value;
    const senhaNova = document.getElementById('senha-nova').value;
    const senhaConfirma = document.getElementById('senha-nova-confirma').value;

    if (senhaNova.length < 6) {
        mostrarMensagem('A nova senha deve ter pelo menos 6 caracteres.', 'erro');
        return;
    }

    if (senhaNova !== senhaConfirma) {
        mostrarMensagem('A confirmação não bate com a nova senha.', 'erro');
        return;
    }

    const resposta = await fetch('../backend/sessao.php?acao=trocar-senha', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ senha_atual: senhaAtual, senha_nova: senhaNova }),
    });
    const dados = await resposta.json();

    if (resposta.status >= 400) {
        mostrarMensagem(dados.erro || 'Não foi possível trocar a senha.', 'erro');
        return;
    }

    window.location.href = 'index.html';
});