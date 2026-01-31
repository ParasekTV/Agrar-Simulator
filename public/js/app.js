/**
 * Landwirtschafts-Simulator - Haupt-JavaScript
 */

// ==========================================
// Initialisierung
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    initNavigation();
    initAlerts();
    initForms();
});

// ==========================================
// Navigation
// ==========================================

function initNavigation() {
    const toggle = document.getElementById('navbar-toggle');
    const menu = document.getElementById('navbar-menu');

    if (toggle && menu) {
        toggle.addEventListener('click', function() {
            menu.classList.toggle('active');
        });

        // Schliesse Menu bei Klick ausserhalb
        document.addEventListener('click', function(e) {
            if (!toggle.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('active');
            }
        });
    }
}

// ==========================================
// Alerts
// ==========================================

function initAlerts() {
    document.querySelectorAll('.alert-close').forEach(function(btn) {
        btn.addEventListener('click', function() {
            this.closest('.alert').remove();
        });
    });

    // Auto-hide nach 5 Sekunden
    document.querySelectorAll('.alert').forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    });
}

// ==========================================
// Forms
// ==========================================

function initForms() {
    // Verhindere doppeltes Absenden
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Bitte warten...';
            }
        });
    });
}

// ==========================================
// Notifications
// ==========================================

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;

    document.body.appendChild(notification);

    // Animation
    setTimeout(function() {
        notification.classList.add('show');
    }, 10);

    // Auto-hide
    setTimeout(function() {
        notification.classList.remove('show');
        setTimeout(function() {
            notification.remove();
        }, 300);
    }, 3000);
}

// ==========================================
// API Helper
// ==========================================

const API = {
    baseUrl: document.querySelector('meta[name="base-url"]')?.content || '/farming-simulator/public',

    async request(method, endpoint, data = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(this.baseUrl + '/api' + endpoint, options);
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            return { success: false, message: 'Verbindungsfehler' };
        }
    },

    get(endpoint) {
        return this.request('GET', endpoint);
    },

    post(endpoint, data) {
        return this.request('POST', endpoint, data);
    },

    delete(endpoint) {
        return this.request('DELETE', endpoint);
    }
};

// ==========================================
// Utility Functions
// ==========================================

function formatCurrency(amount) {
    return new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
}

function formatNumber(num) {
    return new Intl.NumberFormat('de-DE').format(num);
}

function formatTime(milliseconds) {
    const hours = Math.floor(milliseconds / 3600000);
    const minutes = Math.floor((milliseconds % 3600000) / 60000);

    if (hours > 24) {
        const days = Math.floor(hours / 24);
        return `${days}T ${hours % 24}h`;
    }
    return `${hours}h ${minutes}m`;
}

// ==========================================
// Stats Update
// ==========================================

function updateFarmStats() {
    API.get('/farm/stats').then(function(data) {
        if (data) {
            const moneyEl = document.getElementById('farm-money');
            const pointsEl = document.getElementById('farm-points');
            const levelEl = document.getElementById('farm-level');

            if (moneyEl) moneyEl.textContent = formatCurrency(data.money);
            if (pointsEl) pointsEl.textContent = formatNumber(data.points) + ' Punkte';
            if (levelEl) levelEl.textContent = 'Level ' + data.level;
        }
    });
}
