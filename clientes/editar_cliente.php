<?php
require 'config/conexao.php';

$id = $_GET['id'] ?? null;

// buscar cliente
$sql = "SELECT * FROM baseinformacoes WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();

$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    die("Cliente não encontrado.");
}

// atualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sql = "UPDATE baseinformacoes SET
        nome_completo = :nome,
        e_mail = :email,
        cidade = :cidade
        WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':nome' => $_POST['nome_completo'],
        ':email' => $_POST['e_mail'],
        ':cidade' => $_POST['cidade'],
        ':id' => $id
    ]);

    header("Location: clientes.php");
    exit;
}
?>

<h2>Editar Cliente</h2>

<form method="POST">

    <input type="text" name="nome_completo" value="<?= htmlspecialchars($cliente['nome_completo']) ?>">
    <input type="email" name="e_mail" value="<?= $cliente['e_mail'] ?>">
    <input type="text" name="cidade" value="<?= $cliente['cidade'] ?>">

    <button type="submit">Salvar</button>

</form>