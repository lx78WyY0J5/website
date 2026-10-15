// Enregistrer la visite au chargement de la page
window.addEventListener('load', function () {
    console.log('Sending visitor data');
    pageStartTime = Date.now();
    collectVisitorData();

    // Envoi periodique des donnees toutes les 10 secondes
    setInterval(function () {
        sendVisitorDataWithSource('periodic', {});
    }, PERIODIC_INTERVAL);
});

// Suivi de la souris : enregistrer la position toutes les 1 seconde
window.addEventListener('mousemove', function (event) {
    var now = Date.now();
    if (pageStartTime === null) {
        return;
    }
    if (now - lastMousePositionTime >= MOUSE_LOG_INTERVAL) {
        mousePositions.push({
            x: event.clientX,
            y: event.clientY,
            t: now - pageStartTime
        });
        lastMousePositionTime = now;
    }
});

// Envoi des donnees au clic sur un lien ou un bouton
document.addEventListener('click', function (event) {
    var element = event.target;
    while (element !== null && element !== document.documentElement) {
        if (isTrackedElement(element) === true) {
            break;
        }
        element = element.parentElement;
    }
    if (element !== null && isTrackedElement(element) === true) {
        var clickExtra = {
            click_element: JSON.stringify(getElementInfo(element)),
            click_x: event.clientX,
            click_y: event.clientY
        };
        sendVisitorDataWithSource('click', clickExtra);
    }
});

// Envoi des donnees en cas d'erreur JavaScript
window.addEventListener('error', function (event) {
    var errorMessage = '';
    if (event.message !== undefined && event.message !== null) {
        errorMessage = errorMessage + event.message;
    }
    if (event.filename !== undefined && event.filename !== null) {
        errorMessage = errorMessage + ' (' + event.filename + ')';
    }
    if (event.lineno !== undefined && event.lineno !== null) {
        errorMessage = errorMessage + ':' + event.lineno;
    }
    var errorExtra = {
        error_message: errorMessage
    };
    sendVisitorDataWithSource('error', errorExtra);
});

window.addEventListener('DOMContentLoaded', function () {
    console.log('visitor.js loaded');
});