<?php
if(session_status()===PHP_SESSION_NONE)session_start();
require_once __DIR__.'/config/conexao.php'; require_once __DIR__.'/auth/verifica_login.php';
function h($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function nf($v){return number_format((float)$v,2,',','.');}
$msg=$_SESSION['compra_msg']??'';$tipo=$_SESSION['compra_tipo']??'success';unset($_SESSION['compra_msg'],$_SESSION['compra_tipo']);
$editar=null;
if(isset($_GET['editar_compra'])){
    $s=$pdo->prepare("SELECT c.*,ci.id item_compra_id,ci.tipo_item,ci.produto_id,ci.suprimento_id,ci.quantidade,ci.quantidade_recebida,ci.preco_unitario,ci.desconto,
        COALESCE(p.nome,su.nome) item_nome FROM compras c JOIN compra_itens ci ON ci.compra_id=c.id
        LEFT JOIN produtos p ON p.id=ci.produto_id LEFT JOIN suprimentos su ON su.id=ci.suprimento_id WHERE c.id=? LIMIT 1");
    $s->execute([(int)$_GET['editar_compra']]); $editar=$s->fetch()?:null;
}
$fornecedores=$pdo->query("SELECT id,COALESCE(NULLIF(nome_fantasia,''),razao_social) nome,tipo_fornecimento FROM fornecedores WHERE status='ATIVO' ORDER BY nome")->fetchAll();
$produtos=$pdo->query("SELECT id,codigo,sku,nome,preco_custo FROM produtos WHERE status='ATIVO' ORDER BY nome")->fetchAll();
$materiais=$pdo->query("SELECT id,codigo,nome,unidade_medida,estoque_atual FROM suprimentos WHERE status='ATIVO' ORDER BY nome")->fetchAll();
$compras=$pdo->query("SELECT c.id,c.numero,c.data_compra,c.tipo,c.status,COALESCE(NULLIF(f.nome_fantasia,''),f.razao_social) fornecedor,ci.tipo_item,ci.quantidade,ci.preco_unitario,COALESCE(p.nome,su.nome) item_nome,COALESCE(p.codigo,su.codigo) codigo FROM compras c JOIN fornecedores f ON f.id=c.fornecedor_id JOIN compra_itens ci ON ci.compra_id=c.id LEFT JOIN produtos p ON p.id=ci.produto_id LEFT JOIN suprimentos su ON su.id=ci.suprimento_id ORDER BY c.data_compra DESC,c.id DESC LIMIT 150")->fetchAll();
require_once __DIR__.'/includes/header.php'; require_once __DIR__.'/includes/sidebar.php';
$editItemId=$editar ? (int)($editar['tipo_item']==='PRODUTO'?$editar['produto_id']:$editar['suprimento_id']) : 0;
$editTipo=$editar ? ($editar['tipo_item']==='PRODUTO'?'PRODUTO':'MATERIAL') : 'PRODUTO';
?>
<div class="main"><div class="topbar"><div><h1>Compras</h1><p>Registre o que foi comprado e atualize automaticamente o estoque da loja.</p></div></div><div class="content"><div class="suprimentos-container">
<?php if($msg): ?><div class="suprimentos-message <?=h($tipo)?>"><?=h($msg)?></div><?php endif; ?>
<div class="suprimentos-info"><strong>Como funciona:</strong> compra de Produto para Venda entra no estoque local; compra de Material de Uso atualiza o estoque interno. O fornecedor é selecionado em um único cadastro.</div>
<div class="suprimentos-form-box"><h2><?= $editar?'Editar compra':'Registrar compra' ?></h2><?php if($editar): ?><p class="form-help">Altere qualquer informação. Ao salvar, o sistema estorna a movimentação anterior e aplica os novos dados ao estoque.</p><?php endif; ?>
<form method="post" action="controllers/compras_vendas_controller.php" id="form-compra"><input type="hidden" name="acao" value="<?= $editar?'editar_compra':'salvar_compra' ?>"><?php if($editar): ?><input type="hidden" name="compra_id" value="<?=$editar['id']?>"><?php endif; ?>
<div class="suprimentos-form-grid"><div><label>Data da compra *</label><input type="date" name="data_compra" value="<?=h($editar['data_compra']??date('Y-m-d'))?>" required></div><div><label>Número / referência</label><input name="numero" value="<?=h($editar['numero']??'')?>" placeholder="Opcional"></div>
<div class="full"><label>Fornecedor *</label><select name="fornecedor_id" id="compra_fornecedor" required><option value="">Selecione</option><?php foreach($fornecedores as $f): ?><option value="<?=$f['id']?>" data-tipo="<?=h($f['tipo_fornecimento']??'PRODUTO')?>" <?=($editar && (int)$editar['fornecedor_id']===(int)$f['id'])?'selected':''?>><?=h($f['nome'])?></option><?php endforeach; ?></select></div>
<div><label>O que foi comprado? *</label><select name="tipo_item" id="compra_tipo" required><option value="PRODUTO" <?=$editTipo==='PRODUTO'?'selected':''?>>Produto para Venda</option><option value="MATERIAL" <?=$editTipo==='MATERIAL'?'selected':''?>>Material de Uso</option></select></div>
<div><label>Item *</label><select name="item_id" id="compra_item" required></select></div>
<div><label>Quantidade *</label><input type="number" name="quantidade" min="0.01" step="0.01" value="<?=h($editar['quantidade']??'')?>" required></div><div><label>Valor unitário *</label><input type="number" name="preco_unitario" id="compra_preco" min="0" step="0.01" value="<?=h($editar['preco_unitario']??'')?>" required></div>
</div><div class="quick-actions"><button class="btn btn-pri"><?= $editar?'Salvar alterações':'Registrar compra e atualizar estoque' ?></button><?php if($editar): ?><a class="btn btn-cancelar" href="compras.php">Cancelar</a><?php endif; ?></div></form></div>
<div class="card"><div class="section-header"><div><h2>Histórico de compras</h2><p>As compras registradas nesta tela refletem diretamente no estoque.</p></div></div><div class="suprimentos-table-wrap"><table class="suprimentos-table"><thead><tr><th>Data</th><th>Fornecedor</th><th>Tipo</th><th>Item</th><th>Quantidade</th><th>Valor unit.</th><th>Total</th><th>Ação</th></tr></thead><tbody><?php foreach($compras as $c): ?><tr><td><?=date('d/m/Y',strtotime($c['data_compra']))?></td><td><?=h($c['fornecedor'])?></td><td><?=h($c['tipo_item']==='PRODUTO'?'Produto para Venda':'Material de Uso')?></td><td><strong><?=h($c['item_nome'])?></strong><small><?=h($c['codigo']??'')?></small></td><td><?=nf($c['quantidade'])?></td><td>R$ <?=nf($c['preco_unitario'])?></td><td>R$ <?=nf($c['quantidade']*$c['preco_unitario'])?></td><td class="acoes-tabela"><a class="btn btn-salvar" href="compras.php?editar_compra=<?=$c['id']?>">Editar</a><form style="display:inline" method="post" action="controllers/compras_vendas_controller.php" onsubmit="return confirm('Excluir esta compra e estornar o estoque?')"><input type="hidden" name="acao" value="excluir_compra"><input type="hidden" name="compra_id" value="<?=$c['id']?>"><button class="btn btn-cancelar">Excluir</button></form></td></tr><?php endforeach; ?><?php if(!$compras): ?><tr><td colspan="8" class="empty">Nenhuma compra registrada.</td></tr><?php endif; ?></tbody></table></div></div>
</div></div></div>
<script>
const produtosCompra=<?=json_encode($produtos,JSON_UNESCAPED_UNICODE)?>;
const materiaisCompra=<?=json_encode($materiais,JSON_UNESCAPED_UNICODE)?>;
const compraInicial={tipo:<?=json_encode($editTipo)?>,itemId:<?=json_encode($editItemId)?>};
(function(){
 const f=document.getElementById('compra_fornecedor'),t=document.getElementById('compra_tipo'),i=document.getElementById('compra_item');
 function render(preserveItem){
   const tipo=t.value; const opt=f.options[f.selectedIndex]; const ft=opt?opt.dataset.tipo:'';
   Array.from(t.options).forEach(o=>o.disabled=!!ft&&ft!=='AMBOS'&&((ft==='PRODUTO'&&o.value!=='PRODUTO')||(ft==='MATERIAL'&&o.value!=='MATERIAL')));
   if(t.options[t.selectedIndex].disabled)t.value=ft==='MATERIAL'?'MATERIAL':'PRODUTO';
   const arr=t.value==='PRODUTO'?produtosCompra:materiaisCompra;
   i.innerHTML='<option value="">Selecione</option>'+arr.map(x=>'<option value="'+x.id+'">'+(x.codigo||'')+' — '+x.nome+'</option>').join('');
   const alvo=preserveItem ? (compraInicial.itemId||'') : '';
   if(alvo)i.value=String(alvo);
 }
 f.addEventListener('change',()=>render(false)); t.addEventListener('change',()=>render(false));
 render(true);
})();
</script>
<?php require_once __DIR__.'/includes/footer.php'; ?>
