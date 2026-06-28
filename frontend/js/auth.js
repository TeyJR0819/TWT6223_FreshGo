async function sessionGuard(requiredRole) {
    let user = JSON.parse(sessionStorage.getItem('user') || 'null');

    if (!user) {
        try {
            user = await apiFetch('/auth/session.php');
            sessionStorage.setItem('user', JSON.stringify(user));
        } catch {
            // Changed from '/index.html' to '../index.html'
            window.location.href = '../index.html';
            return null;
        }
    }

    if (requiredRole && user.role !== requiredRole) {
        // Changed from '/index.html' to '../index.html'
        window.location.href = '../index.html';
        return null;
    }

    const el = document.getElementById('nav-user-name');
    if (el) el.textContent = user.name;

    return user;
}

async function logout() {
    try {
        await apiFetch('/auth/logout.php', 'POST');
    } finally {
        sessionStorage.removeItem('user');
        // Changed from '/index.html' to '../index.html'
        window.location.href = '../index.html';
    }
}
