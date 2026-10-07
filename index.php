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

        <div class="card">
            <div style="font-size:20px;font-weight:600;color:var(--g900)">
                Sistema de Gestão LGPD
            </div>

            <div style="margin-top:10px;font-size:13px;color:var(--g600);line-height:1.6">
                Este sistema foi desenvolvido para auxiliar pequenas empresas e comércios na organização
                e gestão de dados pessoais de clientes, garantindo maior conformidade com a Lei Geral de
                Proteção de Dados (LGPD).
                <br><br>
                Através da plataforma, é possível registrar informações de clientes, controlar
                consentimentos, acompanhar solicitações dos titulares e visualizar o nível de
                conformidade da empresa de forma simples e prática.
                <br><br>
                O objetivo é facilitar a adequação à LGPD, reduzindo riscos e promovendo mais segurança
                no tratamento de dados pessoais.
            </div>
        </div>

    </div>

</div>

<?php include 'includes/footer.php'; ?>