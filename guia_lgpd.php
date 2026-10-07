<?php
session_start();
require 'auth/verifica_login.php';
?>

<?php include 'includes/header.php'; ?>

<?php include 'includes/sidebar.php'; ?>

<div class="main">
    <div class="topbar">
      <div>
        <div class="topbar-title" id="tb-title">Guia LGPD</div>
        <div class="topbar-sub" id="tb-sub">Conteúdo educativo para o seu negócio</div>
      </div>
      <div id="tb-actions"></div>
    </div>


    <div class="content">
      <div class="screen active" id="screen-guia">
        <div class="cat-tabs">
          <div class="cat-tab active">Básico</div>
        </div>

        <div class="featured-card">
          <div class="feat-img-area">
            <svg width="60" height="60" viewBox="0 0 60 60" fill="none">
              <rect x="8" y="6" width="36" height="46" rx="4" fill="#B5D4F4" />
              <path d="M16 20h20M16 28h20M16 36h14" stroke="#185FA5" stroke-width="2" />
              <circle cx="46" cy="46" r="10" fill="#185FA5" />
              <path d="M42 46h8M46 42v8" stroke="#fff" stroke-width="2" />
            </svg>
          </div>
          <div class="feat-body">
            <span class="feat-tag">O básico</span>
            <div class="feat-title">O que é a LGPD e por que ela vale para o seu negócio?</div>
            <div class="feat-desc">Mesmo sendo pequeno, se você guarda nome e telefone de cliente, a lei já se aplica a
              você. Entenda o que isso significa na prática e como se proteger.</div>
          </div>
        </div>

        <div class="guia-carousel">
          <div class="guia-track">

            <!-- Card 1 -->
            <div class="guia-img-card">
              <div class="guia-img-wrap">
                <img src="assets/img/o_que_e_lgpd.jpeg" alt="O que é LGPD" onclick="openModal(this.src)"
                  onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
                <div class="img-fallback" style="display:none;background:var(--blue-l)">
                  <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
                    <rect x="4" y="4" width="28" height="28" rx="4" fill="#B5D4F4" />
                    <path d="M10 18h16M10 24h10" stroke="#185FA5" stroke-width="1.8" />
                  </svg>
                  <span style="color:var(--blue-d)">O que é LGPD</span>
                </div>
              </div>
              <div class="guia-img-body">
                <span class="tag tag-blue">O básico</span>
                <div class="guia-img-title">O que é LGPD na prática</div>
                <div class="guia-img-desc">Entenda dados pessoais, segurança, direitos do cliente e consentimento.</div>
              </div>
            </div>

            <!-- Card 2 -->
            <div class="guia-img-card">
              <div class="guia-img-wrap">
                <img src="assets/img/infos1.jpeg" alt="Informações sobre a LGPD" onclick="openModal(this.src)"
                  onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
                <div class="img-fallback" style="display:none;background:var(--green-l)">
                  <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
                    <circle cx="18" cy="18" r="14" fill="#C0DD97" />
                    <path d="M11 18l6 6 10-10" stroke="#3B6D11" stroke-width="2" />
                  </svg>
                  <span style="color:var(--green)">Informações sobre a LGPD</span>
                </div>
              </div>
              <div class="guia-img-body">
                <span class="tag tag-ok">LGPD para pequenos negócios</span>
                <div class="guia-img-title">LGPD para pequenos negócios</div>
                <div class="guia-img-desc">Tudo que você precisa saber para começar, sem complicação.</div>
              </div>
            </div>

            <!-- Card 3 -->
            <div class="guia-img-card">
              <div class="guia-img-wrap">
                <img src="assets/img/o_que_e_lgpd_2.jpeg" alt="Detalhes sobre a LGPD" onclick="openModal(this.src)"
                  onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
                <div class="img-fallback" style="display:none;background:var(--purple-l)">
                  <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
                    <path d="M18 3L32 10v10c0 7-6 11-14 12C6 31 0 27 0 20V10L18 3z" fill="#CECBF6" />
                    <path d="M12 18l5 5 10-10" stroke="#534AB7" stroke-width="2" />
                  </svg>
                  <span style="color:var(--purple)">Bases legais</span>
                </div>
              </div>
              <div class="guia-img-body">
                <span class="tag tag-purple">Princípios</span>
                <div class="guia-img-title">Bases legais, finalidade, transparência e armazenamento</div>
                <div class="guia-img-desc">Finalidade, transparência e como armazenar dados corretamente.</div>
              </div>
            </div>


            <!-- Card 4 -->
            <div class="guia-img-card">
              <div class="guia-img-wrap">
                <img src="assets/img/infos2.jpeg" alt="Bases legais e princípios" onclick="openModal(this.src)"
                  onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
                <div class="img-fallback" style="display:none;background:var(--amber-l)">
                  <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
                    <path d="M18 4L34 32H2L18 4z" fill="#FAC775" />
                    <path d="M18 16v9M18 27v3" stroke="#854F0B" stroke-width="2.2" />
                  </svg>
                  <span style="color:var(--amber)">Guia completo</span>
                </div>
              </div>
              <div class="guia-img-body">
                <span class="tag tag-pend">Guia completo</span>
                <div class="guia-img-title">Por que a LGPD é importante e como se adequar</div>
                <div class="guia-img-desc">Da coleta ao descarte: proteja seu negócio e seus clientes.</div>
              </div>
            </div>

          </div>
        </div>
      </div>

  </div>
  </div>

  <div id="img-modal" class="img-modal" onclick="closeModal()">
    <span class="close-btn">&times;</span>
    <img id="modal-img" />
  </div>

  <?php include 'includes/footer.php'; ?>