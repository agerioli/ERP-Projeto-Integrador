<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'auth/verifica_login.php';
require 'config/conexao.php';

/*
  IMPORTANTE:
  o controller deve vir antes OU depois dependendo de quem define o quê.
*/
require 'controllers/clientes_controller.php';

$aba_ativa = $_GET['aba'] ?? 'lista';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aba_ativa'])) {
    $aba_ativa = $_POST['aba_ativa'];
}

/*
  GARANTIA DE SEGURANÇA:
  evita erro de variável não definida vindo do controller
*/
$config_consulta = $config_consulta ?? [
    'colunas' => [],
    'filtros' => [],
    'ordenar' => 'nome_completo',
    'ordem' => 'ASC',
    'limite' => 50
];

$colunas_disponiveis = $colunas_disponiveis ?? [
    'cpf' => 'CPF',
    'nome_completo' => 'Nome Completo',
    'sexo' => 'Sexo',
    'data_de_nascimento' => 'Nascimento',
    'idade' => 'Idade',
    'autorizacao' => 'Autorização LGPD',
    'cep' => 'CEP',
    'endereco' => 'Endereço',
    'bairro' => 'Bairro',
    'cidade' => 'Cidade',
    'estado' => 'Estado',
    'e_mail' => 'E-mail',
    'celular_whatsapp' => 'Celular',
];

$operadores = $operadores ?? [
    '=' => '=',
    'LIKE' => 'Contém',
    '>' => 'Maior que',
    '<' => 'Menor que',
    '>=' => 'Maior ou igual',
    '<=' => 'Menor ou igual',
    '!=' => 'Diferente',
];

if (!in_array($aba_ativa, ['lista', 'consulta', 'cadastro'])) {
    $aba_ativa = 'lista';
}

$mensagem_cadastro = $_SESSION['cadastro_msg'] ?? '';
$tipo_cadastro = $_SESSION['cadastro_tipo'] ?? '';

unset($_SESSION['cadastro_msg'], $_SESSION['cadastro_tipo']);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main">
    <div class="topbar">
      <div>
        <div class="topbar-title" id="tb-title">Clientes</div>
      </div>
      <div id="tb-actions"></div>
    </div>

  <!---<h2>Clientes</h2>-->

  <!-- ABAS -->
  <div class="tabs">
    <button class="tab-btn" data-aba="lista" onclick="trocarAba('lista')">Lista</button>
	<button class="tab-btn" data-aba="consulta" onclick="trocarAba('consulta')">Consulta Avançada</button>
	<button class="tab-btn" data-aba="cadastro" onclick="trocarAba('cadastro')">Cadastro</button>
  </div>

  <div class="content">

    <!-- LISTA -->
	<div id="aba-lista" class="aba">
  <div class="card-lista">

        <div class="card-lista-header">
      <div>
        <h1>📋 Lista de Clientes</h1>
        <p style="margin-top: 6px;">Total de clientes cadastrados: <strong><?= $total ?></strong></p>
      </div>
    </div>

    <div class="card-lista-body">

      <div class="barra-ferramentas">
        <form method="GET" class="busca">
              <input type="hidden" name="aba" value="lista">

              <input type="hidden" name="ordenar" value="<?= $ordenar ?>">
              <input type="hidden" name="ordem" value="<?= $ordem ?>">

              <input type="text" name="busca"
                     value="<?= htmlspecialchars($busca) ?>"
                     placeholder="Buscar por nome, CPF, email ou cidade...">

              <button type="submit">🔍 Buscar</button>

              <?php if (!empty($busca)): ?>
                <a href="clientes.php?aba=lista" class="btn-acao btn-cinza">Limpar</a>
              <?php endif; ?>
            </form>
        <div class="estatisticas">
          <div class="estatistica-item">📊 Mostrando <?= count($clientes) ?> de <?= $total ?></div>
        </div>
      </div>

      <div class="tabela-container">
        <?php if (count($clientes) > 0): ?>
          <table>
            <thead>
              <tr>
                <?php
                  $cols = [
                    'cpf'                => 'CPF',
                    'nome_completo'      => 'Nome',
                    'sexo'               => 'Sexo',
                    'data_de_nascimento' => 'Nascimento',
                    'cidade'             => 'Cidade',
                    'e_mail'             => 'E-mail',
                  ];
                  foreach ($cols as $campo => $label):
                    $seta = $ordenar === $campo ? ($ordem === 'ASC' ? ' ↑' : ' ↓') : '';
                ?>
                <th onclick="ordenar('<?= $campo ?>')"><?= $label . $seta ?></th>
                <?php endforeach; ?>
                <th>Celular</th>
                <th>Autorização</th>
                <th>Ações</th>
              </tr>
            <tbody>
              <?php foreach ($clientes as $c): ?>
              <tr>
                <td><?= substr($c['cpf'],0,3).'.'.substr($c['cpf'],3,3).'.'.substr($c['cpf'],6,3).'-'.substr($c['cpf'],9,2) ?></td>
                <td><strong><?= htmlspecialchars($c['nome_completo']) ?></strong></td>
                <td>
                  <span class="tag <?= $c['sexo'] === 'Feminino' ? 'tag-feminino' : 'tag-masculino' ?>">
                    <?= $c['sexo'] ?>
                  </span>
                </td>
                <td><?= date('d/m/Y', strtotime($c['data_de_nascimento'])) ?></td>
                <td><?= htmlspecialchars($c['cidade']) ?></td>
                <td><?= htmlspecialchars($c['e_mail']) ?></td>
                <td><?= '('.substr($c['celular_whatsapp'],0,2).') '.substr($c['celular_whatsapp'],2,5).'-'.substr($c['celular_whatsapp'],7,4) ?></td>
                <td>
                  <span class="tag <?= $c['autorizacao'] === 'SIM' ? 'tag-autorizado' : 'tag-nao-autorizado' ?>">
                    <?= $c['autorizacao'] ?>
                  </span>
                </td>
                <td class="acoes">

              <button class="btn-acao btn-ver"
                onclick="abrirVer(
                  '<?= $c['nome_completo'] ?>',
                  '<?= $c['cpf'] ?>',
                  '<?= $c['sexo'] ?>',
                  '<?= $c['data_de_nascimento'] ?>',
                  '<?= $c['cidade'] ?>',
                  '<?= $c['e_mail'] ?>',
                  '<?= $c['celular_whatsapp'] ?>',
                  '<?= $c['autorizacao'] ?>'
                )">
                👁️ Ver
              </button>

           
            <button class="btn-acao btn-editar"
              data-json='<?= htmlspecialchars(json_encode([
                "id"                 => $c['id'],
                "cpf"                => $c['cpf'],
                "nome_completo"      => $c['nome_completo'],
                "sexo"               => $c['sexo'],
                "data_de_nascimento" => $c['data_de_nascimento'],
                "autorizacao"        => $c['autorizacao'],
                "cep"                => $c['cep'],
                "endereco"           => $c['endereco'],
                "bairro"             => $c['bairro'],
                "cidade"             => $c['cidade'],
                "estado"             => $c['estado'],
                "e_mail"             => $c['e_mail'],
                "celular_whatsapp"   => $c['celular_whatsapp'],
              ]), ENT_QUOTES) ?>'
              onclick="abrirEditarFromDataset(this)">
              ✏️ Editar
            </button>

                    

              <button class="btn-acao btn-excluir"
                onclick="abrirExcluir(<?= $c['id'] ?>)">
                🗑️ Excluir
              </button>

            </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

                  <?php if ($total_paginas > 1): ?>
          <div class="paginacao">

            <?php if ($pagina > 1): ?>
              <a href="?pagina=1&busca=<?= urlencode($busca) ?>&ordenar=<?= $ordenar ?>&ordem=<?= $ordem ?>" class="pagina">« Primeira</a>

              <a href="?pagina=<?= $pagina - 1 ?>&busca=<?= urlencode($busca) ?>&ordenar=<?= $ordenar ?>&ordem=<?= $ordem ?>" class="pagina">‹ Anterior</a>
            <?php endif; ?>

            <?php for ($i = max(1, $pagina - 2); $i <= min($total_paginas, $pagina + 2); $i++): ?>
              <a href="?pagina=<?= $i ?>&busca=<?= urlencode($busca) ?>&ordenar=<?= $ordenar ?>&ordem=<?= $ordem ?>"
                 class="pagina <?= $i === $pagina ? 'ativa' : '' ?>">
                <?= $i ?>
              </a>
            <?php endfor; ?>

            <?php if ($pagina < $total_paginas): ?>
              <a href="?pagina=<?= $pagina + 1 ?>&busca=<?= urlencode($busca) ?>&ordenar=<?= $ordenar ?>&ordem=<?= $ordem ?>" class="pagina">Próxima ›</a>

              <a href="?pagina=<?= $total_paginas ?>&busca=<?= urlencode($busca) ?>&ordenar=<?= $ordenar ?>&ordem=<?= $ordem ?>" class="pagina">Última »</a>
            <?php endif; ?>

          </div>
        <?php endif; ?>

        <?php else: ?>
          <div class="sem-dados">
            <p>😕 Nenhum cliente encontrado.</p>
            <?php if (!empty($busca)): ?>
              <p>Tente uma busca diferente ou <a href="clientes.php">limpe os filtros</a>.</p>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

    <!-- ========== ABA CONSULTA ========== -->
<div id="aba-consulta" class="aba">
  <div class="card-lista">

    <div class="card-lista-header">
      <div>
        <h1>🔍 Consulta Avançada de Clientes</h1>
        <p>Selecione as colunas, aplique filtros e personalize sua consulta</p>
      </div>
    </div>

    <div class="card-lista-body">
          <form method="POST" action="clientes.php?aba=consulta" id="formConsulta">
        <input type="hidden" name="aba_ativa" value="consulta">

        <!-- COLUNAS -->
        <div class="consulta-secao">
          <h3 class="consulta-titulo">📋 Colunas para visualizar</h3>
          <div class="consulta-checkboxes">
            <?php foreach ($colunas_disponiveis as $val => $rot): ?>
              <label class="consulta-chk">
                <input type="checkbox" name="colunas[]" value="<?= $val ?>"
                       <?= in_array($val, $config_consulta['colunas']) ? 'checked' : '' ?>>
                <?= $rot ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- FILTROS -->
        <div class="consulta-secao">
          <h3 class="consulta-titulo">🎯 Filtros personalizados</h3>
          <div id="filtros-container">
            <?php
            $filtros_exibir = !empty($config_consulta['filtros']) ? $config_consulta['filtros'] : [['coluna'=>'','operador'=>'=','valor'=>'']];
            foreach ($filtros_exibir as $index => $filtro):
            ?>
            <div class="filtro-item">
              <div class="filtro-header">
                <strong>Filtro <?= $index + 1 ?></strong>
                <button type="button" class="btn-remover-filtro" onclick="removerFiltro(this)">✖ Remover</button>
              </div>
              <div class="filtro-grid">
                <div>
                  <label>Coluna</label>
                  <select name="filtros[<?= $index ?>][coluna]">
                    <option value="">Selecione...</option>
                    <?php foreach ($colunas_disponiveis as $val => $rot): ?>
                      <option value="<?= $val ?>" <?= ($filtro['coluna'] ?? '') === $val ? 'selected' : '' ?>><?= $rot ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div>
                  <label>Operador</label>
                  <select name="filtros[<?= $index ?>][operador]">
                    <?php foreach ($operadores as $op => $lbl): ?>
                      <option value="<?= $op ?>" <?= ($filtro['operador'] ?? '=') === $op ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div>
                  <label>Valor</label>
                  <input type="text" name="filtros[<?= $index ?>][valor]"
                         value="<?= htmlspecialchars($filtro['valor'] ?? '') ?>"
                         placeholder="Digite o valor...">
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="btn-adicionar-filtro" onclick="adicionarFiltro()">+ Adicionar Filtro</button>
        </div>

        <!-- ORDENAÇÃO E LIMITE -->
        <div class="consulta-secao">
          <div class="filtro-grid">
            <div>
              <label>Ordenar por</label>
              <select name="ordenar_consulta">
                <?php foreach ($colunas_disponiveis as $val => $rot): ?>
                  <option value="<?= $val ?>" <?= $config_consulta['ordenar'] === $val ? 'selected' : '' ?>><?= $rot ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label>Ordem</label>
              <select name="ordem_consulta">
                <option value="ASC"  <?= $config_consulta['ordem'] === 'ASC'  ? 'selected' : '' ?>>Crescente (A-Z)</option>
                <option value="DESC" <?= $config_consulta['ordem'] === 'DESC' ? 'selected' : '' ?>>Decrescente (Z-A)</option>
              </select>
            </div>
            <div>
              <label>Limite de resultados</label>
              <input type="number" name="limite" value="<?= $config_consulta['limite'] ?>" min="1" max="500">
            </div>
          </div>
        </div>

        <!-- AÇÕES -->
        <div class="consulta-acoes">
          <button type="submit" class="btn-acao btn-ver">🔍 Executar Consulta</button>
          <button type="button" class="btn-acao btn-cinza" onclick="limparConsulta()">🗑️ Limpar Filtros</button>
        </div>
      </form>

      <!-- RESULTADOS -->
      <?php if (!empty($resultados_consulta)): ?>
      <div class="consulta-resultados">
        <div class="consulta-stats">
          <span>📈 <strong><?= count($resultados_consulta) ?></strong> registro(s) encontrado(s)</span>
          <span>📅 <?= date('d/m/Y H:i:s') ?></span>
        </div>
        <div class="tabela-container">
          <table>
            <thead>
              <tr>
                <?php foreach ($config_consulta['colunas'] as $col): ?>
                  <th><?= $colunas_disponiveis[$col] ?? $col ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($resultados_consulta as $row): ?>
              <tr>
                <?php foreach ($config_consulta['colunas'] as $col): ?>
                <td>
                  <?php
                  if ($col === 'idade') {
                      echo calcularIdade($row['data_de_nascimento']);
                  } elseif ($col === 'cpf') {
                      $c = $row['cpf'];
                      echo substr($c,0,3).'.'.substr($c,3,3).'.'.substr($c,6,3).'-'.substr($c,9,2);
                  } elseif ($col === 'celular_whatsapp') {
                      $cel = $row['celular_whatsapp'];
                      echo strlen($cel) == 11
                          ? '('.substr($cel,0,2).') '.substr($cel,2,5).'-'.substr($cel,7,4)
                          : $cel;
                  } elseif ($col === 'autorizacao') {
                      $cls = $row['autorizacao'] === 'SIM' ? 'tag-autorizado' : 'tag-nao-autorizado';
                      echo "<span class='tag $cls'>{$row['autorizacao']}</span>";
                  } elseif ($col === 'sexo') {
                      $cls = $row['sexo'] === 'Feminino' ? 'tag-feminino' : 'tag-masculino';
                      echo "<span class='tag $cls'>{$row['sexo']}</span>";
                  } elseif (in_array($col, ['data_de_nascimento'])) {
                      echo !empty($row[$col]) ? date('d/m/Y', strtotime($row[$col])) : '-';
                  } else {
                      echo htmlspecialchars($row[$col] ?? '-');
                  }
                  ?>
                </td>
                <?php endforeach; ?>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aba_ativa']) && $_POST['aba_ativa'] === 'consulta'): ?>
      <div class="sem-dados" style="background:#fff3cd; color:#856404; border-radius:8px; margin-top:16px;">
        ⚠️ Nenhum resultado encontrado. Tente ajustar os filtros.
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>
      
    <!-- ========== ABA CADASTRO ========== -->
<div id="aba-cadastro" class="aba">
  <div class="card-lista">

    <div class="card-lista-header">
      <div>
        <h1>📝 Cadastro de Cliente</h1>
        <p>Preencha todos os campos abaixo</p>
      </div>
    </div>

    <div class="card-lista-body">

      <?php if (!empty($mensagem_cadastro)): ?>
        <div class="cad-mensagem <?= $tipo_cadastro ?>">
          <?= $mensagem_cadastro ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="clientes.php?aba=cadastro#cadastro" id="formCadastro">
        <input type="hidden" name="acao" value="create">

        <!-- INFORMAÇÕES PESSOAIS -->
        <div class="consulta-secao">
          <h3 class="consulta-titulo">👤 Informações Pessoais</h3>

          <div class="cad-field">
            <label>Nome Completo <span class="cad-req">*</span></label>
            <input type="text" name="nome_completo" required placeholder="Digite o nome completo">
          </div>

          <div class="filtro-grid">
            <div class="cad-field">
              <label>CPF <span class="cad-req">*</span></label>
              <input type="text" name="cpf" id="cpf" required placeholder="000.000.000-00" maxlength="14">
            </div>
            <div class="cad-field">
              <label>Sexo <span class="cad-req">*</span></label>
              <select name="sexo" required>
                <option value="">Selecione</option>
                <option value="Feminino">Feminino</option>
                <option value="Masculino">Masculino</option>
                <option value="Outro">Outro</option>
                <option value="Prefiro não informar">Prefiro não informar</option>
              </select>
            </div>
            <div class="cad-field">
              <label>Data de Nascimento <span class="cad-req">*</span></label>
              <input type="date" name="data_de_nascimento" required>
            </div>
          </div>

          <div class="cad-field" style="margin-top: 12px;">
            <label>Autorização LGPD <span class="cad-req">*</span></label>
            <select name="autorizacao" required>
              <option value="">Selecione</option>
              <option value="SIM">SIM - Autorizo o uso dos dados</option>
              <option value="NAO">NÃO - Não autorizo o uso dos dados</option>
            </select>
          </div>
        </div>

        <!-- ENDEREÇO -->
        <div class="consulta-secao">
          <h3 class="consulta-titulo">🏠 Endereço</h3>

          <div class="filtro-grid">
            <div class="cad-field">
              <label>CEP <span class="cad-req">*</span></label>
              <input type="text" name="cep" id="cep" required placeholder="00000-000" maxlength="9" inputmode="numeric">
              <small id="cep-status" class="cep-status" aria-live="polite"></small>
            </div>
            <div class="cad-field">
              <label>Estado <span class="cad-req">*</span></label>
              <input type="text" name="estado" id="estado" class="endereco-auto" maxlength="2" readonly required>
            </div>
          </div>

          <div class="filtro-grid" style="margin-top: 12px;">
            <div class="cad-field">
              <label>Cidade <span class="cad-req">*</span></label>
              <input type="text" name="cidade" id="cidade" class="endereco-auto" required readonly>
            </div>
            <div class="cad-field">
              <label>Bairro <span class="cad-req">*</span></label>
              <input type="text" name="bairro" id="bairro" class="endereco-auto" required readonly>
            </div>
            <div class="cad-field">
              <label>Número <span class="cad-req">*</span></label>
              <input type="text" name="numero" id="numero" required placeholder="Ex.: 123">
            </div>
            <div class="cad-field">
              <label>Complemento</label>
              <input type="text" name="complemento" id="complemento" placeholder="Apto., sala, bloco...">
            </div>
            <div class="cad-field" style="grid-column: span 2;">
              <label>Logradouro <span class="cad-req">*</span></label>
              <input type="text" name="endereco" id="endereco" class="endereco-auto" required readonly>
            </div>
          </div>
        </div>

        <!-- CONTATO -->
        <div class="consulta-secao">
          <h3 class="consulta-titulo">📱 Contato</h3>

          <div class="filtro-grid">
            <div class="cad-field" style="grid-column: span 2;">
              <label>E-mail <span class="cad-req">*</span></label>
              <input type="email" name="e_mail" required placeholder="exemplo@email.com">
            </div>
            <div class="cad-field">
              <label>Celular / WhatsApp <span class="cad-req">*</span></label>
              <input type="text" name="celular_whatsapp" id="celular" required placeholder="(00) 00000-0000" maxlength="15">
            </div>
          </div>
        </div>

        <!-- PREFERÊNCIAS -->
        <div class="consulta-secao">
          <h3 class="consulta-titulo">🎨 Preferências</h3>

          <div class="filtro-grid">
            <div class="cad-field">
              <label>Forma de Pagamento</label>
              <select name="forma_de_pagamento_preferida">
                <option value="">Selecione</option>
                <option value="Cartão de Crédito">Cartão de Crédito</option>
                <option value="Cartão de Débito">Cartão de Débito</option>
                <option value="PIX">PIX</option>
                <option value="Boleto">Boleto</option>
                <option value="Dinheiro">Dinheiro</option>
              </select>
            </div>
            <div class="cad-field">
              <label>Cor Preferida</label>
              <input type="text" name="cor_preferida" placeholder="Ex: Azul, Vermelho">
            </div>
            <div class="cad-field">
              <label>Tecido Preferido</label>
              <input type="text" name="tecido_preferido" placeholder="Ex: Algodão, Jeans">
            </div>
          </div>

          <div class="filtro-grid" style="margin-top: 12px;">
            <div class="cad-field">
              <label>Tamanho de Camiseta</label>
              <select name="tamanho_de_camiseta">
                <option value="">Selecione</option>
                <option value="PP">PP</option><option value="P">P</option>
                <option value="M">M</option><option value="G">G</option>
                <option value="GG">GG</option><option value="XGG">XGG</option>
              </select>
            </div>
            <div class="cad-field">
              <label>Tamanho de Calça</label>
              <input type="text" name="tamanho_de_calca" placeholder="Ex: 38, 40, 42">
            </div>
            <div class="cad-field">
              <label>Tamanho de Sapato</label>
              <input type="text" name="tamanho_de_sapato" placeholder="Ex: 37, 38, 39">
            </div>
          </div>
        </div>

        <!-- BOTÃO -->
        <div class="consulta-acoes">
          <button type="submit" class="btn-acao btn-ver" style="padding: 12px 28px; font-size: 14px;">
            💾 Cadastrar Cliente
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

  </div>
</div>

<div id="modalVer" class="modal">
  <div class="modal-content">
    <h2>📄 Detalhes do Cliente</h2>

    <p><strong>Nome:</strong> <span id="v-nome_completo"></span></p>
    <p><strong>CPF:</strong> <span id="v-cpf"></span></p>
    <p><strong>Sexo:</strong> <span id="v-sexo"></span></p>
    <p><strong>Nascimento:</strong> <span id="v-data_de_nascimento"></span></p>
    <p><strong>Cidade:</strong> <span id="v-cidade"></span></p>
    <p><strong>Email:</strong> <span id="v-e_mail"></span></p>
    <p><strong>Celular:</strong> <span id="v-celular_whatsapp"></span></p>
    <p><strong>Autorização:</strong> <span id="v-autorizacao"></span></p>

    <button class="btn-fechar" onclick="fecharModal('modalVer')">
  	Fechar
	</button>
  </div>
</div>

<div id="modalEditar" class="modal">
  <div class="modal-content">
    <h2>✏️ Editar Cliente</h2>

    <form method="POST" action="clientes.php?aba=lista">
  	<input type="hidden" name="acao" value="update">

      <input type="hidden" name="id" id="e-id">

      <div class="form-editar-grid">

        <label>CPF</label>
        <input type="text" name="cpf" id="e-cpf">

        <label>Nome Completo</label>
        <input type="text" name="nome_completo" id="e-nome_completo">

        <label>Sexo</label>
        <select name="sexo" id="e-sexo">
          <option value="Feminino">Feminino</option>
          <option value="Masculino">Masculino</option>
          <option value="Outro">Outro</option>
          <option value="Prefiro não informar">Prefiro não informar</option>
        </select>

        <label>Data de Nascimento</label>
        <input type="date" name="data_de_nascimento" id="e-data_de_nascimento">

        <label>Autorização LGPD</label>
        <select name="autorizacao" id="e-autorizacao">
          <option value="SIM">SIM</option>
          <option value="NAO">NÃO</option>
        </select>

        <label>CEP</label>
        <input type="text" name="cep" id="e-cep">

        <label>Logradouro</label>
        <input type="text" name="endereco" id="e-endereco">

        <label>Número</label>
        <input type="text" name="numero" id="e-numero">

        <label>Complemento</label>
        <input type="text" name="complemento" id="e-complemento">

        <label>Bairro</label>
        <input type="text" name="bairro" id="e-bairro">

        <label>Cidade</label>
        <input type="text" name="cidade" id="e-cidade">

        <label>Estado</label>
        <input type="text" name="estado" id="e-estado">

        <label>E-mail</label>
        <input type="email" name="e_mail" id="e-e_mail">

        <label>Celular</label>
        <input type="text" name="celular_whatsapp" id="e-celular_whatsapp">

      </div>

     <div class="modal-acoes">
  <button type="submit" class="btn-acao btn-salvar">
    Salvar
  </button>

  <button type="button"
          class="btn-acao btn-cinza"
          onclick="fecharModal('modalEditar')">
    Cancelar
  </button>
</div>

    </form>
  </div>
</div>

<div id="modalExcluir" class="modal">
  <div class="modal-content">
    <h2>⚠️ Confirmar exclusão?</h2>

    <p>Tem certeza que deseja excluir este cliente?</p>

    <form method="POST" action="clientes.php?aba=lista">
  <input type="hidden" name="acao" value="delete">
  <input type="hidden" name="id" id="del-id">

 <button type="submit" class="btn-acao btn-verde">Sim</button>
<button type="button" class="btn-acao btn-vermelho" onclick="fecharModal('modalExcluir')">Não</button>
</form>

  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    trocarAba('<?= $aba_ativa ?>', false);
  });
</script>

<?php include 'includes/footer.php'; ?>