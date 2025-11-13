import { createIcons, icons } from 'lucide';

// Initialize Lucide icons when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
  });
} else {
  // DOM is already ready
  createIcons({ icons });
}

// Re-initialize icons when new content is added (for widget preview)
if (typeof MutationObserver !== 'undefined') {
  const observer = new MutationObserver(() => {
    createIcons({ icons });
  });
  
  if (document.body) {
    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
  }
}
