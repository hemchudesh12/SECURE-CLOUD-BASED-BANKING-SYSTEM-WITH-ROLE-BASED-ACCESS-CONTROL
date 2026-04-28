/**
 * realtime.js — WebSocket client with auto-reconnect + AJAX fallback.
 * Gold/Navy SecureBank.
 */

class BankingRealTime {
    constructor(wsUrl, authToken) {
        this.url            = wsUrl;
        this.token          = authToken;
        this.ws             = null;
        this.handlers       = {};
        this.reconnectDelay = 2000;
        this.maxDelay       = 30000;
        this.pingInterval   = null;
        this.ajaxInterval   = null;
        this.useAjax        = false;
        this.connect();
    }

    connect() {
        try {
            this.ws = new WebSocket(this.url);

            this.ws.onopen = () => {
                console.log('[RT] WebSocket connected');
                this.reconnectDelay = 2000;
                this.useAjax = false;
                clearInterval(this.ajaxInterval);
                this.ws.send(JSON.stringify({ event: 'auth', token: this.token }));
                this.startPing();
                this.setStatus('online');
            };

            this.ws.onmessage = (e) => {
                try {
                    const msg = JSON.parse(e.data);
                    if (msg.event === 'pong') return;
                    this.dispatch(msg.event, msg.data || {});
                } catch(err) { console.error('[RT] Parse error', err); }
            };

            this.ws.onclose = () => {
                this.setStatus('offline');
                clearInterval(this.pingInterval);
                console.log(`[RT] WS closed — retry in ${this.reconnectDelay}ms`);
                setTimeout(() => this.connect(), this.reconnectDelay);
                this.reconnectDelay = Math.min(this.reconnectDelay * 1.5, this.maxDelay);
                // Start AJAX fallback after first disconnect
                if (!this.ajaxInterval) this.startAjaxFallback();
            };

            this.ws.onerror = () => this.ws.close();

        } catch(e) {
            console.warn('[RT] WebSocket unavailable, using AJAX fallback');
            this.setStatus('offline');
            this.startAjaxFallback();
        }
    }

    dispatch(event, data) {
        if (this.handlers[event]) {
            try { this.handlers[event](data); }
            catch(e) { console.error('[RT] Handler error for', event, e); }
        }
    }

    on(event, fn) { this.handlers[event] = fn; return this; }

    startPing() {
        clearInterval(this.pingInterval);
        this.pingInterval = setInterval(() => {
            if (this.ws && this.ws.readyState === WebSocket.OPEN) {
                this.ws.send(JSON.stringify({ event: 'ping' }));
            }
        }, 30000);
    }

    startAjaxFallback() {
        clearInterval(this.ajaxInterval);
        this.ajaxInterval = setInterval(async () => {
            if (this.ws && this.ws.readyState === WebSocket.OPEN) {
                clearInterval(this.ajaxInterval);
                this.ajaxInterval = null;
                return;
            }
            try {
                const res  = await fetch(window.APP_BASE + '/api/ws-poll');
                const data = await res.json();
                if (data.messages) {
                    data.messages.forEach(m => this.dispatch(m.event, typeof m.data === 'string' ? JSON.parse(m.data) : (m.data || {})));
                }
            } catch(e) {}
        }, 3000);
    }

    setStatus(state) {
        // support both old and new IDs
        ['ws-status','ws-dot'].forEach(id => {
            const dot = document.getElementById(id);
            if (!dot) return;
            dot.className = 'ws-dot' + (state === 'online' ? '' : ' offline');
            dot.title = state === 'online' ? 'Live connection active' : 'Reconnecting...';
        });
    }
}

// ── Initialise ─────────────────────────────────────────────────
const wsUrl = `ws://localhost:${window.WS_PORT || 8080}`;
const rt = new BankingRealTime(wsUrl, window.WS_TOKEN || '');

// ── Balance Updated ────────────────────────────────────────────
rt.on('balance_updated', (d) => {
    const el = document.getElementById('balance-display');
    if (el) {
        el.textContent = '₹' + parseFloat(d.new_balance).toLocaleString('en-IN', { minimumFractionDigits: 2 });
        el.classList.remove('balance-flash');
        void el.offsetWidth;
        el.classList.add('balance-flash');
        setTimeout(() => el.classList.remove('balance-flash'), 1500);
    }
    const nav = document.getElementById('nav-balance');
    if (nav) nav.textContent = '₹' + parseFloat(d.new_balance).toLocaleString('en-IN');
});

// ── Transaction Completed ──────────────────────────────────────
rt.on('transaction_completed', (d) => {
    const feed = document.getElementById('live-feed');
    if (feed) {
        const row = document.createElement('tr');
        row.className = 'new-row-flash';
        const type = d.type || 'transfer';
        const color = type === 'debit' ? 'danger' : (type === 'credit' ? 'success' : 'primary');
        row.innerHTML = `
            <td><code class="text-muted" style="font-size:0.8rem">${d.reference || ''}</code></td>
            <td><span class="badge bg-${color}">${type}</span></td>
            <td class="fw-semibold">₹${parseFloat(d.amount||0).toLocaleString('en-IN',{minimumFractionDigits:2})}</td>
            <td><span class="badge bg-success">completed</span></td>
            <td class="text-muted">${d.note || d.description || '-'}</td>
            <td class="text-muted" style="font-size:0.75rem">Just now</td>`;
        feed.prepend(row);
        if (feed.rows.length > 25) feed.deleteRow(feed.rows.length - 1);
    }
    updateNotificationBell(1);
});

// ── Approval Required ──────────────────────────────────────────
rt.on('approval_required', (d) => {
    const badge = document.getElementById('pending-count');
    if (badge) {
        const curr = parseInt(badge.textContent || '0');
        badge.textContent = curr + 1;
        badge.style.display = 'inline';
    }
    const sb = document.getElementById('sidebar-pending-count');
    if (sb) { sb.textContent = parseInt(sb.textContent||'0')+1; sb.style.display='inline'; }
    showToast('⏳ Approval Required', `TXN ${d.reference || ''} — ₹${parseFloat(d.amount||0).toLocaleString('en-IN')}`, 'warning');
});

// ── Fraud Flagged ─────────────────────────────────────────────
rt.on('fraud_flagged', (d) => {
    const badge = document.getElementById('fraud-count');
    if (badge) {
        badge.textContent = parseInt(badge.textContent || '0') + 1;
        badge.style.display = 'inline';
    }
    showToast('🚨 Fraud Alert', `TXN ${d.reference || ''} — Risk score: ${d.risk_score || 0}`, 'danger');
});

// ── Account Frozen ────────────────────────────────────────────
rt.on('account_frozen', (d) => {
    showToast('🔒 Account Frozen', `Account ${d.account_number} has been frozen. ${d.reason || ''}`, 'danger');
    const card = document.getElementById('balance-card');
    if (card) card.classList.add('frozen-card');
});

// ── Account Unfrozen ──────────────────────────────────────────
rt.on('account_unfrozen', (d) => {
    showToast('🔓 Account Unfrozen', `Account ${d.account_number} has been reactivated.`, 'success');
    const card = document.getElementById('balance-card');
    if (card) card.classList.remove('frozen-card');
});

// ── Scheduled Executed ────────────────────────────────────────
rt.on('scheduled_executed', (d) => {
    showToast('📅 Scheduled Payment Sent', `₹${parseFloat(d.amount||0).toLocaleString('en-IN')} — ${d.description || ''}`, 'info');
});

// ── Approval Granted ──────────────────────────────────────────
rt.on('approval_granted', (d) => {
    showToast('✅ Transfer Approved', `Reference ${d.reference || ''} has been approved.`, 'success');
});

// ── Approval Rejected ─────────────────────────────────────────
rt.on('approval_rejected', (d) => {
    showToast('❌ Transfer Rejected', `Reference ${d.reference || ''}: ${d.reason || 'Rejected'}`, 'danger');
});

// ── Support Reply ─────────────────────────────────────────────
rt.on('support_reply', (d) => {
    showToast('💬 Support Reply', `Ticket #${d.ticket_id}: ${(d.message||'').substring(0,80)}...`, 'info');
    updateNotificationBell(1);
});

// ── System Alert ──────────────────────────────────────────────
rt.on('system_alert', (d) => {
    const banner = document.getElementById('system-banner');
    if (banner) {
        banner.textContent = d.message;
        const cls = d.severity === 'danger' ? 'alert-danger' : (d.severity === 'success' ? 'alert-success' : 'alert-warning');
        banner.className = `alert ${cls} mb-0 text-center py-2`;
        banner.style.display = 'block';
        // Push content down
        document.body.style.paddingTop = '40px';
    }
    showToast('📢 System Alert', d.message, d.severity || 'warning');
});

// ── Generic notification ───────────────────────────────────────
rt.on('notification', (d) => {
    showToast(d.title || 'Notification', d.message || '', d.type || 'info');
    updateNotificationBell(1);
    refreshNotifDropdown();
});

// ── Helpers ───────────────────────────────────────────────────
function updateNotificationBell(delta) {
    const bell = document.getElementById('notif-count');
    if (bell) {
        const curr = parseInt(bell.textContent || '0');
        const next = Math.max(0, curr + delta);
        bell.textContent = next;
        bell.style.display = next > 0 ? 'inline' : 'none';
    }
}

function refreshNotifDropdown() {
    fetch(window.APP_BASE + '/notifications/list')
        .then(r => r.json())
        .then(data => {
            const list  = document.getElementById('notifList');
            const empty = document.getElementById('notif-empty');
            const count = document.getElementById('notif-count');
            if (!list) return;
            // Remove old items
            list.querySelectorAll('.notif-item').forEach(el => el.remove());
            if (!data.notifications || data.notifications.length === 0) {
                if (empty) empty.style.display = 'block';
            } else {
                if (empty) empty.style.display = 'none';
                data.notifications.forEach(n => {
                    const li = document.createElement('li');
                    li.className = 'notif-item' + (n.is_read == 0 ? ' unread' : '');
                    li.innerHTML = `<div class="notif-title">${escHtml(n.title)}</div>
                        <div class="notif-msg">${escHtml((n.message||'').substring(0,80))}</div>
                        <div class="notif-time">${escHtml(n.created_at||'')}</div>`;
                    list.appendChild(li);
                });
            }
            if (count) {
                count.textContent = data.unread_count;
                count.style.display = data.unread_count > 0 ? 'inline' : 'none';
            }
        }).catch(() => {});
}

function markAllRead() {
    fetch(window.APP_BASE + '/notifications/mark-read', { method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(() => {
        const count = document.getElementById('notif-count');
        if (count) { count.textContent = '0'; count.style.display = 'none'; }
        document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
    }).catch(() => {});
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

// Load initial notifications
document.addEventListener('DOMContentLoaded', () => {
    refreshNotifDropdown();
    // Refresh every 30s
    setInterval(refreshNotifDropdown, 30000);
});
