run();
function run() {
    return new Promise(async (resolve, reject) => {
        setInterval(() => {
            setTimeout(() => {
                ping(getProtValues(), getHostValues(), getPortValues());
            }, 5000);
        }, 500);
    });
}

var min = Number.MAX_VALUE;
var max = Number.MIN_VALUE;
var total = 0;
var numberOfPing = 0;

function ping(prot, host, port) {
    document.getElementById("text").textContent = prot + "://" + host + ":" + port;
    document.getElementById("adress").href = prot + "://" + host + ":" + port;

    var started = new Date().getTime();

    fetch(prot + "://" + host + ":" + port, { mode: "no-cors" }).then((response) => {
        var ended = new Date().getTime();
        var milliseconds = ended - started;

        if (min > milliseconds) {
            min = milliseconds;
            document.getElementById("min").textContent = "Min : " + min;
        }

        if (max < milliseconds) {
            max = milliseconds;
            document.getElementById("max").textContent = "Max : " + max;
        }

        total += milliseconds;
        numberOfPing++;
        document.getElementById("moyenne").textContent = "Moyenne : " + Math.round(total / numberOfPing);

        document.getElementById("ping-value").textContent = milliseconds + " ms";
        document.getElementById("errorCode").textContent = "Code : " + response.status + ".";
        document.getElementById("errorCodeDescription").textContent = response.statusText;
    }).catch(exception => {
        document.getElementById("errorCode").textContent = exception;

        console.error(exception);
    });
}

function getProtValues() {
    return document.getElementById("prot").value;
}
function getHostValues() {
    return document.getElementById("host").value;
}
function getPortValues() {
    return document.getElementById("port").value;
}