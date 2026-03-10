// auth-check.js
async function updateNavWithAuth() {
    try {
        const response = await fetch('api/check_session.php');
        const data = await response.json();

        if (data.loggedin) {
            const profileLinkHTML = `
                <a href="profile.html" class="profile-pill" style="display: inline-flex; align-items: center; gap: 10px; text-decoration: none; color: white; background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 50px; font-weight: 500; transition: all 0.3s; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                    <i class="fas fa-user-circle" style="font-size: 1.2rem;"></i>
                    <span>${data.username}</span>
                </a>
            `;

            // Case 1: Elements inside #auth-nav-item (like index.html top nav)
            const authNavItem = document.getElementById('auth-nav-item');
            if (authNavItem) {
                authNavItem.innerHTML = profileLinkHTML;
            }

            // Case 2: All other login links across the site (e.g. .login-link class or a[href="login.html"])
            const loginLinks = document.querySelectorAll('a[href="login.html"], .login-link');
            loginLinks.forEach(link => {
                // If the link is inside a list item (like the main <ul class="nav-links">)
                if (link.parentElement.tagName === 'LI') {
                    link.parentElement.innerHTML = profileLinkHTML;
                } else {
                    // Generic replacement
                    link.outerHTML = profileLinkHTML;
                }
            });
        }
    } catch (err) {
        console.error("Session check failed", err);
    }
}

// Run when the DOM is fully loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', updateNavWithAuth);
} else {
    updateNavWithAuth();
}
