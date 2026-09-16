function cipher() {
    getData();
}

function getData() {
    var text = document.getElementById("input").value;
    var number = document.getElementById("number").value;

    if (text !== "" && number !== "") {
        cipherText(text, number);
    }
}

function cipherText(text, rotation) {
    document.getElementById("output").value = "";
    for (let letter of text) {
        value = parseInt(letter.charCodeAt(0)) + parseInt(rotation);
        document.getElementById("output").value += String.fromCharCode(value);
    }
}