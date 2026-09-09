<div class="cardUI">
    <link rel="stylesheet" href="/src/includes/header/search/navlink.css">
    <a href="/" class="cardTitle">
        <img src="/src/assets/svg/home.svg" class="svg" alt="">
    </a>
    <div tabindex="0" class="subnav">
        <div class="subnav-text">
            <img src="/src/assets/svg/administrator.svg" class="svg" alt="">
            <h2>Compte</h2>
        </div>
        <div class="subnav-content">
            <div>
                <?php
                    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
                        echo "<a href='/logout'><img src='/src/assets/svg/settings.svg' class='svg' alt=''>Logout</a>";
                        echo "<a href='/password/update'><img src='/src/assets/svg/password.svg' class='svg' alt=''>Update password</a>";
                    }
                    else{
                        echo "<a href='/login'><img src='/src/assets/svg/app-gear.svg' class='svg' alt=''>Login</a>";
                        echo "<a href='/register'><img src='/src/assets/svg/app-gear.svg' class='svg' alt=''>Register</a>";
                    }
                ?>
            </div>
        </div>
    </div>
    <div tabindex="0" class="subnav">
        <div class="subnav-text">
            <img src="/src/assets/svg/trademark/github.svg" class="svg" alt="">
            <h2>Github</h2>
        </div>
        <div class="subnav-content">
            <div class="sided">
                <a href="/github/statistiques"><img src="/src/assets/svg/app-gear.svg" class="svg" alt="">Statistiques</a>
                <a href="/github/contributeur"><img src="/src/assets/svg/people.svg" class="svg" alt="">Contributeurs</a>
                <a href="/github/issues"><img src="/src/assets/svg/issue.svg" class="svg" alt="">Issues</a>
                <hr>
                <a href="/github/contribuer"><img src="/src/assets/svg/help-question.svg" class="svg" alt="">Contribuer</a>
                <a href="/github/support"><img src="/src/assets/svg/help-question.svg" class="svg" alt="">Support</a>
                <a href="/github/security"><img src="/src/assets/svg/help-question.svg" class="svg" alt="">Security</a>
                <a href="/github/license"><img src="/src/assets/svg/help-question.svg" class="svg" alt="">License</a>
                <a href="/github/code_of_conduct"><img src="/src/assets/svg/help-question.svg" class="svg" alt="">Code de conduite</a>
            </div>
            <div class="sided">
                <a href="https://github.com/Altherneum/"><img src="/src/assets/svg/trademark/organisation.svg" class="svg" alt="">Github organisation</a>
                <a href="/github/readme"><img src="/src/assets/svg/note.svg" class="svg" alt="">Read me</a>
                <hr>
                <a href="/github/Altherneum.github.io"><img src="/src/assets/svg/trademark/repo.svg" class="svg" alt="">Altherneum.github.io</a>
                <a href="/github/.github"><img src="/src/assets/svg/trademark/repo.svg" class="svg" alt="">.github</a>
                <hr>
                <a href="/github/plugin"><img src="/src/assets/svg/trademark/repo.svg" class="svg" alt="">Plugin (MC)</a>
                <a href="/github/resourcePack"><img src="/src/assets/svg/trademark/repo.svg" class="svg" alt="">Resource pack</a>
                <hr>
                <a href="/github/bot"><img src="/src/assets/svg/trademark/repo.svg" class="svg" alt="">Bot (Discord)</a>
                <a href="/github/server"><img src="/src/assets/svg/trademark/repo.svg" class="svg" alt="">Server</a>
            </div>
        </div>
    </div>

    <div tabindex="0" class="subnav">
        <div class="subnav-text">
            <img src="/src/assets/svg/trademark/discord.svg" class="svg" alt="">
            <h2>Discord</h2>
        </div>
        <div class="subnav-content">
            <a target="_blank" href="/discord" alt="https://discord.gg/rF25kjuv4v"><img src="/src/assets/svg/trademark/discord.svg" class="svg" alt="">Serveur</a>
            <a target="_blank" href="/discord/tempvoc"><img src="/src/assets/svg/telephone.svg" class="svg" alt="">Salon vocal temporaire invité</a>
            <a target="_blank" href="/discord/voc"><img src="/src/assets/svg/telephone.svg" class="svg" alt="">Salon vocal temporaire</a>
            <hr>
            <a href="/discord/login"><img src="/src/assets/svg/trademark/discord.svg" class="svg" alt="">API login</a>
            <a href="/discord/api"><img src="/src/assets/svg/trademark/discord.svg" class="svg" alt="">API</a>
            <hr>
            <a target="_blank" href="https://discord.com/developers"><img src="/src/assets/svg/link.svg" class="svg" alt="">Portail développeur</a>
            <a target="_blank" href="https://www.discordicon.com/"><img src="/src/assets/svg/link.svg" class="svg" alt="">Créateur de badge</a>
            <a target="_blank" href="https://rebane2001.com/discord-colored-text-generator/"><img src="/src/assets/svg/link.svg" class="svg" alt="">Générateur de text en couleur</a>
        </div>
    </div>

    <div tabindex="0" class="subnav">
        <div class="subnav-text">
            <img src="/src/assets/svg/school.svg" class="svg" alt="">
            <h2>Dév</h2>
        </div>
        <div class="subnav-content">
            <div>
                <a href="/cours/web"><img src="/src/assets/svg/languages/html.svg" class="svg" alt="">Web</a>
                <hr>
                <a href="/cours/html"><img src="/src/assets/svg/languages/html.svg" class="svg" alt="">HTML</a>
                <a href="/cours/css"><img src="/src/assets/svg/languages/css.svg" class="svg" alt="">CSS</a>
                <a href="/cours/sql"><img src="/src/assets/svg/languages/sql.svg" class="svg" alt="">SQL</a>
                <a href="/cours/js"><img src="/src/assets/svg/languages/js.svg" class="svg" alt="">JS</a>
                <a href="/cours/php"><img src="/src/assets/svg/languages/php.svg" class="svg" alt="">PHP</a>
                <hr>
                <a href="/cours/binaire"><img src="/src/assets/svg/matrix.svg" class="svg" alt="">Binaire</a>
                <hr><a href="/cours/markdown"><img src="/src/assets/svg/languages/markdown.svg" class="svg" alt="">MarkDown</a>
                <a href="/cours/markdown-listing"><img src="/src/assets/svg/languages/markdown.svg" class="svg" alt="">MarkDown tester</a>
                <a href="/cours/powershell"><img src="/src/assets/svg/languages/powershell.svg" class="svg" alt="">PowerShell</a>
            </div>
        </div>
    </div>

    <div tabindex="0" class="subnav">
        <div class="subnav-text">
            <img src="/src/assets/svg/school.svg" class="svg" alt="">
            <h2>OS</h2>
        </div>
        <div class="subnav-content">
            <div class="sided">
                <a href="/cours/windows"><img src="/src/assets/svg/console.svg" class="svg" alt="">Windows</a>
                <hr>
                <a href="/cours/active-directory"><img src="/src/assets/svg/server.svg" class="svg" alt="">Active Directory</a>
                <a href="/cours/active-directory-approbation"><img src="/src/assets/svg/network.svg" class="svg" alt="">AD Approbation</a>
                <a href="/cours/gpo"><img src="/src/assets/svg/developpement.svg" class="svg" alt="">GPO</a>
                <hr>
                <a href="/cours/hyper-v"><img src="/src/assets/svg/server.svg" class="svg" alt="">Hyper-V</a>
            </div>
            <div class="sided">
                <a href="/cours/forkBomb"><img src="/src/assets/svg/bomb.svg" class="svg" alt="">Fork bomb</a>
                <a href="/cours/binaire"><img src="/src/assets/svg/matrix.svg" class="svg" alt="">Binaire</a>
                <hr>
                <a href="/cours/linux"><img src="/src/assets/svg/console.svg" class="svg" alt="">Linux</a>
                <a href="/cours/docker"><img src="/src/assets/svg/trademark/docker.svg" class="svg-color" alt="">Docker</a>
            </div>
        </div>
    </div>

    <div tabindex="0" class="subnav">
        <div class="subnav-text">
            <img src="/src/assets/svg/school.svg" class="svg" alt="">
            <h2>Réseau</h2>
        </div>
        <div class="subnav-content">
            <div>
                <a href="/cours/network"><img src="/src/assets/svg/network.svg" class="svg" alt="">Réseau</a>
                <a href="/cours/enterprise-network"><img src="/src/assets/svg/network.svg" class="svg" alt="">Réseaux d'entreprises</a>
                <a href="/cours/ip"><img src="/src/assets/svg/network.svg" class="svg" alt="">IP</a>
                <hr>
                <a href="/cours/ethernet"><img src="/src/assets/svg/ethernet.svg" class="svg" alt="">Ethernet</a>
                <a href="/cours/fibre"><img src="/src/assets/svg/cables-cable.svg" class="svg" alt="">Fibre optique</a>
                <hr>
                <a href="/cours/protocoles"><img src="/src/assets/svg/network.svg" class="svg" alt="">Protocoles</a>
                <a href="/cours/cisco"><img src="/src/assets/svg/trademark/cisco.svg" class="svg" alt="">Cisco</a>
                <a href="/cours/dns"><img src="/src/assets/svg/server.svg" class="svg" alt="">DNS</a>
            </div>
        </div>
    </div>

    <div tabindex="0" class="subnav">
        <div class="subnav-text">
            <img src="/src/assets/svg/school.svg" class="svg" alt="">
            <h2>Autre</h2>
        </div>
        <div class="subnav-content">
            <div>
                <a href="/cours/readme"><img src="/src/assets/svg/note.svg" class="svg">Read me</a>
                <hr>
                <a href="/cours/retraite"><img src="/src/assets/svg/old-man.svg" class="svg" alt="">Retraite</a>
                <hr>
                <a href="/cours/lm-studio"><img src="/src/assets/svg/ai-ml.svg" class="svg" alt="">LM-Studio</a>
                <a href="/cours/google-dorks"><img src="/src/assets/svg/note.svg" class="svg" alt="">Google dorks</a>
                <a href="/cours/google-doodle"><img src="/src/assets/svg/note.svg" class="svg" alt="">Google doodle</a>
                <a style="display: none;" href="/cours/github-readocdme"><img src="/src/assets/svg/trademark/github.svg" class="svg" alt="">Github README</a>
                <a style="display: none;" href=""><img src="/src/assets/svg/languages/java.svg" class="svg" alt="">Java</a>
                <a style="display: none;" href=""><img src="/src/assets/svg/languages/dotnet.svg" class="svg" alt="">.NET</a>
                <a style="display: none;" href=""><img src="/src/assets/svg/languages/php.svg" class="svg" alt="">PHP</a>
                <a style="display: none;" href=""><img src="/src/assets/svg/server.svg" class="svg" alt="">Administration</a>
                <a style="display: none;" href=""><img src="/src/assets/svg/trademark/github.svg" class="svg" alt="">Github</a>
            </div>
        </div>
    </div>

    <div tabindex="0" class="subnav">
        <div class="subnav-text">
            <img src="/src/assets/svg/link.svg" class="svg" alt="">
            <h2>Liens</h2>
        </div>
        <div class="subnav-content">
            <div class="sided">
                <a href="/outils/liens"><img src="/src/assets/svg/link.svg" class="svg" alt="">Liens utiles</a>
                <hr>
                <a href="/outils/rss"><img src="/src/assets/svg/languages/rss.svg" class="svg" alt="">RSS</a>
                <a href="/sitemap.xml"><img src="/src/assets/svg/network.svg" class="svg" alt="">Sitemap .xml</a>
                <a href="/outils/logger"><img src="/src/assets/svg/ip.svg" class="svg" alt="">IP & UserAgent</a>
                <a href="/outils/ping"><img src="/src/assets/svg/network.svg" class="svg" alt="">Ping</a>
                <a href="/outils/crashmybrowser"><img src="/src/assets/svg/languages/html.svg" class="svg" alt="">Crash my browser</a>
                <a href="https://www.calculator.net/ip-subnet-calculator"><img src="/src/assets/svg/network.svg" class="svg" alt="">IP subnet calculator</a>
                <hr>
                <a href="/outils/caesar"><img src="/src/assets/svg/password.svg" class="svg" alt="">Chiffrement César</a>
                <a href="/outils/vigenere"><img src="/src/assets/svg/password.svg" class="svg" alt="">Chiffre de Vigenère</a>
                <a href="/outils/base64"><img src="/src/assets/svg/password.svg" class="svg" alt="">Base64</a>
                <a href="/outils/rsa"><img src="/src/assets/svg/password.svg" class="svg" alt="">RSA</a>
            </div>
            <div class="sided">
                <a href="/outils/matrice"><img src="/src/assets/svg/matrix.svg" class="svg" alt="">Matrice</a>
                <a href="/outils/matrice-windows"><img src="/src/assets/svg/console.svg" class="svg" alt="">Matrice Windows</a>
                <a href="/outils/cube"><img src="/src/assets/svg/cube.svg" class="svg" alt="">3D Cube</a>
                <a href="/outils/console"><img src="/src/assets/svg/console.svg" class="svg" alt="">Console</a>
                <a href="/outils/noise"><img src="/src/assets/svg/tv2.svg" class="svg" alt="">TV-noise</a>
                <hr>
                <a href="/games/shopTitans"><img src="/src/assets/image/ShopTitans.webp" alt="">ShopTitans</a>
                <a href="/games/minesweeper"><img src="/src/assets/svg/bomb.svg" class="svg" alt="">MineSweeper</a>
                <a href="/games/cookie"><img src="/src/assets/svg/cookie.svg" alt="">Cookie clicker</a>
                <a style="display: none;" href="/games/fruits"><img src="/src/assets/svg/game.svg" class="svg" alt="">Suika game</a>
            </div>
        </div>
    </div>

    <div tabindex="0" class="subnav">
        <div class="subnav-text">
            <img src="/src/assets/svg/developpement.svg" class="svg" alt="">
            <h2>Shortcut</h2>
        </div>
        <div class="subnav-content">
            <div class="sided">
                <a href="vscode://"><img src="/src/assets/svg/link.svg" class="svg" alt="">VSCode ://</a>
                <a href="https://code.visualstudio.com/"><img src="/src/assets/svg/download.svg" class="svg" alt="">VSCode download</a>
                <a href="https://vscode.dev/"><img src="/src/assets/svg/menu.svg" class="svg" alt="">VSCode .dev</a>
                <hr>
                <a href="github-windows://"><img src="/src/assets/svg/link.svg" class="svg" alt="">Github Windows ://</a>
                <a href="https://desktop.github.com/"><img src="/src/assets/svg/download.svg" class="svg" alt="">Github Windows download</a>
                <a href="https://git-scm.com/"><img src="/src/assets/svg/trademark/git.svg" class="svg" alt="">Git</a>
            </div>
            <div class="sided">
                <a href="https://developer.mozilla.org/"><img src="/src/assets/svg/language.svg" class="svg" alt="">MSDN</a>
                <a href="https://www.svgrepo.com/"><img src="/src/assets/svg/svg.svg" class="svg" alt="">SVGRepo</a>
                <a href="https://explainshell.com/"><img src="/src/assets/svg/console.svg" class="svg" alt="">explain shell</a>
                <hr>
                <a href="/github/readme.html#Repo"><img src="/src/assets/svg/language.svg" class="svg" alt="">ReadMe#Repo</a>
                <a href="https://github.com/Altherneum/"><img src="/src/assets/svg/trademark/organisation.svg" class="svg" alt="">Github organisation</a>
                <hr>
                <a href="calculator://"><img src="/src/assets/svg/link.svg" class="svg" alt="">Calculatrice ://</a>
            </div>
        </div>
    </div>

    <div tabindex="0" class="subnav">
        <div class="subnav-text">
            <img src="/src/assets/svg/security.svg" class="svg" alt="">
            <h2>OpSec</h2>
        </div>
        <div class="subnav-content">
            <div class="sided">
                <a href="/outils/logger"><img src="/src/assets/svg/ip.svg" class="svg" alt="">IP & UserAgent</a>
                <a href="https://openvpn.net/"><img src="/src/assets/svg/network.svg" class="svg" alt="">OpenVPN</a>
                <a href="https://mullvad.net/"><img src="/src/assets/svg/network.svg" class="svg" alt="">Mullvad</a>
                <hr>
                <a href="https://keepassxc.org/"><img src="/src/assets/svg/password.svg" class="svg" alt="">KeePassXC</a>
                <a href="https://www.motdepasse.xyz/"><img src="/src/assets/svg/password.svg" class="svg" alt="">motdepasse .xyz</a>
                <hr>
                <a href="https://veracrypt.fr/"><img src="/src/assets/svg/password.svg" class="svg" alt="">VeraCrypt</a>
                <hr>
                <a href="https://www.virustotal.com/"><img src="/src/assets/svg/virus.svg" class="svg" alt="">VirusTotal</a>
                <a href="https://haveibeenpwned.com/"><img src="/src/assets/svg/help-question.svg" class="svg" alt="">HaveIBeenPwned</a>
            </div>
            <div class="sided">
                <a href="https://www.torproject.org/"><img src="/src/assets/svg/trademark/tor.svg" class="svg" alt="">Tor</a>
                <a href="https://tails.net/"><img src="/src/assets/svg/trademark/tails.svg" class="svg" alt="">Tails</a>
                <hr>
                <a href="https://gnupg.org/"><img src="/src/assets/svg/contact.svg" class="svg" alt="">GnuPG</a>
                <a href="https://pgp.mit.edu/"><img src="/src/assets/svg/verify.svg" class="svg" alt="">PGP MIT</a>
                <hr>
                <a href="https://www.thunderbird.net/"><img src="/src/assets/svg/mail.svg" class="svg" alt="">Thunderbird</a>
                <a href="https://signal.org/"><img src="/src/assets/svg/contact.svg" class="svg" alt="">Signal</a>
                <hr>
                <a href="https://www.getmonero.org/"><img src="/src/assets/svg/xmr.svg" class="svg" alt="">Monero</a>
                <hr>
                <a href="https://www.wireshark.org/"><img src="/src/assets/svg/network.svg" class="svg" alt="">Wireshark</a>
                <a href="https://npcap.com/"><img src="/src/assets/svg/network.svg" class="svg" alt="">Npcap</a>
            </div>
        </div>
    </div>

    <div tabindex="0" class="subnav">
        <div class="subnav-text">
            <img src="/src/assets/svg/administrator.svg" class="svg" alt="">
            <h2>Fondateur</h2>
        </div>
        <div class="subnav-content">
            <a href="/admin/film"><img src="/src/assets/svg/film.svg" class="svg" alt="">Film</a>
            <a href="/admin/series"><img src="/src/assets/svg/tv.svg" class="svg" alt="">Séries</a>
            <a href="/admin/animes"><img src="/src/assets/svg/draw.svg" class="svg" alt="">Animes</a>
            <a href="/admin/to-watch"><img src="/src/assets/svg/help-question.svg" class="svg" alt="">À voir</a>
            <a href="/admin/video"><img src="/src/assets/svg/tv2.svg" class="svg" alt="">Vidéos</a>
            <hr>
            <a href="/admin/lang.html" style="display: none;"><img src="/src/assets/svg/language.svg" class="svg" alt="">Techno</a>
            <a href="/admin/jeu.html" style="display: none;"><img src="/src/assets/svg/game.svg" class="svg" alt="">Jeu</a>
            <a href="/admin/music"><img src="/src/assets/svg/music.svg" class="svg" alt="">Musique</a>
            <a href="/admin/contact"><img src="/src/assets/svg/contact.svg" class="svg" alt="">Contact</a>
            <a href="/admin/donation"><img src="/src/assets/svg/donation.svg" class="svg" alt="">Donation</a>
            <a href="/admin/note"><img src="/src/assets/svg/note.svg" class="svg" alt="">Notes en cours</a>
        </div>
    </div>
</div>