updateStats();
run();
updatePrice();


/*
To do : 
- Jesus effect
- Reset game settings
- Update price with BuyAmount
*/

function getCookies() {
    if (localStorage.getItem("cookie-amount") == null) {
        localStorage.setItem("cookie-amount", 0);
    }
    return Number(localStorage.getItem("cookie-amount"));
}

function cookieClick() {
    var cookieToAdd = getCookiePerClick() * getCookiePerClickMultiplied();
    localStorage.setItem("cookie-amount", getCookies() + cookieToAdd);
    updateStats();
}

function getCookiePerClick() {
    if (localStorage.getItem("cookie-per-click") == null) {
        localStorage.setItem("cookie-per-click", 1);
    }
    return Number(localStorage.getItem("cookie-per-click"));
}

function getCookiePerClickMultiplied() {
    return (1 + (getCookieMultiplicator() * 0.05));
}

function getClickPerSecond() {
    if (localStorage.getItem("click-auto-sec") == null) {
        localStorage.setItem("click-auto-sec", 0);
    }
    return Number(localStorage.getItem("click-auto-sec"));
}

function getCookiePerSecond() {
    var ClickSec = getClickPerSecond();
    var CookiePerClick = getCookiePerClick();

    var CookiePerSec = ClickSec * CookiePerClick;
    return CookiePerSec;
}

function getCookieMultiplicator() {
    if (localStorage.getItem("cookie-multiplicator") == null) {
        localStorage.setItem("cookie-multiplicator", 0);
    }
    return Number(localStorage.getItem("cookie-multiplicator"));
}

function buyBonus(price) {
    if (localStorage.getItem("cookie-amount") >= price) {
        localStorage.setItem("cookie-amount", Number(localStorage.getItem("cookie-amount")) - price);
        updateStats();
        return true;
    }
    return false;
}
function formatCompactNumber(number) {
    const formatter = Intl.NumberFormat("en", { notation: "compact" });
    return formatter.format(number);
}
function updateStats() {
    document.getElementById("cookie-per-click").textContent = formatCompactNumber(getCookiePerClick());
    document.getElementById("cookie-amount").textContent = formatCompactNumber(getCookies());
    document.getElementById("click-per-second").textContent = formatCompactNumber(getClickPerSecond());
    document.getElementById("cookie-per-second").textContent = formatCompactNumber(getCookiePerSecond());
    document.getElementById("cookie-multiplier").textContent = "x " + formatCompactNumber(getCookiePerClickMultiplied());
}

var buyAmountInt = 0;
function setBuyAmount() {
    var buyAmount = Number(localStorage.getItem("buyMode")); /* 1 to 6*/

    if (buyAmount == 1) {
        buyAmountInt = 1;
    } else if (buyAmount == 2) {
        buyAmountInt = 5;
    } else if (buyAmount == 3) {
        buyAmountInt = 10;
    } else if (buyAmount == 4) {
        buyAmountInt = 50;
    } else if (buyAmount == 5) {
        buyAmountInt = 100;
    } else if (buyAmount == 6) {
        buyAmountInt = 1000; //to update for a real max amount
    }
}

function updatePrice(){
    setPrice(1);
    setPrice(2);
    setPrice(3);
    setPrice(4);
}

function setPrice(Bonus) {
    var price = getPriceFormula(Bonus);
    var bonusName;

    if (Bonus == 1) {
        bonusName = "prix-booster";
    }

    else if (Bonus == 2) {
        bonusName = "prix-IA";
    }

    else if (Bonus == 3) {
        bonusName = "prix-multiplicateur";
    }

    else if (Bonus == 4) {
        bonusName = "prix-jesus";
    }

    document.getElementById(bonusName).textContent = price;
}

function getPriceFormula(bonus) {
    var BonusAmount = 0;
    var defaultPrice = 0;

    if (bonus == 1) {
        BonusAmount = Number(localStorage.getItem("cookie-per-click"));
        defaultPrice = 10;
    }

    else if (bonus == 2) {
        BonusAmount = Number(localStorage.getItem("click-auto-sec"));
        defaultPrice = 100;
    }

    else if (bonus == 3) {
        BonusAmount = Number(localStorage.getItem("cookie-multiplicator"));
        defaultPrice = 500;
    }

    else if (bonus == 4) {
        BonusAmount = Number(localStorage.getItem("jesus"));
        defaultPrice = 1000;
    }

    return (BonusAmount * defaultPrice);
}


function bonusClick(bonus) {
    var lastBuyOK = false;

    if (bonus == 1) {
        var BonusAmount = Number(localStorage.getItem("cookie-per-click"));
        if (buyBonus(getPriceFormula(bonus))) {
            localStorage.setItem("cookie-per-click", BonusAmount + 1);
            lastBuyOK = true;
        }
    }

    else if (bonus == 2) {
        var BonusAmount = Number(localStorage.getItem("click-auto-sec"));
        if (buyBonus(getPriceFormula(bonus))) {
            localStorage.setItem("click-auto-sec", BonusAmount + 1);
            lastBuyOK = true;
        }
    }

    else if (bonus == 3) {
        var BonusAmount = Number(localStorage.getItem("cookie-multiplicator"));
        if (buyBonus(getPriceFormula(bonus))) {
            localStorage.setItem("cookie-multiplicator", BonusAmount + 1);
            lastBuyOK = true;
        }
    }

    else if (bonus == 4) {
        var BonusAmount = Number(localStorage.getItem("jesus"));
        if (buyBonus(getPriceFormula(bonus))) {
            localStorage.setItem("jesus", BonusAmount + 1);
            lastBuyOK = true;
        }
    }


    buyAmountInt -= 1;

    if (buyAmountInt >= 1 && lastBuyOK) {
        bonusClick(bonus);
    }
    else {
        updateStats();
        setBuyAmount();
    }
    setPrice(bonus);
}

function run() {
    return new Promise(async (resolve, reject) => {
        setInterval(() => {
            var ClickSec = getClickPerSecond();
            var CookiePerClick = getCookiePerClick();

            var CookiePerSec = ClickSec * CookiePerClick;

            localStorage.setItem("cookie-amount", getCookies() + CookiePerSec / 10);
            updateStats();

            resolve();
        }, 100);
    });
}

function buyMode(mode) {
    const element = document.getElementById("buyMenu");
    for (let i = 0; i < element.children.length; i++) {
        element.children[i].className = "";
    }

    if (mode == 1) {
        document.getElementById("x1").className = "active";
        localStorage.setItem("buyMode", 1);
    }
    else if (mode == 2) {
        document.getElementById("x5").className = "active";
        localStorage.setItem("buyMode", 2);
    }
    else if (mode == 3) {
        document.getElementById("x10").className = "active";
        localStorage.setItem("buyMode", 3);
    }
    else if (mode == 4) {
        document.getElementById("x50").className = "active";
        localStorage.setItem("buyMode", 4);
    }
    else if (mode == 5) {
        document.getElementById("x100").className = "active";
        localStorage.setItem("buyMode", 5);
    }
    else if (mode == 6) {
        document.getElementById("x1000").className = "active";
        localStorage.setItem("buyMode", 6);
    }

    setBuyAmount();
}