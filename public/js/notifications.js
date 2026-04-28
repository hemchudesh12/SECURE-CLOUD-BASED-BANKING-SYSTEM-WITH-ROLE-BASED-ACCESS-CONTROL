/**
 * notifications.js — Toast notification system.
 */

function showToast(title, message, type = 'info') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const bgMap = { success: '#198754', danger: '#dc3545', warning: '#ffc107', info: '#0d6efd', primary: '#1a2744' };
    const iconMap = { success: 'check-circle-fill', danger: 'exclamation-triangle-fill', warning: 'exclamation-circle-fill', info: 'info-circle-fill' };
    const bg   = bgMap[type]   || bgMap.info;
    const icon = iconMap[type] || iconMap.info;
    const textColor = type === 'warning' ? '#000' : '#fff';

    const id   = 'toast-' + Date.now();
    const html = `
    <div id="${id}" class="toast align-items-center text-bg-${type}" role="alert" aria-live="assertive" aria-atomic="true"
         style="border-left:4px solid ${bg};min-width:280px;max-width:360px;">
      <div class="d-flex">
        <div class="toast-body d-flex align-items-start gap-2">
          <i class="bi bi-${icon} mt-1 flex-shrink-0"></i>
          <div>
            <div class="fw-semibold" style="font-size:0.875rem">${escHtmlToast(title)}</div>
            <div style="font-size:0.8rem;opacity:0.9">${escHtmlToast(message)}</div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-${type==='warning'?'dark':'white'} me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>`;

    container.insertAdjacentHTML('beforeend', html);
    const toastEl = document.getElementById(id);
    const toast   = new bootstrap.Toast(toastEl, { delay: 5000 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

function escHtmlToast(str) {
    const d = document.createElement('div');
    d.textContent = String(str || '');
    return d.innerHTML;
}

// Make globally available
window.showToast = showToast;
