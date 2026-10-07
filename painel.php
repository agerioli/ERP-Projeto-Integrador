<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'auth/verifica_login.php';
require 'config/conexao.php';
require 'controllers/painel_controller.php';

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main">

  <div class="content">

    <!-- TOPBAR -->
    <div class="topbar">
      <div>
        <div class="topbar-title">Gestão LGPD</div>
        <div class="topbar-sub">
          Olá, <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?>
        </div>
      </div>
    </div>

    <!-- ALERTAS -->
    <div class="alert-strip">
      <?php if (!empty($pendentes)): ?>
        <?php foreach (array_slice($pendentes, 0, 3) as $p): ?>
          <div class="alert-pill ap-a">
            <span class="dot dot-a"></span>
            Consentimento negado — <?= htmlspecialchars($p['nome_completo']) ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="alert-pill ap-g">
          <span class="dot dot-g"></span>
          Nenhum problema de consentimento
        </div>
      <?php endif; ?>
    </div>

    <!-- MÉTRICAS -->
    <div class="metrics-grid">

      <div class="metric-card">
        <div class="metric-label">Clientes cadastrados</div>
        <div class="metric-value"><?= $total_clientes ?></div>
      </div>

      <div class="metric-card">
        <div class="metric-label">Consentimentos ativos</div>
        <div class="metric-value"><?= $total_autorizados ?></div>
      </div>

      <div class="metric-card">
        <div class="metric-label">Conformidade de consentimento</div>
        <div class="metric-value"><?= $conformidade ?>%</div>
      </div>

    </div>

    <!-- EDUCAÇÃO -->
    <div class="sec-title">Saiba mais sobre LGPD</div>

    <div class="three-col mb20">

      <div class="news-card" onclick="go('guia')">
        <div class="news-img ni-b">
          <!-- svg -->
        </div>
        <div class="news-body">
          <div class="news-q">Posso guardar o CPF do cliente?</div>
          <div class="news-a">É possível, mas jamais divulgá-lo sem autorização.</div>
        </div>
      </div>

      <div class="news-card" onclick="go('guia')">
        <div class="news-img ni-g">
          <!-- svg -->
        </div>
        <div class="news-body">
          <div class="news-q">Preciso de autorização para promoções?</div>
          <div class="news-a">Sim. Consentimento é obrigatório.</div>
        </div>
      </div>

      <div class="news-card" onclick="go('guia')">
        <div class="news-img ni-a">
          <!-- svg -->
        </div>
        <div class="news-body">
          <div class="news-q">Como guardar dados com segurança?</div>
          <div class="news-a">Evite armazenar dados em sistemas inseguros.</div>
        </div>
      </div>

    </div>
      
      <!-- MODELOS DE MENSAGEM -->
<div class="sec-title" style="margin-top: 28px;">Modelos de mensagem — Consentimento LGPD</div>

<div class="three-col mb20">

  <div class="msg-card">
    <span class="msg-badge msg-badge-blue">Solicitação</span>
    <div class="msg-titulo">Pedir autorização de uso de dados</div>
    <div id="msg-solicitar" class="msg-texto">Olá, [NOME]! 👋 Para continuarmos enviando novidades e promoções exclusivas, precisamos da sua autorização conforme a LGPD. Você nos permite usar seus dados para comunicação? Responda SIM para autorizar ou NÃO para recusar. Qualquer dúvida, estamos à disposição!</div>
    <button class="msg-btn-copiar" onclick="copiar('msg-solicitar', this)">📋 Copiar mensagem</button>
  </div>

  <div class="msg-card">
    <span class="msg-badge msg-badge-green">Confirmação</span>
    <div class="msg-titulo">Confirmar autorização recebida</div>
    <div id="msg-confirmar" class="msg-texto">Olá, [NOME]! ✅ Recebemos sua autorização e seus dados serão utilizados somente para comunicações relevantes, com total segurança e respeito à sua privacidade, conforme a LGPD. Obrigado pela confiança!</div>
    <button class="msg-btn-copiar" onclick="copiar('msg-confirmar', this)">📋 Copiar mensagem</button>
  </div>

  <div class="msg-card">
    <span class="msg-badge msg-badge-red">Cancelamento</span>
    <div class="msg-titulo">Respeitar pedido de cancelamento</div>
    <div id="msg-cancelar" class="msg-texto">Olá, [NOME]. Registramos sua solicitação de cancelamento de consentimento. Seus dados não serão mais utilizados para comunicações. Caso mude de ideia, basta nos contatar. Obrigado!</div>
    <button class="msg-btn-copiar" onclick="copiar('msg-cancelar', this)">📋 Copiar mensagem</button>
  </div>

</div>

  </div> <!-- content -->

</div> <!-- main -->

<?php include 'includes/footer.php'; ?>