<?php
// resultado.php
// Recebe os dados via POST, realiza todos os cálculos e exibe o relatório.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$turma     = trim($_POST['turma']    ?? '');
$qtd       = (int) ($_POST['qtd']   ?? 0);
$nomes     = $_POST['nome']          ?? [];
$notas1    = $_POST['nota1']         ?? [];
$notas2    = $_POST['nota2']         ?? [];
$trabalhos = $_POST['trabalho']      ?? [];

if ($turma === '' || $qtd < 1 || count($nomes) < $qtd) {
    header('Location: index.php');
    exit;
}

// ── FUNÇÕES PRÓPRIAS ─────────────────────────────────────────────────────────

function calcularMedia(float $n1, float $n2, float $trab): float {
    return ($n1 + $n2 + $trab) / 3;
}

function calcularRaizSoma(float $n1, float $n2, float $trab): float {
    return sqrt($n1 + $n2 + $trab);
}

function calcularDiferenca(float $n1, float $n2, float $trab): float {
    return abs(max($n1, $n2, $trab) - min($n1, $n2, $trab));
}

function classificarSituacao(float $media): string {
    if ($media >= 7.0)     return 'Aprovado';
    elseif ($media >= 5.0) return 'Recuperação';
    else                   return 'Reprovado';
}

function mensagemDesempenho(float $percentual): array {
    $p = number_format($percentual, 1);
    if ($percentual >= 80) return [
        'classe' => 'excelente',
        'titulo' => 'Excelente desempenho',
        'texto'  => "{$p}% da turma foi aprovada. Parabéns ao professor e aos alunos pelo resultado.",
    ];
    if ($percentual >= 60) return [
        'classe' => 'bom',
        'titulo' => 'Bom desempenho',
        'texto'  => "{$p}% da turma foi aprovada. Há espaço para melhorar o resultado dos alunos em recuperação.",
    ];
    if ($percentual >= 40) return [
        'classe' => 'atencao',
        'titulo' => 'Atenção',
        'texto'  => "Apenas {$p}% da turma foi aprovada. Recomenda-se revisar o conteúdo com os alunos.",
    ];
    return [
        'classe' => 'critico',
        'titulo' => 'Situação crítica',
        'texto'  => "Somente {$p}% da turma foi aprovada. Uma intervenção pedagógica é necessária.",
    ];
}

// ── PROCESSAMENTO ────────────────────────────────────────────────────────────

$alunos         = [];
$somaMedias     = 0.0;
$somaTotalNotas = 0.0;
$aprovados      = 0;
$recuperacoes   = 0;
$reprovados     = 0;

for ($i = 0; $i < $qtd; $i++) {
    $n1   = max(0, min(10, (float) ($notas1[$i]    ?? 0)));
    $n2   = max(0, min(10, (float) ($notas2[$i]    ?? 0)));
    $trab = max(0, min(10, (float) ($trabalhos[$i] ?? 0)));

    $media     = calcularMedia($n1, $n2, $trab);
    $raizSoma  = calcularRaizSoma($n1, $n2, $trab);
    $diferenca = calcularDiferenca($n1, $n2, $trab);
    $situacao  = classificarSituacao($media);

    $somaMedias     += $media;
    $somaTotalNotas += ($n1 + $n2 + $trab);

    if ($situacao === 'Aprovado')        $aprovados++;
    elseif ($situacao === 'Recuperação') $recuperacoes++;
    else                                 $reprovados++;

    $alunos[] = compact('n1', 'n2', 'trab', 'media', 'raizSoma', 'diferenca', 'situacao')
              + ['nome' => trim($nomes[$i])];
}

$mediaGeral = $somaMedias / $qtd;
$percentual = ($aprovados / $qtd) * 100;
$medias     = array_column($alunos, 'media');
$maiorMedia = max($medias);
$menorMedia = min($medias);
$msg        = mensagemDesempenho($percentual);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Relatório — <?= htmlspecialchars($turma) ?></title>
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

  <!-- Cabeçalho do relatório -->
  <div class="card">
    <div class="card-title">Relatório: <?= htmlspecialchars($turma) ?></div>
    <div class="card-desc">
      <?= $qtd ?> aluno<?= $qtd > 1 ? 's' : '' ?> avaliado<?= $qtd > 1 ? 's' : '' ?>
    </div>

    <div class="msg-box <?= $msg['classe'] ?>">
      <div class="msg-title"><?= htmlspecialchars($msg['titulo']) ?></div>
      <?= htmlspecialchars($msg['texto']) ?>
    </div>
  </div>

  <!-- Estatísticas da turma -->
  <div class="card">
    <div class="section-label">Estatísticas da turma</div>
    <div class="stats-grid">

      <div class="stat-card">
        <div class="stat-label">Média geral</div>
        <div class="stat-value v-accent"><?= number_format($mediaGeral, 2) ?></div>
      </div>

      <div class="stat-card">
        <div class="stat-label">Maior média</div>
        <div class="stat-value v-green"><?= number_format($maiorMedia, 2) ?></div>
      </div>

      <div class="stat-card">
        <div class="stat-label">Menor média</div>
        <div class="stat-value v-red"><?= number_format($menorMedia, 2) ?></div>
      </div>

      <div class="stat-card">
        <div class="stat-label">Aprovados</div>
        <div class="stat-value v-green"><?= $aprovados ?></div>
      </div>

      <div class="stat-card">
        <div class="stat-label">Recuperação</div>
        <div class="stat-value v-amber"><?= $recuperacoes ?></div>
      </div>

      <div class="stat-card">
        <div class="stat-label">Reprovados</div>
        <div class="stat-value v-red"><?= $reprovados ?></div>
      </div>

      <div class="stat-card">
        <div class="stat-label">% Aprovação</div>
        <div class="stat-value <?= $percentual >= 70 ? 'v-green' : ($percentual >= 50 ? 'v-amber' : 'v-red') ?>">
          <?= number_format($percentual, 1) ?>%
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-label">Soma total</div>
        <div class="stat-value v-accent"><?= number_format($somaTotalNotas, 1) ?></div>
      </div>

    </div>
  </div>

  <!-- Tabela individual -->
  <div class="card">
    <div class="section-label">Resultados individuais</div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Nome</th>
            <th>Prova 1</th>
            <th>Prova 2</th>
            <th>Trabalho</th>
            <th>Média</th>
            <th>Raiz</th>
            <th>Diferença</th>
            <th>Situação</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($alunos as $i => $a): ?>
          <tr>
            <td class="mono" style="color:var(--text-2)"><?= $i + 1 ?></td>
            <td style="font-weight:500"><?= htmlspecialchars($a['nome']) ?></td>
            <td class="mono"><?= number_format($a['n1'],   1) ?></td>
            <td class="mono"><?= number_format($a['n2'],   1) ?></td>
            <td class="mono"><?= number_format($a['trab'], 1) ?></td>
            <td class="mono" style="font-weight:600"><?= number_format($a['media'],     2) ?></td>
            <td class="mono" style="color:var(--text-2)"><?= number_format($a['raizSoma'],  2) ?></td>
            <td class="mono" style="color:var(--text-2)"><?= number_format($a['diferenca'], 1) ?></td>
            <td>
              <?php
                $badgeClass = match($a['situacao']) {
                  'Aprovado'    => 'badge-aprovado',
                  'Recuperação' => 'badge-recuperacao',
                  default       => 'badge-reprovado',
                };
              ?>
              <span class="badge <?= $badgeClass ?>"><?= $a['situacao'] ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Ações -->
  <div class="btn-row" style="margin-bottom:48px">
    <a href="index.php" class="btn">Nova turma</a>
    <button onclick="window.print()" class="btn btn-outline">Imprimir relatório</button>
  </div>

</div>
</body>
</html>