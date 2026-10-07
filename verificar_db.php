<?php
// Configurações
$host = 'sql208.infinityfree.com';
$dbname = 'if0_41748767_projetointegrador';
$user = 'if0_41748767';
$pass = 'Univesp2026';

try {
    // Conectar ao servidor e banco específico
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexão realizada com sucesso!<br>";
    echo "📊 Banco: $dbname<br><br>";
    
    // Listar todas as tabelas
    $tables = $pdo->query("SHOW TABLES");
    
    if ($tables->rowCount() > 0) {
        echo "<strong>Tabelas encontradas:</strong><br>";
        while ($row = $tables->fetch(PDO::FETCH_NUM)) {
            echo "- " . $row[0] . "<br>";
        }
    } else {
        echo "⚠️ Nenhuma tabela encontrada neste banco.";
    }
    
} catch(PDOException $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>