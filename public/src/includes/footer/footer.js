console.log('footer.js loaded');

function devFooter() {
    var offline = document.getElementById("offline-url");
    var online = document.getElementById("online-url");
    var mixed = document.getElementById("mixed-url");
    var php = document.getElementById("php-url");
    var github = document.getElementById("github-url");

    php.href = "http://localhost:8000" + window.location.pathname;
    github.href = "https://altherneum.github.io" + window.location.pathname;
    offline.href = "http://127.0.0.1:3000" + window.location.pathname;
    online.href = "https://doc.Altherneum.fr" + window.location.pathname;
    mixed.href = "https://3000.code.altherneum.fr" + window.location.pathname;
}
devFooter();