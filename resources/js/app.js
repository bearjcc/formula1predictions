import './bootstrap';
import Chart from 'chart.js/auto';
import './notifications';

// Make Chart available globally
window.Chart = Chart;

// Listen for appearance changes from settings
document.addEventListener('livewire:init', () => {
  Livewire.on('appearance-changed', ({ appearance }) => {
    const isDark = appearance === 'dark' || (appearance === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.setAttribute('data-appearance', appearance);
  });
});
