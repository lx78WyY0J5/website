function base64ToBytes(base64) {
    const binString = atob(base64);
    return Uint8Array.from(binString, (m) => m.codePointAt(0));
}

function bytesToBase64(bytes) {
    const binString = Array.from(bytes, (byte) =>
        String.fromCodePoint(byte),
    ).join("");
    return btoa(binString);
}

function stringToBase64(text) {
    return bytesToBase64(new TextEncoder().encode(text));
}

function base64ToString(base64) {
    return new TextDecoder().decode(base64ToBytes(base64));
}

function base64Encoder() {
    try {
        var encode = getEncodeOrDecode();
        var text = getTextInput();
        var output;
        console.log(text);
        if (!encode) {
            output = stringToBase64(text);
        }
        else {
            output = base64ToString(text);
        }
        console.log(output);

    }
    catch (error) {
        output = "erreur : " + error;
        console.log(output);
    }

    var outputElem = document.getElementById("base64Output");
    outputElem.textContent = output;
}

function getEncodeOrDecode() {
    var checkbox = document.getElementById("base64EncodeSwitch");
    return checkbox.checked;
}

function getTextInput() {
    var inputElem = document.getElementById("base64Input");
    return inputElem.value;
}

base64Switcher();
function base64Switcher() {
    var textSwitch = document.getElementById("base64EncodeSwitchText");
    if (getEncodeOrDecode()) {
        textSwitch.textContent = "Base64 ▶ String";
    } else {
        textSwitch.textContent = "String ▶ Base64";
    }
}