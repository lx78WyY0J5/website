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
                if (adapter !== null && adapter !== undefined) {
                    adapterName = adapter.info.description;
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
            'bytedance',
            'shapbot',
            'opencode', 'electron',
            'ahrefsbot', 'semrushbot', 'screaming-frog-seo-spider', 'dotbot', 'mj12bot',
            'gptbot', 'oai-searchbot', 'chatgpt-user',
            'claudebot', 'claude-web', 'claude-user', 'claude-code', 'anthropic-ai',
            'perplexitybot', 'perplexity-user',
            'google-extended', 'google-cloudvertexbot', 'gemini-deep-research', 'Google-read-aloud',
            'ccbot', 'ai2bot',
            'bytespider',
            'cohere-ai', 'cohere-training-data-crawler',
            'diffbot', 'img2dataset', 'crawl4ai', 'firecrawl', 'youbot', 'timpi',
            'bot', 'scan', 'crawler', 'spider', 'scrap', 'agent', 'harvester', 'extractor', 'monitor', 'security', 'research'
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

function getScrollPercentage() {
    var doc = document.documentElement;
    var body = document.body;
    var top = 'scrollTop';
    var height = 'scrollHeight';
    var scrollTop = doc[top] || body[top];
    var scrollHeight = (doc[height] || body[height]) - doc.clientHeight;
    var percent = 0;
    if (scrollHeight > 0) {
        percent = (scrollTop / scrollHeight) * 100;
    }
    if (percent < 0) {
        percent = 0;
    }
    if (percent > 100) {
        percent = 100;
    }
    return percent;
}

function getTimeSpent() {
    var seconds = 0;
    if (pageStartTime !== null) {
        seconds = Math.floor((Date.now() - pageStartTime) / 1000);
    }
    return seconds;
}

function getPageLoadTime() {
    var loadTime = 0;
    if (typeof performance !== 'undefined' && performance !== null && typeof performance.timing !== 'undefined' && performance.timing !== null) {
        var timing = performance.timing;
        if (timing.loadEventEnd > 0 && timing.navigationStart > 0) {
            loadTime = timing.loadEventEnd - timing.navigationStart;
        }
    }
    return loadTime;
}

function isTrackedElement(element) {
    var tracked = false;
    if (element === null) {
        return tracked;
    }
    var tagName = element.tagName;
    if (tagName === 'A' || tagName === 'BUTTON') {
        tracked = true;
    }
    else if (tagName === 'INPUT') {
        var type = element.getAttribute('type');
        if (type === 'button' || type === 'submit' || type === 'reset') {
            tracked = true;
        }
    }
    return tracked;
}

function getElementInfo(element) {
    var info = {
        tag: '',
        text: '',
        href: '',
        id: '',
        class: ''
    };
    if (element === null) {
        return info;
    }
    info.tag = element.tagName;
    if (element.innerText !== undefined && element.innerText !== null) {
        info.text = element.innerText;
    }
    else if (element.textContent !== undefined && element.textContent !== null) {
        info.text = element.textContent;
    }
    var href = element.getAttribute('href');
    if (href !== null) {
        info.href = href;
    }
    var id = element.getAttribute('id');
    if (id !== null) {
        info.id = id;
    }
    var classAttr = element.getAttribute('class');
    if (classAttr !== null) {
        info.class = classAttr;
    }
    return info;
}