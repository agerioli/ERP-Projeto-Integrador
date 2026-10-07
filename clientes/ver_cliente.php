<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../config/conexao.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID inválido");
}

$sql = "SELECT * FROM baseinformacoes WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();

$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    die("Cliente não encontrado");
}

// formatações básicas
$cpf = $cliente['cpf'];
$cpf_formatado = substr($cpf,0,3).'.'.substr($cpf,3,3).'.'.substr($cpf,6,3).'-'.substr($cpf,9,2);

$cel = $cliente['celular_whatsapp'];
$cel_formatado = strlen($cel) == 11
    ? '('.substr($cel,0,2).') '.substr($cel,2,5).'-'.substr($cel,7,4)
    : $cel;

$data = !empty($cliente['data_de_nascimento'])
    ? date('d/m/Y', strtotime($cliente['data_de_nascimento']))
    : '-';
?>

<h1><?= htmlspecialchars($cliente['nome_completo']) ?></h1>

<p><strong>CPF:</strong> <?= $cpf_formatado ?></p>
<p><strong>Sexo:</strong> <?= htmlspecialchars($cliente['sexo']) ?></p>
<p><strong>Nascimento:</strong> <?= $data ?></p>
<p><strong>Cidade:</strong> <?= htmlspecialchars($cliente['cidade']) ?></p>
<p><strong>E-mail:</strong> <?= htmlspecialchars($cliente['e_mail']) ?></p>
<p><strong>Celular:</strong> <?= $cel_formatado ?></p>
<p><strong>Autorização:</strong> <?= htmlspecialchars($cliente['autorizacao']) ?></p>