console.log('footer.js loaded');

devFooter();
function devFooter() {
    var offline = document.getElementById("offline-url");
    var online = document.getElementById("online-url");
    var mixed = document.getElementById("mixed-url");
    var php = document.getElementById("php-url");
    var github = document.getElementById("github-url");

    offline.href = "http://127.0.0.1:3000" + window.location.pathname;
    online.href = "https://doc.Altherneum.fr" + window.location.pathname;
    mixed.href = "https://3000.code.altherneum.fr" + window.location.pathname;
    php.href = "https://3000.code.altherneum.fr" + window.location.pathname;
    github.href = "https://3000.code.altherneum.fr" + window.location.pathname;
}