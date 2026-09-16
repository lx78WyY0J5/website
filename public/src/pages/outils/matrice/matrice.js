var symbolContainer = document.querySelector(".symbol-container");
const symbolCount = 500;

for (let i = 0; i < symbolCount; i++) {
    var symbol = document.createElement("div");
    symbol.className = "symbol";
    symbol.textContent = randomChar();
    symbol.style.left = Math.random() * 100 + "%";
    symbol.style.animationDelay = ((Math.random() + 0.2)) + "s";
    symbol.style.animationDuration = ((Math.random() + 0.2) * 10) + "s";
    var colorAdder = Math.random() * 50;
    symbol.style.color = "rgb(" + colorAdder + ", " + (255 - (Math.random() * 50)) + " , " + colorAdder + ")";
    symbolContainer.appendChild(symbol);
}

function randomChar() {
    var min = 33;
    var max = 5000;
    var r = Math.random() * (max - min) + min << 0;
    var string = String.fromCharCode(r);
    return string;
}