<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/conexao.php';
require_once __DIR__ . '/auth/verifica_login.php';

$aba = $_GET['aba'] ?? 'suprimentos';
$valid = ['suprimentos', 'fornecedores', 'compras', 'consumo', 'demandas'];
if (!in_array($aba, $valid, true)) {
    $aba = 'suprimentos';
}

function h($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function nf($v) {
    return number_format((float) $v, 2, ',', '.');
}

$fornecedores = $pdo->query(
    "SELECT id, razao_social, nome_fantasia, cnpj, contato, telefone, email, cidade, estado, status
     FROM fornecedores
     ORDER BY COALESCE(NULLIF(nome_fantasia,''), razao_social)"
)->fetchAll();

$suprimentos = $pdo->query(
    "SELECT s.id, s.codigo, s.nome, s.unidade_medida, s.estoque_atual, s.estoque_minimo,
            fs.fornecedor_id, f.nome_fantasia, fs.preco_unitario, fs.prazo_entrega_dias
     FROM suprimentos s
     LEFT JOIN fornecedor_suprimentos fs
       ON fs.suprimento_id = s.id AND fs.fornecedor_preferencial = 1
     LEFT JOIN fornecedores f ON f.id = fs.fornecedor_id
     ORDER BY s.nome"
)->fetchAll();

/* Necessidade de compra: todos os suprimentos, priorizando primeiro Comprar, depois Estoque baixo e por fim Normal. */
$necessidades = $suprimentos;
usort($necessidades, function ($a, $b) {
    $classificar = function ($item) {
        $at = (float) $item['estoque_atual'];
        $min = (float) $item['estoque_minimo'];

        if ($min <= 0) {
            return $at <= 0 ? [0, 0] : [2, $at];
        }

        if ($at <= $min) {
            return [0, $at / $min]; // Comprar
        }

        if ($at <= ($min * 1.40)) {
            return [1, $at / $min]; // Estoque baixo
        }

        return [2, $at / $min]; // Normal
    };

    [$grupoA, $relA] = $classificar($a);
    [$grupoB, $relB] = $classificar($b);

    if ($grupoA !== $grupoB) {
        return $grupoA <=> $grupoB;
    }

    if ($relA != $relB) {
        return $relA <=> $relB;
    }

    return strcasecmp((string) $a['nome'], (string) $b['nome']);
});

$demandas = $pdo->query(
    "SELECT d.id, d.numero, d.data_solicitacao, d.prioridade, d.status,
            s.nome AS suprimento_nome, dci.quantidade_solicitada AS quantidade,
            f.nome_fantasia AS fornecedor_nome
     FROM demandas_compra d
     JOIN demanda_compra_itens dci ON dci.demanda_id = d.id
     JOIN suprimentos s ON s.id = dci.suprimento_id
     LEFT JOIN fornecedores f ON f.id = dci.fornecedor_id
     ORDER BY d.id DESC"
)->fetchAll();

$compras = $pdo->query(
    "SELECT c.id, c.numero, c.data_compra, c.status,
            f.nome_fantasia,
            ci.quantidade,
            s.nome AS suprimento_nome,
            COALESCE(ci.quantidade * ci.preco_unitario, 0) AS valor_total
     FROM compras c
     LEFT JOIN fornecedores f ON f.id = c.fornecedor_id
     LEFT JOIN compra_itens ci ON ci.compra_id = c.id AND ci.tipo_item = 'SUPRIMENTO'
     LEFT JOIN suprimentos s ON s.id = ci.suprimento_id
     WHERE c.tipo = 'SUPRIMENTO'
     ORDER BY c.id DESC
     LIMIT 100"
)->fetchAll();

$editarFornecedor = null;
if ($aba === 'fornecedores' && isset($_GET['editar_fornecedor'])) {
    $id = (int) $_GET['editar_fornecedor'];
    if ($id > 0) {
        $st = $pdo->prepare("SELECT id, razao_social, nome_fantasia, cnpj, contato, telefone, email, cidade, estado, observacoes, status FROM fornecedores WHERE id = ?");
        $st->execute([$id]);
        $editarFornecedor = $st->fetch() ?: null;
    }
}

$editarSuprimento = null;
if ($aba === 'suprimentos' && isset($_GET['editar_suprimento'])) {
    $id = (int) $_GET['editar_suprimento'];
    if ($id > 0) {
        $st = $pdo->prepare("SELECT id, codigo, nome, unidade_medida, estoque_atual, estoque_minimo, status FROM suprimentos WHERE id = ?");
        $st->execute([$id]);
        $editarSuprimento = $st->fetch() ?: null;
    }
}

$editarCompra = null;
if ($aba === 'compras' && isset($_GET['editar_compra'])) {
    $editarId = (int) $_GET['editar_compra'];
    if ($editarId > 0) {
        $st = $pdo->prepare(
            "SELECT c.id, c.numero, c.fornecedor_id, c.data_compra,
                    ci.id AS compra_item_id, ci.suprimento_id, ci.quantidade, ci.preco_unitario
             FROM compras c
             JOIN compra_itens ci ON ci.compra_id = c.id AND ci.tipo_item = 'SUPRIMENTO'
             WHERE c.id = ?
             LIMIT 1"
        );
        $st->execute([$editarId]);
        $editarCompra = $st->fetch() ?: null;
    }
}


$editarConsumo = null;
if ($aba === 'consumo' && isset($_GET['editar_consumo'])) {
    $id = (int) $_GET['editar_consumo'];
    if ($id > 0) {
        $st = $pdo->prepare(
            "SELECT m.id, m.suprimento_id, m.quantidade, m.created_at, s.nome AS suprimento_nome
             FROM movimentacoes_suprimentos m
             JOIN suprimentos s ON s.id = m.suprimento_id
             WHERE m.id = ? AND m.tipo = 'CONSUMO'
             LIMIT 1"
        );
        $st->execute([$id]);
        $editarConsumo = $st->fetch() ?: null;
    }
}

$consumosHistorico = $pdo->query(
    "SELECT m.id, m.suprimento_id, s.nome AS suprimento_nome, s.unidade_medida,
            m.quantidade, DATE(m.created_at) AS data_consumo
     FROM movimentacoes_suprimentos m
     JOIN suprimentos s ON s.id = m.suprimento_id
     WHERE m.tipo = 'CONSUMO'
     ORDER BY m.created_at DESC, m.id DESC
     LIMIT 100"
)->fetchAll();

$consumo = $pdo->query(
    "SELECT s.nome, s.unidade_medida,
            COALESCE(SUM(CASE WHEN m.tipo = 'CONSUMO' THEN m.quantidade ELSE 0 END), 0) AS consumo_30
     FROM suprimentos s
     LEFT JOIN movimentacoes_suprimentos m
       ON m.suprimento_id = s.id
      AND m.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
     GROUP BY s.id, s.nome, s.unidade_medida
     ORDER BY consumo_30 DESC, s.nome"
)->fetchAll();

$comprar = array_filter($suprimentos, function ($s) {
    return (float) $s['estoque_atual'] <= (float) $s['estoque_minimo'];
});

$estoqueBaixo = array_filter($suprimentos, function ($s) {
    $at = (float) $s['estoque_atual'];
    $min = (float) $s['estoque_minimo'];
    return $min > 0 && $at > $min && $at <= ($min * 1.40);
});

$suprimentoMaisConsumido = $consumo[0] ?? null;

$inicioMes = date('Y-m-01 00:00:00');
$proximoMes = date('Y-m-01 00:00:00', strtotime('+1 month'));
$stComprasMes = $pdo->prepare(
    "SELECT COUNT(*) FROM compras c
     WHERE c.tipo = 'SUPRIMENTO'
       AND c.data_compra >= ?
       AND c.data_compra < ?"
);
$stComprasMes->execute([$inicioMes, $proximoMes]);
$comprasMes = (int) $stComprasMes->fetchColumn();

$msg = $_SESSION['mensagem_suprimentos'] ?? '';
$tipo = $_SESSION['tipo_suprimentos'] ?? 'success';
unset($_SESSION['mensagem_suprimentos'], $_SESSION['tipo_suprimentos']);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="main">
    <div class="topbar">
        <div>
            <h1>Gestão de Suprimentos</h1>
            <p>Fornecedores, materiais, consumo e planejamento de compras.</p>
        </div>
    </div>

    <div class="content">
        <div class="suprimentos-container">

            <?php if ($msg): ?>
                <div class="suprimentos-message <?= h($tipo) ?>"><?= h($msg) ?></div>
            <?php endif; ?>

            <div class="estoque-kpis">
                <div class="metric-card">
                    <span>Comprar</span>
                    <strong><?= count($comprar) ?></strong>
                    <small>Suprimentos no mínimo ou abaixo</small>
                </div>
                <div class="metric-card">
                    <span>Estoque baixo</span>
                    <strong><?= count($estoqueBaixo) ?></strong>
                    <small>Próximos do estoque mínimo</small>
                </div>
                <div class="metric-card">
                    <span>Suprimento mais consumido</span>
                    <?php if ($suprimentoMaisConsumido): ?>
                        <strong class="kpi-nome"><?= h($suprimentoMaisConsumido['nome']) ?></strong>
                        <small><?= nf($suprimentoMaisConsumido['consumo_30']) ?> <?= h($suprimentoMaisConsumido['unidade_medida']) ?> nos últimos 30 dias</small>
                    <?php else: ?>
                        <strong>—</strong>
                        <small>Nenhum consumo registrado</small>
                    <?php endif; ?>
                </div>
                <div class="metric-card">
                    <span>Compras no mês</span>
                    <strong><?= $comprasMes ?></strong>
                    <small>Compras de suprimentos registradas</small>
                </div>
            </div>

            <!-- Ordem operacional simplificada -->
            <nav class="suprimentos-tabs">
                <a class="<?= $aba === 'suprimentos' ? 'active' : '' ?>" href="gestao_suprimentos.php?aba=suprimentos">Suprimentos</a>
                <a class="<?= $aba === 'fornecedores' ? 'active' : '' ?>" href="gestao_suprimentos.php?aba=fornecedores">Fornecedores</a>
                <a class="<?= $aba === 'compras' ? 'active' : '' ?>" href="gestao_suprimentos.php?aba=compras">Compras</a>
                <a class="<?= $aba === 'consumo' ? 'active' : '' ?>" href="gestao_suprimentos.php?aba=consumo">Consumo</a>
                <a class="<?= $aba === 'demandas' ? 'active' : '' ?>" href="gestao_suprimentos.php?aba=demandas">Necessidade de compra</a>
            </nav>

            <?php if ($aba === 'suprimentos'): ?>

                <div class="suprimentos-form-box">
                    <h2><?= $editarSuprimento ? 'Editar suprimento' : 'Novo suprimento' ?></h2>
                    <p class="form-help">Cadastre somente as informações básicas do suprimento. O controle de reposição será feito pela tela de Necessidade de compra.</p>

                    <form method="post" action="controllers/suprimentos_controller.php">
                        <input type="hidden" name="acao" value="<?= $editarSuprimento ? 'editar_suprimento' : 'salvar_suprimento' ?>">
                        <?php if ($editarSuprimento): ?><input type="hidden" name="suprimento_id" value="<?= (int)$editarSuprimento['id'] ?>"><?php endif; ?>

                        <div class="suprimentos-form-grid">
                            <div>
                                <label>Código</label>
                                <input name="codigo" placeholder="SUP-001" value="<?= h($editarSuprimento['codigo'] ?? '') ?>">
                            </div>

                            <div>
                                <label>Nome *</label>
                                <input name="nome" value="<?= h($editarSuprimento['nome'] ?? '') ?>" required>
                            </div>

                            <div>
                                <label>Unidade *</label>
                                <select name="unidade_medida" required>
                                    <option <?= (($editarSuprimento['unidade_medida'] ?? 'UN') === 'UN') ? 'selected' : '' ?>>UN</option>
                                    <option <?= (($editarSuprimento['unidade_medida'] ?? 'UN') === 'KG') ? 'selected' : '' ?>>KG</option>
                                    <option <?= (($editarSuprimento['unidade_medida'] ?? 'UN') === 'G') ? 'selected' : '' ?>>G</option>
                                    <option <?= (($editarSuprimento['unidade_medida'] ?? 'UN') === 'M') ? 'selected' : '' ?>>M</option>
                                    <option <?= (($editarSuprimento['unidade_medida'] ?? 'UN') === 'M2') ? 'selected' : '' ?>>M2</option>
                                    <option <?= (($editarSuprimento['unidade_medida'] ?? 'UN') === 'L') ? 'selected' : '' ?>>L</option>
                                    <option <?= (($editarSuprimento['unidade_medida'] ?? 'UN') === 'CX') ? 'selected' : '' ?>>CX</option>
                                    <option <?= (($editarSuprimento['unidade_medida'] ?? 'UN') === 'PCT') ? 'selected' : '' ?>>PCT</option>
                                    <option <?= (($editarSuprimento['unidade_medida'] ?? 'UN') === 'ROLO') ? 'selected' : '' ?>>ROLO</option>
                                </select>
                            </div>

                            <div>
                                <label>Estoque atual</label>
                                <input type="number" step="0.01" min="0" name="estoque_atual" value="<?= h($editarSuprimento['estoque_atual'] ?? 0) ?>">
                            </div>

                            <div>
                                <label>Estoque mínimo</label>
                                <input type="number" step="0.01" min="0" name="estoque_minimo" value="<?= h($editarSuprimento['estoque_minimo'] ?? 0) ?>">
                            </div>
                        </div>

                        <button class="btn btn-pri"><?= $editarSuprimento ? 'Salvar alterações' : 'Salvar suprimento' ?></button>
                        <?php if ($editarSuprimento): ?><a class="btn btn-cancelar" href="gestao_suprimentos.php?aba=suprimentos">Cancelar</a><?php endif; ?>
                    </form>

                </div>

                <div class="card">
                    <div class="suprimentos-table-wrap">
                        <table class="suprimentos-table">
                            <thead>
                                <tr>
                                    <th>Suprimento</th>
                                    <th>Unidade</th>
                                    <th>Estoque atual</th>
                                    <th>Estoque mínimo</th>
                                    <th>Status</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$suprimentos): ?>
                                    <tr><td colspan="6" class="empty-state">Nenhum suprimento cadastrado.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($suprimentos as $s):
                                        $at = (float) $s['estoque_atual'];
                                        $min = (float) $s['estoque_minimo'];
                                        $cl = $at <= 0 ? 'critico' : ($at <= $min ? 'baixo' : 'ok');
                                        $status = $at <= 0 ? 'COMPRAR' : ($at <= $min ? 'ESTOQUE BAIXO' : 'NORMAL');
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?= h($s['nome']) ?></strong>
                                                <small><?= h($s['codigo']) ?></small>
                                            </td>
                                            <td><?= h($s['unidade_medida']) ?></td>
                                            <td><?= nf($at) ?></td>
                                            <td><?= nf($min) ?></td>
                                            <td><span class="estoque-tag <?= $cl ?>"><?= h($status) ?></span></td>
                                            <td><a class="btn btn-salvar" href="gestao_suprimentos.php?aba=suprimentos&amp;editar_suprimento=<?= (int)$s['id'] ?>">Editar</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($aba === 'fornecedores'): ?>

                <div class="suprimentos-form-box">
                    <h2><?= $editarFornecedor ? 'Editar fornecedor' : 'Novo fornecedor' ?></h2>
                    <form method="post" action="controllers/suprimentos_controller.php">
                        <input type="hidden" name="acao" value="<?= $editarFornecedor ? 'editar_fornecedor' : 'salvar_fornecedor' ?>">
                        <?php if ($editarFornecedor): ?><input type="hidden" name="fornecedor_id" value="<?= (int)$editarFornecedor['id'] ?>"><?php endif; ?>

                        <div class="suprimentos-form-grid">
                            <div><label>Razão Social *</label><input name="razao_social" value="<?= h($editarFornecedor['razao_social'] ?? '') ?>" required></div>
                            <div><label>Nome Fantasia</label><input name="nome_fantasia" value="<?= h($editarFornecedor['nome_fantasia'] ?? '') ?>"></div>
                            <div><label>CNPJ</label><input name="cnpj" value="<?= h($editarFornecedor['cnpj'] ?? '') ?>"></div>
                            <div><label>Contato</label><input name="contato" value="<?= h($editarFornecedor['contato'] ?? '') ?>"></div>
                            <div><label>Telefone</label><input name="telefone" value="<?= h($editarFornecedor['telefone'] ?? '') ?>"></div>
                            <div><label>E-mail</label><input type="email" name="email" value="<?= h($editarFornecedor['email'] ?? '') ?>"></div>
                            <div><label>Cidade</label><input name="cidade" value="<?= h($editarFornecedor['cidade'] ?? '') ?>"></div>
                            <div><label>Estado</label><input name="estado" maxlength="2" value="<?= h($editarFornecedor['estado'] ?? '') ?>"></div>
                            <?php if ($editarFornecedor): ?>
                                <div><label>Status</label><select name="status"><option value="ATIVO" <?= ($editarFornecedor['status'] ?? 'ATIVO') === 'ATIVO' ? 'selected' : '' ?>>Ativo</option><option value="INATIVO" <?= ($editarFornecedor['status'] ?? '') === 'INATIVO' ? 'selected' : '' ?>>Inativo</option></select></div>
                            <?php endif; ?>
                            <div class="full"><label>Observações</label><textarea name="observacoes"><?= h($editarFornecedor['observacoes'] ?? '') ?></textarea></div>
                        </div>

                        <button class="btn btn-pri"><?= $editarFornecedor ? 'Salvar alterações' : 'Salvar fornecedor' ?></button>
                        <?php if ($editarFornecedor): ?><a class="btn btn-cancelar" href="gestao_suprimentos.php?aba=fornecedores">Cancelar</a><?php endif; ?>
                    </form>
                </div>

                <div class="card">
                    <div class="suprimentos-table-wrap">
                        <table class="suprimentos-table">
                            <thead>
                                <tr><th>Fornecedor</th><th>CNPJ</th><th>Contato</th><th>Telefone</th><th>E-mail</th><th>Local</th><th>Status</th><th>Ação</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fornecedores as $f): ?>
                                    <tr>
                                        <td><strong><?= h($f['nome_fantasia'] ?: $f['razao_social']) ?></strong><small><?= h($f['razao_social']) ?></small></td>
                                        <td><?= h($f['cnpj']) ?></td>
                                        <td><?= h($f['contato']) ?></td>
                                        <td><?= h($f['telefone']) ?></td>
                                        <td><?= h($f['email']) ?></td>
                                        <td><?= h($f['cidade']) ?><?= $f['estado'] ? ' / ' . h($f['estado']) : '' ?></td>
                                        <td><?= h($f['status']) ?></td>
                                        <td><a class="btn btn-salvar" href="gestao_suprimentos.php?aba=fornecedores&amp;editar_fornecedor=<?= (int)$f['id'] ?>">Editar</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($aba === 'compras'): ?>

                <?php if ($editarCompra): ?>
                    <div class="suprimentos-form-box">
                        <h2>Editar compra</h2>
                        <p class="form-help">Altere os dados lançados. O estoque será ajustado automaticamente de acordo com a nova quantidade e suprimento.</p>

                        <form method="post" action="controllers/suprimentos_controller.php">
                            <input type="hidden" name="acao" value="editar_compra">
                            <input type="hidden" name="compra_id" value="<?= (int) $editarCompra['id'] ?>">

                            <div class="suprimentos-form-grid">
                                <div>
                                    <label>Fornecedor *</label>
                                    <select name="compra_fornecedor_id" required>
                                        <option value="">Selecione</option>
                                        <?php foreach ($fornecedores as $f): ?>
                                            <?php if (($f['status'] ?? 'ATIVO') === 'ATIVO' || (int)$editarCompra['fornecedor_id'] === (int)$f['id']): ?>
                                                <option value="<?= $f['id'] ?>" <?= (int)$editarCompra['fornecedor_id'] === (int)$f['id'] ? 'selected' : '' ?>><?= h($f['nome_fantasia'] ?: $f['razao_social']) ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label>Data *</label>
                                    <input type="date" name="compra_data" value="<?= h($editarCompra['data_compra']) ?>" required>
                                </div>

                                <div>
                                    <label>Suprimento *</label>
                                    <select name="compra_suprimento_id" required>
                                        <option value="">Selecione</option>
                                        <?php foreach ($suprimentos as $s): ?>
                                            <option value="<?= $s['id'] ?>" <?= (int)$editarCompra['suprimento_id'] === (int)$s['id'] ? 'selected' : '' ?>><?= h($s['nome']) ?> — estoque <?= nf($s['estoque_atual']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label>Quantidade *</label>
                                    <input type="number" step="0.01" min="0.01" name="compra_quantidade" value="<?= h($editarCompra['quantidade']) ?>" required>
                                </div>

                                <div>
                                    <label>Preço unitário *</label>
                                    <input type="number" step="0.01" min="0" name="compra_preco" value="<?= h($editarCompra['preco_unitario']) ?>" required>
                                </div>

                                <div>
                                    <label>Número do pedido/NF</label>
                                    <input name="compra_numero" value="<?= h($editarCompra['numero']) ?>">
                                </div>
                            </div>

                            <button class="btn btn-pri">Salvar alterações</button>
                            <a class="btn btn-cancelar" href="gestao_suprimentos.php?aba=compras">Cancelar</a>
                            <button type="submit" form="form-excluir-compra" class="btn btn-cancelar" onclick="return confirm('Excluir esta compra? A quantidade será retirada do estoque atual.')">Excluir compra</button>
                        </form>
                        <form id="form-excluir-compra" method="post" action="controllers/suprimentos_controller.php" style="display:none">
                            <input type="hidden" name="acao" value="excluir_compra">
                            <input type="hidden" name="compra_id" value="<?= (int)$editarCompra['id'] ?>">
                        </form>
                    </div>
                <?php else: ?>
                    <div class="suprimentos-form-box">
                        <h2>Registrar compra</h2>
                        <p class="form-help">Ao registrar a compra, a quantidade informada é adicionada imediatamente ao estoque do suprimento.</p>

                        <form method="post" action="controllers/suprimentos_controller.php">
                            <input type="hidden" name="acao" value="salvar_compra">

                            <div class="suprimentos-form-grid">
                                <div>
                                    <label>Fornecedor *</label>
                                    <select name="compra_fornecedor_id" required>
                                        <option value="">Selecione</option>
                                        <?php foreach ($fornecedores as $f): ?>
                                            <?php if (($f['status'] ?? 'ATIVO') === 'ATIVO'): ?>
                                                <option value="<?= $f['id'] ?>"><?= h($f['nome_fantasia'] ?: $f['razao_social']) ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label>Data</label>
                                    <input type="date" name="compra_data" value="<?= date('Y-m-d') ?>">
                                </div>

                                <div>
                                    <label>Suprimento *</label>
                                    <select name="compra_suprimento_id" required>
                                        <option value="">Selecione</option>
                                        <?php foreach ($suprimentos as $s): ?>
                                            <option value="<?= $s['id'] ?>"><?= h($s['nome']) ?> — estoque <?= nf($s['estoque_atual']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label>Quantidade *</label>
                                    <input type="number" step="0.01" min="0.01" name="compra_quantidade" required>
                                </div>

                                <div>
                                    <label>Preço unitário *</label>
                                    <input type="number" step="0.01" min="0" name="compra_preco" required>
                                </div>

                                <div>
                                    <label>Número do pedido/NF</label>
                                    <input name="compra_numero">
                                </div>
                            </div>

                            <button class="btn btn-pri">Registrar compra</button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="suprimentos-table-wrap">
                        <table class="suprimentos-table">
                            <thead>
                                <tr><th>Número</th><th>Data</th><th>Fornecedor</th><th>Suprimento</th><th>Quantidade</th><th>Total</th><th>Ação</th></tr>
                            </thead>
                            <tbody>
                                <?php if (!$compras): ?>
                                    <tr><td colspan="7" class="empty-state">Nenhuma compra registrada.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($compras as $c): ?>
                                        <tr>
                                            <td><?= h($c['numero']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($c['data_compra'])) ?></td>
                                            <td><?= h($c['nome_fantasia'] ?: '—') ?></td>
                                            <td>
                                                <strong><?= h($c['suprimento_nome'] ?: '—') ?></strong>
                                            </td>
                                            <td><?= nf($c['quantidade']) ?></td>
                                            <td>R$ <?= nf($c['valor_total']) ?></td>
                                            <td><a class="btn btn-salvar" href="gestao_suprimentos.php?aba=compras&amp;editar_compra=<?= (int)$c['id'] ?>">Editar</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($aba === 'consumo'): ?>

                <div class="suprimentos-form-box">
                    <h2><?= $editarConsumo ? 'Editar consumo' : 'Registrar consumo' ?></h2>
                    <p class="form-help">Informe a data em que o suprimento foi efetivamente consumido. O consumo reduz o estoque atual.</p>

                    <form method="post" action="controllers/suprimentos_controller.php">
                        <input type="hidden" name="acao" value="<?= $editarConsumo ? 'editar_consumo' : 'registrar_consumo' ?>">
                        <?php if ($editarConsumo): ?>
                            <input type="hidden" name="consumo_id" value="<?= (int) $editarConsumo['id'] ?>">
                        <?php endif; ?>

                        <div class="suprimentos-form-grid">
                            <div>
                                <label>Suprimento *</label>
                                <select name="suprimento_id" required>
                                    <option value="">Selecione</option>
                                    <?php foreach ($suprimentos as $s): ?>
                                        <option value="<?= $s['id'] ?>" <?= ($editarConsumo && (int)$editarConsumo['suprimento_id'] === (int)$s['id']) ? 'selected' : '' ?>>
                                            <?= h($s['nome']) ?> — estoque <?= nf($s['estoque_atual']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label>Quantidade *</label>
                                <input type="number" step="0.01" min="0.01" name="quantidade" value="<?= $editarConsumo ? h($editarConsumo['quantidade']) : '' ?>" required>
                            </div>

                            <div>
                                <label>Data do consumo *</label>
                                <input type="date" name="data_consumo" value="<?= $editarConsumo ? h(date('Y-m-d', strtotime($editarConsumo['created_at']))) : date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <button class="btn btn-pri"><?= $editarConsumo ? 'Salvar alteração' : 'Registrar consumo' ?></button>
                        <?php if ($editarConsumo): ?>
                            <a class="btn" href="gestao_suprimentos.php?aba=consumo">Cancelar</a>
                        <?php endif; ?>
                    </form>

                    <?php if ($editarConsumo): ?>
                        <form method="post" action="controllers/suprimentos_controller.php" style="display:inline-block;margin-top:8px;" onsubmit="return confirm('Excluir este consumo? A quantidade consumida será devolvida ao estoque atual.');">
                            <input type="hidden" name="acao" value="excluir_consumo">
                            <input type="hidden" name="consumo_id" value="<?= (int) $editarConsumo['id'] ?>">
                            <button type="submit" class="btn btn-danger">Excluir consumo</button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="section-header">
                        <div>
                            <h2>Histórico de consumo</h2>
                            <p>Os consumos registrados podem ser editados. A alteração ajusta o estoque atual automaticamente.</p>
                        </div>
                    </div>

                    <div class="suprimentos-table-wrap">
                        <table class="suprimentos-table">
                            <thead>
                                <tr><th>Data</th><th>Suprimento</th><th>Unidade</th><th>Quantidade</th><th>Ação</th></tr>
                            </thead>
                            <tbody>
                                <?php if (!$consumosHistorico): ?>
                                    <tr><td colspan="5" class="empty-state">Nenhum consumo registrado.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($consumosHistorico as $m): ?>
                                        <tr>
                                            <td><?= h(date('d/m/Y', strtotime($m['data_consumo']))) ?></td>
                                            <td><?= h($m['suprimento_nome']) ?></td>
                                            <td><?= h($m['unidade_medida']) ?></td>
                                            <td><?= nf($m['quantidade']) ?></td>
                                            <td><a class="btn btn-salvar" href="gestao_suprimentos.php?aba=consumo&amp;editar_consumo=<?= (int)$m['id'] ?>">Editar</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="section-header">
                        <div>
                            <h2>Resumo de consumo — últimos 30 dias</h2>
                            <p>Base para acompanhar o ritmo de consumo e planejar reposição.</p>
                        </div>
                    </div>

                    <div class="suprimentos-table-wrap">
                        <table class="suprimentos-table">
                            <thead>
                                <tr><th>Suprimento</th><th>Unidade</th><th>Consumo em 30 dias</th><th>Média diária</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($consumo as $c): ?>
                                    <tr>
                                        <td><?= h($c['nome']) ?></td>
                                        <td><?= h($c['unidade_medida']) ?></td>
                                        <td><?= nf($c['consumo_30']) ?></td>
                                        <td><?= nf((float) $c['consumo_30'] / 30) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($aba === 'demandas'): ?>

                <div class="suprimentos-info">
                    <strong>Necessidade de compra</strong><br>
                    Todos os suprimentos são exibidos abaixo, começando pelos que estão com estoque mais baixo.
                    <br><br>
                    <strong>Normal:</strong> mais de 40% acima do estoque mínimo &nbsp; | &nbsp;
                    <strong>Estoque baixo:</strong> acima do mínimo e até 40% acima do mínimo &nbsp; | &nbsp;
                    <strong>Comprar:</strong> igual ou abaixo do estoque mínimo.
                </div>

                <div class="card">
                    <div class="suprimentos-table-wrap">
                        <table class="suprimentos-table">
                            <thead>
                                <tr>
                                    <th>Suprimento</th>
                                    <th>Unidade</th>
                                    <th>Estoque atual</th>
                                    <th>Estoque mínimo</th>
                                    <th>Situação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$necessidades): ?>
                                    <tr><td colspan="5" class="empty-state">Nenhum suprimento cadastrado.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($necessidades as $s):
                                        $at = (float) $s['estoque_atual'];
                                        $min = (float) $s['estoque_minimo'];
                                        if ($min <= 0) {
                                            // Sem estoque mínimo definido, não há base para calcular a faixa.
                                            $situacao = $at > 0 ? 'Normal' : 'Comprar';
                                            $classe = $at > 0 ? 'ok' : 'critico';
                                        } elseif ($at <= $min) {
                                            $situacao = 'Comprar';
                                            $classe = 'critico';
                                        } elseif ($at <= ($min * 1.40)) {
                                            $situacao = 'Estoque baixo';
                                            $classe = 'baixo';
                                        } else {
                                            $situacao = 'Normal';
                                            $classe = 'ok';
                                        }
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?= h($s['nome']) ?></strong>
                                                <small><?= h($s['codigo']) ?></small>
                                            </td>
                                            <td><?= h($s['unidade_medida']) ?></td>
                                            <td><?= nf($at) ?></td>
                                            <td><?= nf($min) ?></td>
                                            <td><span class="estoque-tag <?= $classe ?>"><?= h($situacao) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
