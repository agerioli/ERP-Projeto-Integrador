<?php
session_start();
require 'auth/verifica_login.php';
require 'config/conexao.php';
require 'controllers/encomendas_controller.php';

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
            <div class="topbar-title">Encomendas</div>
            <div class="topbar-sub">Pedidos com data prevista de entrega</div>
        </div>
    </div>

    <!-- MÉTRICAS -->
    <div class="dev-metricas">
        <div class="metric-card">
            <div class="metric-label">Encomendas ativas</div>
            <div class="metric-value" style="color:var(--blue)"><?= (int)$metricas_enc['total_ativas'] ?></div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Em andamento</div>
            <div class="metric-value" style="color:var(--purple)"><?= (int)$metricas_enc['em_andamento'] ?></div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Prontas p/ entrega</div>
            <div class="metric-value" style="color:var(--green)"><?= (int)$metricas_enc['prontas'] ?></div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Atrasadas</div>
            <div class="metric-value" style="color:var(--red)"><?= (int)$metricas_enc['atrasadas'] ?></div>
        </div>
    </div>

    <!-- ABAS -->
    <div class="tabs">
        <button class="tab-btn" data-aba="lista"    onclick="trocarAba('lista')">📋 Lista</button>
        <button class="tab-btn" data-aba="cadastro" onclick="trocarAba('cadastro')">➕ Nova encomenda</button>
    </div>

    <div class="content">

        <!-- ══════════════ ABA LISTA ══════════════ -->
        <div id="aba-lista" class="aba">
            <div class="card-lista">

                <div class="card-lista-header">
                    <div>
                        <h1>📦 Encomendas</h1>
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
                                <a href="encomendas.php?aba=lista" class="btn-acao btn-cinza">Limpar</a>
                            <?php endif; ?>
                        </div>
                        <div style="display:flex;gap:8px;align-items:center">
                            <select name="filtro_status" class="dev-select" onchange="this.form.submit()">
                                <option value="">Todos os status</option>
                                <option value="aguardando"   <?= ($_GET['filtro_status'] ?? '') === 'aguardando'   ? 'selected' : '' ?>>Aguardando</option>
                                <option value="em_producao"  <?= ($_GET['filtro_status'] ?? '') === 'em_producao'  ? 'selected' : '' ?>>Em produção</option>
                                <option value="pronto"       <?= ($_GET['filtro_status'] ?? '') === 'pronto'       ? 'selected' : '' ?>>Pronto</option>
                                <option value="entregue"     <?= ($_GET['filtro_status'] ?? '') === 'entregue'     ? 'selected' : '' ?>>Entregue</option>
                            </select>
                        </div>
                    </form>

                    <!-- TABELA -->
                    <div class="tabela-container">
                        <?php if (count($encomendas) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th onclick="ordenarEnc('b.nome_completo')">Cliente</th>
                                    <th onclick="ordenarEnc('e.data_pedido')">Pedido</th>
                                    <th>Produto</th>
                                    <th onclick="ordenarEnc('e.data_prevista')">Entrega prevista</th>
                                    <th>Valor</th>
                                    <th onclick="ordenarEnc('e.status')">Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($encomendas as $enc): ?>
                                <?php
                                    $atrasada = ($enc['data_prevista'] < date('Y-m-d'))
                                                && !in_array($enc['status'], ['entregue','cancelado']);
                                ?>
                                <tr <?= $atrasada ? 'style="background:#fff5f5"' : '' ?>>
                                    <td>
                                        <strong><?= htmlspecialchars($enc['nome_completo']) ?></strong>
                                        <div style="font-size:11px;color:var(--g500)">
                                            <?= substr($enc['cliente_cpf'],0,3).'.'.substr($enc['cliente_cpf'],3,3).'.'.substr($enc['cliente_cpf'],6,3).'-'.substr($enc['cliente_cpf'],9,2) ?>
                                        </div>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($enc['data_pedido'])) ?></td>
                                    <td>
                                        <?= htmlspecialchars($enc['produto_descricao']) ?>
                                        <div style="font-size:11px;color:var(--g500)">
                                            <?= $enc['tamanho'] ? 'Tam: '.$enc['tamanho'] : '' ?>
                                            <?= $enc['cor']     ? ' · '.$enc['cor']       : '' ?>
                                            <?= $enc['quantidade'] > 1 ? ' · '.$enc['quantidade'].'x' : '' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($enc['data_prevista'])) ?>
                                        <?php if ($atrasada): ?>
                                            <span class="tag tag-red">Atrasada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($enc['valor_total']): ?>
                                            <div style="font-size:13px;font-weight:600;color:var(--g900)">
                                                R$ <?= number_format($enc['valor_total'], 2, ',', '.') ?>
                                            </div>
                                            <?php if ($enc['valor_sinal']): ?>
                                            <div style="font-size:11px;color:var(--g500)">
                                                Sinal: R$ <?= number_format($enc['valor_sinal'], 2, ',', '.') ?>
                                            </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color:var(--g400)">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $st_labels_enc = [
                                            'aguardando'  => ['Aguardando',   'tag-pend'],
                                            'em_producao' => ['Em produção',  'tag-blue'],
                                            'pronto'      => ['Pronto',       'tag-purple'],
                                            'entregue'    => ['Entregue',     'tag-ok'],
                                        ];
                                        [$st_label, $st_cls] = $st_labels_enc[$enc['status']] ?? [$enc['status'], 'tag-pend'];
                                        echo "<span class=\"tag $st_cls\">$st_label</span>";
                                        ?>
                                    </td>
                                    <td class="acoes">
                                        <button class="btn-acao btn-ver"
                                        data-json='<?= htmlspecialchars(json_encode($enc), ENT_QUOTES) ?>'
                                        onclick="abrirVerEnc(this)">
                                        👁️ Ver
                                    </button>
                                        <button class="btn-acao btn-editar"
                                            data-json='<?= htmlspecialchars(json_encode($enc), ENT_QUOTES) ?>'
                                            onclick="abrirEditarEnc(this)">
                                            ✏️ Editar
                                        </button>
                                        <button class="btn-acao btn-excluir"
                                            onclick="abrirExcluirEnc(<?= $enc['id'] ?>)">
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
                            <p>Nenhuma encomenda encontrada.</p>
                            <?php if (!empty($busca)): ?>
                                <p>Tente uma busca diferente ou <a href="encomendas.php">limpe os filtros</a>.</p>
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
                        <h1>➕ Nova Encomenda</h1>
                        <p>Registre o pedido e a data prevista de entrega</p>
                    </div>
                </div>

                <div class="card-lista-body">

                    <?php if (!empty($mensagem_enc)): ?>
                        <div class="cad-mensagem <?= $tipo_enc === 'sucesso' ? 'success' : 'error' ?>">
                            <?= $mensagem_enc ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="encomendas.php?aba=cadastro">
                        <input type="hidden" name="acao" value="create">

                        <div class="consulta-secao">
                            <h3 class="consulta-titulo">👤 Cliente</h3>
                            <div class="filtro-grid">
                                <div class="cad-field">
                                    <label>CPF do cliente <span class="cad-req">*</span></label>
                                    <input type="text" name="cliente_cpf" id="enc-cpf" required placeholder="000.000.000-00" maxlength="14">
                                </div>
                                <div class="cad-field">
                                    <label>Data do pedido <span class="cad-req">*</span></label>
                                    <input type="date" name="data_pedido" required value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="cad-field">
                                    <label>Data prevista de entrega <span class="cad-req">*</span></label>
                                    <input type="date" name="data_prevista" required>
                                </div>
                            </div>
                        </div>

                        <div class="consulta-secao">
                            <h3 class="consulta-titulo">👗 Produto</h3>
                            <div class="filtro-grid">
                                <div class="cad-field" style="grid-column:span 3">
                                    <label>Descrição do produto <span class="cad-req">*</span></label>
                                    <input type="text" name="produto_descricao" required placeholder="Ex: Vestido de festa longo bordado">
                                </div>
                                <div class="cad-field">
                                    <label>Tamanho</label>
                                    <input type="text" name="tamanho" placeholder="Ex: M, 38, GG">
                                </div>
                                <div class="cad-field">
                                    <label>Cor</label>
                                    <input type="text" name="cor" placeholder="Ex: Azul marinho">
                                </div>
                                <div class="cad-field">
                                    <label>Quantidade</label>
                                    <input type="number" name="quantidade" value="1" min="1">
                                </div>
                            </div>
                        </div>

                        <div class="consulta-secao">
                            <h3 class="consulta-titulo">💰 Valores e status</h3>
                            <div class="filtro-grid">
                                <div class="cad-field">
                                    <label>Valor do sinal (R$)</label>
                                    <input type="number" name="valor_sinal" step="0.01" min="0" placeholder="0,00">
                                </div>
                                <div class="cad-field">
                                    <label>Valor total (R$)</label>
                                    <input type="number" name="valor_total" step="0.01" min="0" placeholder="0,00">
                                </div>
                                <div class="cad-field">
                                    <label>Status inicial</label>
                                    <select name="status">
                                        <option value="aguardando">Aguardando</option>
                                        <option value="em_producao">Em produção</option>
                                        <option value="pronto">Pronto</option>
                                    </select>
                                </div>
                            </div>
                            <div class="cad-field" style="margin-top:10px">
                                <label>Observações</label>
                                <textarea name="observacoes" rows="3"
                                    style="padding:9px 12px;border:1px solid var(--g300);border-radius:var(--rs);font-family:inherit;font-size:13px;background:var(--g50);resize:vertical"
                                    placeholder="Detalhes extras do pedido, preferências, referências..."></textarea>
                            </div>
                        </div>

                        <div class="consulta-acoes">
                            <button type="submit" class="btn-acao btn-ver" style="padding:12px 28px;font-size:14px">
                                💾 Registrar encomenda
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div><!-- /content -->
</div><!-- /main -->

<!-- ══ MODAL VER ══ -->
<div id="modalVerEnc" class="modal">
    <div class="modal-content" style="width:480px">
        <h2>📄 Detalhes da Encomenda</h2>
        <div id="verEncBody" style="margin-top:14px;display:flex;flex-direction:column;gap:6px;font-size:13px"></div>
        <button class="btn-fechar" onclick="fecharModal('modalVerEnc')">Fechar</button>
    </div>
</div>

<!-- MODAL VER -->
<div id="modalVerEnc" class="modal">
  <div class="modal-content">
    <h2>📄 Detalhes da Encomenda</h2>
    <p><strong>Cliente:</strong> <span id="ve-nome"></span></p>
    <p><strong>Produto:</strong> <span id="ve-produto"></span></p>
    <p><strong>Tamanho:</strong> <span id="ve-tam"></span> &nbsp; <strong>Cor:</strong> <span id="ve-cor"></span> &nbsp; <strong>Qtd:</strong> <span id="ve-qtd"></span></p>
    <p><strong>Data do pedido:</strong> <span id="ve-pedido"></span></p>
    <p><strong>Entrega prevista:</strong> <span id="ve-prevista"></span></p>
    <p><strong>Entrega real:</strong> <span id="ve-real"></span></p>
    <p><strong>Valor sinal:</strong> <span id="ve-sinal"></span> &nbsp; <strong>Total:</strong> <span id="ve-total"></span></p>
    <p><strong>Status:</strong> <span id="ve-status"></span></p>
    <p><strong>Observações:</strong> <span id="ve-obs"></span></p>
    <button class="btn-fechar" onclick="fecharModal('modalVerEnc')">Fechar</button>
  </div>
</div>

<!-- MODAL EDITAR -->
<div id="modalEditarEnc" class="modal">
  <div class="modal-content">
    <h2>✏️ Editar Encomenda</h2>
    <form method="POST" action="encomendas.php?aba=lista">
      <input type="hidden" name="acao" value="update">
      <input type="hidden" name="id"   id="ee-id">
      <div class="form-editar-grid">
        <label>Produto</label>
        <input type="text" name="produto_descricao" id="ee-produto">

        <label>Tamanho</label>
        <input type="text" name="tamanho" id="ee-tamanho">

        <label>Cor</label>
        <input type="text" name="cor" id="ee-cor">

        <label>Quantidade</label>
        <input type="number" name="quantidade" id="ee-quantidade" min="1">

        <label>Valor sinal (R$)</label>
        <input type="number" name="valor_sinal" id="ee-sinal" step="0.01">

        <label>Valor total (R$)</label>
        <input type="number" name="valor_total" id="ee-total" step="0.01">

        <label>Data prevista</label>
        <input type="date" name="data_prevista" id="ee-data_prevista">

        <label>Data entrega real</label>
        <input type="date" name="data_entrega_real" id="ee-data_entrega">

        <label>Status</label>
        <select name="status" id="ee-status">
          <option value="aguardando">Aguardando</option>
          <option value="em_producao">Em produção</option>
          <option value="pronto">Pronto</option>
          <option value="entregue">Entregue</option>
        </select>

        <label>Observações</label>
        <input type="text" name="observacoes" id="ee-obs">
      </div>
      <div class="modal-acoes">
        <button type="submit" class="btn-acao btn-ver">Salvar</button>
        <button type="button" class="btn-acao btn-cinza" onclick="fecharModal('modalEditarEnc')">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL EXCLUIR -->
<div id="modalExcluirEnc" class="modal">
  <div class="modal-content">
    <h2>⚠️ Cancelar encomenda?</h2>
    <p>O registro será marcado como cancelado.</p>
    <form method="POST" action="encomendas.php?aba=lista">
      <input type="hidden" name="acao" value="delete">
      <input type="hidden" name="id"   id="del-enc-id">
      <div style="display:flex;gap:10px;margin-top:16px">
        <button type="submit" class="btn-acao btn-ver">Sim, cancelar</button>
        <button type="button" class="btn-acao btn-cinza" onclick="fecharModal('modalExcluirEnc')">Não</button>
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
