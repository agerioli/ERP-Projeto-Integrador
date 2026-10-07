<?php
session_start();

$host = 'sql208.infinityfree.com';
$dbname = 'if0_41748767_projetointegrador';
$user = 'if0_41748767';
$pass = 'Univesp2026';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configuração de paginação
    $itens_por_pagina = 10;
    $pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $offset = ($pagina_atual - 1) * $itens_por_pagina;
    
    // Configuração de busca e ordenação
    $busca = isset($_GET['busca']) ? $_GET['busca'] : '';
    $ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'nome_completo';
    $ordem = isset($_GET['ordem']) ? $_GET['ordem'] : 'ASC';
    
    // Construir query com busca
    $where = "";
    if (!empty($busca)) {
        $where = "WHERE nome_completo LIKE :busca 
                  OR cpf LIKE :busca 
                  OR e_mail LIKE :busca 
                  OR cidade LIKE :busca";
    }
    
    // Contar total de registros
    $sql_count = "SELECT COUNT(*) as total FROM baseinformacoes $where";
    $stmt_count = $pdo->prepare($sql_count);
    if (!empty($busca)) {
        $busca_param = "%$busca%";
        $stmt_count->bindParam(':busca', $busca_param);
    }
    $stmt_count->execute();
    $total_registros = $stmt_count->fetch()['total'];
    $total_paginas = ceil($total_registros / $itens_por_pagina);
    
    // Buscar clientes com paginação e ordenação
    $sql = "SELECT * FROM baseinformacoes $where 
            ORDER BY $ordenar $ordem 
            LIMIT :offset, :itens_por_pagina";
    
    $stmt = $pdo->prepare($sql);
    
    if (!empty($busca)) {
        $stmt->bindParam(':busca', $busca_param);
    }
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':itens_por_pagina', $itens_por_pagina, PDO::PARAM_INT);
    $stmt->execute();
    
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $erro = "Erro ao listar clientes: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Clientes - Sistema LGPD</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #185FA5 0%, #0e4680 100%);
            color: white;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .header h1 {
            font-size: 28px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .btn-cadastro {
            background: white;
            color: #185FA5;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s;
        }
        
        .btn-cadastro:hover {
            transform: translateY(-2px);
        }
        
        .content {
            padding: 30px;
        }
        
        .barra-ferramentas {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .busca {
            flex: 1;
            min-width: 200px;
            display: flex;
            gap: 10px;
        }
        
        .busca input {
            flex: 1;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .busca button {
            padding: 10px 20px;
            background: #185FA5;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .estatisticas {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .estatistica-item {
            background: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            color: #185FA5;
        }
        
        .tabela-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
            cursor: pointer;
            user-select: none;
        }
        
        th:hover {
            background: #e9ecef;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .tag {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .tag-autorizado {
            background: #d4edda;
            color: #155724;
        }
        
        .tag-nao-autorizado {
            background: #f8d7da;
            color: #721c24;
        }
        
        .tag-masculino {
            background: #cce5ff;
            color: #004085;
        }
        
        .tag-feminino {
            background: #fce4ec;
            color: #c2185b;
        }
        
        .acoes {
            display: flex;
            gap: 8px;
        }
        
        .btn-acao {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-ver {
            background: #185FA5;
            color: white;
        }
        
        .btn-editar {
            background: #ffc107;
            color: #333;
        }
        
        .btn-excluir {
            background: #dc3545;
            color: white;
        }
        
        .paginacao {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .pagina {
            padding: 8px 15px;
            background: #f8f9fa;
            border-radius: 8px;
            text-decoration: none;
            color: #185FA5;
            transition: all 0.2s;
        }
        
        .pagina.ativa {
            background: #185FA5;
            color: white;
        }
        
        .pagina:hover:not(.ativa) {
            background: #e0e0e0;
        }
        
        .sem-dados {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        .mensagem {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .mensagem.success {
            background: #d4edda;
            color: #155724;
        }
        
        .mensagem.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .seta-ordem {
            margin-left: 5px;
            font-size: 10px;
        }
        
        @media (max-width: 768px) {
            th, td {
                font-size: 12px;
                padding: 8px;
            }
            
            .barra-ferramentas {
                flex-direction: column;
            }
            
            .estatisticas {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>📋 Lista de Clientes</h1>
                <p>Total de clientes cadastrados: <strong><?php echo $total_registros; ?></strong></p>
            </div>
            <a href="cadastro_cliente.php" class="btn-cadastro">➕ Novo Cadastro</a>
        </div>
        
        <div class="content">
            <?php if(isset($_SESSION['mensagem'])): ?>
                <div class="mensagem <?php echo $_SESSION['tipo']; ?>">
                    <?php 
                        echo $_SESSION['mensagem']; 
                        unset($_SESSION['mensagem']);
                        unset($_SESSION['tipo']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="barra-ferramentas">
                <form method="GET" class="busca">
                    <input type="text" name="busca" placeholder="Buscar por nome, CPF, email ou cidade..." 
                           value="<?php echo htmlspecialchars($busca); ?>">
                    <button type="submit">🔍 Buscar</button>
                    <?php if(!empty($busca)): ?>
                        <a href="lista_clientes.php" class="btn-acao btn-ver" style="background:#6c757d;">Limpar</a>
                    <?php endif; ?>
                </form>
                
                <div class="estatisticas">
                    <div class="estatistica-item">
                        📊 Mostrando <?php echo count($clientes); ?> de <?php echo $total_registros; ?>
                    </div>
                </div>
            </div>
            
            <div class="tabela-container">
                <?php if(count($clientes) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th onclick="ordenar('cpf')">CPF 
                                    <?php if($ordenar == 'cpf'): ?>
                                        <span class="seta-ordem"><?php echo $ordem == 'ASC' ? '↑' : '↓'; ?></span>
                                    <?php endif; ?>
                                </th>
                                <th onclick="ordenar('nome_completo')">Nome 
                                    <?php if($ordenar == 'nome_completo'): ?>
                                        <span class="seta-ordem"><?php echo $ordem == 'ASC' ? '↑' : '↓'; ?></span>
                                    <?php endif; ?>
                                </th>
                                <th onclick="ordenar('sexo')">Sexo
                                    <?php if($ordenar == 'sexo'): ?>
                                        <span class="seta-ordem"><?php echo $ordem == 'ASC' ? '↑' : '↓'; ?></span>
                                    <?php endif; ?>
                                </th>
                                <th onclick="ordenar('data_de_nascimento')">Nascimento
                                    <?php if($ordenar == 'data_de_nascimento'): ?>
                                        <span class="seta-ordem"><?php echo $ordem == 'ASC' ? '↑' : '↓'; ?></span>
                                    <?php endif; ?>
                                </th>
                                <th onclick="ordenar('cidade')">Cidade
                                    <?php if($ordenar == 'cidade'): ?>
                                        <span class="seta-ordem"><?php echo $ordem == 'ASC' ? '↑' : '↓'; ?></span>
                                    <?php endif; ?>
                                </th>
                                <th onclick="ordenar('e_mail')">E-mail
                                    <?php if($ordenar == 'e_mail'): ?>
                                        <span class="seta-ordem"><?php echo $ordem == 'ASC' ? '↑' : '↓'; ?></span>
                                    <?php endif; ?>
                                </th>
                                <th>Celular</th>
                                <th>Autorização</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($clientes as $cliente): ?>
                                <tr>
                                    <td>
                                        <?php 
                                            $cpf = $cliente['cpf'];
                                            echo substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
                                        ?>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($cliente['nome_completo']); ?></strong></td>
                                    <td>
                                        <span class="tag <?php echo $cliente['sexo'] == 'Feminino' ? 'tag-feminino' : 'tag-masculino'; ?>">
                                            <?php echo $cliente['sexo']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($cliente['data_de_nascimento'])); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['cidade']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['e_mail']); ?></td>
                                    <td>
                                        <?php 
                                            $celular = $cliente['celular_whatsapp'];
                                            echo '(' . substr($celular, 0, 2) . ') ' . substr($celular, 2, 5) . '-' . substr($celular, 7, 4);
                                        ?>
                                    </td>
                                    <td>
                                        <span class="tag <?php echo $cliente['autorizacao'] == 'SIM' ? 'tag-autorizado' : 'tag-nao-autorizado'; ?>">
                                            <?php echo $cliente['autorizacao']; ?>
                                        </span>
                                    </td>
                                    <td class="acoes">
                                        <a href="ver_cliente.php?id=<?php echo $cliente['cpf']; ?>" class="btn-acao btn-ver">👁️ Ver</a>
                                        <a href="editar_cliente.php?id=<?php echo $cliente['cpf']; ?>" class="btn-acao btn-editar">✏️ Editar</a>
                                        <a href="excluir_cliente.php?id=<?php echo $cliente['cpf']; ?>" 
                                           class="btn-acao btn-excluir"
                                           onclick="return confirm('Tem certeza que deseja excluir este cliente?')">🗑️ Excluir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Paginação -->
                    <?php if($total_paginas > 1): ?>
                        <div class="paginacao">
                            <?php if($pagina_atual > 1): ?>
                                <a href="?pagina=1&busca=<?php echo urlencode($busca); ?>&ordenar=<?php echo $ordenar; ?>&ordem=<?php echo $ordem; ?>" class="pagina">« Primeira</a>
                                <a href="?pagina=<?php echo $pagina_atual - 1; ?>&busca=<?php echo urlencode($busca); ?>&ordenar=<?php echo $ordenar; ?>&ordem=<?php echo $ordem; ?>" class="pagina">‹ Anterior</a>
                            <?php endif; ?>
                            
                            <?php
                            $inicio = max(1, $pagina_atual - 2);
                            $fim = min($total_paginas, $pagina_atual + 2);
                            
                            for ($i = $inicio; $i <= $fim; $i++):
                            ?>
                                <a href="?pagina=<?php echo $i; ?>&busca=<?php echo urlencode($busca); ?>&ordenar=<?php echo $ordenar; ?>&ordem=<?php echo $ordem; ?>" 
                                   class="pagina <?php echo $i == $pagina_atual ? 'ativa' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if($pagina_atual < $total_paginas): ?>
                                <a href="?pagina=<?php echo $pagina_atual + 1; ?>&busca=<?php echo urlencode($busca); ?>&ordenar=<?php echo $ordenar; ?>&ordem=<?php echo $ordem; ?>" class="pagina">Próxima ›</a>
                                <a href="?pagina=<?php echo $total_paginas; ?>&busca=<?php echo urlencode($busca); ?>&ordenar=<?php echo $ordenar; ?>&ordem=<?php echo $ordem; ?>" class="pagina">Última »</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="sem-dados">
                        <p>😕 Nenhum cliente encontrado.</p>
                        <?php if(!empty($busca)): ?>
                            <p>Tente uma busca diferente ou <a href="lista_clientes.php">limpe os filtros</a>.</p>
                        <?php else: ?>
                            <p>Clique em "Novo Cadastro" para adicionar clientes.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function ordenar(campo) {
            let ordenarAtual = '<?php echo $ordenar; ?>';
            let ordemAtual = '<?php echo $ordem; ?>';
            let novaOrdem = 'ASC';
            
            if (ordenarAtual === campo && ordemAtual === 'ASC') {
                novaOrdem = 'DESC';
            }
            
            window.location.href = `?pagina=1&busca=<?php echo urlencode($busca); ?>&ordenar=${campo}&ordem=${novaOrdem}`;
        }
    </script>
</body>
</html>