/**
 * auth.js - Sistema de Autenticação SINCRONIZADO (v6.0 FINAL)
 * Agora funciona PERFEITAMENTE com storage.js
 */

class AuthSystem {
  constructor() {
    this.API_BASE = window.API_BASE || (window.location.origin + '/Novamoda/api');
    this.ADMIN_EMAILS = [
      'admin@novamoda.com',
      'nicollastheodoro97@gmail.com'
    ];
    this.init();
  }

  // ==========================================
  // COOKIES (MESMO MÉTODO DO STORAGE.JS)
  // ==========================================
  
  setCookie(name, value, days = 7) {
    const expires = new Date(Date.now() + days * 864e5).toUTCString();
    document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/`;
  }

  getCookie(name) {
    return document.cookie.split('; ').reduce((r, v) => {
      const parts = v.split('=');
      return parts[0] === name ? decodeURIComponent(parts[1]) : r;
    }, '');
  }

  deleteCookie(name) {
    this.setCookie(name, '', -1);
  }

  // ==========================================
  // VALIDAÇÕES
  // ==========================================
  
  validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  validatePassword(password) {
    if (password.length < 6) {
      return { valid: false, message: 'Senha deve ter no mínimo 6 caracteres' };
    }
    return { valid: true };
  }

  isAdmin(email) {
    return this.ADMIN_EMAILS.includes(email.toLowerCase());
  }

  // ==========================================
  // CADASTRO (SINCRONIZADO COM STORAGE.JS)
  // ==========================================
  
  async signup(name, email, password, passwordConfirm) {
    name = name.trim();
    email = email.trim().toLowerCase();

    if (!name || name.length < 3) {
      return { success: false, message: 'Nome deve ter no mínimo 3 caracteres' };
    }

    if (!this.validateEmail(email)) {
      return { success: false, message: 'Email inválido' };
    }

    const passValidation = this.validatePassword(password);
    if (!passValidation.valid) {
      return { success: false, message: passValidation.message };
    }

    if (password !== passwordConfirm) {
      return { success: false, message: 'As senhas não conferem' };
    }

    try {
      const response = await fetch(`${this.API_BASE}/auth/register.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          nome: name,
          email: email,
          password: password
        })
      });

      const data = await response.json();

      if (data.success) {
        // ✅ SINCRONIZAR: Salvar nos mesmos lugares que storage.js
        this.setSession(data.user, data.token);
        
        // ✅ SINCRONIZAR: Atualizar storage.js também
        if (window.storage) {
          window.storage.user = data.user;
          await window.storage.loadCartFromServer();
        }
        
        this.trackEvent('user_signup', { email });
        return { success: true, user: data.user };
      } else {
        return { success: false, message: data.message || 'Erro ao criar conta' };
      }

    } catch (error) {
      console.error('Erro no signup:', error);
      return { success: false, message: 'Erro de conexão com o servidor' };
    }
  }

  // ==========================================
  // LOGIN (SINCRONIZADO COM STORAGE.JS)
  // ==========================================
  
  async login(email, password) {
    email = email.trim().toLowerCase();

    if (!this.validateEmail(email) || !password) {
      return { success: false, message: 'Email ou senha inválidos' };
    }

    try {
      console.log('🔐 Tentando login:', { email, api: `${this.API_BASE}/auth/login.php` });
      
      const response = await fetch(`${this.API_BASE}/auth/login.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          email: email,
          password: password
        })
      });

      console.log('📡 Status da resposta:', response.status, response.statusText);

      // Se não for 200 OK, tentar ler o erro mesmo assim
      let data;
      try {
        data = await response.json();
        console.log('📦 Dados recebidos:', data);
      } catch (e) {
        console.error('❌ Erro ao parsear resposta JSON:', e);
        return { success: false, message: `Erro no servidor (${response.status})` };
      }

      if (data.success) {
        console.log('✅ Login bem-sucedido!');
        
        // ✅ SINCRONIZAR: Salvar nos mesmos lugares que storage.js
        this.setSession(data.user, data.token);
        
        // ✅ SINCRONIZAR: Atualizar storage.js também
        if (window.storage) {
          window.storage.user = data.user;
          await window.storage.loadCartFromServer();
        }
        
        this.trackEvent('user_login', { email });
        return { success: true, user: data.user };
      } else {
        console.error('❌ Login falhou:', data.message);
        return { success: false, message: data.message || 'Credenciais incorretas' };
      }

    } catch (error) {
      console.error('❌ Erro no login:', error);
      return { success: false, message: 'Erro de conexão com o servidor' };
    }
  }

  // ==========================================
  // SESSÃO (USA COOKIES COMO STORAGE.JS)
  // ==========================================
  
  getSession() {
    try {
      const userCookie = this.getCookie('novamoda_user');
      return userCookie ? JSON.parse(userCookie) : null;
    } catch {
      return null;
    }
  }

  setSession(user, token) {
    const sessionData = {
      id: user.id,
      name: user.nome || user.name,
      email: user.email,
      isAdmin: user.isAdmin || this.isAdmin(user.email),
      loginAt: new Date().toISOString()
    };
    
    // ✅ Salvar nos COOKIES (como storage.js)
    this.setCookie('novamoda_user', JSON.stringify(sessionData));
    
    // ✅ Salvar token também
    if (token) {
      this.setCookie('novamoda_token', token);
    }
    
    // ✅ BACKUP: também salvar no localStorage (compatibilidade)
    localStorage.setItem('novamoda_user', JSON.stringify(sessionData));
    
    this.updateUI();
    
    console.log('✅ Sessão salva:', sessionData);
  }

  clearSession() {
    // ✅ Limpar COOKIES
    this.deleteCookie('novamoda_user');
    this.deleteCookie('novamoda_token');
    
    // ✅ Limpar localStorage também
    localStorage.removeItem('novamoda_user');
    
    this.updateUI();
    
    console.log('🗑️ Sessão limpa');
  }

  // ==========================================
  // VERIFICAR SE ESTÁ LOGADO
  // ==========================================
  
  isLoggedIn() {
    const session = this.getSession();
    const isLogged = session !== null;
    console.log('🔐 Verificação de login:', isLogged, session);
    return isLogged;
  }

  // ==========================================
  // LOGOUT
  // ==========================================
  
  logout() {
    const session = this.getSession();
    if (session) {
      this.trackEvent('user_logout', { email: session.email });
    }
    
    // ✅ Limpar storage.js também
    if (window.storage) {
      window.storage.logout();
    } else {
      this.clearSession();
      window.location.href = 'index.html';
    }
  }

  // ==========================================
  // PROTEÇÃO DE PÁGINAS (REATIVADA)
  // ==========================================
  
  requireAuth(redirectToLogin = true) {
    if (!this.isLoggedIn()) {
      if (redirectToLogin) {
        this.showToast('⚠️ Faça login para continuar', 'info');
        setTimeout(() => {
          const currentPage = window.location.pathname.split('/').pop();
          window.location.href = `login.html?next=${currentPage}`;
        }, 1500);
      }
      return false;
    }
    return true;
  }

  requireAdmin(redirectToHome = true) {
    const session = this.getSession();
    if (!session || !session.isAdmin) {
      if (redirectToHome) {
        this.showToast('❌ Acesso negado', 'error');
        setTimeout(() => {
          window.location.href = 'index.html';
        }, 1500);
      }
      return false;
    }
    return true;
  }

  // ==========================================
  // UI - DESATIVADA (storage.js gerencia o header)
  // ==========================================
  
  updateUI() {
    // ✅ NÃO faz nada - deixa storage.js gerenciar o header
    // Isso evita conflito entre os dois sistemas
    console.log('📌 auth.js: UI não gerenciada (storage.js cuida disso)');
  }

  // ==========================================
  // MANIPULAR FORMULÁRIOS
  // ==========================================
  
  handleSignupForm(formElement) {
    if (!formElement) return;

    formElement.addEventListener('submit', async (e) => {
      e.preventDefault();

      const nameField = formElement.querySelector('[name="name"], #signupName, #name');
      const emailField = formElement.querySelector('[name="email"], #signupEmail, #email');
      const passField = formElement.querySelector('[name="password"], #signupPassword, #password');
      const confirmField = formElement.querySelector('[name="passwordConfirm"], #signupPasswordConfirm');

      const name = nameField?.value || '';
      const email = emailField?.value || '';
      const password = passField?.value || '';
      const passwordConfirm = confirmField?.value || '';

      const result = await this.signup(name, email, password, passwordConfirm);

      if (result.success) {
        this.showToast('✓ Conta criada com sucesso!', 'success');
        setTimeout(() => {
          window.location.href = 'index.html';
        }, 1000);
      } else {
        this.showToast(result.message, 'error');
      }
    });
  }

  handleLoginForm(formElement) {
    if (!formElement) return;

    formElement.addEventListener('submit', async (e) => {
      e.preventDefault();

      const emailField = formElement.querySelector('[name="email"], #loginEmail, #email');
      const passField = formElement.querySelector('[name="password"], #loginPassword, #password');

      const email = emailField?.value || '';
      const password = passField?.value || '';

      const result = await this.login(email, password);

      if (result.success) {
        this.showToast('✓ Login realizado com sucesso!', 'success');
        
        setTimeout(() => {
          if (result.user && this.isAdmin(result.user.email)) {
            window.location.href = 'admin.html';
          } else {
            const urlParams = new URLSearchParams(window.location.search);
            const next = urlParams.get('next') || 'index.html';
            window.location.href = next;
          }
        }, 1000);
      } else {
        this.showToast(result.message, 'error');
      }
    });
  }

  // ==========================================
  // TOAST
  // ==========================================
  
  showToast(message, type = 'info') {
    const colors = {
      success: '#14d0d6',
      error: '#ff3b30',
      info: '#0ea5e9'
    };

    const toast = document.createElement('div');
    toast.textContent = message;
    toast.style.cssText = `
      position: fixed;
      bottom: 30px;
      right: 30px;
      background: ${colors[type]};
      color: ${type === 'error' ? '#fff' : '#000'};
      padding: 16px 24px;
      border-radius: 8px;
      font-weight: 600;
      box-shadow: 0 8px 20px rgba(0,0,0,0.3);
      z-index: 9999;
      animation: authSlideIn 0.3s ease;
      font-family: Arial, sans-serif;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
      toast.style.animation = 'authSlideOut 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  // ==========================================
  // TRACK EVENT
  // ==========================================
  
  trackEvent(eventName, data) {
    console.log(`📊 Event: ${eventName}`, data);
  }

  // ==========================================
  // SANITIZAÇÃO
  // ==========================================
  
  escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  // ==========================================
  // INICIALIZAÇÃO
  // ==========================================
  
  init() {
    const signupForm = document.querySelector('#signupForm, form[name="signup"], form.signup');
    const loginForm = document.querySelector('#loginForm, form[name="login"], form.login');

    if (signupForm) this.handleSignupForm(signupForm);
    if (loginForm) this.handleLoginForm(loginForm);

    this.updateUI();

    // ✅ API Global
    window.NovamodaAuth = {
      requireAuth: (redirect) => this.requireAuth(redirect),
      requireAdmin: (redirect) => this.requireAdmin(redirect),
      getSession: () => this.getSession(),
      isLoggedIn: () => this.isLoggedIn(),
      logout: () => this.logout(),
      isAdmin: (email) => this.isAdmin(email)
    };

    console.log('✅ Auth System v6.0 FINAL (SINCRONIZADO)');
    console.log('🔐 Status login:', this.isLoggedIn(), this.getSession());
  }
}

// CSS para animações
if (!document.getElementById('auth-animations')) {
  const styleEl = document.createElement('style');
  styleEl.id = 'auth-animations';
  styleEl.textContent = `
    @keyframes authSlideIn {
      from { transform: translateX(400px); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    @keyframes authSlideOut {
      from { transform: translateX(0); opacity: 1; }
      to { transform: translateX(400px); opacity: 0; }
    }
  `;
  document.head.appendChild(styleEl);
}

// Inicializar
const auth = new AuthSystem();
window.auth = auth;