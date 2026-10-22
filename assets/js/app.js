/* ============================================================
   app.js - Global utilities & shared behavior
   ============================================================ */

// Confirm dialog helper
function confirmAction(message, form) {
    if (confirm(message || 'Are you sure?')) {
        if (form) form.submit();
        return true;
    }
    return false;
}

// Show a toast notification
function showToast(message, type = 'success') {
    const icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
    const colors = { success: '#2a9d8f', error: '#e76f51', warning: '#f4a261', info: '#457b9d' };
    function getContrastingTextColor(hex) {
        hex = hex.replace('#', '');
        const r = parseInt(hex.substring(0,2), 16) / 255;
        const g = parseInt(hex.substring(2,4), 16) / 255;
        const b = parseInt(hex.substring(4,6), 16) / 255;
        const linearize = (c) => c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
        const rL = linearize(r);
        const gL = linearize(g);
        const bL = linearize(b);
        const luminance = 0.2126 * rL + 0.7152 * gL + 0.0722 * bL;
        return luminance > 0.5 ? '#000000' : '#ffffff';
    }
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;';
        document.body.appendChild(container);
    }
    const bgColor = colors[type];
    const textColor = getContrastingTextColor(bgColor);
    const toast = document.createElement('div');
    toast.style.cssText = `background:${bgColor};color:${textColor};padding:14px 20px;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.2);display:flex;align-items:center;gap:10px;font-weight:600;min-width:260px;max-width:320px;word-wrap:break-word;overflow-wrap:anywhere;animation:slideIn .3s ease;`;
    toast.innerHTML = `<i class="bi ${icons[type]}"></i><span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateX(20px)'; toast.style.transition = 'all .4s'; }, 3000);
    setTimeout(() => toast.remove(), 3500);
}

// Auto-dismiss alerts
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            if (alert) alert.style.transition = 'opacity .5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 600);
        }, 4000);
    });
});

// Logout confirmation modal
document.addEventListener('DOMContentLoaded', function() {
    const logoutModal = document.getElementById('logoutConfirmModal');
    const confirmBtn = document.getElementById('confirmLogoutBtn');
    const logoutForm = document.getElementById('logout-form');

    if (logoutModal && confirmBtn && logoutForm) {
        logoutModal.addEventListener('show.bs.modal', function() {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = 'Yes, Log Out';
        });

        confirmBtn.addEventListener('click', function() {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Logging out...';
            logoutForm.submit();
        });
    }
});

// Sidebar toggle for mobile (< 992px)
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');
    const backdrop = document.getElementById('sidebar-backdrop');

    function openSidebar() {
        sidebar.classList.add('open');
        backdrop.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.remove('open');
        backdrop.classList.remove('show');
        document.body.style.overflow = '';
    }

    if (toggleBtn && sidebar && backdrop) {
        // Use both click and touchstart for reliable mobile interaction
        const toggleSidebar = (e) => {
            e.preventDefault();
            e.stopPropagation();
            sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
        };
        
        toggleBtn.addEventListener('click', toggleSidebar);
        toggleBtn.addEventListener('touchstart', toggleSidebar, { passive: false });
        
        backdrop.addEventListener('click', closeSidebar);
        backdrop.addEventListener('touchstart', (e) => {
            e.preventDefault();
            closeSidebar();
        }, { passive: false });
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                closeSidebar();
            }
        });
        
        sidebar.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) closeSidebar();
            });
        });
    }
});

// Format number with peso
function formatPeso(value) {
    return '₱' + Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Smooth scroll to top
function scrollTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
