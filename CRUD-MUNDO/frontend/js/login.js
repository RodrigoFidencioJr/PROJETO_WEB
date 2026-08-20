document.getElementById('form-login').addEventListener('submit', async (evento) => {
    evento.preventDefault();

    const username = document.getElementById('login-username').value.trim();
    const senha = document.getElementById('login-senha').value;

    if (!username || !senha) {
        mostrarMensagem('Informe usuário e senha.', 'erro');
        return;
    }

    const resposta = await fetch('../backend/sessao.php?acao=login', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, senha }),
    });
    const dados = await resposta.json();

    if (resposta.status >= 400) {
        mostrarMensagem(dados.erro || 'Não foi possível entrar.', 'erro');
        return;
    }

    window.location.href = dados.primeiro_acesso ? 'trocar-senha.html' : 'index.html';
});