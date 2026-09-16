start()

async function start() {
    fetch('https://api.ipify.org?format=json')
        .then(response => response.json())
        .then(data => setIPv4(data.ip));

    fetch('https://api64.ipify.org?format=json')
        .then(response => response.json())
        .then(data => setIPv6(data.ip));

    getLocalIP()
        .then((ipAddr) => { setIPLocal(ipAddr); });

    show();
}

function getLocalIP() {
    return new Promise(function (resolve, reject) {
        var RTCPeerConnection = window.RTCPeerConnection || window.webkitRTCPeerConnection || window.mozRTCPeerConnection;

        if (!RTCPeerConnection) {
            reject('Your browser does not support this API');
        }

        var rtc = new RTCPeerConnection({ iceServers: [] });
        var addrs = {};
        addrs["0.0.0.0"] = false;

        function grepSDP(sdp) {
            var hosts = [];
            var finalIP = '';
            sdp.split('\r\n').forEach(function (line) {
                if (~line.indexOf("a=candidate")) {
                    var parts = line.split(' '),
                        addr = parts[4],
                        type = parts[7];
                    if (type === 'host') {
                        finalIP = addr;
                    }
                } else if (~line.indexOf("c=")) {
                    var parts = line.split(' '),
                        addr = parts[2];
                    finalIP = addr;
                }
            });
            return finalIP;
        }

        if (1 || window.mozRTCPeerConnection) {
            rtc.createDataChannel('', { reliable: false });
        };

        rtc.onicecandidate = function (evt) {
            if (evt.candidate) {
                var addr = grepSDP("a=" + evt.candidate.candidate);
                resolve(addr);
            }
        };
        rtc.createOffer(function (offerDesc) {
            rtc.setLocalDescription(offerDesc);
        }, function (e) { console.warn("offer failed", e); });
    });
}

function setIPv4(string) {
    document.getElementById("ipv4").textContent = string;
}

function setIPv6(string) {
    document.getElementById("ipv6").textContent = string;
}

function setIPLocal(string) {
    document.getElementById("ipLocal").textContent = string;
}

function logIntoDocumentBR() {
    document.getElementById("logger").innerHTML += "<br>";
}

function logIntoDocumentHR() {
    document.getElementById("logger").innerHTML += "<hr>";
}

async function show() {
    logIntoDocumentHR();

    try {
        logIntoDocument("Langue : ", navigator.language);
        logIntoDocument("Langue(s) : ", navigator.languages);
    } catch { }

    logIntoDocumentHR();

    try {
        logIntoDocument("OS : ", navigator.userAgentData.platform);
        logIntoDocument("Téléphone : ", navigator.userAgentData.mobile);
    } catch { }

    try {
        logIntoDocument("Browser : ", navigator.vendor);
    } catch { }

    try {
        logIntoDocument("AgentData : ", navigator.userAgentData.brands[1].brand + " v." + navigator.userAgentData.brands[1].version);
    } catch { }

    try {
        logIntoDocument("UserAgent : ", window.navigator.userAgent);
    } catch { }

    logIntoDocumentHR();

    try {
        logIntoDocument("Memory : ", navigator.deviceMemory + "GB of browser RAM");
        logIntoDocument("logical processors : ", navigator.hardwareConcurrency);
    } catch (error) { }

    try {
        logIntoDocument("Connection : ", navigator.connection.effectiveType);
        logIntoDocument("DownLink : ", navigator.connection.downlink + " Mb / s");
        logIntoDocument("Économiseur de données : ", navigator.connection.saveData);
        logIntoDocument("En ligne : ", navigator.onLine);
        logIntoDocumentHR();
    } catch { }

    try {
        logIntoDocument("Cookie actifs :", navigator.cookieEnabled);
        logIntoDocument("Java actifs :", navigator.javaEnabled());
        logIntoDocumentHR();
    } catch { }

    try {
        const adapter = await navigator.gpu.requestAdapter();
        for (value in adapter.limits) {
            logIntoDocument(value + " : ", adapter.limits[value]);
        }
    } catch { }


    try {
        const battery = navigator.getBattery();
        battery.then((resultat) => {
            for (value in resultat) {
                if ((!value.startsWith("on") && !value.includes("change")) && !value.includes("Event")) {
                    logIntoDocument(value + " : ", resultat[value]);
                }
            }
        });

        logIntoDocumentHR();
    }
    catch { }


    try {
        if ("credentials" in navigator) {
            navigator.credentials.get({ password: true }).then((creds) => {
                if (creds != null) {
                    logIntoDocumentHR();
                    logIntoDocument(creds);
                    logIntoDocumentHR();
                }
            });
        }
    }
    catch { }

    try {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(geolocationCallBack);
        } else {
            logIntoDocument("Geolocation : ", false);
        }
    }
    catch (error) {
        if (error.code == error.PERMISSION_DENIED) {
            logIntoDocument("Gelocation : ", "Permission Off");
        }
    }

    try {
        logIntoDocument("Width : ", screen.width);
        logIntoDocument("Height : ", screen.height);
        logIntoDocument("Color depth : ", screen.colorDepth);
        logIntoDocument("Pixel depth : ", screen.pixelDepth);
        logIntoDocumentHR();
    }
    catch { }
}

function geolocationCallBack(position) {
    if (position === undefined) {

        logIntoDocument("Gelocation : ", "Permission Off");
    }
    logIntoDocument("Latitude: ", position.coords.latitude);
    logIntoDocument("Longitude: " + position.coords.longitude);
    logIntoDocumentHR();
}

function logIntoDocument(text, data) {
    try {
        document.getElementById("logger").innerHTML += "<h1>" + text + "</h1><p>" + data + "</p><br>";
    } catch {

    }
}