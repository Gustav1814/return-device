/**
 * Chart.js Implementation for Device Return Dashboard
 */

export const chartManager = {
  init() {
    this.renderTrends();
    this.renderDistribution();
  },

  renderTrends() {
    const ctx = document.getElementById('shipmentsTrendChart');
    if (!ctx) return;

    const canvasCtx = ctx.getContext('2d');
    
    // Create Vibrant Nexus-style Gradients
    const grad1 = canvasCtx.createLinearGradient(0, 0, 0, 350);
    grad1.addColorStop(0, '#6366f1'); // Purple/Blue
    grad1.addColorStop(1, 'rgba(99, 102, 241, 0)');

    const grad2 = canvasCtx.createLinearGradient(0, 0, 0, 350);
    grad2.addColorStop(0, '#10b981'); // Emerald
    grad2.addColorStop(1, 'rgba(16, 185, 129, 0)');

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'],
        datasets: [
          {
            label: 'Revenue',
            data: [6500, 5900, 8000, 8100, 5600, 5500],
            backgroundColor: grad1,
            borderColor: '#6366f1',
            borderWidth: 2,
            borderRadius: 8,
            barPercentage: 0.6,
          },
          {
            label: 'Margin',
            data: [2800, 4800, 4000, 1900, 8600, 2700],
            backgroundColor: grad2,
            borderColor: '#10b981',
            borderWidth: 2,
            borderRadius: 8,
            barPercentage: 0.6,
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 16,
            titleFont: { size: 14, weight: 'bold' },
            bodyFont: { size: 13 },
            cornerRadius: 12,
            displayColors: true,
            callbacks: {
              label: (context) => ` ${context.dataset.label}: $${context.parsed.y.toLocaleString()}`
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { color: '#94a3b8', font: { size: 12, weight: '500' } }
          },
          y: {
            grid: { color: 'rgba(148, 163, 184, 0.05)', drawBorder: false },
            ticks: { 
              color: '#94a3b8', 
              font: { size: 11 },
              callback: (value) => '$' + value / 1000 + 'k'
            }
          }
        }
      }
    });
  },

  renderDistribution() {
    const ctx = document.getElementById('equipmentDistChart');
    if (!ctx) return;

    const data = {
      labels: ['Laptops', 'Monitors', 'Tablets', 'Peripherals'],
      datasets: [{
        data: [55, 25, 12, 8],
        backgroundColor: [
          '#10b981',
          '#6366f1',
          '#f59e0b',
          '#8b5cf6'
        ],
        borderWidth: 0,
        hoverOffset: 10
      }]
    };

    new Chart(ctx, {
      type: 'doughnut',
      data: data,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
          legend: { display: false }
        }
      }
    });

    this.renderLegend('equipment-legend', data);
  },

  renderLegend(containerId, data) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const fragment = document.createDocumentFragment();
    const grid = document.createElement('div');
    grid.className = 'legend-grid';

    data.labels.forEach((label, i) => {
      const value = data.datasets[0].data[i];
      const color = data.datasets[0].backgroundColor[i];
      
      const item = document.createElement('div');
      item.className = 'legend-item-detailed';
      
      const dot = document.createElement('span');
      dot.className = 'dot';
      dot.style.background = color;
      
      const labelSpan = document.createElement('span');
      labelSpan.className = 'label';
      labelSpan.textContent = label;
      
      const valueSpan = document.createElement('span');
      valueSpan.className = 'value';
      valueSpan.textContent = `${value}%`;
      
      item.appendChild(dot);
      item.appendChild(labelSpan);
      item.appendChild(valueSpan);
      grid.appendChild(item);
    });
    
    fragment.appendChild(grid);
    container.innerHTML = '';
    container.appendChild(fragment);
  }
};

document.addEventListener('DOMContentLoaded', () => chartManager.init());
