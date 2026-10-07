<?php
if (session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__.'/config/conexao.php'; require_once __DIR__.'/auth/verifica_login.php';
function h($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function formatarCnpj($v){$d=preg_replace('/\D+/','',(string)$v);if(strlen($d)!==14)return $v?:'—';return substr($d,0,2).'.'.substr($d,2,3).'.'.substr($d,5,3).'/'.substr($d,8,4).'-'.substr($d,12,2); }
$msg=$_SESSION['fornecedor_msg']??''; $tipo=$_SESSION['fornecedor_tipo']??'success'; unset($_SESSION['fornecedor_msg'],$_SESSION['fornecedor_tipo']);
$editar=null;
if(isset($_GET['editar'])){$s=$pdo->prepare('SELECT * FROM fornecedores WHERE id=?');$s->execute([(int)$_GET['editar']]);$editar=$s->fetch()?:null;}
$fornecedores=$pdo->query("SELECT f.*, CASE WHEN f.tipo_fornecimento='AMBOS' THEN 'Produto de Venda + Material de Uso' WHEN f.tipo_fornecimento='MATERIAL' THEN 'Material de Uso' ELSE 'Produto de Venda' END tipo_label FROM fornecedores f ORDER BY f.status='ATIVO' DESC, COALESCE(NULLIF(f.nome_fantasia,''),f.razao_social)")->fetchAll();
require_once __DIR__.'/includes/header.php'; require_once __DIR__.'/includes/sidebar.php';
?>
<div class="main"><div class="topbar"><div><h1>Fornecedores</h1><p>Cadastro único para fornecedores de produtos de venda e materiais de uso.</p></div></div><div class="content"><div class="suprimentos-container">
<?php if($msg): ?><div class="suprimentos-message <?=h($tipo)?>"><?=h($msg)?></div><?php endif; ?>
<div class="suprimentos-form-box"><h2><?= $editar?'Editar fornecedor':'Novo fornecedor' ?></h2><p class="form-help">Informe o CNPJ e consulte o CEP para preencher automaticamente o endereço.</p>
<form method="post" action="controllers/fornecedores_controller.php"><input type="hidden" name="acao" value="<?= $editar?'editar':'salvar' ?>"><?php if($editar): ?><input type="hidden" name="fornecedor_id" value="<?=$editar['id']?>"><?php endif; ?>
<div class="suprimentos-form-grid">
<div><label>CNPJ *</label><input name="cnpj" id="cnpj" value="<?=h($editar['cnpj']??'')?>" placeholder="00.000.000/0000-00" inputmode="numeric" maxlength="18" autocomplete="off" required aria-describedby="cnpj-ajuda"><small id="cnpj-ajuda" class="form-help">Digite os 14 números; a pontuação será preenchida automaticamente durante a digitação.</small></div>
<div><label>Razão Social *</label><input name="razao_social" value="<?=h($editar['razao_social']??'')?>" required></div>
<div><label>Nome Fantasia</label><input name="nome_fantasia" value="<?=h($editar['nome_fantasia']??'')?>"></div>
<div><label>Pessoa de contato</label><input name="contato" value="<?=h($editar['contato']??'')?>"></div>
<div><label>Telefone</label><input name="telefone" value="<?=h($editar['telefone']??'')?>"></div>
<div><label>E-mail</label><input type="email" name="email" value="<?=h($editar['email']??'')?>"></div>
<div><label>Tipo de fornecimento *</label><select name="tipo_fornecimento" required><option value="" <?=empty($editar['tipo_fornecimento'])?'selected':''?>>Selecione o que o fornecedor fornece</option><option value="PRODUTO" <?=($editar['tipo_fornecimento']??'')==='PRODUTO'?'selected':''?>>Produto de Venda</option><option value="MATERIAL" <?=($editar['tipo_fornecimento']??'')==='MATERIAL'?'selected':''?>>Material de Uso</option><option value="AMBOS" <?=($editar['tipo_fornecimento']??'')==='AMBOS'?'selected':''?>>Produto de Venda e Material de Uso</option></select></div>
<div><label>CEP *</label><input name="cep" id="cep_fornecedor" value="<?=h($editar['cep']??'')?>" inputmode="numeric" maxlength="9" placeholder="00000-000" required></div>
<div><label>Número *</label><input name="numero" value="<?=h($editar['numero']??'')?>" required></div>
<div class="full"><label>Complemento</label><input name="complemento" value="<?=h($editar['complemento']??'')?>"></div>
<div class="full"><label>Logradouro</label><input name="endereco" id="endereco_fornecedor" value="<?=h($editar['endereco']??'')?>" readonly></div>
<div><label>Bairro</label><input name="bairro" id="bairro_fornecedor" value="<?=h($editar['bairro']??'')?>" readonly></div>
<div><label>Cidade</label><input name="cidade" id="cidade_fornecedor" value="<?=h($editar['cidade']??'')?>" readonly></div>
<div><label>Estado</label><input name="estado" id="estado_fornecedor" value="<?=h($editar['estado']??'')?>" maxlength="2" readonly></div>
<div class="full"><label>Observações</label><textarea name="observacoes"><?=h($editar['observacoes']??'')?></textarea></div>
</div><div class="quick-actions"><button class="btn btn-pri"><?= $editar?'Salvar alterações':'Cadastrar fornecedor' ?></button><?php if($editar): ?><a class="btn btn-cancelar" href="fornecedores.php">Cancelar</a><?php endif; ?></div></form></div>
<div class="card"><div class="section-header"><div><h2>Fornecedores cadastrados</h2><p>Um único cadastro pode atender produtos, materiais de uso ou ambos.</p></div></div><div class="suprimentos-table-wrap"><table class="suprimentos-table"><thead><tr><th>Fornecedor</th><th>CNPJ</th><th>Tipo</th><th>Contato</th><th>Localização</th><th>Status</th><th>Ações</th></tr></thead><tbody><?php foreach($fornecedores as $f): ?><tr><td><strong><?=h($f['nome_fantasia']?:$f['razao_social'])?></strong><small><?=h($f['razao_social'])?></small></td><td><?=h(formatarCnpj($f['cnpj']??''))?></td><td><?=h($f['tipo_label'])?></td><td><?=h($f['telefone']?:'—')?><small><?=h($f['email']?:'')?></small></td><td><?=h($f['cidade']?:'—')?><?= $f['estado']?' / '.h($f['estado']):'' ?></td><td><span class="estoque-tag <?=$f['status']==='ATIVO'?'ok':'atencao'?>"><?=h($f['status'])?></span></td><td><a class="btn btn-salvar" href="fornecedores.php?editar=<?=$f['id']?>">Editar</a><?php if($f['status']==='ATIVO'): ?><form style="display:inline" method="post" action="controllers/fornecedores_controller.php" onsubmit="return confirm('Inativar este fornecedor?')"><input type="hidden" name="acao" value="inativar"><input type="hidden" name="fornecedor_id" value="<?=$f['id']?>"><button class="btn btn-cancelar">Inativar</button></form><?php else: ?><form style="display:inline" method="post" action="controllers/fornecedores_controller.php" onsubmit="return confirm('Reativar este fornecedor?')"><input type="hidden" name="acao" value="reativar"><input type="hidden" name="fornecedor_id" value="<?=$f['id']?>"><button class="btn btn-salvar">Reativar</button></form><?php endif; ?></td></tr><?php endforeach; ?><?php if(!$fornecedores): ?><tr><td colspan="7" class="empty">Nenhum fornecedor cadastrado.</td></tr><?php endif; ?></tbody></table></div></div>
</div></div></div>
<script src="<?=h($assetBase??'/Site')?>/assets/js/cep.js?v=20261003"></script>
<script>document.addEventListener('DOMContentLoaded',function(){if(window.CEPForm)CEPForm.bind('cep_fornecedor',{logradouro:'endereco_fornecedor',bairro:'bairro_fornecedor',cidade:'cidade_fornecedor',uf:'estado_fornecedor'});if(window.CNPJMask)CNPJMask.bind('cnpj');});</script>
<?php require_once __DIR__.'/includes/footer.php'; ?>
