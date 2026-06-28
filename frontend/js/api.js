async function apiFetch(endpoint, method = 'GET', body = null) {
    // Some hosts (confirmed on InfinityFree) don't reliably deliver the
    // request body to PHP's php://input for PUT/DELETE -- only POST works
    // consistently. Send the real method folded into the JSON body instead;
    // the backend reads it from there for the endpoints that need it.
    const isOverridden = method === 'PUT' || method === 'DELETE';
    const sendBody = isOverridden ? { ...(body || {}), _method: method } : body;

    const opts = {
        method: isOverridden ? 'POST' : method,
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
    };
    if (sendBody) opts.body = JSON.stringify(sendBody);

    try {
        const res = await fetch(API_BASE + endpoint, opts);
        const data = await res.json();

        if (res.status === 401) {
            sessionStorage.removeItem('user');
            const onLoginPage = window.location.pathname === '/' || window.location.pathname.endsWith('/index.html');
            if (!onLoginPage) {
                showToast('Session expired. Please log in again.', 'error');
                setTimeout(() => { window.location.href = '../index.html'; }, 1500);
            }
            throw new Error(data.error || 'Unauthenticated');
        }
        if (!res.ok) {
            throw new Error(data.error || `Request failed (${res.status})`);
        }

        return data;
    } catch (err) {
        if (err.message === 'Failed to fetch') {
            showToast('Cannot connect to server. Check your internet connection.', 'error');
        } else if (err.message !== 'Unauthenticated' && err.message !== 'Forbidden') {
            showToast(err.message, 'error');
        }
        throw err;
    }
}
