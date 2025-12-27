// ============================================
// AUTHENTICATION JAVASCRIPT UTILITIES
// Shared across all auth pages
// ============================================

/**
 * Toggle password visibility
 */
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = event.currentTarget;
    const icon = button.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

/**
 * Get session token from storage
 */
function getSessionToken() {
    return localStorage.getItem('session_token') || localStorage.getItem('remember_token');
}

/**
 * Get user data from storage
 */
function getUserData() {
    const data = localStorage.getItem('user_data');
    return data ? JSON.parse(data) : null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return !!getSessionToken() && !!getUserData();
}

/**
 * Logout user (clear session)
 */
function logout() {
    const token = getSessionToken();

    if (token) {
        // Notify server
        fetch('profile.php', {
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + token
            }
        }).catch(err => console.log(err));
    }

    // Clear local storage
    localStorage.removeItem('session_token');
    localStorage.removeItem('remember_token');
    localStorage.removeItem('user_data');

    // Redirect to login
    window.location.href = 'login.html';
}

/**
 * Fetch user profile
 */
async function fetchUserProfile() {
    const token = getSessionToken();

    if (!token) {
        return null;
    }

    try {
        const response = await fetch('profile.php?token=' + encodeURIComponent(token));
        const result = await response.json();

        if (result.success) {
            return result.user;
        } else {
            // Session expired
            logout();
            return null;
        }
    } catch (error) {
        console.error('Error fetching profile:', error);
        return null;
    }
}

/**
 * Update user profile
 */
async function updateUserProfile(data) {
    const token = getSessionToken();

    if (!token) {
        return { success: false, message: 'Not authenticated' };
    }

    try {
        const response = await fetch('profile.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify(data)
        });

        return await response.json();
    } catch (error) {
        console.error('Error updating profile:', error);
        return { success: false, message: 'Network error' };
    }
}

/**
 * Upload profile picture
 */
async function uploadProfilePicture(file) {
    const token = getSessionToken();

    if (!token) {
        return { success: false, message: 'Not authenticated' };
    }

    const formData = new FormData();
    formData.append('profile_pic', file);
    formData.append('token', token);

    try {
        const response = await fetch('profile.php', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            body: formData
        });

        return await response.json();
    } catch (error) {
        console.error('Error uploading picture:', error);
        return { success: false, message: 'Upload failed' };
    }
}

/**
 * Add authorization header to all fetch requests
 */
function addAuthHeader(headers = {}) {
    const token = getSessionToken();
    if (token) {
        headers['Authorization'] = 'Bearer ' + token;
    }
    return headers;
}

/**
 * Check token validity and refresh UI
 */
async function validateSession() {
    const user = await fetchUserProfile();
    return user !== null;
}

// Set up global error handler for 401 responses
document.addEventListener('DOMContentLoaded', () => {
    // Check session on page load
    const token = getSessionToken();
    if (!token && window.location.pathname !== '/login.html' && window.location.pathname !== '/signup.html') {
        // Silently redirect or show message
        if (document.body.id !== 'public-page') {
            window.location.href = 'login.html';
        }
    }
});

// ============================================
// FORM VALIDATION HELPERS
// ============================================

/**
 * Validate email format
 */
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Validate password strength
 */
function validatePasswordStrength(password) {
    return {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        isStrong: password.length >= 8 && /[A-Z]/.test(password) && /[a-z]/.test(password) && /[0-9]/.test(password)
    };
}

/**
 * Validate username format
 */
function validateUsername(username) {
    const regex = /^[a-zA-Z0-9_]{3,50}$/;
    return regex.test(username);
}

/**
 * Format date for display
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

/**
 * Format time ago (e.g., "2 hours ago")
 */
function timeAgo(dateString) {
    const date = new Date(dateString);
    const seconds = Math.floor((new Date() - date) / 1000);

    if (seconds < 60) return 'just now';
    if (seconds < 3600) return Math.floor(seconds / 60) + ' minutes ago';
    if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
    if (seconds < 604800) return Math.floor(seconds / 86400) + ' days ago';

    return formatDate(dateString);
}

// ============================================
// END AUTH UTILITIES
// ============================================
