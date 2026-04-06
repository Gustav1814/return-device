/**
 * Theme and Branding Manager
 */

export const themeManager = {
  init() {
    this.loadBranding();
    this.setupThemeToggle();
    this.applyScrollReveal();
  },

  async loadBranding() {
    const urlParams = new URLSearchParams(window.location.search);
    const brand = urlParams.get('brand') || 'partner';
    
    try {
      // Check localStorage first for user customizations
      const savedConfig = localStorage.getItem(`company_config_${brand}`);
      if (savedConfig) {
        this.applyBranding(JSON.parse(savedConfig));
        return;
      }

      const response = await fetch(`/frontend/data/company.${brand}.json`);
      const config = await response.json();
      
      this.applyBranding(config);
    } catch (e) {
      console.error("Failed to load branding", e);
    }
  },

  applyBranding(config) {
    const root = document.documentElement;
    
    if (config.btnBgColor) {
      root.style.setProperty('--brand-primary', config.btnBgColor);
      // Derive RGB for transparency
      const rgb = this.hexToRgb(config.btnBgColor);
      if (rgb) root.style.setProperty('--brand-primary-rgb', `${rgb.r}, ${rgb.g}, ${rgb.b}`);
    }
    
    if (config.btnTextColor) root.style.setProperty('--text-inverse', config.btnTextColor);
    
    // Set theme
    const currentTheme = config.theme || localStorage.getItem('theme') || 'light';
    root.dataset.theme = currentTheme;
    
    // Update logos
    const logos = document.querySelectorAll('.logo-img');
    logos.forEach(img => img.src = config.logoUrl);
    
    // Update favicon
    let favicon = document.querySelector('link[rel="icon"]');
    if (!favicon) {
      favicon = document.createElement('link');
      favicon.rel = 'icon';
      document.head.appendChild(favicon);
    }
    favicon.href = config.faviconUrl;
  },

  hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
      r: parseInt(result[1], 16),
      g: parseInt(result[2], 16),
      b: parseInt(result[3], 16)
    } : null;
  },

  setupThemeToggle() {
    const toggle = document.querySelector('#theme-toggle');
    if (!toggle) return;

    toggle.checked = document.documentElement.dataset.theme === 'dark';
    
    toggle.addEventListener('change', (e) => {
      const theme = e.target.checked ? 'dark' : 'light';
      document.documentElement.dataset.theme = theme;
      localStorage.setItem('theme', theme);
    });
  },

  applyScrollReveal() {
    const observerOptions = {
      threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('active');
        }
      });
    }, observerOptions);

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
  }
};

// Auto-init
document.addEventListener('DOMContentLoaded', () => themeManager.init());
