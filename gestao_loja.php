<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__.'/config/conexao.php';
require_once __DIR__.'/auth/verifica_login.php';
function h($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function nf($v){return number_format((float)$v,2,',','.');}
$aba=$_GET['aba']??'visao';
$valid=['visao','mercado_livre','materiais_uso','devolucoes','inventario','historico'];
if(!in_array($aba,$valid,true))$aba='visao';
$msg=$_SESSION['estoque_mensagem']??'';$tipo=$_SESSION['estoque_mensagem_tipo']??'success';unset($_SESSION['estoque_mensagem'],$_SESSION['estoque_mensagem_tipo']);
$fornecedoresProdutos=$pdo->query("SELECT id,COALESCE(NULLIF(nome_fantasia,''),razao_social) AS nome,status FROM fornecedores WHERE status='ATIVO' ORDER BY nome")->fetchAll();
$produtos=$pdo->query("SELECT p.*,COALESCE(NULLIF(fp.nome_fantasia,''),fp.razao_social,'Não informado') fornecedor_nome,
COALESCE((SELECT SUM(e.quantidade) FROM estoque e WHERE e.produto_id=p.id AND e.local_estoque='FISICO'),0) estoque_local,
COALESCE((SELECT SUM(e.quantidade_reservada) FROM estoque e WHERE e.produto_id=p.id AND e.local_estoque='FISICO'),0) reservado_local,
COALESCE((SELECT SUM(e.quantidade) FROM estoque e WHERE e.produto_id=p.id AND e.local_estoque='MERCADO_LIVRE'),0) estoque_ml
FROM produtos p LEFT JOIN fornecedores fp ON fp.id=p.fornecedor_id WHERE p.status<>'INATIVO' ORDER BY p.nome")->fetchAll();
$devolucoes=$pdo->query("SELECT d.*,p.nome produto_nome,p.codigo FROM devolucoes_produtos d JOIN produtos p ON p.id=d.produto_id ORDER BY d.data_devolucao DESC,d.id DESC LIMIT 100")->fetchAll();
$editarDevolucao=null;if($aba==='devolucoes'&&isset($_GET['editar_devolucao'])){$s=$pdo->prepare("SELECT * FROM devolucoes_produtos WHERE id=?");$s->execute([(int)$_GET['editar_devolucao']]);$editarDevolucao=$s->fetch()?:null;}
$pdo->exec("CREATE TABLE IF NOT EXISTS transferencias_mercado_livre (id INT UNSIGNED NOT NULL AUTO_INCREMENT, produto_id INT NOT NULL, quantidade DECIMAL(15,3) NOT NULL, data_transferencia DATE NOT NULL, motivo VARCHAR(255) NULL, usuario_id INT UNSIGNED NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(id), KEY idx_tml_produto(produto_id), CONSTRAINT fk_tml_produto FOREIGN KEY(produto_id) REFERENCES produtos(id) ON UPDATE CASCADE ON DELETE RESTRICT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$transferenciasML=$pdo->query("SELECT t.*,p.nome produto_nome,p.codigo FROM transferencias_mercado_livre t JOIN produtos p ON p.id=t.produto_id ORDER BY t.data_transferencia DESC,t.id DESC LIMIT 100")->fetchAll();
$editarTransferenciaML=null;if($aba==='mercado_livre'&&isset($_GET['editar_transferencia'])){$s=$pdo->prepare("SELECT * FROM transferencias_mercado_livre WHERE id=?");$s->execute([(int)$_GET['editar_transferencia']]);$editarTransferenciaML=$s->fetch()?:null;}
$inicio30=date('Y-m-d 00:00:00',strtotime('-29 days'));$vendas30=$pdo->prepare("SELECT vi.produto_id,SUM(vi.quantidade) qtd FROM venda_itens vi JOIN vendas v ON v.id=vi.venda_id WHERE v.status<>'CANCELADA' AND v.data_venda>=? GROUP BY vi.produto_id");$vendas30->execute([$inicio30]);$vendas30Map=[];foreach($vendas30 as $r)$vendas30Map[(int)$r['produto_id']]=(float)$r['qtd'];
$mlAlertas=[];$mlProximos=[];$comprar=[];$localProximos=[];$localBaixo=[];$maiorVenda=null;$maiorQ=0;
foreach($produtos as &$p){
 $p['disponivel_local']=max(0,(float)$p['estoque_local']-(float)$p['reservado_local']);
 $p['vendas_30']=$vendas30Map[(int)$p['id']]??0;$p['media_diaria']=$p['vendas_30']/30;
 $p['meta_ml']=max((float)$p['estoque_minimo_mercado_livre']*3,$p['media_diaria']*5);
 $p['necessario_ml']=max(0,$p['meta_ml']-(float)$p['estoque_ml']);
 $p['disponivel_transferencia']=max(0,$p['disponivel_local']-(float)$p['estoque_minimo_local']);
 $p['transferir']=min($p['necessario_ml'],$p['disponivel_transferencia']);
 $p['comprar']=max(0,$p['necessario_ml']-$p['transferir']);
 $minLocal=(float)$p['estoque_minimo_local'];$minML=(float)$p['estoque_minimo_mercado_livre'];
 $p['local_status']=$minLocal>0&&$p['disponivel_local']<=$minLocal?'COMPRAR':($minLocal>0&&$p['disponivel_local']<=$minLocal*1.20?'PRÓXIMO DO MÍNIMO':'NORMAL');
 $p['ml_status']=$minML>0&&$p['estoque_ml']<=$minML?'REPOR / TRANSFERIR':($minML>0&&$p['estoque_ml']<=$minML*1.20?'PRÓXIMO DO MÍNIMO':'NORMAL');
 if($minML>0&&$p['estoque_ml']<=$minML*0.10)$mlAlertas[]=$p;
 if($minML>0&&$p['estoque_ml']>$minML*0.10&&$p['estoque_ml']<=$minML*1.20)$mlProximos[]=$p;
 if($minLocal>0&&$p['disponivel_local']<=$minLocal)$localBaixo[]=$p;
 elseif($minLocal>0&&$p['disponivel_local']<=$minLocal*1.20)$localProximos[]=$p;
 if($p['comprar']>0||$p['local_status']==='COMPRAR')$comprar[]=$p;
 if($p['vendas_30']>$maiorQ){$maiorQ=$p['vendas_30'];$maiorVenda=$p;}
}unset($p);
$materiaisUso=$pdo->query("SELECT s.id,s.codigo,s.nome,s.estoque_atual,s.estoque_minimo,COALESCE(NULLIF(f.nome_fantasia,''),f.razao_social,'Não informado') fornecedor_nome FROM suprimentos s LEFT JOIN fornecedor_suprimentos fs ON fs.suprimento_id=s.id AND fs.fornecedor_preferencial=1 LEFT JOIN fornecedores f ON f.id=fs.fornecedor_id WHERE s.status='ATIVO' ORDER BY s.estoque_atual ASC,s.nome")->fetchAll();
$materiaisBaixos=array_values(array_filter($materiaisUso,function($m){return (float)$m['estoque_minimo']>0&&(float)$m['estoque_atual']<=(float)$m['estoque_minimo'];}));
$materiaisProximos=array_values(array_filter($materiaisUso,function($m){return (float)$m['estoque_minimo']>0&&(float)$m['estoque_atual']>(float)$m['estoque_minimo']&&(float)$m['estoque_atual']<=(float)$m['estoque_minimo']*1.20;}));
$valorEstoqueCusto=(float)$pdo->query("SELECT COALESCE(SUM(e.quantidade*p.preco_custo),0) FROM estoque e JOIN produtos p ON p.id=e.produto_id WHERE p.status<>'INATIVO'")->fetchColumn();
$valorMateriaisCusto=(float)$pdo->query("SELECT COALESCE(SUM(s.estoque_atual*COALESCE(fs.preco_unitario,0)),0) FROM suprimentos s LEFT JOIN fornecedor_suprimentos fs ON fs.suprimento_id=s.id AND fs.fornecedor_preferencial=1 WHERE s.status='ATIVO'")->fetchColumn();
$valorCustoTotal=$valorEstoqueCusto+$valorMateriaisCusto;
$perdasMes=(float)$pdo->query("SELECT COALESCE(SUM(valor_perda),0) FROM devolucoes_produtos WHERE avaria=1 AND data_devolucao>=DATE_FORMAT(CURDATE(),'%Y-%m-01')")->fetchColumn();
$vendasMes=(float)$pdo->query("SELECT COALESCE(SUM(valor_total),0) FROM vendas WHERE status<>'CANCELADA' AND data_venda>=DATE_FORMAT(CURDATE(),'%Y-%m-01')")->fetchColumn();
$custoProdutosMes=(float)$pdo->query("SELECT COALESCE(SUM(vi.quantidade*COALESCE(p.preco_custo,0)),0) FROM vendas v JOIN venda_itens vi ON vi.venda_id=v.id JOIN produtos p ON p.id=vi.produto_id WHERE v.status<>'CANCELADA' AND v.data_venda>=DATE_FORMAT(CURDATE(),'%Y-%m-01')")->fetchColumn();
$resultadoLiquidoMes=$vendasMes-$custoProdutosMes-$perdasMes;
$movimentacoesEstoque=$pdo->query("SELECT m.id,m.created_at,p.codigo,p.nome produto_nome,m.local_estoque,m.tipo,m.quantidade,m.estoque_anterior,m.estoque_posterior,m.motivo FROM movimentacoes_estoque m JOIN produtos p ON p.id=m.produto_id ORDER BY m.created_at DESC,m.id DESC LIMIT 150")->fetchAll();
$movimentacoesMateriais=$pdo->query("SELECT m.id,m.created_at,s.codigo,s.nome produto_nome,'MATERIAL_USO' local_estoque,m.tipo,m.quantidade,m.estoque_anterior,m.estoque_posterior,m.motivo FROM movimentacoes_suprimentos m JOIN suprimentos s ON s.id=m.suprimento_id ORDER BY m.created_at DESC,m.id DESC LIMIT 150")->fetchAll();
$movimentacoes=array_merge($movimentacoesEstoque,$movimentacoesMateriais);
usort($movimentacoes,function($a,$b){$ta=strtotime($a['created_at']);$tb=strtotime($b['created_at']);return $tb<=>$ta;});
$movimentacoes=array_slice($movimentacoes,0,250);
$mesSelecionado=preg_match('/^\d{4}-\d{2}$/',$_GET['mes']??'')?$_GET['mes']:date('Y-m');
$inicioMes=$mesSelecionado.'-01';$inicioProx=date('Y-m-d',strtotime($inicioMes.' +1 month'));$inicioAnterior=date('Y-m-d',strtotime($inicioMes.' -1 month'));$fimAnterior=$inicioMes;
function resumoMes(PDO $pdo,string $inicio,string $fim):array{
 $s=$pdo->prepare("SELECT COALESCE(SUM(valor_total),0) FROM vendas WHERE status<>'CANCELADA' AND data_venda>=? AND data_venda<?");$s->execute([$inicio,$fim]);$v=(float)$s->fetchColumn();
 $s=$pdo->prepare("SELECT COALESCE(SUM(vi.quantidade),0) FROM vendas v JOIN venda_itens vi ON vi.venda_id=v.id WHERE v.status<>'CANCELADA' AND v.data_venda>=? AND v.data_venda<?");$s->execute([$inicio,$fim]);$q=(float)$s->fetchColumn();
 $s=$pdo->prepare("SELECT COALESCE(SUM(vi.quantidade*COALESCE(p.preco_custo,0)),0) FROM vendas v JOIN venda_itens vi ON vi.venda_id=v.id JOIN produtos p ON p.id=vi.produto_id WHERE v.status<>'CANCELADA' AND v.data_venda>=? AND v.data_venda<?");$s->execute([$inicio,$fim]);$c=(float)$s->fetchColumn();
 $s=$pdo->prepare("SELECT COALESCE(SUM(valor_perda),0) FROM devolucoes_produtos WHERE avaria=1 AND data_devolucao>=? AND data_devolucao<?");$s->execute([$inicio,$fim]);$p=(float)$s->fetchColumn();
 $resultado=$v-$c-$p; return ['vendas'=>$v,'qtd'=>$q,'custo'=>$c,'perdas'=>$p,'resultado'=>$resultado,'margem'=>$v>0?($resultado/$v*100):0,'ticket'=>$q>0?$v/$q:0];
}
$resumoAtual=resumoMes($pdo,$inicioMes,$inicioProx);$resumoAnterior=resumoMes($pdo,$inicioAnterior,$fimAnterior);
function variacao($atual,$anterior){if(abs((float)$anterior)<0.000001)return null;return (($atual-$anterior)/$anterior)*100;}
$varVendas=variacao($resumoAtual['vendas'],$resumoAnterior['vendas']);$varResultado=variacao($resumoAtual['resultado'],$resumoAnterior['resultado']);$varQtd=variacao($resumoAtual['qtd'],$resumoAnterior['qtd']);
$usoMateriais=$pdo->query("SELECT c.*,s.codigo,s.nome,s.estoque_atual,s.estoque_minimo FROM consumos_materiais c JOIN suprimentos s ON s.id=c.material_id ORDER BY CASE WHEN s.estoque_minimo>0 AND s.estoque_atual<=s.estoque_minimo THEN 0 WHEN s.estoque_minimo>0 AND s.estoque_atual<=s.estoque_minimo*1.20 THEN 1 ELSE 2 END, c.data_uso DESC,c.id DESC LIMIT 200")->fetchAll();

require_once __DIR__.'/includes/header.php';require_once __DIR__.'/includes/sidebar.php';
?>
<div class="main"><div class="topbar"><div><h1>Gestão da Loja</h1><p>Visão geral das ações da loja, estoque, reposição, transferências e custos.</p></div></div><div class="content"><div class="suprimentos-container">
<?php if($msg): ?><div class="suprimentos-message <?=h($tipo==='erro'?'error':'success')?>"><?=h($msg)?></div><?php endif; ?>
<nav class="suprimentos-tabs">
<?php foreach(['visao'=>'Visão Geral','mercado_livre'=>'Mercado Livre','materiais_uso'=>'Materiais de Uso','devolucoes'=>'Devolução','inventario'=>'Inventário','historico'=>'Histórico'] as $k=>$label): ?><a class="<?=$aba===$k?'active':''?>" href="gestao_loja.php?aba=<?=$k?>"><?=h($label)?></a><?php endforeach; ?>
</nav>
<?php if($aba==='visao'): ?>
<div class="loja-dashboard executive-dashboard">
  <div class="dashboard-head card">
    <div class="dashboard-head-copy"><span class="dashboard-eyebrow">PAINEL GERENCIAL</span><h2>Resumo da operação</h2><p>Uma visão rápida de vendas, custos, estoque, devoluções e ações que precisam de atenção.</p></div>
    <form method="get" class="mes-selector dashboard-month"><input type="hidden" name="aba" value="visao"><label for="mes">Período</label><div><input id="mes" type="month" name="mes" value="<?=h($mesSelecionado)?>"><button class="btn btn-pri">Atualizar</button></div></form>
  </div>

  <div class="dashboard-kpis executive-kpis">
    <div class="dashboard-kpi kpi-blue"><div class="kpi-icon">🛒</div><div><span>Vendas</span><strong>R$ <?=nf($resumoAtual['vendas'])?></strong><small><?=is_null($varVendas)?'Sem base anterior':(($varVendas>=0?'▲ ':'▼ ').nf(abs($varVendas)).'% vs. mês anterior')?></small></div></div>
    <div class="dashboard-kpi kpi-green"><div class="kpi-icon">📦</div><div><span>Unidades vendidas</span><strong><?=nf($resumoAtual['qtd'])?> un.</strong><small><?=is_null($varQtd)?'Sem base anterior':(($varQtd>=0?'▲ ':'▼ ').nf(abs($varQtd)).'% vs. mês anterior')?></small></div></div>
    <div class="dashboard-kpi kpi-amber"><div class="kpi-icon">💰</div><div><span>Custo das vendas</span><strong>R$ <?=nf($resumoAtual['custo'])?></strong><small>Produtos vendidos no período</small></div></div>
    <div class="dashboard-kpi kpi-red"><div class="kpi-icon">📈</div><div><span>Resultado operacional</span><strong>R$ <?=nf($resumoAtual['resultado'])?></strong><small><?=is_null($varResultado)?'Sem base anterior':(($varResultado>=0?'▲ ':'▼ ').nf(abs($varResultado)).'% vs. mês anterior')?></small></div></div>
    <div class="dashboard-kpi kpi-purple"><div class="kpi-icon">%</div><div><span>Margem</span><strong><?=nf($resumoAtual['margem'])?>%</strong><small>Após custos e perdas</small></div></div>
  </div>

  <div class="dashboard-main-grid">
    <div class="card dashboard-panel sales-panel">
      <div class="panel-heading"><div><h3>Vendas e resultado</h3><p>Comparação com o mês anterior.</p></div><span class="panel-icon">📊</span></div>
      <div class="metric-rows">
        <div class="metric-row"><span>Vendas</span><b>R$ <?=nf($resumoAtual['vendas'])?></b><em class="<?=is_null($varVendas)?'neutral':($varVendas>=0?'positive':'negative')?>"><?=is_null($varVendas)?'—':(($varVendas>=0?'▲ ':'▼ ').nf(abs($varVendas)).'%')?></em></div>
        <div class="metric-row"><span>Resultado</span><b>R$ <?=nf($resumoAtual['resultado'])?></b><em class="<?=is_null($varResultado)?'neutral':($varResultado>=0?'positive':'negative')?>"><?=is_null($varResultado)?'—':(($varResultado>=0?'▲ ':'▼ ').nf(abs($varResultado)).'%')?></em></div>
        <div class="metric-row"><span>Ticket médio por unidade</span><b>R$ <?=nf($resumoAtual['ticket'])?></b><em>Anterior: R$ <?=nf($resumoAnterior['ticket'])?></em></div>
        <div class="metric-row"><span>Perdas / avarias</span><b>R$ <?=nf($resumoAtual['perdas'])?></b><em><?= $resumoAtual['perdas']>0?'Atenção':'Sem perdas' ?></em></div>
      </div>
    </div>
    <div class="card dashboard-panel stock-panel">
      <div class="panel-heading"><div><h3>Resumo de estoque</h3><p>Posição atual para compra e reposição.</p></div><span class="panel-icon">📦</span></div>
      <div class="stock-cards">
        <div class="stock-card stock-cost"><span>Valor de custo</span><strong>R$ <?=nf($valorCustoTotal)?></strong></div>
        <div class="stock-card stock-danger"><span>Itens para comprar</span><strong><?=count($comprar)+count($materiaisBaixos)?></strong></div>
        <div class="stock-card stock-warning"><span>Próximos do mínimo</span><strong><?=count($localProximos)+count($mlProximos)+count($materiaisProximos)?></strong></div>
        <div class="stock-card stock-info"><span>Transferências sugeridas</span><strong><?=count($mlAlertas)+count($mlProximos)?></strong></div>
      </div>
    </div>
  </div>

  <div class="dashboard-secondary-grid">
    <div class="card dashboard-panel occurrence-panel"><div class="panel-heading"><div><h3>Ocorrências</h3><p>Informações relevantes para decisão.</p></div><span class="panel-icon">🔎</span></div><div class="occurrence-list"><div><span>Devoluções / perdas</span><b>R$ <?=nf($resumoAtual['perdas'])?></b></div><div><span>Unidades vendidas</span><b><?=nf($resumoAtual['qtd'])?></b></div><div><span>Maior saída · 30 dias</span><b><?=h($maiorVenda['nome']??'—')?></b></div></div></div>
    <div class="card decision-banner executive-decision"><div class="decision-icon">💡</div><div><span class="dashboard-eyebrow">LEITURA PARA DECISÃO</span><h3><?= $resumoAtual['resultado']>=0 ? 'A operação apresenta resultado positivo no período.' : 'O resultado do período exige atenção aos custos e perdas.' ?></h3><p>Consulte Mercado Livre para transferências, Materiais de Uso para consumo e Inventário para manter os saldos atualizados.</p></div></div>
  </div>

  <div class="card actions-panel"><div class="panel-heading"><div><h3>Prioridades da operação</h3><p>O que merece atenção primeiro.</p></div><span class="panel-icon">🎯</span></div><div class="priority-grid"><div class="priority priority-red"><span>🛒</span><div><b>Comprar</b><small><?=count($comprar)+count($materiaisBaixos)?> item(ns) abaixo do mínimo</small></div></div><div class="priority priority-amber"><span>⚠️</span><div><b>Revisar estoque</b><small><?=count($localProximos)+count($mlProximos)+count($materiaisProximos)?> item(ns) próximos do mínimo</small></div></div><div class="priority priority-blue"><span>🚚</span><div><b>Mercado Livre</b><small><?=count($mlAlertas)+count($mlProximos)?> transferência(s) sugerida(s)</small></div></div></div></div>
</div>
<?php elseif($aba==='materiais_uso'): ?>
<div class="card materials-card"><div class="section-header"><div><span class="dashboard-eyebrow">CONTROLE DE CONSUMO</span><h2>Materiais de Uso</h2><p>Use esta aba para <strong>ajustar o estoque mínimo</strong>, <strong>registrar o uso dos materiais</strong> e acompanhar o histórico de consumo. Os itens com status <strong>COMPRAR</strong> aparecem primeiro.</p></div></div>
<div class="suprimentos-table-wrap"><table class="suprimentos-table materials-table"><thead><tr><th>Material</th><th>Estoque atual</th><th>Estoque mínimo</th><th>Status</th><th>Ação</th></tr></thead><tbody>
<?php foreach($materiaisUso as $m): $ma=(float)$m['estoque_atual'];$mm=(float)$m['estoque_minimo'];$st=$mm>0&&$ma<=$mm?'COMPRAR':($mm>0&&$ma<=$mm*1.20?'PRÓXIMO DO MÍNIMO':'NORMAL'); ?>
<tr><td><strong><?=h($m['nome'])?></strong><small><?=h($m['codigo'])?></small></td><td><?=nf($ma)?></td><td><form method="post" action="controllers/estoque_controller.php" class="inline-min-form"><input type="hidden" name="acao" value="atualizar_minimo_material"><input type="hidden" name="material_id" value="<?=$m['id']?>"><input type="number" min="0" step="0.01" name="estoque_minimo" value="<?=h($mm)?>"><button class="btn btn-salvar">Salvar</button></form></td><td><span class="estoque-tag <?=$st==='COMPRAR'?'critico':($st==='PRÓXIMO DO MÍNIMO'?'atencao':'ok')?>"><?=h($st)?></span></td><td class="material-action"><a class="btn btn-salvar" href="uso_materiais.php?material_id=<?=$m['id']?>">Registrar Uso</a></td></tr>
<?php endforeach; ?>
</tbody></table></div></div>
<div class="card"><div class="section-header"><div><h2>Histórico de uso</h2><p>O consumo reduz o estoque e pode gerar automaticamente a necessidade de compra.</p></div></div><div class="suprimentos-table-wrap"><table class="suprimentos-table"><thead><tr><th>Data</th><th>Material</th><th>Quantidade</th><th>Estoque atual</th><th>Status atual</th><th>Motivo</th><th>Ação</th></tr></thead><tbody><?php foreach($usoMateriais as $u): $ua=(float)$u['estoque_atual'];$um=(float)$u['estoque_minimo'];$ust=$um>0&&$ua<=$um?'COMPRAR':($um>0&&$ua<=$um*1.20?'PRÓXIMO DO MÍNIMO':'NORMAL'); ?><tr><td><?=date('d/m/Y',strtotime($u['data_uso']))?></td><td><?=h($u['nome'])?><small><?=h($u['codigo'])?></small></td><td><?=nf($u['quantidade'])?></td><td><?=nf($ua)?></td><td><span class="estoque-tag <?=$ust==='COMPRAR'?'critico':($ust==='PRÓXIMO DO MÍNIMO'?'atencao':'ok')?>"><?=h($ust)?></span></td><td><?=h($u['motivo']??'—')?></td><td><a class="btn btn-xs btn-xs-pri" href="uso_materiais.php?editar=<?=h($u['id'])?>">Editar</a></td></tr><?php endforeach; ?><?php if(!$usoMateriais): ?><tr><td colspan="7">Nenhum uso de material registrado.</td></tr><?php endif; ?></tbody></table></div></div>
<?php elseif($aba==='devolucoes'): ?>
<div class="suprimentos-form-box"><h2><?=$editarDevolucao?'Editar devolução':'Registrar devolução'?></h2><p class="form-help">Devolução aproveitável volta para o estoque LOCAL. Se marcar avaria, vira PERDA e não retorna ao estoque.</p><form method="post" action="controllers/estoque_controller.php"><input type="hidden" name="acao" value="<?=$editarDevolucao?'editar_devolucao':'salvar_devolucao'?>"><?php if($editarDevolucao): ?><input type="hidden" name="devolucao_id" value="<?=$editarDevolucao['id']?>"><?php endif; ?><div class="suprimentos-form-grid"><div class="full"><label>Produto *</label><select name="produto_id" required><option value="">Selecione</option><?php foreach($produtos as $p): ?><option value="<?=$p['id']?>" <?=((int)($editarDevolucao['produto_id']??0)===(int)$p['id'])?'selected':''?>><?=h($p['codigo'])?> — <?=h($p['nome'])?></option><?php endforeach; ?></select></div><div><label>Data da devolução *</label><input type="date" name="data_devolucao" value="<?=h($editarDevolucao['data_devolucao']??date('Y-m-d'))?>" required></div><div><label>Quantidade *</label><input type="number" step="0.01" min="0.01" name="quantidade" value="<?=h($editarDevolucao['quantidade']??'')?>" required></div><div><label>Origem *</label><select name="origem"><option value="LOCAL" <?=($editarDevolucao['origem']??'LOCAL')==='LOCAL'?'selected':''?>>Venda local</option><option value="MERCADO_LIVRE" <?=($editarDevolucao['origem']??'')==='MERCADO_LIVRE'?'selected':''?>>Mercado Livre</option></select></div><div><label>É avaria? *</label><select name="avaria"><option value="0" <?=empty($editarDevolucao['avaria'])?'selected':''?>>Não — retorna ao estoque local</option><option value="1" <?=!empty($editarDevolucao['avaria'])?'selected':''?>>Sim — baixar como perda</option></select></div><div class="full"><label>Motivo da devolução *</label><input name="motivo" value="<?=h($editarDevolucao['motivo']??'')?>" required></div><div class="full"><label>Observações</label><textarea name="observacoes"><?=h($editarDevolucao['observacoes']??'')?></textarea></div></div><button class="btn btn-pri"><?=$editarDevolucao?'Salvar alterações':'Registrar devolução'?></button><?php if($editarDevolucao): ?><a class="btn btn-cancelar" href="gestao_loja.php?aba=devolucoes">Cancelar</a><?php endif; ?></form></div>
<div class="card"><div class="section-header"><div><h2>Histórico de devoluções</h2><p>Perdas por avaria ficam separadas para o dashboard mensal.</p></div></div><div class="suprimentos-table-wrap"><table class="suprimentos-table"><thead><tr><th>Data</th><th>Produto</th><th>Origem</th><th>Qtd.</th><th>Resultado</th><th>Motivo</th><th>Perda</th><th>Ação</th></tr></thead><tbody><?php foreach($devolucoes as $d): ?><tr><td><?=date('d/m/Y',strtotime($d['data_devolucao']))?></td><td><?=h($d['produto_nome'])?><small><?=h($d['codigo'])?></small></td><td><?=h($d['origem']==='MERCADO_LIVRE'?'Mercado Livre':'Local')?></td><td><?=nf($d['quantidade'])?></td><td><span class="estoque-tag <?=$d['avaria']?'critico':'ok'?>"><?=$d['avaria']?'PERDA / AVARIA':'RETORNOU AO ESTOQUE'?></span></td><td><?=h($d['motivo'])?></td><td>R$ <?=nf($d['valor_perda'])?></td><td><a class="btn btn-salvar" href="gestao_loja.php?aba=devolucoes&editar_devolucao=<?=$d['id']?>">Editar</a></td></tr><?php endforeach; ?></tbody></table></div></div>

<?php elseif($aba==='mercado_livre'): ?>
<div class="suprimentos-info">O estoque mínimo do Mercado Livre é editável. Ao salvar, a Gestão da Loja recalcula automaticamente a meta de reposição e as sugestões de transferência.</div>
<div class="card"><div class="section-header"><div><h2>Estoque Mercado Livre</h2><p>O sistema considera o mínimo configurado e, para a meta, utiliza o maior valor entre 3× o mínimo e 5 dias da média de vendas.</p></div></div><div class="suprimentos-table-wrap"><table class="suprimentos-table"><thead><tr><th>Produto</th><th>Estoque Local</th><th>Estoque ML</th><th>Mín. ML</th><th>Meta ML</th><th>Sugerido</th><th>Comprar</th><th>Ação</th></tr></thead><tbody><?php foreach($produtos as $p): ?><tr><td><strong><?=h($p['nome'])?></strong><small><?=h($p['codigo'])?></small></td><td><?=nf($p['disponivel_local'])?></td><td><?=nf($p['estoque_ml'])?></td><td><form method="post" action="controllers/estoque_controller.php" class="inline-min-form"><input type="hidden" name="acao" value="atualizar_minimo_ml"><input type="hidden" name="produto_id" value="<?=$p['id']?>"><input type="number" name="estoque_minimo_mercado_livre" min="0" step="0.01" value="<?=h($p['estoque_minimo_mercado_livre'])?>"><button class="btn btn-salvar" title="Salvar mínimo ML">Salvar</button></form></td><td><?=nf($p['meta_ml'])?></td><td><?=nf($p['transferir'])?></td><td><?=nf($p['comprar'])?></td><td><?php if($p['estoque_local']>0): ?><form method="post" action="controllers/estoque_controller.php" class="transfer-form" style="display:flex;gap:4px;align-items:center;flex-wrap:wrap"><input type="hidden" name="acao" value="enviar_mercado_livre"><input type="hidden" name="produto_id" value="<?=$p['id']?>"><input type="number" name="quantidade" value="<?=h($p['transferir']>0?$p['transferir']:'')?>" min="0.01" max="<?=h($p['estoque_local'])?>" step="0.01" placeholder="Qtd." style="width:80px"><button class="btn btn-salvar">Transferir</button></form><?php elseif($p['comprar']>0): ?><span class="estoque-tag critico">COMPRAR</span><?php else: ?><span class="estoque-tag ok">SEM ESTOQUE LOCAL</span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div>
<div class="card"><div class="section-header"><div><h2>Transferências realizadas</h2><p>Histórico das transferências para o Mercado Livre.</p></div></div><div class="suprimentos-table-wrap"><table class="suprimentos-table"><thead><tr><th>Data</th><th>Produto</th><th>Quantidade</th><th>Motivo</th><th>Ação</th></tr></thead><tbody><?php foreach($transferenciasML as $t): ?><tr><td><?=date('d/m/Y',strtotime($t['data_transferencia']))?></td><td><?=h($t['produto_nome'])?><small><?=h($t['codigo'])?></small></td><td><?=nf($t['quantidade'])?></td><td><?=h($t['motivo']??'—')?></td><td><a class="btn btn-salvar" href="gestao_loja.php?aba=mercado_livre&editar_transferencia=<?=$t['id']?>">Editar</a> <form style="display:inline" method="post" action="controllers/estoque_controller.php" onsubmit="return confirm('Excluir esta transferência e devolver a quantidade ao estoque local?')"><input type="hidden" name="acao" value="excluir_transferencia_ml"><input type="hidden" name="transferencia_id" value="<?=$t['id']?>"><button class="btn btn-cancelar">Excluir</button></form></td></tr><?php endforeach; ?><?php if(!$transferenciasML): ?><tr><td colspan="5">Nenhuma transferência registrada.</td></tr><?php endif; ?></tbody></table></div></div>

<?php elseif($aba==='inventario'): ?>
<div class="suprimentos-form-box"><h2>Conferência de inventário</h2><p class="form-help">Ajuste tanto produtos de venda quanto Materiais de Uso. Todos os ajustes ficam registrados no histórico.</p>
<div class="card"><h3>Produto de Venda</h3><form method="post" action="controllers/estoque_controller.php"><input type="hidden" name="acao" value="ajustar_inventario"><div class="suprimentos-form-grid"><div class="full"><label>Produto *</label><select name="produto_id" required><option value="">Selecione</option><?php foreach($produtos as $p): ?><option value="<?=$p['id']?>"><?=h($p['codigo'])?> — <?=h($p['nome'])?></option><?php endforeach; ?></select></div><div><label>Local *</label><select name="local_estoque"><option value="FISICO">Local</option><option value="MERCADO_LIVRE">Mercado Livre</option></select></div><div><label>Quantidade encontrada *</label><input type="number" min="0" step="0.01" name="quantidade_real" required></div><div class="full"><label>Motivo</label><input name="motivo" value="Inventário físico"></div></div><button class="btn btn-pri">Conferir produto</button></form></div>
<div class="card"><h3>Material de Uso</h3><form method="post" action="controllers/estoque_controller.php"><input type="hidden" name="acao" value="ajustar_inventario_material"><div class="suprimentos-form-grid"><div class="full"><label>Material *</label><select name="material_id" required><option value="">Selecione</option><?php foreach($materiaisUso as $m): ?><option value="<?=$m['id']?>"><?=h($m['codigo'])?> — <?=h($m['nome'])?></option><?php endforeach; ?></select></div><div><label>Quantidade encontrada *</label><input type="number" min="0" step="0.01" name="quantidade_real" required></div><div class="full"><label>Motivo</label><input name="motivo" value="Inventário de Material de Uso"></div></div><button class="btn btn-pri">Conferir material</button></form></div></div>
<?php elseif($aba==='historico'): ?>
<div class="card"><div class="section-header"><div><h2>Histórico de movimentações</h2><p>Entradas, saídas, transferências, devoluções, perdas e ajustes.</p></div></div><div class="suprimentos-table-wrap"><table class="suprimentos-table"><thead><tr><th>Data</th><th>Produto</th><th>Local</th><th>Tipo</th><th>Qtd.</th><th>Antes</th><th>Depois</th><th>Motivo</th></tr></thead><tbody><?php foreach($movimentacoes as $m): ?><tr><td><?=date('d/m/Y H:i',strtotime($m['created_at']))?></td><td><?=h($m['produto_nome'])?><small><?=h($m['codigo'])?></small></td><td><?=h($m['local_estoque']==='FISICO'?'Local':($m['local_estoque']==='MATERIAL_USO'?'Material de Uso':'Mercado Livre'))?></td><td><?=h($m['tipo'])?></td><td><?=nf($m['quantidade'])?></td><td><?=nf($m['estoque_anterior'])?></td><td><?=nf($m['estoque_posterior'])?></td><td><?=h($m['motivo'])?></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php endif; ?>
</div></div></div>
<?php require_once __DIR__.'/includes/footer.php'; ?>
