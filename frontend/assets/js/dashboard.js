import { appConfig } from './app.config.js';

export const dashboardManager = {
  async init() {
    const brand = new URLSearchParams(window.location.search).get('brand') || 'partner';
    
    try {
      // Load from localStorage or default
      const savedConfig = localStorage.getItem(`company_config_${brand}`);
      let config;
      if (savedConfig) {
        config = JSON.parse(savedConfig);
      } else {
        const response = await fetch(`/frontend/data/company.${brand}.json`);
        if (!response.ok) throw new Error('Config not found');
        config = await response.json();
      }
      
      this.applyWidgetVisibility(config.settings_data.dashboard);
      this.setupAddWidget(brand, config);
    } catch (error) {
      console.error('Dashboard init failed:', error);
    }
  },

  applyWidgetVisibility(config) {
    if (!config || !config.widgets) return;
    
    const fragment = document.createDocumentFragment();
    config.widgets.forEach(w => {
      const el = document.getElementById(w.id);
      if (el) {
        el.style.display = w.visible ? 'flex' : 'none';
        if (w.visible) {
          el.classList.add('reveal', 'active');
        }
      }
    });
  },

  setupAddWidget(brand, config) {
    const addBtn = document.getElementById('add-widget');
    if (!addBtn) return;

    addBtn.addEventListener('click', () => {
      const widgetId = prompt("Enter widget ID to enable (e.g., kpi_commission):");
      if (!widgetId) return;

      const widget = config.settings_data.dashboard.widgets.find(w => w.id === widgetId);
      if (widget) {
        widget.visible = true;
        localStorage.setItem(`company_config_${brand}`, JSON.stringify(config));
        this.applyWidgetVisibility(config.settings_data.dashboard);
      } else {
        console.warn("Widget not found in configuration.");
      }
    });
  }
};

document.addEventListener('DOMContentLoaded', () => dashboardManager.init());
