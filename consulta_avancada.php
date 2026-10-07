<?php
session_start();

$host = 'sql208.infinityfree.com';
$dbname = 'if0_41748767_projetointegrador';
$user = 'if0_41748767';
$pass = 'Univesp2026';

// Definir todas as colunas disponíveis com seus rótulos
$colunas_disponiveis = [
    'cpf' => 'CPF',
    'nome_completo' => 'Nome Completo',
    'sexo' => 'Sexo',
    'data_de_nascimento' => 'Data de Nascimento',
    'idade' => 'Idade', // Campo calculado
    'autorizacao' => 'Autorização LGPD',
    'cep' => 'CEP',
    'endereco' => 'Endereço',
    'bairro' => 'Bairro',
    'cidade' => 'Cidade',
    'estado' => 'Estado',
    'e_mail' => 'E-mail',
    'celular_whatsapp' => 'Celular/WhatsApp',
    'forma_de_pagamento_preferida' => 'Forma de Pagamento',
    'cor_preferida' => 'Cor Preferida',
    'tecido_preferido' => 'Tecido Preferido',
    'tamanho_de_camiseta' => 'Tamanho Camiseta',
    'tamanho_de_calca' => 'Tamanho Calça',
    'tamanho_de_sapato' => 'Tamanho Sapato'
];

// Definir operadores para filtros
$operadores = [
    '=' => 'Igual a',
    'LIKE' => 'Contém',
    '>' => 'Maior que',
    '<' => 'Menor que',
    '>=' => 'Maior ou igual',
    '<=' => 'Menor ou igual',
    '!=' => 'Diferente de',
    'IN' => 'Está entre'
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Recuperar configurações da consulta (salvas em sessão ou POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $_SESSION['consulta_config'] = [
            'colunas' => isset($_POST['colunas']) ? $_POST['colunas'] : ['nome_completo', 'cpf', 'cidade'],
            'filtros' => isset($_POST['filtros']) ? $_POST['filtros'] : [],
            'ordenar' => isset($_POST['ordenar']) ? $_POST['ordenar'] : 'nome_completo',
            'ordem' => isset($_POST['ordem']) ? $_POST['ordem'] : 'ASC',
            'limite' => isset($_POST['limite']) ? (int)$_POST['limite'] : 50
        ];
    }
    
    // Carregar configuração atual
    $config = isset($_SESSION['consulta_config']) ? $_SESSION['consulta_config'] : [
        'colunas' => ['nome_completo', 'cpf', 'cidade'],
        'filtros' => [],
        'ordenar' => 'nome_completo',
        'ordem' => 'ASC',
        'limite' => 50
    ];
    
    // Construir query SQL com base nos filtros
    $where_conditions = [];
    $params = [];
    
    if (!empty($config['filtros'])) {
        foreach ($config['filtros'] as $index => $filtro) {
            if (!empty($filtro['coluna']) && !empty($filtro['valor'])) {
                $coluna = $filtro['coluna'];
                $operador = $filtro['operador'];
                $valor = $filtro['valor'];
                
                if ($operador == 'LIKE') {
                    $where_conditions[] = "$coluna LIKE :valor_$index";
                    $params[":valor_$index"] = "%$valor%";
                } elseif ($operador == 'IN') {
                    $valores = array_map('trim', explode(',', $valor));
                    $placeholders = [];
                    foreach ($valores as $i => $v) {
                        $placeholders[] = ":valor_{$index}_$i";
                        $params[":valor_{$index}_$i"] = $v;
                    }
                    $where_conditions[] = "$coluna IN (" . implode(',', $placeholders) . ")";
                } else {
                    $where_conditions[] = "$coluna $operador :valor_$index";
                    $params[":valor_$index"] = $valor;
                }
            }
        }
    }
    
    $where_sql = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Buscar dados
    $sql = "SELECT * FROM baseinformacoes 
            $where_sql 
            ORDER BY {$config['ordenar']} {$config['ordem']} 
            LIMIT {$config['limite']}";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular idade
    function calcularIdade($data_nascimento) {
        if (empty($data_nascimento)) return '-';
        $data = new DateTime($data_nascimento);
        $hoje = new DateTime();
        $idade = $hoje->diff($data)->y;
        return $idade;
    }
    
} catch(PDOException $e) {
    $erro = "Erro na consulta: " . $e->getMessage();
    $resultados = [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta Avançada - Sistema LGPD</title>
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
        }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #185FA5 0%, #0e4680 100%);
            color: white;
            padding: 20px 30px;
        }
        
        .card-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .card-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .configuracao {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .configuracao h3 {
            color: #185FA5;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 13px;
            color: #333;
        }
        
        select, input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        
        select:focus, input:focus {
            outline: none;
            border-color: #185FA5;
        }
        
        .filtro-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid #e0e0e0;
            position: relative;
        }
        
        .filtro-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .filtro-header strong {
            color: #185FA5;
        }
        
        .btn-remover-filtro {
            background: #dc3545;
            color: white;
            border: none;
            padding: 3px 8px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 11px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            margin-right: 10px;
        }
        
        .btn-primary {
            background: #185FA5;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        
        .tabela-container {
            overflow-x: auto;
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        
        th {
            background: #185FA5;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        
        td {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-sim {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-nao {
            background: #f8d7da;
            color: #721c24;
        }
        
        .estatisticas {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .btn-adicionar-filtro {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
        }
        
        .acoes-rapidas {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        @media (max-width: 768px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
            
            .card-body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Card de Configuração -->
        <div class="card">
            <div class="card-header">
                <h1>🔍 Consulta Avançada de Clientes</h1>
                <p>Selecione as colunas, aplique filtros e personalize sua consulta</p>
            </div>
            
            <div class="card-body">
                <form method="POST" id="formConsulta">
                    <div class="configuracao">
                        <h3>📋 Selecione as Colunas para Visualizar</h3>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
                            <?php foreach($colunas_disponiveis as $valor => $rotulo): ?>
                                <label style="display: flex; align-items: center; gap: 5px; background: white; padding: 5px 10px; border-radius: 20px; border: 1px solid #ddd;">
                                    <input type="checkbox" name="colunas[]" value="<?php echo $valor; ?>" 
                                           <?php echo in_array($valor, $config['colunas']) ? 'checked' : ''; ?>>
                                    <?php echo $rotulo; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        
                        <h3>🎯 Filtros Personalizados</h3>
                        <div id="filtros-container">
                            <?php if(!empty($config['filtros'])): ?>
                                <?php foreach($config['filtros'] as $index => $filtro): ?>
                                    <div class="filtro-item">
                                        <div class="filtro-header">
                                            <strong>Filtro <?php echo $index + 1; ?></strong>
                                            <button type="button" class="btn-remover-filtro" onclick="removerFiltro(this)">✖ Remover</button>
                                        </div>
                                        <div class="grid-2">
                                            <div>
                                                <label>Coluna</label>
                                                <select name="filtros[<?php echo $index; ?>][coluna]" class="filtro-coluna">
                                                    <option value="">Selecione...</option>
                                                    <?php foreach($colunas_disponiveis as $valor => $rotulo): ?>
                                                        <option value="<?php echo $valor; ?>" <?php echo $filtro['coluna'] == $valor ? 'selected' : ''; ?>>
                                                            <?php echo $rotulo; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div>
                                                <label>Operador</label>
                                                <select name="filtros[<?php echo $index; ?>][operador]">
                                                    <?php foreach($operadores as $op => $label): ?>
                                                        <option value="<?php echo $op; ?>" <?php echo $filtro['operador'] == $op ? 'selected' : ''; ?>>
                                                            <?php echo $label; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div>
                                                <label>Valor</label>
                                                <input type="text" name="filtros[<?php echo $index; ?>][valor]" 
                                                       value="<?php echo htmlspecialchars($filtro['valor']); ?>" 
                                                       placeholder="Digite o valor...">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="filtro-item">
                                    <div class="filtro-header">
                                        <strong>Filtro 1</strong>
                                    </div>
                                    <div class="grid-2">
                                        <div>
                                            <label>Coluna</label>
                                            <select name="filtros[0][coluna]" class="filtro-coluna">
                                                <option value="">Selecione...</option>
                                                <?php foreach($colunas_disponiveis as $valor => $rotulo): ?>
                                                    <option value="<?php echo $valor; ?>"><?php echo $rotulo; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label>Operador</label>
                                            <select name="filtros[0][operador]">
                                                <?php foreach($operadores as $op => $label): ?>
                                                    <option value="<?php echo $op; ?>"><?php echo $label; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label>Valor</label>
                                            <input type="text" name="filtros[0][valor]" placeholder="Digite o valor...">
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <button type="button" class="btn-adicionar-filtro" onclick="adicionarFiltro()">+ Adicionar Filtro</button>
                        
                        <div class="grid-2" style="margin-top: 20px;">
                            <div>
                                <label>Ordenar por</label>
                                <select name="ordenar">
                                    <?php foreach($colunas_disponiveis as $valor => $rotulo): ?>
                                        <option value="<?php echo $valor; ?>" <?php echo $config['ordenar'] == $valor ? 'selected' : ''; ?>>
                                            <?php echo $rotulo; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label>Ordem</label>
                                <select name="ordem">
                                    <option value="ASC" <?php echo $config['ordem'] == 'ASC' ? 'selected' : ''; ?>>Crescente (A-Z)</option>
                                    <option value="DESC" <?php echo $config['ordem'] == 'DESC' ? 'selected' : ''; ?>>Decrescente (Z-A)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Limite de resultados</label>
                            <input type="number" name="limite" value="<?php echo $config['limite']; ?>" min="1" max="500">
                        </div>
                        
                        <div class="acoes-rapidas">
                            <button type="submit" class="btn btn-primary">🔍 Executar Consulta</button>
                            <button type="button" class="btn btn-secondary" onclick="limparFiltros()">🗑️ Limpar Filtros</button>
                            <button type="button" class="btn btn-success" onclick="exportarCSV()">📊 Exportar CSV</button>
                            <a href="lista_clientes.php" class="btn btn-warning" style="text-decoration: none;">← Voltar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Card de Resultados -->
        <?php if(isset($resultados)): ?>
        <div class="card">
            <div class="card-header">
                <h1>📊 Resultados da Consulta</h1>
                <p>Encontrados <?php echo count($resultados); ?> cliente(s)</p>
            </div>
            
            <div class="card-body">
                <?php if(isset($erro)): ?>
                    <div class="estatisticas" style="background: #f8d7da; color: #721c24;">
                        ❌ <?php echo $erro; ?>
                    </div>
                <?php elseif(count($resultados) > 0): ?>
                    <div class="estatisticas">
                        <div>
                            <strong>📈 Total de registros:</strong> <?php echo count($resultados); ?>
                        </div>
                        <div>
                            <strong>📅 Última consulta:</strong> <?php echo date('d/m/Y H:i:s'); ?>
                        </div>
                    </div>
                    
                    <div class="tabela-container">
                        <table>
                            <thead>
                                <tr>
                                    <?php foreach($config['colunas'] as $coluna): ?>
                                        <th><?php echo $colunas_disponiveis[$coluna] ?? $coluna; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($resultados as $row): ?>
                                    <tr>
                                        <?php foreach($config['colunas'] as $coluna): ?>
                                            <td>
                                                <?php
                                                if ($coluna == 'idade') {
                                                    echo calcularIdade($row['data_de_nascimento']);
                                                } elseif ($coluna == 'cpf') {
                                                    $cpf = $row['cpf'];
                                                    echo substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
                                                } elseif ($coluna == 'celular_whatsapp') {
                                                    $cel = $row['celular_whatsapp'];
                                                    if (strlen($cel) == 11) {
                                                        echo '(' . substr($cel, 0, 2) . ') ' . substr($cel, 2, 5) . '-' . substr($cel, 7, 4);
                                                    } else {
                                                        echo $cel;
                                                    }
                                                } elseif ($coluna == 'autorizacao') {
                                                    $class = $row['autorizacao'] == 'SIM' ? 'badge-sim' : 'badge-nao';
                                                    echo "<span class='badge $class'>{$row['autorizacao']}</span>";
                                                } elseif ($coluna == 'sexo') {
                                                    $class = $row['sexo'] == 'Feminino' ? 'badge-sim' : 'badge-nao';
                                                    echo "<span class='badge $class'>{$row['sexo']}</span>";
                                                } elseif (in_array($coluna, ['data_de_nascimento', 'consentimento_data'])) {
                                                    echo !empty($row[$coluna]) ? date('d/m/Y', strtotime($row[$coluna])) : '-';
                                                } else {
                                                    echo htmlspecialchars($row[$coluna] ?? '-');
                                                }
                                                ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif(count($resultados) == 0 && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                    <div class="estatisticas" style="background: #fff3cd; color: #856404;">
                        ⚠️ Nenhum resultado encontrado com os filtros aplicados. Tente ajustar os critérios de busca.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        let filtroCount = <?php echo !empty($config['filtros']) ? count($config['filtros']) : 1; ?>;
        
        function adicionarFiltro() {
            const container = document.getElementById('filtros-container');
            const novoFiltro = document.createElement('div');
            novoFiltro.className = 'filtro-item';
            novoFiltro.innerHTML = `
                <div class="filtro-header">
                    <strong>Filtro ${filtroCount + 1}</strong>
                    <button type="button" class="btn-remover-filtro" onclick="removerFiltro(this)">✖ Remover</button>
                </div>
                <div class="grid-2">
                    <div>
                        <label>Coluna</label>
                        <select name="filtros[${filtroCount}][coluna]" class="filtro-coluna">
                            <option value="">Selecione...</option>
                            <?php foreach($colunas_disponiveis as $valor => $rotulo): ?>
                                <option value="<?php echo $valor; ?>"><?php echo $rotulo; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Operador</label>
                        <select name="filtros[${filtroCount}][operador]">
                            <?php foreach($operadores as $op => $label): ?>
                                <option value="<?php echo $op; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Valor</label>
                        <input type="text" name="filtros[${filtroCount}][valor]" placeholder="Digite o valor...">
                    </div>
                </div>
            `;
            container.appendChild(novoFiltro);
            filtroCount++;
        }
        
        function removerFiltro(botao) {
            const filtroItem = botao.closest('.filtro-item');
            filtroItem.remove();
        }
        
        function limparFiltros() {
            if(confirm('Tem certeza que deseja limpar todos os filtros?')) {
                window.location.href = 'consulta_avancada.php';
            }
        }
        
        function exportarCSV() {
            const form = document.getElementById('formConsulta');
            const formData = new FormData(form);
            formData.append('exportar', 'csv');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            }).then(response => response.blob())
              .then(blob => {
                  const url = window.URL.createObjectURL(blob);
                  const a = document.createElement('a');
                  a.href = url;
                  a.download = 'consulta_clientes.csv';
                  document.body.appendChild(a);
                  a.click();
                  a.remove();
              });
        }
    </script>
</body>
</html>