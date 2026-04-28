/**
 * validation.js — client-side form validation.
 */
document.addEventListener('DOMContentLoaded', () => {

    // Bootstrap validation
    document.querySelectorAll('form.needs-validation').forEach(form => {
        form.addEventListener('submit', e => {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Transfer amount: real-time format display
    const amountInput = document.getElementById('transfer-amount');
    const amountPreview = document.getElementById('amount-preview');
    if (amountInput && amountPreview) {
        amountInput.addEventListener('input', () => {
            const val = parseFloat(amountInput.value);
            if (!isNaN(val) && val > 0) {
                amountPreview.textContent = '₹' + val.toLocaleString('en-IN', { minimumFractionDigits: 2 });
                amountPreview.className = 'text-success fw-semibold';
            } else {
                amountPreview.textContent = '';
            }
        });
    }

    // Account lookup on blur
    const toAccountInput = document.getElementById('to_account');
    const accountHint = document.getElementById('account-hint');
    if (toAccountInput && accountHint) {
        let lookupTimer;
        toAccountInput.addEventListener('input', () => {
            clearTimeout(lookupTimer);
            const val = toAccountInput.value.trim();
            if (val.length >= 6) {
                lookupTimer = setTimeout(() => {
                    accountHint.innerHTML = '<span class="text-muted">Looking up account...</span>';
                    fetch(`${window.APP_BASE}/customer/beneficiaries/lookup?account=${encodeURIComponent(val)}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.found) {
                                accountHint.innerHTML = `<span class="text-success"><i class="bi bi-check-circle me-1"></i>
                                    ${escHtmlV(data.holder_name)} — ${data.account_type}</span>`;
                            } else {
                                accountHint.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Account not found</span>';
                            }
                        }).catch(() => { accountHint.innerHTML = ''; });
                }, 500);
            } else {
                accountHint.innerHTML = '';
            }
        });
    }

    // Password strength indicator
    const pwdInput = document.getElementById('password-new') || document.getElementById('password');
    const pwdStrength = document.getElementById('password-strength');
    if (pwdInput && pwdStrength) {
        pwdInput.addEventListener('input', () => {
            const p = pwdInput.value;
            let score = 0;
            if (p.length >= 8) score++;
            if (/[A-Z]/.test(p)) score++;
            if (/[0-9]/.test(p)) score++;
            if (/[^A-Za-z0-9]/.test(p)) score++;
            const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
            const colors = ['', 'danger', 'warning', 'info', 'success'];
            pwdStrength.innerHTML = score > 0 ? `<span class="text-${colors[score]}">${labels[score]}</span>` : '';
        });
    }
});

function escHtmlV(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
