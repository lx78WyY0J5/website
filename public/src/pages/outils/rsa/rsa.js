var privateKey;
var publicKey;
var consoleHTML = document.getElementById("rsa-console");
consoleHTML.textContent += "\n- RSA (Info console) -";

async function getRandomKey(maxBits) {
    consoleHTML.textContent += "\nStarting generation of random prime number";
    let value = getRandomNumber(maxBits);
    consoleHTML.textContent += "\nStarting with randome prime : " + value;

    while(!isPrime(value)){
        value += 1;
        consoleHTML.textContent += "\ntesting prime : " + value;

        await yieldControl(); //Give browser control for UI & event & rendering pipeline
    }

    return value;
}

function getRandomNumber(maxBits) {
    return Math.floor(Math.random() * bitsToInt(maxBits));
}

function intToBits(value){
    return Math.round(Math.log(x)/Math.log(2) + 1);
}

function bitsToInt(maxBits){
    return Math.pow(2, maxBits)-1;
}

function isPrime(value) {
    if (value > 1) {
        for (let i = 2; i <= value / 2; i++) {
            if (value % i == 0) {
                return false;
            }
        }
        return true;
    }
    else { return false; }
}

// Might be faster (to test if working)
function isPrimeBis(value) {
    if (value < 2) return false;
    if (value === 2 || value === 3) return true;
    if (value % 2 === 0 || value % 3 === 0) return false;

    // Check divisibility only up to the square root of value
    for (let i = 5; i * i <= value; i += 6) {
        if (value % i === 0 || value % (i + 2) === 0) {
            return false;
        }
    }
    return true;
}

function getCoprime(prime, maxBits) {
    consoleHTML.textContent += "\ngetting coprime";
    let value = getRandomKey(maxBits);
    while (!IsFactorOf(prime, value) && value > prime) {
        consoleHTML.textContent += "\ntesting : " + value;

        if (value > 1) {
            value -= 1;
            consoleHTML.textContent += "\ntesting new coprime " + value;
        }
        else {
            value = getRandomKey(maxBits);
            consoleHTML.textContent += "\nValue to low, getting new random key " + value;
        }
    }
    consoleHTML.textContent += "\nfound value is " + value;

    return value;
}

function IsFactorOf(prime, value) {
    consoleHTML.textContent += "Check if " + prime + " is factor of " + value + " = ";
    let boolIsFactor = prime % value == 0;
    consoleHTML.textContent += boolIsFactor;
    return boolIsFactor;
}

function gcd(a, b) {
    if (!b) {
      return a;
    }

    return gcd(b, a % b);
}

function modInverse(value, modulo) {
    if (gcd(value, modulo) > 1) {
        return -1;
    }

    for(let x = 1; x < modulo; x++){
        if (((value % modulo) * (x % modulo)) % modulo == 1){
            return x;
        }
    }
}

async function RSAKeyGen() {
    var maxBits = document.getElementById("valueMaxBits").value;
    consoleHTML.textContent += "\nstarting RSA keyGen";

    var p = await getRandomKey(maxBits);
    consoleHTML.textContent += "\n\np = " + p;

    var q = getRandomKey(maxBits);
    consoleHTML.textContent += "\n\nq = " + q;

    var n = p * q;
    consoleHTML.textContent += "\n\nn = " + p + "x" + q + " = " + n;
    document.getElementById("valueN").value = n;

    var phi = (p-1) * (q-1);
    consoleHTML.textContent += "\n\nphi = " + phi;

    var e = getCoprime(phi, maxBits);
    consoleHTML.textContent += "\n\ncoprime e = " + e;
    document.getElementById("valueE").value = e;

    //var d = modInverse(e, phi); // seem not OK
    var d = getPrivateKey(e, phi, maxBits);
    consoleHTML.textContent += "\n\nmodInverse d = " + d;
    document.getElementById("valueD").value = d;

    privateKey = {n,d};
    publicKey = {n,e};

    return;
}

function getPrivateKey(e, phi, maxBits) {
    let value = 2;
    while (!isPrivateKeyOK(e, phi, value)) {
        value += 1;
    }
    return value;
}

function isPrivateKeyOK(e, phi, value) {
    if ((value * e) % phi === 1) {
        return true;
    }
    return false;
}

function encrypt() {
    let value = document.getElementById("value").value;
    let maxBits = document.getElementById("valueMaxBits").value;

    if (value < 0 || value >= publicKey.n) {
        consoleHTML.textContent += "Condition 0 <= m < n not met. m = " + value;
        RSAKeyGen(maxBits); encrypt(value);
    }

    if (gcd(value, publicKey.n) !== 1) {
        consoleHTML.textContent += "Condition gcd(value, n) = 1 not met.";
        RSAKeyGen(maxBits); encrypt(value);
    }

    //var x = Math.pow(BigInt(value), BigInt(publicKey.e)) % BigInt(publicKey.n);
    let x = Number(BigInt(value) ** BigInt(publicKey.e) % BigInt(publicKey.n));

    //BigInt(60n ** 41n % 133n) = 72n // is OK

    consoleHTML.textContent += "\n\n---------------------- crypt  " + x;
    return x;
    // value ** publicKey.e % publicKey.n
}

function decrypt() {
    let value = document.getElementById("value").value;

    let x = Number(BigInt(value) ** BigInt(privateKey.d) % BigInt(privateKey.n));

    consoleHTML.textContent += "\n\n---------------------- decrypt  " + x;
    return x;
    //value ** privateKey.d % privateKey.n
}

async function test() {
    consoleHTML.textContent += "\n\nSTARTING RSA TEST";

    await RSAKeyGen();




    consoleHTML.textContent += "\n\nvalue to crypt ; " + valueToCrypt;


    let x1 = Math.round(encrypt(valueToCrypt, maxBits));
    consoleHTML.textContent += "\n\ncrypt  "+x1;


    let x2 = Math.round(decrypt(x1));
    consoleHTML.textContent += "\n\ndecrypt  " + x2;


    if (valueToCrypt === x2)
    {
        consoleHTML.textContent += "\nOK result\n\n";
    }
    else {
        consoleHTML.textContent += "\nResult NOK\n\n";
    }

    return;
}