// Donnees de suivi dynamique de la visite
var visitorBaseData = {};
var baseDataReady = false;
var pageStartTime = null;
var pageLoadTime = 0;
var mousePositions = [];
var lastMousePositionTime = 0;

// Intervals (en millisecondes)
var PERIODIC_INTERVAL = 30000; // Envoi des donnees toutes les 30 secondes
var MOUSE_LOG_INTERVAL = 1000; // Position de la souris toutes les 1 seconde

function prepareVisitorData(source, extra) {
    var data = {};
    var key;
    // Copier les donnees de base (empreinte visiteur)
    for (key in visitorBaseData) {
        if (visitorBaseData.hasOwnProperty(key)) {
            data[key] = visitorBaseData[key];
        }
    }
    // Champ indiquant l'origine de l'envoi des donnees
    data.source = source;
    // Temps passe sur la page depuis le chargement (secondes)
    data.time_spent = getTimeSpent();
    // Temps de chargement de la page (millisecondes)
    data.page_load_time = pageLoadTime;
    // Pourcentage de scroll actuel
    data.scroll_percentage = getScrollPercentageBis();
    // Positions de la souris (tableau stocke en JSON)
    data.mouse_positions = JSON.stringify(mousePositions);
    // Fusionner les donnees supplementaires
    if (extra !== null && extra !== undefined && typeof extra === 'object') {
        for (key in extra) {
            if (extra.hasOwnProperty(key)) {
                data[key] = extra[key];
            }
        }
    }
    return data;
}

function sendVisitorDataWithSource(source, extra) {
    var data = prepareVisitorData(source, extra);
    sendVisitorData(data);
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
    // NOTE : localIP, referrer, battery, battery_charging, path, connection_effective_type,
    // connection_downlink, connection_save_data sont exclus du hash pour plus de stabilite.
    // Ces champs sont tout de meme envoyes au serveur mais ne font pas partie de l'empreinte.
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
        data.screen_resolution + '|' +
        data.color_depth + '|' +
        data.pixel_depth + '|' +
        data.gpu + '|' +
        data.gpu_adapter + '|' +
        data.cookies_enabled + '|' +
        data.do_not_track;

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

        // NOTE : battery_level, battery_charging, local_ip ne sont pas ajoutés au hash
        // pour maintenir la stabilite de l'empreinte. Ces valeurs sont however
        // conservees dans data et envoyees au serveur.

        generateFingerprint(fingerprintSource).then(function (hash) {
            data.fingerprint = hash;
            visitorBaseData = data;
            baseDataReady = true;
            pageLoadTime = getPageLoadTime();
            // Envoyer les donnees au chargement de la page (source: page_load)
            var loadData = prepareVisitorData('page_load', {});
            sendVisitorData(loadData);
        });
    });
}

function sendBeaconData(data) {
    var url = '/api/data';
    var body = JSON.stringify(data);
    var blob = new Blob([body], { type: 'application/json; charset=utf-8' });

    if (typeof navigator !== 'undefined' && navigator !== null && typeof navigator.sendBeacon === 'function') {
        navigator.sendBeacon(url, blob);
        return;
    }

    // Fallback si sendBeacon n'est pas supporte
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json; charset=utf-8'
        },
        body: body,
        keepalive: true
    }).catch(function (error) {
        console.warn('Impossible d\'envoyer les donnees de visite (fallback) : ' + error);
    });
}

function sendVisitorData(data) {
    sendBeaconData(data);
}