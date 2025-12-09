/**
 * admin-api.js - 100% BANCO (ZERO localStorage)
 * @version 2.0.0
 */

const API_BASE = window.API_BASE || (window.location.origin + '/Novamoda/api');

class AdminAPI {
  constructor() { this.init(); }
  
  async request(endpoint, options = {}) {
    try {
      const url = `${API_BASE}${endpoint}`;
      const response = await fetch(url, {
        headers: { 'Content-Type': 'application/json', ...options.headers },
        ...options
      });
      const data = await response.json();
      if (!response.ok) throw new Error(data.message || 'Erro');
      return data;
    } catch (error) {
      console.error('❌ API:', error);
      this.showToast(error.message, 'error');
      throw error;
    }
  }

  async loadDashboard() {
    try {
      const stats = await this.request('/admin/dashboard.php');
      if (stats.success) {
        const d = stats.data;
        this.updateElement('totalPedidos', d.total_pedidos);
        this.updateElement('totalClientes', d.total_clientes);
        this.updateElement('newCustomers', d.novos_clientes);
        this.updateElement('vendasHoje', `R$ ${this.formatMoney(d.vendas_hoje)}`);
        if (d.estoque_baixo > 0) this.updateElement('lowStockCount', d.estoque_baixo);
      }
    } catch (error) {
      console.error('Erro dashboard:', error);
    }
  }

  async renderProdutosAdmin() {
    const grid = document.getElementById('productsGrid');
    if (!grid) return;
    grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:#888;">⏳ Carregando...</div>';

    try {
      const response = await this.request('/produtos/listar.php?limit=100');
      if (!response.success || !response.data || response.data.length === 0) {
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:60px;color:#888;">📦 Nenhum produto</div>';
        return;
      }

      grid.innerHTML = response.data.map(p => {
        const stockColor = p.estoque > 10 ? '#4ade80' : (p.estoque > 0 ? '#fbbf24' : '#ff3b30');
        const stockLabel = p.estoque > 10 ? 'Em Estoque' : (p.estoque > 0 ? 'Baixo' : 'Esgotado');
        return `
          <div style="background:#111;border:1px solid #222;border-radius:12px;overflow:hidden;">
            <img src="${p.imagem_principal}" style="width:100%;height:200px;object-fit:cover;" onerror="this.src='https://via.placeholder.com/400'">
            <div style="padding:16px;">
              <h3 style="color:#fff;font-size:16px;margin:0 0 8px 0;">${p.nome}</h3>
              <div style="color:#14d0d6;font-size:20px;font-weight:800;margin-bottom:8px;">R$ ${parseFloat(p.preco).toFixed(2).replace('.', ',')}</div>
              <div style="display:flex;gap:8px;font-size:13px;color:#888;margin-bottom:12px;">
                <span style="color:${stockColor};">● ${stockLabel}: ${p.estoque}</span>
              </div>
              <div style="display:flex;gap:8px;">
                <button class="btn btn-primary" onclick="editarProduto(${p.id})" style="flex:1;padding:8px;">✏️ Editar</button>
                <button class="btn btn-danger" onclick="deletarProduto(${p.id})" style="padding:8px 12px;">🗑️</button>
              </div>
            </div>
          </div>
        `;
      }).join('');
    } catch (error) {
      grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:#ff3b30;">❌ Erro</div>';
    }
  }

  async renderClientesAdmin() {
    const tbody = document.querySelector('#clientsTable tbody');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:40px;color:#888;">⏳ Carregando...</td></tr>';

    try {
      const response = await this.request('/admin/clientes.php');
      if (!response.success || !response.data || response.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:40px;color:#888;">👥 Nenhum cliente</td></tr>';
        return;
      }

      tbody.innerHTML = response.data.map(c => {
        const data = new Date(c.data_cadastro).toLocaleDateString('pt-BR');
        return `
          <tr>
            <td><strong>#${c.id}</strong></td>
            <td>
              <div style="font-weight:600;color:#fff;">${c.nome}</div>
              <div style="font-size:12px;color:#888;">${c.email}</div>
            </td>
            <td>${c.telefone || '-'}</td>
            <td>${c.total_pedidos || 0}</td>
            <td><strong style="color:#14d0d6;">R$ ${this.formatMoney(c.total_gasto || 0)}</strong></td>
            <td>${data}</td>
          </tr>
        `;
      }).join('');
    } catch (error) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#ff3b30;">❌ Erro</td></tr>';
    }
  }

  async renderPedidosAdmin() {
    const tbody = document.querySelector('#ordersTable tbody');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:40px;color:#888;">⏳ Carregando...</td></tr>';

    try {
      const response = await this.request('/admin/pedidos/listar.php');
      if (!response.success || !response.data || response.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:40px;color:#888;">📦 Nenhum pedido</td></tr>';
        return;
      }

      tbody.innerHTML = response.data.slice(0, 10).map(p => {
        const data = new Date(p.data_pedido).toLocaleDateString('pt-BR');
        return `
          <tr>
            <td><strong>${p.numero_pedido}</strong></td>
            <td>
              <strong>${p.cliente_nome}</strong><br>
              <small style="color:#888;">${p.cliente_email}</small>
            </td>
            <td>${p.total_itens || 0} itens</td>
            <td><strong style="color:#14d0d6;">R$ ${this.formatMoney(p.total)}</strong></td>
            <td><span class="status status-${p.status}">${this.getStatusLabel(p.status)}</span></td>
            <td>${data}</td>
            <td><button class="btn" onclick="verPedido(${p.id})">Ver</button></td>
          </tr>
        `;
      }).join('');
    } catch (error) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#ff3b30;">❌ Erro</td></tr>';
    }
  }

  formatMoney(v) { return parseFloat(v || 0).toFixed(2).replace('.', ','); }
  updateElement(id, v) { const el = document.getElementById(id); if (el) el.textContent = v; }
  getStatusLabel(s) {
    const l = {pendente:'Pendente',processando:'Processando',enviado:'Enviado',entregue:'Entregue',cancelado:'Cancelado'};
    return l[s] || s;
  }
  
  showToast(msg, type = 'info') {
    const colors = {success:'#14d0d6',error:'#ff3b30',info:'#0ea5e9'};
    const toast = document.createElement('div');
    toast.textContent = msg;
    toast.style.cssText = `position:fixed;bottom:30px;right:30px;background:${colors[type]};color:${type==='error'?'#fff':'#000'};padding:16px 24px;border-radius:8px;font-weight:600;box-shadow:0 8px 20px rgba(0,0,0,0.3);z-index:9999;animation:adminSlideIn 0.3s;`;
    document.body.appendChild(toast);
    setTimeout(() => {
      toast.style.animation = 'adminSlideOut 0.3s';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  init() {
    const path = window.location.pathname;
    if (path.includes('admin.html')) {
      this.loadDashboard();
      this.renderPedidosAdmin();
    } else if (path.includes('admin-produtos')) {
      this.renderProdutosAdmin();
    } else if (path.includes('admin-clientes')) {
      this.renderClientesAdmin();
    } else if (path.includes('admin-pedidos')) {
      this.renderPedidosAdmin();
    }
    console.log('✅ AdminAPI 100% Banco');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  window.adminAPI = new AdminAPI();
  
  window.verPedido = async (id) => {
    try {
      const response = await fetch(`${API_BASE}/admin/pedidos/detalhes.php?id=${id}`);
      const data = await response.json();
      if (data.success) {
        const p = data.data;
        const itens = p.itens.map(i => `- ${i.quantidade}x ${i.produto_nome} R$ ${parseFloat(i.preco_unitario).toFixed(2)}`).join('\n');
        alert(`📦 ${p.numero_pedido}\n\n${p.cliente_nome}\n${p.cliente_email}\n\nItens:\n${itens}\n\nTotal: R$ ${parseFloat(p.total).toFixed(2)}`);
      }
    } catch (error) {
      alert('Erro ao buscar pedido');
    }
  };
});

if (!document.getElementById('admin-animations')) {
  const styleEl = document.createElement('style');
  styleEl.id = 'admin-animations';
  styleEl.textContent = `@keyframes adminSlideIn{from{transform:translateX(400px);opacity:0}to{transform:translateX(0);opacity:1}}@keyframes adminSlideOut{from{transform:translateX(0);opacity:1}to{transform:translateX(400px);opacity:0}}`;
  document.head.appendChild(styleEl);
}
