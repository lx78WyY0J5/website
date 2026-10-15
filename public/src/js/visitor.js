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

function getWebdriver() {
    var value = 0;
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (navigator.webdriver === true) {
            value = 1;
        }
    }
    return value;
}

function getAppVersion() {
    var appVersion = '';
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (navigator.appVersion !== undefined) {
            appVersion = navigator.appVersion;
        }
    }
    return appVersion;
}

function getTimezoneOffset() {
    var offset = 0;
    try {
        offset = new Date().getTimezoneOffset();
    }
    catch (error) {
        offset = 0;
    }
    return offset;
}

function getBattery() {
    return new Promise(function (resolve) {
        var battery = {
            level: 0,
            charging: 0
        };
        if (typeof navigator !== 'undefined' && navigator !== null && typeof navigator.getBattery === 'function') {
            navigator.getBattery().then(function (batteryManager) {
                battery.level = batteryManager.level;
                if (batteryManager.charging === true) {
                    battery.charging = 1;
                }
                else {
                    battery.charging = 0;
                }
                resolve(battery);
            }).catch(function (error) {
                resolve(battery);
            });
        }
        else {
            resolve(battery);
        }
    });
}

function getUserAgentDataMobile() {
    var value = 0;
    if (typeof navigator !== 'undefined' && navigator !== null && typeof navigator.userAgentData !== 'undefined' && navigator.userAgentData !== null) {
        if (navigator.userAgentData.mobile === true) {
            value = 1;
        }
    }
    return value;
}

function getLanguages() {
    var languages = '';
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (navigator.languages !== undefined && navigator.languages !== null) {
            var i;
            for (i = 0; i < navigator.languages.length; i = i + 1) {
                if (i > 0) {
                    languages = languages + ',';
                }
                languages = languages + navigator.languages[i];
            }
        }
    }
    return languages;
}

function getVendor() {
    var vendor = '';
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (navigator.vendor !== undefined) {
            vendor = navigator.vendor;
        }
    }
    return vendor;
}

function getUserAgentBrands() {
    var brands = '';
    if (typeof navigator !== 'undefined' && navigator !== null && typeof navigator.userAgentData !== 'undefined' && navigator.userAgentData !== null) {
        if (navigator.userAgentData.brands !== undefined && navigator.userAgentData.brands !== null) {
            var i;
            var brandList = [];
            for (i = 0; i < navigator.userAgentData.brands.length; i = i + 1) {
                brandList.push(navigator.userAgentData.brands[i].brand + ' ' + navigator.userAgentData.brands[i].version);
            }
            brands = brandList.join(', ');
        }
    }
    return brands;
}

function getAppName() {
    var appName = '';
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (navigator.appName !== undefined) {
            appName = navigator.appName;
        }
    }
    return appName;
}

function getAppCodeName() {
    var appCodeName = '';
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (navigator.appCodeName !== undefined) {
            appCodeName = navigator.appCodeName;
        }
    }
    return appCodeName;
}

function getProductSub() {
    var productSub = '';
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (navigator.productSub !== undefined) {
            productSub = navigator.productSub;
        }
    }
    return productSub;
}

function getProduct() {
    var product = '';
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (navigator.product !== undefined) {
            product = navigator.product;
        }
    }
    return product;
}

function getOscpu() {
    var oscpu = '';
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (navigator.oscpu !== undefined) {
            oscpu = navigator.oscpu;
        }
    }
    return oscpu;
}

function getDeviceMemory() {
    var value = 0;
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (navigator.deviceMemory !== undefined) {
            value = navigator.deviceMemory;
        }
    }
    return value;
}

function getHardwareConcurrency() {
    var value = 0;
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (navigator.hardwareConcurrency !== undefined) {
            value = navigator.hardwareConcurrency;
        }
    }
    return value;
}

function getConnection() {
    var connection = null;
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (navigator.connection !== undefined && navigator.connection !== null) {
            connection = navigator.connection;
        }
        else if (navigator.mozConnection !== undefined && navigator.mozConnection !== null) {
            connection = navigator.mozConnection;
        }
        else if (navigator.webkitConnection !== undefined && navigator.webkitConnection !== null) {
            connection = navigator.webkitConnection;
        }
    }
    return connection;
}

function getConnectionEffectiveType() {
    var value = '';
    var connection = getConnection();
    if (connection !== null && connection.effectiveType !== undefined) {
        value = connection.effectiveType;
    }
    return value;
}

function getConnectionDownlink() {
    var value = 0;
    var connection = getConnection();
    if (connection !== null && connection.downlink !== undefined) {
        value = connection.downlink;
    }
    return value;
}

function getConnectionSaveData() {
    var value = 0;
    var connection = getConnection();
    if (connection !== null && connection.saveData === true) {
        value = 1;
    }
    return value;
}

function getOnLine() {
    var value = 0;
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (navigator.onLine === true) {
            value = 1;
        }
    }
    return value;
}

function getJavaEnabled() {
    var value = 0;
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (typeof navigator.javaEnabled === 'function') {
            if (navigator.javaEnabled() === true) {
                value = 1;
            }
        }
    }
    return value;
}

function getPlugins() {
    var plugins = '';
    if (typeof navigator !== 'undefined' && navigator !== null && navigator.plugins !== undefined && navigator.plugins !== null) {
        var i;
        for (i = 0; i < navigator.plugins.length; i = i + 1) {
            if (i > 0) {
                plugins = plugins + ', ';
            }
            plugins = plugins + navigator.plugins[i].name;
        }
    }
    return plugins;
}

function getGpuAdapter() {
    return new Promise(function (resolve) {
        var adapterName = '';
        if (typeof navigator !== 'undefined' && navigator !== null && typeof navigator.gpu !== 'undefined' && navigator.gpu !== null && typeof navigator.gpu.requestAdapter === 'function') {
            navigator.gpu.requestAdapter().then(function (adapter) {
                if (adapter !== null && adapter !== undefined && adapter.name !== undefined) {
                    adapterName = adapter.name;
                }
                resolve(adapterName);
            }).catch(function (error) {
                resolve(adapterName);
            });
        }
        else {
            resolve(adapterName);
        }
    });
}

function getPixelDepth() {
    var depth = 0;
    if (typeof screen !== 'undefined' && screen !== null) {
        if (screen.pixelDepth !== undefined) {
            depth = screen.pixelDepth;
        }
    }
    return depth;
}

function getLocalIP() {
    return new Promise(function (resolve) {
        var RTCPeerConnection = window.RTCPeerConnection || window.webkitRTCPeerConnection || window.mozRTCPeerConnection;

        if (typeof RTCPeerConnection === 'undefined' || RTCPeerConnection === null) {
            resolve('');
            return;
        }

        var rtc = new RTCPeerConnection({ iceServers: [] });

        function grepSDP(sdp) {
            var finalIP = '';
            var lines = sdp.split('\r\n');
            var i;
            for (i = 0; i < lines.length; i = i + 1) {
                var line = lines[i];
                if (line.indexOf("a=candidate") !== -1) {
                    var parts = line.split(' ');
                    var addr = parts[4];
                    var type = parts[7];
                    if (type === 'host') {
                        finalIP = addr;
                    }
                }
                else if (line.indexOf("c=") !== -1) {
                    var parts = line.split(' ');
                    var addr = parts[2];
                    finalIP = addr;
                }
            }
            return finalIP;
        }

        rtc.createDataChannel('', { reliable: false });

        var timeout = setTimeout(function () {
            resolve('');
            rtc.close();
        }, 5000);

        rtc.onicecandidate = function (evt) {
            if (evt.candidate) {
                var addr = grepSDP("a=" + evt.candidate.candidate);
                if (addr !== '' && addr !== '0.0.0.0') {
                    clearTimeout(timeout);
                    resolve(addr);
                    rtc.close();
                }
            }
        };

        rtc.createOffer().then(function (offerDesc) {
            rtc.setLocalDescription(offerDesc);
        }).catch(function (e) {
            console.warn("offer failed", e);
            resolve('');
            rtc.close();
        });
    });
}

function getHeadlessScore() {
    var score = 0;
    var ua = getUserAgent();
    if (typeof navigator !== 'undefined' && navigator !== null) {
        if (navigator.webdriver === true) {
            score = score + 1;
        }

        if (ua.indexOf('HeadlessChrome') !== -1) {
            score = score + 1;
        }

        if (navigator.plugins !== undefined && navigator.plugins !== null && navigator.plugins.length === 0) {
            score = score + 1;
        }

        if (navigator.languages !== undefined && navigator.languages !== null && navigator.languages.length === 0) {
            score = score + 1;
        }

        if (navigator.hardwareConcurrency !== undefined && navigator.hardwareConcurrency === 0) {
            score = score + 1;
        }

        if (navigator.brand === '' || navigator.brand === null) {
            score = score + 1;
        }

        var tz = getTimeZone();
        var tzOffset = getTimezoneOffset();
        if (tz === 'UTC' || tzOffset === 0) {
            score = score + 1;
        }

        var gpu = getGpuInfo();
        if (gpu === '' || gpu === 'WebGL non supporte') {
            score = score + 1;
        }

        var oscpu = getOscpu();
        var platform = getPlatform();
        if ((oscpu === '' || oscpu === undefined) && (platform === '' || platform === undefined)) {
            score = score + 1;
        }

        var appVersion = getAppVersion();
        var uaLower = ua.toLowerCase();
        var appVersionLower = appVersion.toLowerCase();
        var botWords = [
            'googlebot', 'googlebot-image', 'googlebot-news', 'googlebot-video', 'mediapartners-google',
            'bingbot', 'msnbot', 'bingpreview',
            'yandexbot', 'yandexadditional',
            'baiduspider', 'baidu',
            'duckduckbot', 'duckassistbot',
            'bravebot',
            'applebot', 'applebot-extended',
            'amazonbot', 'amzn-searchbot',
            'facebookexternalhit', 'facebookbot', 'meta-externalagent', 'meta-webindexer', 'meta-externalagent',
            'linkedinbot',
            'twitterbot',
            'slackbot',
            'pinterestbot',
            'ahrefsbot', 'semrushbot', 'screaming-frog-seo-spider', 'dotbot', 'mj12bot',
            'gptbot', 'oai-searchbot', 'chatgpt-user',
            'claudebot', 'claude-web', 'claude-user', 'claude-code', 'anthropic-ai',
            'perplexitybot', 'perplexity-user',
            'google-extended', 'google-cloudvertexbot', 'gemini-deep-research', 'Google-Read-Aloud',
            'ccbot', 'ai2bot',
            'bytespider',
            'cohere-ai', 'cohere-training-data-crawler',
            'diffbot', 'img2dataset', 'crawl4ai', 'firecrawl', 'youbot', 'timpi',
            'bot', 'crawler', 'spider', 'scrap', 'agent', 'harvester', 'extractor', 'monitor'
        ];
        var i;
        var containsBotWord = 0;
        for (i = 0; i < botWords.length; i = i + 1) {
            if (uaLower.indexOf(botWords[i]) !== -1 || appVersionLower.indexOf(botWords[i]) !== -1) {
                containsBotWord = 1;
                break;
            }
        }
        if (containsBotWord === 1) {
            score = score + 1;
        }

        if (ua.indexOf('http://') !== -1 || ua.indexOf('https://') !== -1 || ua.indexOf('www.') !== -1) {
            score = score + 1;
        }
    }
    return score;
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
        // Navigateur / User Agent
        user_agent: getUserAgent(),
        app_version: getAppVersion(),
        app_name: getAppName(),
        app_code_name: getAppCodeName(),
        product: getProduct(),
        product_sub: getProductSub(),
        vendor: getVendor(),
        oscpu: getOscpu(),
        platform: getPlatform(),
        // Langues (cote a cote)
        language: getLanguage(),
        languages: getLanguages(),
        // Detection client / bot
        webdriver: getWebdriver(),
        java_enabled: getJavaEnabled(),
        is_mobile: getUserAgentDataMobile(),
        user_agent_brands: getUserAgentBrands(),
        plugins: getPlugins(),
        headless_score: getHeadlessScore(),
        // Materiel / systeme
        device_memory: getDeviceMemory(),
        hardware_concurrency: getHardwareConcurrency(),
        time_zone: getTimeZone(),
        timezone_offset: getTimezoneOffset(),
        on_line: getOnLine(),
        // Reseau / connexion
        connection_effective_type: getConnectionEffectiveType(),
        connection_downlink: getConnectionDownlink(),
        connection_save_data: getConnectionSaveData(),
        local_ip: '',
        // Ecran / affichage
        screen_resolution: '',
        color_depth: 0,
        pixel_depth: getPixelDepth(),
        // GPU / graphisme
        gpu: getGpuInfo(),
        gpu_adapter: '',
        battery_level: 0,
        battery_charging: 0,
        // Visite / technique
        cookies_enabled: getCookiesEnabled(),
        do_not_track: getDoNotTrack(),
        referrer: getReferrer(),
        path: window.location.href,
        // Empreinte / debug
        fingerprint: ''
    };

    var screen = getScreenInfo();
    data.screen_resolution = screen.width + 'x' + screen.height;
    data.color_depth = screen.colorDepth;

    // Construire une chaine pour generer l'empreinte (fingerprint) du visiteur
    var fingerprintSource = data.user_agent + '|' +
        data.app_version + '|' +
        data.app_name + '|' +
        data.app_code_name + '|' +
        data.product + '|' +
        data.product_sub + '|' +
        data.vendor + '|' +
        data.oscpu + '|' +
        data.platform + '|' +
        data.language + '|' +
        data.languages + '|' +
        data.webdriver + '|' +
        data.java_enabled + '|' +
        data.is_mobile + '|' +
        data.user_agent_brands + '|' +
        data.plugins + '|' +
        data.headless_score + '|' +
        data.device_memory + '|' +
        data.hardware_concurrency + '|' +
        data.time_zone + '|' +
        data.timezone_offset + '|' +
        data.on_line + '|' +
        data.connection_effective_type + '|' +
        data.connection_downlink + '|' +
        data.connection_save_data + '|' +
        data.local_ip + '|' +
        data.screen_resolution + '|' +
        data.color_depth + '|' +
        data.pixel_depth + '|' +
        data.gpu + '|' +
        data.gpu_adapter + '|' +
        data.battery_level + '|' +
        data.battery_charging + '|' +
        data.cookies_enabled + '|' +
        data.do_not_track + '|' +
        data.referrer + '|' +
        data.path;

    // Recuperer les donnees asynchrones (batterie, IP local, GPU adapter) avant de generer l'empreinte et d'envoyer les donnees
    Promise.all([
        getBattery(),
        getLocalIP(),
        getGpuAdapter()
    ]).then(function (results) {
        data.battery_level = results[0].level;
        data.battery_charging = results[0].charging;
        data.local_ip = results[1];
        data.gpu_adapter = results[2];

        fingerprintSource = fingerprintSource + '|' +
            data.battery_level + '|' +
            data.battery_charging + '|' +
            data.local_ip + '|' +
            data.gpu_adapter;

        generateFingerprint(fingerprintSource).then(function (hash) {
            data.fingerprint = hash;
            sendVisitorData(data);
        });
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
