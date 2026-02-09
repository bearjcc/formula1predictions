// Real-time notification handling
document.addEventListener('DOMContentLoaded', function() {
    // Check if user is authenticated
    if (typeof window.userId === 'undefined') {
        return;
    }

    // Listen for real-time notifications
    window.Echo.private(`user.${window.userId}`)
        .listen('.notification.received', (e) => {
            // Dispatch Livewire event to update notification components
            window.Livewire.dispatch('notification-received', {
                notification: e.notification,
                unreadCount: e.unread_count
            });

            // Show toast notification
            showNotificationToast(e.notification);
        });
});

// Sanitize a string for safe insertion into the DOM
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Validate a URL is safe for use in href attributes
function isValidUrl(url) {
    try {
        const parsed = new URL(url, window.location.origin);
        return parsed.protocol === 'http:' || parsed.protocol === 'https:';
    } catch {
        return false;
    }
}

// Show a toast notification
function showNotificationToast(notification) {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg p-4 z-50 transform translate-x-full transition-transform duration-300';

    // Build the toast content safely using DOM APIs
    const wrapper = document.createElement('div');
    wrapper.className = 'flex items-start space-x-3';

    const indicator = document.createElement('div');
    indicator.className = 'flex-shrink-0';
    indicator.innerHTML = '<div class="w-2 h-2 bg-blue-500 rounded-full"></div>';

    const content = document.createElement('div');
    content.className = 'flex-1 min-w-0';

    const message = document.createElement('p');
    message.className = 'text-sm font-medium text-zinc-900 dark:text-white';
    message.textContent = notification.message || '';

    const actions = document.createElement('div');
    actions.className = 'mt-1 flex items-center space-x-2';

    if (notification.action_url && isValidUrl(notification.action_url)) {
        const viewLink = document.createElement('a');
        viewLink.href = notification.action_url;
        viewLink.className = 'text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300';
        viewLink.textContent = 'View';
        viewLink.addEventListener('click', () => toast.remove());
        actions.appendChild(viewLink);
    }

    const dismissBtn = document.createElement('button');
    dismissBtn.className = 'text-xs text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300';
    dismissBtn.textContent = 'Dismiss';
    dismissBtn.addEventListener('click', () => toast.remove());
    actions.appendChild(dismissBtn);

    content.appendChild(message);
    content.appendChild(actions);
    wrapper.appendChild(indicator);
    wrapper.appendChild(content);
    toast.appendChild(wrapper);

    // Add to page
    document.body.appendChild(toast);

    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }
    }, 5000);
}

// Initialize notification system
window.initializeNotifications = function() {
    // This function can be called to reinitialize notifications if needed
};
