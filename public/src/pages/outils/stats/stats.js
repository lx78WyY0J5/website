/**
 * Dashboard SQL - Récupère et affiche les statistiques de la base de données
 */

document.addEventListener('DOMContentLoaded', function() {
    fetchStats();
});

async function fetchStats() {
    try {
        const response = await fetch('/src/php/stats-dashboard.php');
        if (!response.ok) {
            throw new Error('Erreur réseau: ' + response.status);
        }
        const data = await response.json();

        if (!data.success) {
            throw new Error('Erreur API: ' + data.error);
        }

        displayVisites(data.visites);
        displayUsers(data.users);
        updateSummary(data.total_visits, data.total_users, data.total_page_views);
    } catch (error) {
        console.error('Erreur lors du chargement des stats:', error);
        displayError('Erreur lors du chargement des statistiques');
    }
}

function displayVisites(visites) {
    const tbody = document.querySelector('#visites-section tbody');
    const loading = document.querySelector('#visites-section .loading');

    if (visites.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="no-data">Aucune visite enregistrée</td></tr>';
        loading.style.display = 'none';
        return;
    }

    loading.style.display = 'none';

    let rows = '';
    visites.forEach(vite => {
        const ip = vite.ip || 'N/A';
        const date = formatDate(vite.date_visite);
        const score = vite.headless_score !== undefined ? vite.headless_score : 'N/A';
        const userAgent = vite.user_agent ? truncateString(vite.user_agent, 30) : 'Inconnu';
        const appName = vite.app_name ? truncateString(vite.app_name, 20) : 'Non détecté';

        rows += `<tr>
            <td><strong>${ip}</strong></td>
            <td>${date}</td>
            <td>${score}</td>
            <td>${userAgent}</td>
            <td>${appName}</td>
        </tr>`;
    });

    tbody.innerHTML = rows;
}

function displayUsers(users) {
    const tbody = document.querySelector('#users-section tbody');
    const loading = document.querySelector('#users-section .loading');

    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="no-data">Aucun utilisateur enregistré</td></tr>';
        loading.style.display = 'none';
        return;
    }

    loading.style.display = 'none';

    let rows = '';
    users.forEach(user => {
        const username = user.username || 'Inconnu';
        const createdAt = user.created_at ? formatDateFrancais(user.created_at) : 'Inconnue';
        const totalViews = user.total_views !== undefined ? user.total_views : 0;
        const totalApiViews = user.total_api_views !== undefined ? user.total_api_views : 0;

        rows += `<tr>
            <td>${username}</td>
            <td>${createdAt}</td>
            <td>${totalViews}</td>
            <td>${totalApiViews}</td>
        </tr>`;
    });

    tbody.innerHTML = rows;
}

function updateSummary(totalVisits, totalUsers, totalPageViews) {
    document.getElementById('total-visits').textContent = totalVisits;
    document.getElementById('total-users').textContent = totalUsers;
    document.getElementById('total-page-views').textContent = totalPageViews;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    return date.toLocaleString();
}

function formatDateFrancais(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    const options = { year: 'numeric', month: 'numeric', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleString('fr-FR', options);
}

function truncateString(str, maxLength) {
    if (!str) return '';
    if (str.length <= maxLength) return str;
    return str.substring(0, maxLength - 1) + '…';
}

function displayError(message) {
    const sections = document.querySelectorAll('.stats-section');
    sections.forEach(section => {
        section.innerHTML = `<div class="error">${message}</div>`;
    });
}