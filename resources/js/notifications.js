// Real-time notification handling
document.addEventListener('DOMContentLoaded', function() {
    // Check if user is authenticated
    if (typeof window.userId === 'undefined') {
        return;
    }

    // Listen for real-time notifications
    window.Echo.private(`user.${window.userId}`)
        .listen('.notification.received', (e) => {
            console.log('Notification received:', e);
            
            // Dispatch Livewire event to update notification components
            window.Livewire.dispatch('notification-received', {
                notification: e.notification,
                unreadCount: e.unread_count
            });

            // Show toast notification
            showNotificationToast(e.notification);
        });
});

// Show a toast notification
function showNotificationToast(notification) {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg p-4 z-50 transform translate-x-full transition-transform duration-300';
    toast.innerHTML = `
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-zinc-900 dark:text-white">
                    ${notification.message}
                </p>
                <div class="mt-1 flex items-center space-x-2">
                    ${notification.action_url ? `
                        <a href="${notification.action_url}" 
                           class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                           onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                            View
                        </a>
                    ` : ''}
                    <button onclick="this.parentElement.parentElement.parentElement.parentElement.remove()" 
                            class="text-xs text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                        Dismiss
                    </button>
                </div>
            </div>
        </div>
    `;

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
    console.log('Notification system initialized');
};
