<?php
session_start();
require 'config/conexao.php';
require 'auth/verifica_login.php';
?>

<?php include 'includes/header.php'; ?>

<?php include 'includes/sidebar.php'; ?>

<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
      <div>
        <div class="topbar-title">Inicio</div>
        <div class="topbar-sub">
          Olá, <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?>
        </div>
      </div>
    </div>

    <div class="content">

        <div class="card home-module-card">
            <div class="home-module-icon">📦</div>
            <div class="home-module-content">
                <div class="home-module-title">Gestão de Suprimentos</div>
                <div class="home-module-desc">
                    Cadastre fornecedores e suprimentos, acompanhe estoque mínimo e atual e registre demandas de compra.
                </div>
            </div>
            <a href="gestao_suprimentos.php" class="btn btn-pri home-module-btn">Acessar Gestão →</a>
        </div>

        <div class="card home-module-card">
            <div class="home-module-icon">📊</div>
            <div class="home-module-content">
                <div class="home-module-title">Gestão de Estoque</div>
                <div class="home-module-desc">
                    Controle o estoque físico e do Mercado Livre, registre entradas, saídas, devoluções, perdas e acompanhe a necessidade de reposição.
                </div>
            </div>
            <a href="gestao_estoque.php" class="btn btn-pri home-module-btn">Acessar Gestão →</a>
        </div>

        <div class="card home-module-card">
            <div class="home-module-icon">🔐</div>
            <div class="home-module-content">
                <a href="painel.php" class="home-module-title" style="text-decoration:none;color:inherit;">Gestão LGPD</a>
                <div class="home-module-desc">
                    Faça a gestão das solicitações de LGPD dos clientes, acompanhe consentimentos e consulte informações de conformidade.
                </div>
            </div>
            <a href="painel.php" class="btn btn-pri home-module-btn">Acessar Gestão →</a>
        </div>


    </div>

</div>

<?php include 'includes/footer.php'; ?>