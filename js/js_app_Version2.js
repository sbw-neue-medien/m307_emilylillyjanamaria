/**
 * ProjektAdmin - Main Application
 */

const App = {
    showMessage(message, type = 'info', duration = 5000) {
        const alert = document.createElement('div');
        const icon = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' }[type] || 'ℹ️';
        alert.className = `alert alert-${type === 'error' ? 'danger' : type}`;
        alert.innerHTML = `
            <span>${icon}</span>
            <span>${message}</span>
            <span class="alert-close" onclick="this.parentElement.remove()">×</span>
        `;
        const container = document.querySelector('main') || document.body;
        container.insertBefore(alert, container.firstChild);
        if (duration > 0) setTimeout(() => alert.remove(), duration);
    },

    formatDate(date) {
        return new Date(date).toLocaleDateString('de-DE');
    },

    formatNumber(num, decimals = 2) {
        return parseFloat(num).toFixed(decimals).replace('.', ',');
    }
};

function confirmDelete(name = 'Datensatz') {
    return confirm(`Sind Sie sicher, dass Sie ${name} löschen möchten?`);
}