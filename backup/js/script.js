    const meta = {
      painel: { title: 'Painel geral', sub: 'Bem-vinda, USUÁRIO' },
      clientes: { title: 'Clientes', sub: 'Cadastro e gestão de clientes' },
      consentimentos: { title: 'Consentimentos', sub: 'Templates e controle de autorizações' },
      guia: { title: '📚 Guia LGPD', sub: 'Conteúdo educativo para o seu negócio' },
      conformidade: { title: 'Minha conformidade', sub: 'Mapa de dados e solicitações de titulares' }
    };

    function go(name) {
      document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
      document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
      document.getElementById('screen-' + name).classList.add('active');
      const nav = document.getElementById('nav-' + name);
      if (nav) nav.classList.add('active');
      document.getElementById('tb-title').textContent = meta[name].title;
      document.getElementById('tb-sub').textContent = meta[name].sub;
      if (name === 'clientes') {
        document.getElementById('form-cliente').style.display = 'none';
        document.getElementById('lista-clientes').style.display = 'block';
      }
    }

    function toggleForm() {
      const form = document.getElementById('form-cliente');
      const lista = document.getElementById('lista-clientes');
      const open = form.style.display !== 'none';
      form.style.display = open ? 'none' : 'block';
      lista.style.display = open ? 'block' : 'none';
    }

    function setTab(el) {
      document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
      el.classList.add('active');
    }

    function openModal(src) {
      document.getElementById('modal-img').src = src;
      document.getElementById('img-modal').classList.add('active');
    }

    function closeModal() {
      document.getElementById('img-modal').classList.remove('active');
    }