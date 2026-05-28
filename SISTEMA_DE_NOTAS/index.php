<?php
// index.php
// Tela de entrada de dados da turma.
// Os campos dos alunos são gerados via JavaScript.
// O formulário envia tudo via POST para resultado.php.
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema de Análise de Turma</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="header">
  <div class="header-mark">
    <svg viewBox="0 0 24 24">
      <path d="M3 3v18h18"/><path d="M7 16l4-4 4 4 4-6"/>
    </svg>
  </div>
  <div class="header-text">
    <h1>Análise de Turma</h1>
    <p>Sistema de avaliação estatística escolar</p>
  </div>
</div>

<div class="container">

  <!-- PASSO 1: Dados da turma — fora do form, só coleta nome e qtd -->
  <div class="card">
    <div class="card-title">Dados da turma</div>
    <div class="card-desc">Preencha o nome da turma e a quantidade de alunos. Depois clique em carregar.</div>

    <div class="grid-turma">
      <div class="form-group">
        <label for="turma">Nome da turma</label>
        <input type="text" id="turma" placeholder="Ex: 3º Ano — Manhã" maxlength="80">
      </div>
      <div class="form-group">
        <label for="qtd">Qtd. de alunos</label>
        <input type="number" id="qtd" placeholder="Ex: 30" min="1" max="50">
      </div>
    </div>

    <div class="btn-row" style="margin-top:16px">
      <!-- type="button" garante que NUNCA vai submeter o form -->
      <button type="button" class="btn" onclick="gerarCampos()">Carregar campos</button>
    </div>
  </div>

  <!-- PASSO 2: Form com os campos dos alunos — gerado pelo JS -->
  <!-- Campos ocultos de turma e qtd são injetados aqui pelo JS também -->
  <form method="POST" action="resultado.php" id="form-principal">

    <div id="area-alunos"></div>

    <div id="btn-enviar" style="display:none" class="btn-row">
      <button type="submit" class="btn">Gerar Relatório</button>
      <button type="button" class="btn btn-outline" onclick="limparTudo()">Limpar</button>
    </div>

  </form>

</div>

<script>
function gerarCampos() {
  const turma = document.getElementById('turma').value.trim();
  const qtd   = parseInt(document.getElementById('qtd').value, 10);

  // Validações básicas com mensagens claras
  if (turma === '') {
    alert('Preencha o nome da turma.');
    document.getElementById('turma').focus();
    return;
  }
  if (!qtd || qtd < 1 || qtd > 50) {
    alert('Informe uma quantidade de alunos entre 1 e 50.');
    document.getElementById('qtd').focus();
    return;
  }

  const area      = document.getElementById('area-alunos');
  const btnEnviar = document.getElementById('btn-enviar');

  // Monta os campos ocultos de turma e qtd dentro do form
  // para que cheguem no $_POST do resultado.php
  let html = `
    <input type="hidden" name="turma" value="${turma.replace(/"/g, '&quot;')}">
    <input type="hidden" name="qtd"   value="${qtd}">
  `;

  // Gera um bloco de campos para cada aluno
  for (let i = 0; i < qtd; i++) {
    html += `
      <div class="card aluno-block">
        <div class="aluno-header">
          <div class="aluno-num">${i + 1}</div>
          <span class="aluno-label">Aluno ${i + 1}</span>
        </div>

        <div class="form-group">
          <label>Nome completo</label>
          <input type="text" name="nome[]" placeholder="Nome do aluno" maxlength="100" required>
        </div>

        <div class="grid-3">
          <div class="form-group">
            <label>Prova 1</label>
            <input type="number" name="nota1[]" placeholder="0 – 10" min="0" max="10" step="0.1" required>
          </div>
          <div class="form-group">
            <label>Prova 2</label>
            <input type="number" name="nota2[]" placeholder="0 – 10" min="0" max="10" step="0.1" required>
          </div>
          <div class="form-group">
            <label>Trabalho</label>
            <input type="number" name="trabalho[]" placeholder="0 – 10" min="0" max="10" step="0.1" required>
          </div>
        </div>
      </div>
    `;
  }

  area.innerHTML = html;
  btnEnviar.style.display = 'flex';

  // Rola a página para o primeiro campo gerado
  area.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function limparTudo() {
  document.getElementById('area-alunos').innerHTML = '';
  document.getElementById('btn-enviar').style.display = 'none';
  document.getElementById('qtd').value   = '';
  document.getElementById('turma').value = '';
  document.getElementById('turma').focus();
}
</script>

</body>
</html>