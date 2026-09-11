window.addEventListener('DOMContentLoaded', () => {
    console.log('contact.js loaded');
});

// The maximum is exclusive and the minimum is inclusive
function getRandomInt(min, max) {
    const minCeiled = Math.ceil(min);
    const maxFloored = Math.floor(max);
    return Math.floor(Math.random() * (maxFloored - minCeiled) + minCeiled); 
}

function intToChar(value){
    return String.fromCharCode(value);
}

async function setContactNameRandom(){
    var length = getRandomInt(6, 11);
    var name = "";

    for(var i = 0; i <= length; i++){
        var x = getRandomInt(32, 1024);

        var y = intToChar(x);
        name += y;
    }

    var text = document.getElementById("contact-name");
    text.textContent = name;
}

function swapNameLetter(){
    var text = document.getElementById("contact-name");
    var textLength = text.textContent.length;

    var charToSwap = getRandomInt(0, textLength+1);
    var textContent = text.textContent;

    var newLetterValue = getRandomInt(32, 1024);
    var newLetter = intToChar(newLetterValue);

    text.textContent = replaceCharWith(textContent, newLetter, charToSwap);
}

function replaceCharWith(textContent, newLetter, charToSwap) {
    var length = textContent.length;
    var text = "";
    for (var i = 0; i < length; i++){
        if (i === charToSwap) {
            text += newLetter;
        }
        else {
            text += textContent[i];
        }
    }
    return text;
}

setContactNameRandom();
setInterval(swapNameLetter, 150);

let pubkeyURL = "/src/assets/txt/GPG/pub.asc";
getPubKey(pubkeyURL);

async function getPubKey(URL){
    const response = await fetch(URL);

    const data = await response.text(); 

    const textarea = document.getElementById("pub-key");

    textarea.textContent = data;
}

function copyGPG() {
    const textarea = document.getElementById("pub-key");
    navigator.clipboard.writeText(textarea.textContent);
}

function downloadGPG() {
    const content = document.getElementById("pub-key").textContent;
    let filename = "altherneum.pub.asc";

    const blob = new Blob([content], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = url;
    a.download = filename;

    document.body.appendChild(a);
    a.click();

    setTimeout(() => {
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }, 0);
}