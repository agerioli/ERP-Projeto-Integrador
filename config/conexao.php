<?php
$host = 'sql208.infinityfree.com';
$dbname = 'if0_41748767_projetointegrador';
$user = 'if0_41748767';
$pass = 'Univesp2026';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão");
}