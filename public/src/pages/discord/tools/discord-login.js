
const fragment = new URLSearchParams(window.location.hash.slice(1));
const [accessToken, tokenType] = [fragment.get('access_token'), fragment.get('token_type')];

if (!accessToken) {
    window.location.assign('/')
}

fetch('https://discord.com/api/users/@me', {
    headers: {
        authorization: `${tokenType} ${accessToken}`,
    },
})
    .then(result => result.json())
    .then(response => {
        console.log(response);
        const { username, discriminator, avatar, id, mfa_enabled, locale, premium_type } = response;

        var name = document.createElement("p");
        name.id = "name";
        name.className = "discord";
        name.textContent = username + " #" + discriminator;
        document.querySelector("#contentArticle").appendChild(name);

        var idText = document.createElement("p");
        idText.id = "id";
        idText.className = "discord";
        idText.textContent = id + " : " + locale + " : MFA;" + mfa_enabled + " : Premium;" + premium_type;
        document.querySelector("#contentArticle").appendChild(idText);

        var image = document.createElement("img");
        image.id = "avatar";
        image.className = "discord";
        image.src = "https://cdn.discordapp.com/avatars/" + id + "/" + avatar + ".webp?size=1024";
        document.querySelector("#contentArticle").appendChild(image);
    })
    .catch(console.error); 