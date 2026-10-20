/**
 * Dashboard SQL - Récupère et affiche les statistiques de la base de données
 */

document.addEventListener('DOMContentLoaded', function() {
    fetchStats();
});

async function fetchStats() {
    try {
        const response = await fetch('/api/stats-dashboard');
        if (!response.ok) {
            throw new Error('Erreur réseau: ' + response.status);
        }
        const data = await response.json();

        if (!data.success) {
            throw new Error('Erreur API: ' + data.error);
        }

        displayVisites(data.visites);
        displayUsers(data.users);
        updateSummary(data.total_visits, data.total_users, data.total_page_views, data.total_page_views_url);
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
        let ip;
        if (vite.ip) {
            ip = vite.ip;
        } else {
            ip = 'N/A';
        }

        const date = formatDateFrancais(vite.date_visite);
        let score;
        if (vite.headless_score !== undefined) {
            score = vite.headless_score;
        } else {
            score = 'N/A';
        }

        let source;
        if (vite.source) {
            source = vite.source;
        } else {
            source = 'Inconnue';
        }

        let userAgent;
        if (vite.user_agent) {
            userAgent = vite.user_agent;
        } else {
            userAgent = 'Inconnu';
        }

        let fingerprint;
        if (vite.fingerprint) {
            fingerprint = vite.fingerprint;
        } else {
            fingerprint = 'Inconnu';
        }

        let oscpu;
        if (vite.oscpu) {
            oscpu = vite.oscpu;
        } else {
            oscpu = 'Non détecté';
        }

        let platform;
        if (vite.platform) {
            platform = vite.platform;
        } else {
            platform = 'Pas d\'OS';
        }

        let path;
        if (vite.path) {
            path = vite.path;
        } else {
            path = 'Aucune URL';
        }

        let languages;
        if (vite.languages) {
            languages = vite.languages;
        } else {
            languages = 'Aucune langue';
        }

        let gpu;
        if (vite.gpu) {
            gpu = vite.gpu;
        } else {
            gpu = 'Aucun GPU';
        }

        let plugins;
        if (vite.plugins) {
            plugins = vite.plugins;
        } else {
            plugins = 'Aucun plugin';
        }

        let time_spent;
        if (vite.time_spent !== undefined) {
            time_spent = vite.time_spent;
        } else {
            time_spent = 0;
        }

        rows += `<tr>
            <td><strong>${ip}</strong></td>
            <td>${date}</td>
            <td>${source}</td>
            <td>${score}</td>
            <td>${userAgent}</td>
            <td>${fingerprint}</td>
            <td>${oscpu}</td>
            <td>${platform}</td>
            <td>${path}</td>
            <td>${languages}</td>
            <td>${gpu}</td>
            <td>${plugins}</td>
            <td>${time_spent}s</td>
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
        let username;
        if (user.username) {
            username = user.username;
        } else {
            username = 'Inconnu';
        }

        let userid;
        if (user.id !== undefined) {
            userid = user.id;
        } else {
            userid = 0;
        }

        let userdiscordid;
        if (user.discord_id !== undefined) {
            userdiscordid = user.discord_id;
        } else {
            userdiscordid = 0;
        }

        let createdAt;
        if(user.created_at){
            createdAt = formatDateFrancais(user.created_at);
        } else {
            createdAt = 'Inconnu';
        }

        let discordlinkedat;
        if(user.discord_linked_at){
            discordlinkedat = formatDateFrancais(user.discord_linked_at);
        } else {
            discordlinkedat = 'Inconnu';
        }

        let totalViews;
        if (user.total_views !== undefined) {
            totalViews = user.total_views;
        } else {
            totalViews = 0;
        }

        let totalApiViews;
        if (user.total_views !== undefined) {
            totalApiViews = user.total_api_views;
        } else {
            totalApiViews = 0;
        }

        rows += `<tr>
            <td>${username}</td>
            <td>${userid}</td>
            <td>${createdAt}</td>
            <td>${userdiscordid}</td>
            <td>${discordlinkedat}</td>
            <td>${totalViews}</td>
            <td>${totalApiViews}</td>
        </tr>`;
    });

    tbody.innerHTML = rows;
}

function updateSummary(totalVisits, totalUsers, totalPageViews, totalPageViewsUrl) {
    document.getElementById('total-visits').textContent = totalVisits;
    document.getElementById('total-users').textContent = totalUsers;
    document.getElementById('top-total-page-views').textContent = totalPageViews;
    document.getElementById('top-total-page-views-url').textContent = "/" + totalPageViewsUrl;
}

function formatDateFrancais(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString.endsWith('Z') ? dateString : dateString + 'Z');   

    if (isNaN(date.getTime())) return dateString;

    const options = {
        year: 'numeric', month: 'numeric', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
        timeZone: 'Europe/Paris'
    };
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