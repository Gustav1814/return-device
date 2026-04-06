import { navigationConfig, appConfig } from './app.config.js';

export const navigationManager = {
  init() {
    this.renderNav();
    this.highlightActive();
  },

  renderNav() {
    const navContainer = document.getElementById('main-nav');
    if (!navContainer) return;

    const groups = this.groupBy(navigationConfig, 'group');
    const fragment = document.createDocumentFragment();
    
    // Default group (no label)
    if (groups['undefined']) {
      this.renderGroup(groups['undefined'], fragment);
    }

    // Other groups
    Object.entries(groups).forEach(([groupName, items]) => {
      if (groupName !== 'undefined') {
        const groupDiv = document.createElement('div');
        groupDiv.className = 'nav-group';
        
        const label = document.createElement('div');
        label.className = 'nav-label';
        label.textContent = groupName;
        
        groupDiv.appendChild(label);
        this.renderGroup(items, groupDiv);
        fragment.appendChild(groupDiv);
      }
    });

    navContainer.innerHTML = '';
    navContainer.appendChild(fragment);
    lucide.createIcons();
  },

  renderGroup(items, container) {
    items
      .filter(item => !item.requiresPlatform || appConfig.tenantMode === 'platform')
      .forEach(item => {
        const navItem = document.createElement('a');
        navItem.href = item.path;
        navItem.className = 'nav-item';
        navItem.dataset.navId = item.id;
        
        const icon = document.createElement('i');
        icon.setAttribute('data-lucide', item.icon);
        
        const span = document.createElement('span');
        span.textContent = item.label;
        
        navItem.appendChild(icon);
        navItem.appendChild(span);
        
        if (item.children) {
          const chevron = document.createElement('i');
          chevron.setAttribute('data-lucide', 'chevron-down');
          chevron.className = 'nav-chevron';
          navItem.appendChild(chevron);
        }
        
        container.appendChild(navItem);
      });
  },

  groupBy(array, key) {
    return array.reduce((result, currentValue) => {
      (result[currentValue[key]] = result[currentValue[key]] || []).push(currentValue);
      return result;
    }, {});
  },

  highlightActive() {
    const currentPath = window.location.pathname;
    const navItems = document.querySelectorAll('.nav-item');
    
    navItems.forEach(item => {
      const href = item.getAttribute('href');
      if (currentPath.endsWith(href) || (currentPath === '/frontend/' && href.includes('index.html'))) {
        item.classList.add('active');
      } else {
        item.classList.remove('active');
      }
    });
  },

  setupSidebarToggle() {
    const toggleBtn = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    
    if (!toggleBtn || !sidebar) return;

    toggleBtn.addEventListener('click', () => {
      if (window.innerWidth <= 768) {
        sidebar.classList.toggle('mobile-open');
      } else {
        sidebar.classList.toggle('collapsed');
        // Save state
        localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
      }
    });

    // Restore state on desktop
    if (window.innerWidth > 768 && localStorage.getItem('sidebar-collapsed') === 'true') {
      sidebar.classList.add('collapsed');
    }
  }
};

document.addEventListener('DOMContentLoaded', () => {
  navigationManager.init();
  navigationManager.setupSidebarToggle();
});
