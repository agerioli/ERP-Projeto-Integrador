<?php
session_start();


require 'auth/verifica_login.php';
require 'config/conexao.php';
require 'controllers/devolucoes_controller.php';

$aba_ativa = $_GET['aba'] ?? 'lista';
if (!in_array($aba_ativa, ['lista', 'cadastro'])) {
    $aba_ativa = 'lista';
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main">

    <div class="topbar">
        <div>
            <div class="topbar-title">Devoluções</div>
            <div class="topbar-sub">Registro e acompanhamento de devoluções</div>
        </div>
    </div>

    <!-- MÉTRICAS -->
    <div class="dev-metricas">
        <div class="metric-card">
            <div class="metric-label">Total este mês</div>
            <div class="metric-value" style="color:var(--blue)"><?= (int)$metricas['total_mes'] ?></div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Aguardando análise</div>
            <div class="metric-value" style="color:var(--amber)"><?= (int)$metricas['aguardando'] ?></div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Concluídas</div>
            <div class="metric-value" style="color:var(--green)"><?= (int)$metricas['concluidas'] ?></div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Recusadas</div>
            <div class="metric-value" style="color:var(--red)"><?= (int)$metricas['recusadas'] ?></div>
        </div>
    </div>

    <!-- ABAS -->
    <div class="tabs">
        <button class="tab-btn" data-aba="lista"    onclick="trocarAba('lista')">📋 Lista</button>
        <button class="tab-btn" data-aba="cadastro" onclick="trocarAba('cadastro')">➕ Nova devolução</button>
    </div>

    <div class="content">

        <!-- ══════════════ ABA LISTA ══════════════ -->
        <div id="aba-lista" class="aba">
            <div class="card-lista">

                <div class="card-lista-header">
                    <div>
                        <h1>📋 Devoluções</h1>
                        <p>Total de registros: <strong><?= $total ?></strong></p>
                    </div>
                </div>

                <div class="card-lista-body">

                    <!-- FILTROS -->
                    <form method="GET" class="barra-ferramentas">
                        <input type="hidden" name="aba" value="lista">
                        <div class="busca">
                            <input type="text" name="busca"
                                   value="<?= htmlspecialchars($busca) ?>"
                                   placeholder="Buscar por CPF">
                            <button type="submit">🔍 Buscar</button>
                            <?php if (!empty($busca)): ?>
                                <a href="devolucoes.php?aba=lista" class="btn-acao btn-cinza">Limpar</a>
                            <?php endif; ?>
                        </div>
                        <div style="display:flex;gap:8px;align-items:center">
                            <select name="filtro_status" class="dev-select" onchange="this.form.submit()">
                                <option value="">Todos os status</option>
                                <option value="aguardando"  <?= ($_GET['filtro_status'] ?? '') === 'aguardando'  ? 'selected' : '' ?>>Aguardando</option>
                                <option value="em_analise"  <?= ($_GET['filtro_status'] ?? '') === 'em_analise'  ? 'selected' : '' ?>>Em análise</option>
                                <option value="concluida"   <?= ($_GET['filtro_status'] ?? '') === 'concluida'   ? 'selected' : '' ?>>Concluída</option>
                                <option value="recusada"    <?= ($_GET['filtro_status'] ?? '') === 'recusada'    ? 'selected' : '' ?>>Recusada</option>
                            </select>
                            <select name="filtro_motivo" class="dev-select" onchange="this.form.submit()">
                                <option value="">Todos os motivos</option>
                                <option value="tamanho_errado"  <?= ($_GET['filtro_motivo'] ?? '') === 'tamanho_errado'  ? 'selected' : '' ?>>Tamanho errado</option>
                                <option value="defeito"         <?= ($_GET['filtro_motivo'] ?? '') === 'defeito'         ? 'selected' : '' ?>>Defeito</option>
                                <option value="arrependimento"  <?= ($_GET['filtro_motivo'] ?? '') === 'arrependimento'  ? 'selected' : '' ?>>Arrependimento</option>
                                <option value="cor_modelo"      <?= ($_GET['filtro_motivo'] ?? '') === 'cor_modelo'      ? 'selected' : '' ?>>Cor/modelo diferente</option>
                                <option value="outro"           <?= ($_GET['filtro_motivo'] ?? '') === 'outro'           ? 'selected' : '' ?>>Outro</option>
                            </select>
                        </div>
                    </form>

                    <!-- TABELA -->
                    <div class="tabela-container">
                        <?php if (count($devolucoes) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th onclick="ordenarDev('b.nome_completo')">Cliente</th>
                                    <th onclick="ordenarDev('d.data_solicitacao')">Data</th>
                                    <th>Produto</th>
                                    <th>Motivo</th>
                                    <th>Resolução</th>
                                    <th onclick="ordenarDev('d.status')">Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($devolucoes as $d): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($d['nome_completo']) ?></strong>
                                        <div style="font-size:11px;color:var(--g500)">
                                            <?= substr($d['cliente_cpf'],0,3).'.'.substr($d['cliente_cpf'],3,3).'.'.substr($d['cliente_cpf'],6,3).'-'.substr($d['cliente_cpf'],9,2) ?>
                                        </div>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($d['data_solicitacao'])) ?></td>
                                    <td>
                                        <?= htmlspecialchars($d['produto']) ?>
                                        <?php if ($d['tamanho']): ?>
                                            <span class="tag tag-blue"><?= htmlspecialchars($d['tamanho']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $motivos = [
                                            'tamanho_errado' => 'Tamanho errado',
                                            'defeito'        => 'Defeito',
                                            'arrependimento' => 'Arrependimento',
                                            'cor_modelo'     => 'Cor/modelo',
                                            'outro'          => 'Outro',
                                        ];
                                        echo '<span class="tag tag-purple">' . ($motivos[$d['motivo']] ?? $d['motivo']) . '</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $res_labels = [
                                            'troca'        => ['Troca',        'tag-blue'],
                                            'reembolso'    => ['Reembolso',    'tag-ok'],
                                            'credito_loja' => ['Crédito loja', 'tag-blue'],
                                            'recusada'     => ['Recusada',     'tag-red'],
                                            'pendente'     => ['Pendente',     'tag-pend'],
                                        ];
                                        [$label, $cls] = $res_labels[$d['resolucao']] ?? [$d['resolucao'], 'tag-pend'];
                                        echo "<span class=\"tag $cls\">$label</span>";
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $st_labels = [
                                            'aguardando' => ['Aguardando', 'tag-pend'],
                                            'em_analise' => ['Em análise', 'tag-blue'],
                                            'concluida'  => ['Concluída',  'tag-ok'],
                                            'recusada'   => ['Recusada',   'tag-red'],
                                        ];
                                        [$st_label, $st_cls] = $st_labels[$d['status']] ?? [$d['status'], 'tag-pend'];
                                        echo "<span class=\"tag $st_cls\">$st_label</span>";
                                        ?>
                                    </td>
                                    <td class="acoes">
                                        <button class="btn-acao btn-ver"
                                            data-json='<?= htmlspecialchars(json_encode($d), ENT_QUOTES) ?>'
                                            onclick="abrirVerDev(this)">
                                            👁️ Ver
                                        </button>
                                        <button class="btn-acao btn-editar"
                                            data-json='<?= htmlspecialchars(json_encode($d), ENT_QUOTES) ?>'
                                            onclick="abrirEditarDev(this)">
                                            ✏️ Editar
                                        </button>
                                        <button class="btn-acao btn-excluir"
                                            onclick="abrirExcluirDev(<?= $d['id'] ?>)">
                                            🗑️
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- PAGINAÇÃO -->
                        <?php if ($total_paginas > 1): ?>
                        <div class="paginacao">
                            <?php if ($pagina > 1): ?>
                                <a href="?aba=lista&pagina=1&busca=<?= urlencode($busca) ?>" class="pagina">« Primeira</a>
                                <a href="?aba=lista&pagina=<?= $pagina - 1 ?>&busca=<?= urlencode($busca) ?>" class="pagina">‹ Anterior</a>
                            <?php endif; ?>
                            <?php for ($i = max(1, $pagina - 2); $i <= min($total_paginas, $pagina + 2); $i++): ?>
                                <a href="?aba=lista&pagina=<?= $i ?>&busca=<?= urlencode($busca) ?>"
                                   class="pagina <?= $i === $pagina ? 'ativa' : '' ?>"><?= $i ?></a>
                            <?php endfor; ?>
                            <?php if ($pagina < $total_paginas): ?>
                                <a href="?aba=lista&pagina=<?= $pagina + 1 ?>&busca=<?= urlencode($busca) ?>" class="pagina">Próxima ›</a>
                                <a href="?aba=lista&pagina=<?= $total_paginas ?>&busca=<?= urlencode($busca) ?>" class="pagina">Última »</a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php else: ?>
                        <div class="sem-dados">
                            <p>Nenhuma devolução encontrada.</p>
                            <?php if (!empty($busca)): ?>
                                <p>Tente uma busca diferente ou <a href="devolucoes.php">limpe os filtros</a>.</p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════ ABA CADASTRO ══════════════ -->
        <div id="aba-cadastro" class="aba">
            <div class="card-lista">

                <div class="card-lista-header">
                    <div>
                        <h1>➕ Registrar Devolução</h1>
                        <p>Informe os dados da devolução</p>
                    </div>
                </div>

                <div class="card-lista-body">

                    <?php if (!empty($mensagem_dev)): ?>
                        <div class="cad-mensagem <?= $tipo_dev === 'sucesso' ? 'success' : 'error' ?>">
                            <?= $mensagem_dev ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="devolucoes.php?aba=cadastro">
                        <input type="hidden" name="acao" value="create">

                        <div class="consulta-secao">
                            <h3 class="consulta-titulo">👤 Cliente</h3>
                            <div class="filtro-grid">
                                <div class="cad-field">
                                    <label>CPF do cliente <span class="cad-req">*</span></label>
                                    <input type="text" name="cliente_cpf" id="dev-cpf" required placeholder="000.000.000-00" maxlength="14">
                                </div>
                                <div class="cad-field">
                                    <label>Data da solicitação <span class="cad-req">*</span></label>
                                    <input type="date" name="data_solicitacao" required value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="consulta-secao">
                            <h3 class="consulta-titulo">👗 Produto</h3>
                            <div class="filtro-grid">
                                <div class="cad-field" style="grid-column:span 2">
                                    <label>Descrição do produto <span class="cad-req">*</span></label>
                                    <input type="text" name="produto" required placeholder="Ex: Vestido floral manga curta">
                                </div>
                                <div class="cad-field">
                                    <label>Tamanho</label>
                                    <input type="text" name="tamanho" placeholder="Ex: M, 38, GG">
                                </div>
                            </div>
                        </div>

                        <div class="consulta-secao">
                            <h3 class="consulta-titulo">📋 Motivo e resolução</h3>
                            <div class="filtro-grid">
                                <div class="cad-field">
                                    <label>Motivo <span class="cad-req">*</span></label>
                                    <select name="motivo" required>
                                        <option value="">Selecione</option>
                                        <option value="tamanho_errado">Tamanho errado</option>
                                        <option value="defeito">Defeito no produto</option>
                                        <option value="arrependimento">Arrependimento</option>
                                        <option value="cor_modelo">Cor/modelo diferente</option>
                                        <option value="outro">Outro</option>
                                    </select>
                                </div>
                                <div class="cad-field">
                                    <label>Resolução</label>
                                    <select name="resolucao">
                                        <option value="pendente">Pendente</option>
                                        <option value="troca">Troca</option>
                                        <option value="reembolso">Reembolso</option>
                                        <option value="credito_loja">Crédito na loja</option>
                                        <option value="recusada">Recusada</option>
                                    </select>
                                </div>
                                <div class="cad-field">
                                    <label>Status</label>
                                    <select name="status">
                                        <option value="aguardando">Aguardando</option>
                                        <option value="em_analise">Em análise</option>
                                        <option value="concluida">Concluída</option>
                                        <option value="recusada">Recusada</option>
                                    </select>
                                </div>
                            </div>
                            <div class="cad-field" style="margin-top:10px">
                                <label>Descrição detalhada</label>
                                <textarea name="descricao" rows="3"
                                    style="padding:9px 12px;border:1px solid var(--g300);border-radius:var(--rs);font-family:inherit;font-size:13px;background:var(--g50);resize:vertical"
                                    placeholder="Descreva o problema relatado pela cliente..."></textarea>
                            </div>
                            <div class="filtro-grid" style="margin-top:10px">
                                <div class="cad-field">
                                    <label>Data de resolução</label>
                                    <input type="date" name="data_resolucao">
                                </div>
                                <div class="cad-field" style="grid-column:span 2">
                                    <label>Observações internas</label>
                                    <input type="text" name="observacoes" placeholder="Notas internas sobre a devolução">
                                </div>
                            </div>
                        </div>

                        <div class="consulta-acoes">
                            <button type="submit" class="btn-acao btn-ver" style="padding:12px 28px;font-size:14px">
                                💾 Registrar devolução
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div><!-- /content -->
</div><!-- /main -->

<!-- ══ MODAL VER ══ -->
<div id="modalVerDev" class="modal">
    <div class="modal-content" style="width:480px">
        <h2>📄 Detalhes da Devolução</h2>
        <div id="verDevBody" style="margin-top:14px;display:flex;flex-direction:column;gap:6px;font-size:13px"></div>
        <button class="btn-fechar" onclick="fecharModal('modalVerDev')">Fechar</button>
    </div>
</div>

<!-- MODAL VER -->
<div id="modalVerDev" class="modal">
  <div class="modal-content">
    <h2>📄 Detalhes da Devolução</h2>
    <p><strong>Cliente:</strong> <span id="vd-nome"></span></p>
    <p><strong>Data:</strong> <span id="vd-data"></span></p>
    <p><strong>Produto:</strong> <span id="vd-produto"></span></p>
    <p><strong>Motivo:</strong> <span id="vd-motivo"></span></p>
    <p><strong>Descrição:</strong> <span id="vd-descricao"></span></p>
    <p><strong>Resolução:</strong> <span id="vd-resolucao"></span></p>
    <p><strong>Status:</strong> <span id="vd-status"></span></p>
    <p><strong>Observações:</strong> <span id="vd-obs"></span></p>
    <button class="btn-fechar" onclick="fecharModal('modalVerDev')">Fechar</button>
  </div>
</div>

<!-- MODAL EDITAR -->
<div id="modalEditarDev" class="modal">
  <div class="modal-content">
    <h2>✏️ Editar Devolução</h2>
    <form method="POST" action="devolucoes.php?aba=lista">
      <input type="hidden" name="acao" value="update">
      <input type="hidden" name="id"   id="ed-id">
      <div class="form-editar-grid">
        <label>Produto</label>
        <input type="text" name="produto" id="ed-produto">

        <label>Tamanho</label>
        <input type="text" name="tamanho" id="ed-tamanho">

        <label>Motivo</label>
        <select name="motivo" id="ed-motivo">
          <option value="tamanho_errado">Tamanho errado</option>
          <option value="defeito">Defeito</option>
          <option value="arrependimento">Arrependimento</option>
          <option value="cor_modelo">Cor/modelo diferente</option>
          <option value="outro">Outro</option>
        </select>

        <label>Resolução</label>
        <select name="resolucao" id="ed-resolucao">
          <option value="pendente">Pendente</option>
          <option value="troca">Troca</option>
          <option value="reembolso">Reembolso</option>
          <option value="credito_loja">Crédito na loja</option>
          <option value="recusada">Recusada</option>
        </select>

        <label>Status</label>
        <select name="status" id="ed-status">
          <option value="aguardando">Aguardando</option>
          <option value="em_analise">Em análise</option>
          <option value="concluida">Concluída</option>
          <option value="recusada">Recusada</option>
        </select>

        <label>Data de resolução</label>
        <input type="date" name="data_resolucao" id="ed-data_resolucao">

        <label>Observações</label>
        <input type="text" name="observacoes" id="ed-observacoes">
      </div>
      <div class="modal-acoes">
        <button type="submit" class="btn-acao btn-ver">Salvar</button>
        <button type="button" class="btn-acao btn-cinza" onclick="fecharModal('modalEditarDev')">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL EXCLUIR -->
<div id="modalExcluirDev" class="modal">
  <div class="modal-content">
    <h2>⚠️ Cancelar devolução?</h2>
    <p>O registro será marcado como cancelado.</p>
    <form method="POST" action="devolucoes.php?aba=lista">
      <input type="hidden" name="acao" value="delete">
      <input type="hidden" name="id"   id="del-dev-id">
      <div style="display:flex;gap:10px;margin-top:16px">
        <button type="submit" class="btn-acao btn-ver">Sim, cancelar</button>
        <button type="button" class="btn-acao btn-cinza" onclick="fecharModal('modalExcluirDev')">Não</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    trocarAba('<?= $aba_ativa ?>', false);
});
</script>

<?php include 'includes/footer.php'; ?>
