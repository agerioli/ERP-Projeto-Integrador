<!-- ========================= -->
<!-- /includes/sidebar.php -->
<!-- ========================= -->

<div class="sidebar">

    <div class="sb-header"></div>

    <!-- INÍCIO -->
    <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/index.php"
       class="guia-btn">

        <svg width="14"
             height="14"
             viewBox="0 0 14 14"
             fill="none">

            <circle cx="7"
                    cy="7"
                    r="5.5"
                    stroke="#fff"
                    stroke-width="1.3" />

            <path d="M7 6.5v4M7 4.5v1"
                  stroke="#fff"
                  stroke-width="1.5" />

        </svg>

        <span>Início</span>

    </a>


    <!-- CLIENTES -->
    <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/clientes.php"
       class="nav-item">

        <svg width="15"
             height="15"
             viewBox="0 0 15 15"
             fill="none">

            <circle cx="7.5"
                    cy="5.5"
                    r="2.8"
                    stroke="currentColor"
                    stroke-width="1.3" />

            <path d="M2 14c0-3 2.5-5.5 5.5-5.5S13 11 13 14"
                  stroke="currentColor"
                  stroke-width="1.3" />

        </svg>

        Clientes

    </a>


    <!-- GESTÃO DE SUPRIMENTOS -->
<a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/gestao_suprimentos.php" class="nav-item">
    <span>📦</span>

        Gestão de Suprimentos

    </a>

      <!-- GESTÃO DE ESTOQUE -->
      <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/gestao_estoque.php" class="nav-item">
      <span>📊</span>

          Gestão de Estoque

    </a>

    <!-- GESTÃO LGPD -->
    <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/painel.php"
       class="nav-item">

        <svg width="15"
             height="15"
             viewBox="0 0 15 15"
             fill="none">

            <rect x="1"
                  y="1"
                  width="5.5"
                  height="5.5"
                  rx="1.2"
                  fill="currentColor" />

            <rect x="8.5"
                  y="1"
                  width="5.5"
                  height="5.5"
                  rx="1.2"
                  fill="currentColor"
                  opacity=".35" />

            <rect x="1"
                  y="8.5"
                  width="5.5"
                  height="5.5"
                  rx="1.2"
                  fill="currentColor"
                  opacity=".35" />

            <rect x="8.5"
                  y="8.5"
                  width="5.5"
                  height="5.5"
                  rx="1.2"
                  fill="currentColor"
                  opacity=".35" />

        </svg>

        Gestão LGPD

    </a>

    <!-- GUIA LGPD -->
    <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/guia_lgpd.php"
       class="nav-item">

        <svg width="15"
             height="15"
             viewBox="0 0 15 15"
             fill="none">

            <rect x="2"
                  y="1"
                  width="11"
                  height="13"
                  rx="1.5"
                  stroke="currentColor"
                  stroke-width="1.3" />

            <path d="M5 5h5M5 8h5M5 11h3"
                  stroke="currentColor"
                  stroke-width="1.3" />

        </svg>

        Guia LGPD

    </a>

    <div class="nav-sep"></div>


    <!-- LOGOUT -->
    <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/auth/logout.php"
       class="nav-item">

        <svg width="15"
             height="15"
             viewBox="0 0 15 15"
             fill="none">

            <path d="M6 2H3.5A1.5 1.5 0 002 3.5v8A1.5 1.5 0 003.5 13H6"
                  stroke="currentColor"
                  stroke-width="1.3"/>

            <path d="M9 10.5L13 7.5L9 4.5"
                  stroke="currentColor"
                  stroke-width="1.3"/>

            <path d="M13 7.5H5"
                  stroke="currentColor"
                  stroke-width="1.3"/>

        </svg>

        Sair

    </a>

</div>