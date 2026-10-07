<?php
session_start();

$host = 'sql208.infinityfree.com';
$dbname = 'if0_41748767_projetointegrador';
$user = 'if0_41748767';
$pass = 'Univesp2026';

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // SQL para inserir todos os campos
        $sql = "INSERT INTO baseinformacoes (
            autorizacao, cpf, nome_completo, sexo, data_de_nascimento, cep, endereco, 
            bairro, cidade, estado, e_mail, celular_whatsapp, forma_de_pagamento_preferida, 
            cor_preferida, tecido_preferido, tamanho_de_camiseta, tamanho_de_calca, tamanho_de_sapato
        ) VALUES (
            :autorizacao, :cpf, :nome_completo, :sexo, :data_de_nascimento, :cep, :endereco,
            :bairro, :cidade, :estado, :e_mail, :celular_whatsapp, :forma_de_pagamento_preferida,
            :cor_preferida, :tecido_preferido, :tamanho_de_camiseta, :tamanho_de_calca, :tamanho_de_sapato
        )";
        
        $stmt = $pdo->prepare($sql);
        
        // Executar com os dados do formulário
        $stmt->execute([
            ':autorizacao' => $_POST['autorizacao'],
            ':cpf' => preg_replace('/[^0-9]/', '', $_POST['cpf']), // Remove formatação do CPF
            ':nome_completo' => $_POST['nome_completo'],
            ':sexo' => $_POST['sexo'],
            ':data_de_nascimento' => $_POST['data_de_nascimento'],
            ':cep' => preg_replace('/[^0-9]/', '', $_POST['cep']), // Remove formatação do CEP
            ':endereco' => $_POST['endereco'],
            ':bairro' => $_POST['bairro'],
            ':cidade' => $_POST['cidade'],
            ':estado' => $_POST['estado'],
            ':e_mail' => $_POST['e_mail'],
            ':celular_whatsapp' => preg_replace('/[^0-9]/', '', $_POST['celular_whatsapp']),
            ':forma_de_pagamento_preferida' => $_POST['forma_de_pagamento_preferida'],
            ':cor_preferida' => $_POST['cor_preferida'],
            ':tecido_preferido' => $_POST['tecido_preferido'],
            ':tamanho_de_camiseta' => $_POST['tamanho_de_camiseta'],
            ':tamanho_de_calca' => $_POST['tamanho_de_calca'],
            ':tamanho_de_sapato' => $_POST['tamanho_de_sapato']
        ]);
        
        $_SESSION['mensagem'] = "✅ Dados inseridos com sucesso!";
        $_SESSION['tipo'] = "success";
        
        // Limpar formulário após envio bem sucedido
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
        
    } catch(PDOException $e) {
        if ($e->errorInfo[1] == 1062) { // Código de erro para duplicidade (CPF já existe)
            $_SESSION['mensagem'] = "❌ Erro: CPF já cadastrado no sistema!";
        } else {
            $_SESSION['mensagem'] = "❌ Erro ao inserir dados: " . $e->getMessage();
        }
        $_SESSION['tipo'] = "error";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Cliente - Formulário Completo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body        {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
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
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .content {
            padding: 30px;
        }
        
        .mensagem {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
        }
        
        .mensagem.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .mensagem.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        .form-section h3 {
            color: #185FA5;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 2px solid #185FA5;
            padding-bottom: 5px;
            display: inline-block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        label .required {
            color: #e74c3c;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: inherit;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #185FA5;
            box-shadow: 0 0 0 3px rgba(24, 95, 165, 0.1);
        }
        
        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }
        
        button {
            background: linear-gradient(135deg, #185FA5 0%, #0e4680 100%);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s;
        }
        
        button:hover {
            transform: translateY(-2px);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .info-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .row, .row-3 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Cadastro de Cliente</h1>
            <p>Preencha todos os campos abaixo</p>
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
            
            <form method="POST">
                <!-- Seção 1: Informações Pessoais -->
                <div class="form-section">
                    <h3>👤 Informações Pessoais</h3>
                    
                    <div class="form-group">
                        <label>Nome Completo <span class="required">*</span></label>
                        <input type="text" name="nome_completo" required placeholder="Digite o nome completo">
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label>CPF <span class="required">*</span></label>
                            <input type="text" name="cpf" id="cpf" required placeholder="000.000.000-00" maxlength="14">
                            <div class="info-text">Digite apenas números ou use pontos e traço</div>
                        </div>
                        
                        <div class="form-group">
                            <label>Sexo <span class="required">*</span></label>
                            <select name="sexo" required>
                                <option value="">Selecione</option>
                                <option value="Feminino">Feminino</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Outro">Outro</option>
                                <option value="Prefiro não informar">Prefiro não informar</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label>Data de Nascimento <span class="required">*</span></label>
                            <input type="date" name="data_de_nascimento" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Autorização LGPD <span class="required">*</span></label>
                            <select name="autorizacao" required>
                                <option value="">Selecione</option>
                                <option value="SIM">SIM - Autorizo o uso dos dados</option>
                                <option value="NAO">NÃO - Não autorizo o uso dos dados</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Seção 2: Endereço -->
                <div class="form-section">
                    <h3>🏠 Endereço</h3>
                    
                    <div class="row">
                        <div class="form-group">
                            <label>CEP <span class="required">*</span></label>
                            <input type="text" name="cep" id="cep" required placeholder="00000-000" maxlength="9">
                        </div>
                        
                        <div class="form-group">
                            <label>Estado <span class="required">*</span></label>
                            <select name="estado" required>
                                <option value="">Selecione</option>
                                <option value="AC">Acre</option>
                                <option value="AL">Alagoas</option>
                                <option value="AP">Amapá</option>
                                <option value="AM">Amazonas</option>
                                <option value="BA">Bahia</option>
                                <option value="CE">Ceará</option>
                                <option value="DF">Distrito Federal</option>
                                <option value="ES">Espírito Santo</option>
                                <option value="GO">Goiás</option>
                                <option value="MA">Maranhão</option>
                                <option value="MT">Mato Grosso</option>
                                <option value="MS">Mato Grosso do Sul</option>
                                <option value="MG">Minas Gerais</option>
                                <option value="PA">Pará</option>
                                <option value="PB">Paraíba</option>
                                <option value="PR">Paraná</option>
                                <option value="PE">Pernambuco</option>
                                <option value="PI">Piauí</option>
                                <option value="RJ">Rio de Janeiro</option>
                                <option value="RN">Rio Grande do Norte</option>
                                <option value="RS">Rio Grande do Sul</option>
                                <option value="RO">Rondônia</option>
                                <option value="RR">Roraima</option>
                                <option value="SC">Santa Catarina</option>
                                <option value="SP">São Paulo</option>
                                <option value="SE">Sergipe</option>
                                <option value="TO">Tocantins</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Cidade <span class="required">*</span></label>
                        <input type="text" name="cidade" required placeholder="Digite a cidade">
                    </div>
                    
                    <div class="form-group">
                        <label>Bairro <span class="required">*</span></label>
                        <input type="text" name="bairro" required placeholder="Digite o bairro">
                    </div>
                    
                    <div class="form-group">
                        <label>Endereço <span class="required">*</span></label>
                        <input type="text" name="endereco" required placeholder="Rua, número, complemento">
                    </div>
                </div>
                
                <!-- Seção 3: Contato -->
                <div class="form-section">
                    <h3>📱 Contato</h3>
                    
                    <div class="form-group">
                        <label>E-mail <span class="required">*</span></label>
                        <input type="email" name="e_mail" required placeholder="exemplo@email.com">
                    </div>
                    
                    <div class="form-group">
                        <label>Celular / WhatsApp <span class="required">*</span></label>
                        <input type="text" name="celular_whatsapp" id="celular" required placeholder="(00) 00000-0000" maxlength="15">
                        <div class="info-text">Número com DDD</div>
                    </div>
                </div>
                
                <!-- Seção 4: Preferências -->
                <div class="form-section">
                    <h3>🎨 Preferências</h3>
                    
                    <div class="row-3">
                        <div class="form-group">
                            <label>Forma de Pagamento Preferida</label>
                            <select name="forma_de_pagamento_preferida">
                                <option value="">Selecione</option>
                                <option value="Cartão de Crédito">Cartão de Crédito</option>
                                <option value="Cartão de Débito">Cartão de Débito</option>
                                <option value="PIX">PIX</option>
                                <option value="Boleto">Boleto</option>
                                <option value="Dinheiro">Dinheiro</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Cor Preferida</label>
                            <input type="text" name="cor_preferida" placeholder="Ex: Azul, Vermelho">
                        </div>
                        
                        <div class="form-group">
                            <label>Tecido Preferido</label>
                            <input type="text" name="tecido_preferido" placeholder="Ex: Algodão, Jeans">
                        </div>
                    </div>
                    
                    <div class="row-3">
                        <div class="form-group">
                            <label>Tamanho de Camiseta</label>
                            <select name="tamanho_de_camiseta">
                                <option value="">Selecione</option>
                                <option value="PP">PP</option>
                                <option value="P">P</option>
                                <option value="M">M</option>
                                <option value="G">G</option>
                                <option value="GG">GG</option>
                                <option value="XGG">XGG</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Tamanho de Calça</label>
                            <input type="text" name="tamanho_de_calca" placeholder="Ex: 38, 40, 42">
                        </div>
                        
                        <div class="form-group">
                            <label>Tamanho de Sapato</label>
                            <input type="text" name="tamanho_de_sapato" placeholder="Ex: 37, 38, 39">
                        </div>
                    </div>
                </div>
                
                <button type="submit">💾 Cadastrar Cliente</button>
            </form>
        </div>
    </div>
    
    <script>
        // Formatação automática do CPF
        document.getElementById('cpf').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                e.target.value = value;
            }
        });
        
        // Formatação automática do CEP
        document.getElementById('cep').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 8) {
                value = value.replace(/^(\d{5})(\d)/, '$1-$2');
                e.target.value = value;
            }
        });
        
        // Formatação automática do Celular
        document.getElementById('celular').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
                e.target.value = value;
            }
        });
        
        // Buscar endereço pelo CEP (opcional)
        document.getElementById('cep').addEventListener('blur', function() {
            let cep = this.value.replace(/\D/g, '');
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    ..log(data => {
                        if (!data.erro) {
                            document.querySelector('[name="endereco"]').value = data.logradouro;
                            document.querySelector('[name="bairro"]').value = data.bairro;
                            document.querySelector('[name="cidade"]').value = data.localidade;
                            document.querySelector('[name="estado"]').value = data.uf;
                        }
                    })
                    .catch(error => console.log('Erro ao buscar CEP'));
            }
        });
    </script>
</body>
</html>