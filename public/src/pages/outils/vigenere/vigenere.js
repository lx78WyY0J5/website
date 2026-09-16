var alphabet;
var alphabetLength;
var text;
var textLength;
var clef;
var clefLength;

setDefaultAlphabet();
function setDefaultAlphabet() {
    var textarea = document.getElementById("alphabet-default");
    var alphabet = document.getElementById("alphabet");
    var chars = "";
    for (var i = 32; i < 255; i++)
    {
        chars += String.fromCharCode(i);
    }
    textarea.value = chars;
    alphabet.value = chars;
}

function cipher(uncipher) {
    getData();
    if (alphabet !== "" && text !== "" && clef !== "") {
        var textFinal = chiffrement(text, textLength, alphabet, alphabetLength, clef, clefLength, uncipher);
        document.getElementById("output").value = textFinal;
    }
}

function getData() {
    alphabet = document.getElementById("alphabet").value;
    alphabetLength = alphabet.length;
    text = document.getElementById("text").value;
    textLength = text.length;
    clef = document.getElementById("clef").value;
    clefLength = clef.length;
}

function chiffrement(text, textLength, alphabet, alphabetLength, clef, clefLength, uncipher) {
    var result = "";

    for (i = 0; i < textLength; i++) {
        indexLettreAChiffrer = alphabet.indexOf(text[i]);
        indexLettreClef = alphabet.indexOf(clef[i % clefLength]);

        if (!uncipher) {
            LettreChiffre = alphabet[(indexLettreAChiffrer + indexLettreClef) % alphabetLength];
        }
        else if (uncipher) {
            LettreChiffre = alphabet[(indexLettreAChiffrer - indexLettreClef + alphabetLength) % alphabetLength];
        }

        result += LettreChiffre;
    }

    return result;
}



/*
Note de sécurité ;
La taille de la clef n'est pas check
La clef doit être >= text en taille
Sans quoi la clef utilisé une fois fini sera le dernier caractère jusqu'à la fin du text

Solution :
Si clefTaille < textTaille
Loop la clef afin qu'elle soit de la bonne taille
*/