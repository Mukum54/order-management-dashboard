// notifications.js
class ToastService {
    constructor() {
        this.container = document.createElement('div');
        this.container.id = 'toast-container';
        // Add container to DOM
        document.body.appendChild(this.container);

        // Style container
        Object.assign(this.container.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            zIndex: '9999',
            display: 'flex',
            flexDirection: 'column',
            gap: '10px'
        });
    }

    show(message, type = 'info') {
        const toast = document.createElement('div');

        // Basic colors
        const colors = {
            success: '#10B981',
            error: '#EF4444',
            warning: '#F59E0B',
            info: '#3B82F6'
        };
        const color = colors[type];

        Object.assign(toast.style, {
            background: 'var(--bg-card)',
            color: 'var(--text-primary)',
            padding: '16px 20px',
            borderRadius: '8px',
            borderLeft: `4px solid ${color}`,
            boxShadow: 'var(--shadow-lg)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            minWidth: '250px',
            cursor: 'pointer',
            opacity: '0',
            transform: 'translateX(50px)',
            transition: 'all 0.3s ease'
        });

        toast.innerHTML = `<span>${message}</span>`;

        this.container.appendChild(toast);

        // Keep at most 4
        if (this.container.children.length > 4) {
            this.container.removeChild(this.container.firstChild);
        }

        // Animate in
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        });

        toast.addEventListener('click', () => this.dismiss(toast));

        setTimeout(() => this.dismiss(toast), 4000);
    }

    dismiss(toast) {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(50px)';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }
}

window.Toast = new ToastService();
