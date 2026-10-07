<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../auth/verifica_login.php';

function voltarItem(string $msg='', string $tipo='sucesso') {
    if ($msg !== '') { $_SESSION['itens_msg']=$msg; $_SESSION['itens_tipo']=$tipo; }
    header('Location: ../cadastro_itens.php'); exit;
}
function numItem($v): float { return (float)str_replace(',', '.', (string)($v ?? 0)); }
function uidItem(){ return $_SESSION['usuario_id'] ?? null; }

$acao=$_POST['acao']??'';
try {
    if ($acao !== 'salvar_item') voltarItem();
    $tipo=$_POST['tipo_item']??'';
    $fornecedor=(int)($_POST['fornecedor_id']??0);
    if (!in_array($tipo,['PRODUTO','MATERIAL'],true)) throw new Exception('Selecione um tipo de item válido.');
    $st=$pdo->prepare("SELECT id FROM fornecedores WHERE id=? AND status='ATIVO'"); $st->execute([$fornecedor]);
    if (!$st->fetchColumn()) throw new Exception('Selecione um fornecedor ativo.');
    $codigo=trim($_POST['codigo']??'') ?: null; $nome=trim($_POST['nome']??'');
    $unidade=trim($_POST['unidade_medida']??'UN') ?: 'UN';
    if ($nome==='') throw new Exception('Informe o nome do item.');

    $pdo->beginTransaction();
    if ($tipo==='PRODUTO') {
        $sku=trim($_POST['sku']??'') ?: null; $custo=numItem($_POST['preco_custo']??0); $venda=numItem($_POST['preco_venda']??0);
        $minLocal=numItem($_POST['estoque_minimo_local']??0); $minML=numItem($_POST['estoque_minimo_mercado_livre']??0);
        $qLocal=numItem($_POST['estoque_local_inicial']??0); $qML=numItem($_POST['estoque_ml_inicial']??0);
        if($custo<0||$venda<0||$minLocal<0||$minML<0||$qLocal<0||$qML<0) throw new Exception('Informe valores de estoque e preços válidos.');
        if($sku){$q=$pdo->prepare("SELECT id FROM produtos WHERE sku=? AND status='ATIVO'");$q->execute([$sku]);if($q->fetchColumn())throw new Exception('Já existe um produto ativo com este SKU.');}
        $q=$pdo->prepare("INSERT INTO produtos(codigo,sku,nome,unidade_medida,preco_custo,preco_venda,estoque_minimo,estoque_minimo_local,estoque_minimo_mercado_livre,fornecedor_id,fornecedor_produto_id,status) VALUES(?,?,?,?,?,?,?,?,?,?,NULL,'ATIVO')");
        $q->execute([$codigo,$sku,$nome,$unidade,$custo,$venda,$minLocal,$minLocal,$minML,$fornecedor]); $pid=(int)$pdo->lastInsertId();
        foreach(['FISICO'=>$qLocal,'MERCADO_LIVRE'=>$qML] as $loc=>$qtd){$pdo->prepare("INSERT INTO estoque(produto_id,local_estoque,quantidade,quantidade_reservada) VALUES(?,?,?,0)")->execute([$pid,$loc,$qtd]); if($qtd>0)$pdo->prepare("INSERT INTO movimentacoes_estoque(produto_id,local_estoque,tipo,quantidade,estoque_anterior,estoque_posterior,motivo,usuario_id,referencia_tipo,referencia_id) VALUES(?,?,?,?,?,?,?,?,?,?)")->execute([$pid,$loc,'AJUSTE_ENTRADA',$qtd,0,$qtd,'Estoque inicial - cadastro unificado',uidItem(),'CADASTRO_ITEM',$pid]);}
        $pdo->prepare("INSERT INTO fornecedor_produtos(fornecedor_id,produto_id,preco_unitario,fornecedor_preferencial,observacoes) VALUES(?,?,?,1,'Cadastro unificado V03') ON DUPLICATE KEY UPDATE preco_unitario=VALUES(preco_unitario),fornecedor_preferencial=1")->execute([$fornecedor,$pid,$custo]);
        $pdo->commit(); voltarItem('Produto cadastrado com sucesso.');
    }

    $estoque=numItem($_POST['estoque_atual']??0); $min=numItem($_POST['estoque_minimo']??0);
    if($estoque<0||$min<0) throw new Exception('Informe valores de estoque válidos.');
    $q=$pdo->prepare("INSERT INTO suprimentos(codigo,nome,unidade_medida,estoque_minimo,estoque_atual,status) VALUES(?,?,?,?,?,'ATIVO')");
    $q->execute([$codigo,$nome,$unidade,$min,$estoque]); $sid=(int)$pdo->lastInsertId();
    $pdo->prepare("INSERT INTO fornecedor_suprimentos(fornecedor_id,suprimento_id,preco_unitario,fornecedor_preferencial,observacoes) VALUES(?,?,0,1,'Cadastro unificado V03') ON DUPLICATE KEY UPDATE fornecedor_preferencial=1")->execute([$fornecedor,$sid]);
    if($estoque>0)$pdo->prepare("INSERT INTO movimentacoes_suprimentos(suprimento_id,tipo,quantidade,estoque_anterior,estoque_posterior,motivo,usuario_id) VALUES(?,?,?,?,?,?,?)")->execute([$sid,'AJUSTE_ENTRADA',$estoque,0,$estoque,'Estoque inicial - cadastro unificado',uidItem()]);
    $pdo->commit(); $label = 'Material de Uso';
    voltarItem($label.' cadastrado com sucesso.');
} catch(Throwable $e) {
    if($pdo->inTransaction()) $pdo->rollBack(); voltarItem($e->getMessage(),'erro');
}
