function showToast(message, type = 'info') {
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add('toast--visible'));
    setTimeout(() => {
        toast.classList.remove('toast--visible');
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

function formatDate(timestamp) {
    return new Date(timestamp).toLocaleString('en-MY', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

function formatPrice(amount) {
    return 'RM ' + parseFloat(amount).toFixed(2);
}

function statusBadge(status) {
    const map = {
        pending:          { label: 'Pending',          cls: 'badge--amber'  },
        preparing:        { label: 'Preparing',        cls: 'badge--blue'   },
        out_for_delivery: { label: 'Out for Delivery', cls: 'badge--purple' },
        delivered:        { label: 'Delivered',        cls: 'badge--green'  },
        cancelled:        { label: 'Cancelled',        cls: 'badge--red'    },
    };
    const s = map[status] || { label: status, cls: '' };
    return `<span class="badge ${s.cls}">${s.label}</span>`;
}

function showFieldError(inputId, message) {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.classList.add('input--error');
    let err = input.parentElement.querySelector('.field-error');
    if (!err) {
        err = document.createElement('span');
        err.className = 'field-error';
        input.parentElement.appendChild(err);
    }
    err.textContent = message;
}

function clearFieldErrors() {
    document.querySelectorAll('.input--error').forEach(el => el.classList.remove('input--error'));
    document.querySelectorAll('.field-error').forEach(el => el.remove());
}

function setButtonLoading(btn, loading) {
    if (loading) {
        btn.dataset.originalText = btn.textContent;
        btn.textContent = 'Loading...';
        btn.disabled = true;
    } else {
        btn.textContent = btn.dataset.originalText || btn.textContent;
        btn.disabled = false;
    }
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}
