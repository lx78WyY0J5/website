<footer>
    <link rel="stylesheet" href="/src/includes/footer/footer.css">

    <div>
        <p>Site fait avec <img src="/src/assets/svg/heart-color.svg" class="svg-color"></img> par <a href="/admin/contact.html">lx78WyY0J5</a></p>
    </div>

    <hr>

    <nav class="footer-logolist">
        <a href="/"><img src="/src/assets/svg/home.svg" class="svg" alt=""></a>
        <a href="/discord.html"><img src="/src/assets/svg/trademark/discord.svg" class="svg" alt=""></a>
        <a href="mailto:contact@altherneum.fr"><img src="/src/assets/svg/mail.svg" class="svg" alt=""></a>
        <a href="/admin/contact.html"><img src="/src/assets/svg/administrator.svg" class="svg" alt=""></a>
        <a href="https://github.com/Altherneum"><img src="/src/assets/svg/trademark/github.svg" class="svg" alt=""></a>
        <a href="https://Play.Altherneum.fr"><img src="/src/assets/svg/trademark/minecraft.svg" class="svg" alt=""></a>
    </nav>
    <nav class="footer-logolist">
        <a href="https://instagram.com/Altherneum"><img src="/src/assets/svg/trademark/instagram.svg" class="svg" alt=""></a>
        <a href="https://youtube.com/@Altherneum"><img src="/src/assets/svg/trademark/youtube.svg" class="svg" alt=""></a>
        <a href="https://Twitter.com/Altherneum"><img src="/src/assets/svg/trademark/twitter.svg" class="svg" alt=""></a>
        <a href="https://Twitch.com/Altherneum"><img src="/src/assets/svg/trademark/twitch.svg" class="svg" alt=""></a>
        <a href="https://steamcommunity.com/id/Altherneum/"><img src="/src/assets/svg/trademark/steam.svg" class="svg" alt=""></a>
    </nav>

    <?php
        if($_SESSION["is_admin"] === true){
            echo '<div>';

                echo '<hr>';

                echo '<nav class="footer-logolist">';
                    echo '<a href="https://code.altherneum.fr/"><img src="/src/assets/svg/trademark/vscode.svg" class="svg" alt=""></a>';
                    echo '<a href="https://git.altherneum.fr/"><img src="/src/assets/svg/trademark/git.svg" class="svg" alt=""></a>';
                    echo '<a href="https://keeweb.altherneum.fr/"><img src="/src/assets/svg/password.svg" class="svg" alt=""></a>';
                echo '</nav>';

                echo '<hr>';

                echo '<nav>';
                    echo '<a href="http://127.0.0.1:3000/">127.0.0.1:3000</a><p>|</p>';
                    echo '<a href="http://localhost:8000/">localhost:8000</a><p>|</p>';
                    echo '<a href="https://3000.code.altherneum.fr/">3000.code.altherneum.fr</a><p>|</p>';
                    echo '<a href="https://Altherneum.github.io">Github.io</a><p>|</p>';
                    echo '<a href="https://doc.Altherneum.fr">doc.altherneum.fr</a>';
                echo '</nav>';

                echo '<hr>';

                echo '<nav>';
                    echo '<a href="" id="offline-url">127.0.0.1:3000/..</a><p>|</p>';
                    echo '<a href="" id="php-url">localhost:8000/..</a><p>|</p>';
                    echo '<a href="" id="mixed-url">3000.code.altherneum.fr/..</a><p>|</p>';
                    echo '<a href="" id="github-url">Github.io/..</a><p>|</p>';
                    echo '<a href="" id="online-url">doc.altherneum.fr/..</a>';
                echo '</nav>';
            echo '</div>';
            echo '<script src="/src/includes/footer/footer.js"></script>';
        }
    ?>

</footer>
<a id="footer"></a>