import './bootstrap';
import Chart from 'chart.js/auto';
import './notifications';

// Make Chart available globally
window.Chart = Chart;

// Apply theme from data-appearance (so wire:navigate and first load stay in sync)
function applyAppearance() {
  const appearance = document.documentElement.getAttribute('data-appearance') || 'system';
  const isDark = appearance === 'dark' || (appearance === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
  document.documentElement.classList.toggle('dark', isDark);
  document.documentElement.setAttribute('data-appearance', appearance);
}

document.addEventListener('livewire:init', () => {
  Livewire.on('appearance-changed', ({ appearance }) => {
    document.documentElement.setAttribute('data-appearance', appearance);
    applyAppearance();
  });
});
// Re-apply after wire:navigate (inline script in head does not run on replaced document)
document.addEventListener('livewire:navigated', () => applyAppearance());
