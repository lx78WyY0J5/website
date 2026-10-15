function getGpuInfo() {
    var gpuInfo = '';
    try {
        var canvas = document.createElement('canvas');
        var gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
        if (gl !== null) {
            var debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
            if (debugInfo !== null) {
                gpuInfo = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
            }
            else {
                gpuInfo = gl.getParameter(gl.RENDERER);
            }
        }
    }
    catch (error) {
        gpuInfo = '';
    }
    return gpuInfo;
}

function getScreenInfo() {
    var width = 0;
    var height = 0;
    var colorDepth = 0;
    if (typeof screen !== 'undefined' && screen !== null) {
        width = screen.width;
        height = screen.height;
        colorDepth = screen.colorDepth;
    }
    return {
        width: width,
        height: height,
        colorDepth: colorDepth
    };
}

function getLanguage() {
    var language = '';
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (navigator.language !== undefined) {
            language = navigator.language;
        }
    }
    return language;
}

function getPlatform() {
    var platform = '';
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (navigator.platform !== undefined) {
            platform = navigator.platform;
        }
    }
    return platform;
}

function getTimeZone() {
    var timeZone = '';
    try {
        timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    }
    catch (error) {
        timeZone = '';
    }
    return timeZone;
}

function getUserAgent() {
    var userAgent = '';
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (navigator.userAgent !== undefined) {
            userAgent = navigator.userAgent;
        }
    }
    return userAgent;
}

function getCookiesEnabled() {
    var enabled = 0;
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (navigator.cookieEnabled === true) {
            enabled = 1;
        }
    }
    return enabled;
}

function getDoNotTrack() {
    var value = 0;
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (navigator.doNotTrack === '1' || navigator.doNotTrack === 'yes') {
            value = 1;
        }
    }
    return value;
}

function getReferrer() {
    var referrer = '';
    if (typeof document !== 'undefined' && document !== null) {
        if (document.referrer !== undefined) {
            referrer = document.referrer;
        }
    }
    return referrer;
}

function bufferToHex(buffer) {
    var view = new Uint8Array(buffer);
    var hex = '';
    var i;
    for (i = 0; i < view.length; i = i + 1) {
        hex = hex + view[i].toString(16).padStart(2, '0');
    }
    return hex;
}

function generateFingerprint(text) {
    return new Promise(function (resolve, reject) {
        if (typeof crypto === 'undefined' || crypto === null || typeof crypto.subtle === 'undefined') {
            resolve('');
            return;
        }

        var encoder = new TextEncoder();
        var encoded = encoder.encode(text);

        crypto.subtle.digest('SHA-256', encoded).then(function (hash) {
            resolve(bufferToHex(hash));
        }).catch(function (error) {
            resolve('');
        });
    });
}

function collectVisitorData() {
    var data = {
        user_agent: getUserAgent(),
        time_zone: getTimeZone(),
        language: getLanguage(),
        platform: getPlatform(),
        gpu: getGpuInfo(),
        referrer: getReferrer(),
        path: window.location.href,
        cookies_enabled: getCookiesEnabled(),
        do_not_track: getDoNotTrack(),
        fingerprint: ''
    };

    var screen = getScreenInfo();
    data.screen_resolution = screen.width + 'x' + screen.height;
    data.color_depth = screen.colorDepth;

    // Construire une chaine pour generer l'empreinte (fingerprint) du visiteur
    var fingerprintSource = data.user_agent + '|' +
        data.time_zone + '|' +
        data.language + '|' +
        data.platform + '|' +
        data.screen_resolution + '|' +
        data.color_depth + '|' +
        data.gpu + '|' +
        data.referrer + '|' +
        data.path;

    generateFingerprint(fingerprintSource).then(function (hash) {
        data.fingerprint = hash;
        sendVisitorData(data);
    });
}

function sendVisitorData(data) {
    fetch('/api/data', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json; charset=utf-8'
        },
        body: JSON.stringify(data)
    }).then(function (response) {
        return response.json();
    }).then(function (result) {
        if (result.success !== true) {
            console.warn('Visite non enregistre : ' + result.error);
        }
        else{ console.log("Visite enregistré ");
            console.log(result);
        }
    }).catch(function (error) {
        console.warn('Impossible d\'envoyer les donnees de visite : ' + error);
    });
}

// Enregistrer la visite au chargement de la page
window.addEventListener('load', function () {
    console.log('Sending visitor data');
    collectVisitorData();
});

window.addEventListener('DOMContentLoaded', () => {
    console.log('visitor.js loaded');
});
