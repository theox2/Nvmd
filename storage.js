/**
 * storage.js - Sistema de armazenamento COM BANCO DE DADOS
 * Gerencia: Autenticação, Carrinho, Pedidos e UI do Header
 */

const API_BASE = window.API_BASE || (window.location.origin + '/Novamoda/api');

class Storage {
  constructor() {
    this.user = null;
    this.cart = [];
    this.ADMIN_EMAILS = [
      'admin@novamoda.com',
      'nicollastheodoro97@gmail.com'
    ];
    this.init();
  }

  // ==========================================
  // INICIALIZAÇÃO
  // ==========================================
  async init() {
    // ✅ Verificar cookies primeiro
    console.log('🍪 Cookies disponíveis:', document.cookie);
    
    const userSession = this.getCookie('novamoda_user');
    console.log('👤 Cookie novamoda_user:', userSession);
    
    if (userSession) {
      try {
        this.user = JSON.parse(userSession);
        console.log('✅ Usuário carregado:', this.user);
      } catch (e) {
        console.error('❌ Erro ao parsear usuário:', e);
        this.user = null;
      }
    } else {
      console.warn('⚠️ Cookie novamoda_user não encontrado');
    }
    
    await this.loadCartFromServer();
    
    // ✅ Atualizar UI quando DOM estiver pronto
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.updateUI());
    } else {
      this.updateUI();
    }
    
    // ✅ Atualizar UI novamente após 500ms (garantir que carregou)
    setTimeout(() => this.updateUI(), 500);
    
    console.log('✅ Storage inicializado COM BANCO DE DADOS');
  }

  // ==========================================
  // COOKIES (substituem localStorage)
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
  // USUÁRIO
  // ==========================================
  async login(email, password) {
    try {
      const response = await fetch(`${API_BASE}/auth/login.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      });

      const data = await response.json();

      if (data.success) {
        this.user = data.user;
        this.setCookie('novamoda_user', JSON.stringify(data.user));
        this.setCookie('novamoda_token', data.token);
        this.updateUI();
        return { success: true, user: data.user };
      } else {
        return { success: false, message: data.message };
      }
    } catch (error) {
      console.error('Erro no login:', error);
      return { success: false, message: 'Erro de conexão' };
    }
  }

  async register(userData) {
    try {
      const response = await fetch(`${API_BASE}/auth/register.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(userData)
      });

      const data = await response.json();

      if (data.success) {
        this.user = data.user;
        this.setCookie('novamoda_user', JSON.stringify(data.user));
        this.setCookie('novamoda_token', data.token);
        this.updateUI();
        return { success: true, user: data.user };
      } else {
        return { success: false, message: data.message };
      }
    } catch (error) {
      console.error('Erro no cadastro:', error);
      return { success: false, message: 'Erro de conexão' };
    }
  }

  logout() {
    this.user = null;
    this.cart = [];
    this.deleteCookie('novamoda_user');
    this.deleteCookie('novamoda_token');
    localStorage.removeItem('novamoda_user');
    this.updateUI();
    window.location.href = 'index.html';
  }

  getUser() {
    return this.user;
  }

  isLoggedIn() {
    return this.user !== null;
  }

  isAdmin(email) {
    if (!email && this.user) {
      email = this.user.email;
    }
    return this.ADMIN_EMAILS.includes(email?.toLowerCase());
  }

  // ==========================================
  // UI - ATUALIZAR HEADER
  // ==========================================
  updateUI() {
    console.log('🎨 updateUI chamado. Usuário:', this.user);
    
    document.querySelectorAll('.novamoda-user-area').forEach(el => {
      console.log('🗑️ Removendo área existente');
      el.remove();
    });

    const userArea = document.createElement('div');
    userArea.className = 'novamoda-user-area';
    userArea.style.cssText = 'display:flex;align-items:center;gap:10px;';

    if (this.user) {
      const firstName = (this.user.name || this.user.nome || 'Usuário').split(' ')[0];
      const isUserAdmin = this.isAdmin(this.user.email);
      
      console.log('✅ Criando UI logada para:', firstName);
      
      userArea.innerHTML = `
        <div style="display:flex;align-items:center;gap:12px;background:#111;padding:8px 12px;border-radius:8px;">
          <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#14d0d6,#0ea5e9);display:flex;align-items:center;justify-content:center;font-weight:700;color:#000;">
            ${firstName[0].toUpperCase()}
          </div>
          <div>
            <div style="color:#fff;font-size:13px;font-weight:600;">Olá, ${this.escapeHtml(firstName)}</div>
            <div style="font-size:11px;color:#888;">${isUserAdmin ? '👑 Admin' : 'Cliente'}</div>
          </div>
        </div>
        ${isUserAdmin ? '<a href="admin.html" class="btn" style="padding:8px 12px;font-size:13px;margin-left:8px;">📊 Admin</a>' : ''}
        <button id="storage-logout-btn" class="btn" style="background:#222;color:#aaa;padding:8px 12px;font-size:13px;border:none;border-radius:6px;cursor:pointer;">Sair</button>
      `;
    } else {
      console.log('⚪ Criando UI deslogada');
      userArea.innerHTML = '<a href="login.html" class="btn entrar-btn">Entrar</a>';
    }

    const rightArea = document.querySelector('.right-area');
    const entrarBtn = document.querySelector('.entrar-btn');
    
    console.log('📍 rightArea:', rightArea);
    console.log('📍 entrarBtn:', entrarBtn);
    
    if (entrarBtn) {
      console.log('✅ Substituindo botão Entrar');
      entrarBtn.replaceWith(userArea);
    } else if (rightArea) {
      const icons = rightArea.querySelector('.icons');
      if (icons) {
        console.log('✅ Inserindo antes dos ícones');
        rightArea.insertBefore(userArea, icons);
      } else {
        console.log('✅ Adicionando ao final da rightArea');
        rightArea.appendChild(userArea);
      }
    } else {
      console.warn('⚠️ Não encontrou onde inserir o userArea!');
    }

    const logoutBtn = document.getElementById('storage-logout-btn');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', () => this.logout());
    }

    console.log('🎨 UI atualizada:', this.user ? 'Logado' : 'Deslogado');
  }

  escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  // ==========================================
  // CARRINHO - USA BANCO DE DADOS
  // ==========================================
  async loadCartFromServer() {
    if (!this.user) {
      this.cart = [];
      this.updateCartCount();
      return;
    }

    try {
      const response = await fetch(`${API_BASE}/carrinho/listar.php?usuario_id=${this.user.id}`);
      const data = await response.json();

      console.log('📦 Resposta da API do carrinho:', data);

      if (data.success && data.data.itens) {
        // ✅ MAPEAMENTO CORRETO DOS CAMPOS DO PHP
        this.cart = data.data.itens.map(item => {
          const mappedItem = {
            id: item.produto_id,
            name: item.produto_nome,  // ✅ produto_nome (não nome_produto)
            price: parseFloat(item.preco_unitario),
            img: item.imagem_principal || item.imagem || 'https://via.placeholder.com/400',  // ✅ imagem_principal
            qty: item.quantidade,
            size: item.tamanho,
            color: item.cor
          };
          console.log('✅ Item mapeado:', mappedItem);
          return mappedItem;
        });
        
        console.log('✅ Carrinho carregado:', this.cart);
      } else {
        console.log('⚠️ Carrinho vazio ou erro na API');
        this.cart = [];
      }
      
      this.updateCartCount();
    } catch (error) {
      console.error('❌ Erro ao carregar carrinho:', error);
      this.cart = [];
    }
  }

  async addToCart(product, qty = 1) {
    if (!this.user) {
      alert('Faça login para adicionar produtos ao carrinho');
      window.location.href = 'login.html';
      return false;
    }

    try {
      const response = await fetch(`${API_BASE}/carrinho/adicionar.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          usuario_id: this.user.id,
          produto_id: product.id,
          quantidade: qty,
          tamanho: product.size || null,
          cor: product.color || null
        })
      });

      const data = await response.json();

      if (data.success) {
        await this.loadCartFromServer();
        this.showToast(`✓ ${product.name} adicionado ao carrinho!`, 'success');
        return true;
      } else {
        this.showToast(data.message, 'error');
        return false;
      }
    } catch (error) {
      console.error('Erro ao adicionar ao carrinho:', error);
      this.showToast('Erro ao adicionar produto', 'error');
      return false;
    }
  }

  async updateCartQty(productId, delta) {
    const item = this.cart.find(i => i.id === productId);
    if (!item) return false;

    const newQty = item.qty + delta;
    if (newQty < 1) {
      return this.removeFromCart(productId);
    }

    try {
      const response = await fetch(`${API_BASE}/carrinho/atualizar.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          usuario_id: this.user.id,
          produto_id: productId,
          quantidade: newQty
        })
      });

      const data = await response.json();

      if (data.success) {
        await this.loadCartFromServer();
        return true;
      }
      return false;
    } catch (error) {
      console.error('Erro ao atualizar quantidade:', error);
      return false;
    }
  }

  async removeFromCart(productId) {
    try {
      const response = await fetch(`${API_BASE}/carrinho/remover.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          usuario_id: this.user.id,
          produto_id: productId
        })
      });

      const data = await response.json();

      if (data.success) {
        await this.loadCartFromServer();
        return true;
      }
      return false;
    } catch (error) {
      console.error('Erro ao remover do carrinho:', error);
      return false;
    }
  }

  async clearCart() {
    try {
      const response = await fetch(`${API_BASE}/carrinho/remover.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          usuario_id: this.user.id,
          limpar_tudo: true
        })
      });

      const data = await response.json();

      if (data.success) {
        this.cart = [];
        this.updateCartCount();
        return true;
      }
      return false;
    } catch (error) {
      console.error('Erro ao limpar carrinho:', error);
      return false;
    }
  }

  getCart() {
    return this.cart;
  }

  getCartTotal() {
    return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
  }

  updateCartCount() {
    const count = this.cart.reduce((sum, item) => sum + item.qty, 0);
    document.querySelectorAll('.cart-count').forEach(el => {
      el.textContent = count;
      el.style.display = count > 0 ? 'inline-block' : 'none';
    });
  }

  // ==========================================
  // PEDIDOS
  // ==========================================
  async getOrders() {
    if (!this.user) return [];

    try {
      const response = await fetch(`${API_BASE}/pedidos/listar.php?usuario_id=${this.user.id}`);
      const data = await response.json();

      if (data.success) {
        return data.data || [];
      }
      return [];
    } catch (error) {
      console.error('Erro ao buscar pedidos:', error);
      return [];
    }
  }

  async saveOrder(orderData) {
    if (!this.user) {
      alert('Faça login para finalizar a compra');
      return null;
    }

    try {
      const response = await fetch(`${API_BASE}/pedidos/criar.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          usuario_id: this.user.id,
          endereco: {
            cep: orderData.cep,
            estado: orderData.state,
            cidade: orderData.city,
            bairro: orderData.neighborhood,
            endereco: orderData.address,
            numero: orderData.number,
            complemento: orderData.complement
          },
          forma_pagamento: orderData.payment,
          itens: this.cart.map(item => ({
            produto_id: item.id,
            nome: item.name,
            quantidade: item.qty,
            tamanho: item.size,
            cor: item.color,
            preco: item.price
          })),
          subtotal: this.getCartTotal(),
          desconto: 0,
          frete: 0,
          total: this.getCartTotal(),
          observacoes: null
        })
      });

      const data = await response.json();

      if (data.success) {
        await this.clearCart();
        return data.pedido;
      } else {
        this.showToast(data.message, 'error');
        return null;
      }
    } catch (error) {
      console.error('Erro ao criar pedido:', error);
      this.showToast('Erro ao processar pedido', 'error');
      return null;
    }
  }

  // ==========================================
  // TOAST NOTIFICATIONS
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
      animation: storageSlideIn 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
      toast.style.animation = 'storageSlideOut 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }
}

// Inicializar storage globalmente
const storage = new Storage();
window.storage = storage;

// CSS para animações
if (!document.getElementById('storage-animations')) {
  const styleEl = document.createElement('style');
  styleEl.id = 'storage-animations';
  styleEl.textContent = `
    @keyframes storageSlideIn {
      from { transform: translateX(400px); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    @keyframes storageSlideOut {
      from { transform: translateX(0); opacity: 1; }
      to { transform: translateX(400px); opacity: 0; }
    }
  `;
  document.head.appendChild(styleEl);
}