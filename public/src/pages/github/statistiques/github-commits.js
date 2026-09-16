(async function () {
    await getCommits(3, ".github");
    await getCommits(3, "bot");
    await getCommits(3, "Altherneum.github.io");
    await getCommits(3, "server");
    await getCommits(3, "plugin");
    await getCommits(3, "resourcePack");
})();


async function getCommits(amount, repoName) {
    console.log("Getting Github-Commits : " + repoName);

    var x = await gather('https://api.github.com/repos/Altherneum/' + repoName + '/commits');
    for (let i = 0; i < amount; i++) {
        var y = getValue(x[i], "sha");
        if (y !== undefined){
        if (String(y).length >= 8) {
            var y2 = String(y.substring(0, 12) + "...");
        }
        var z = getValue(x[i], "commit")
        var z2 = getValue(z, "author");
        var name = getValue(z2, "name");

        var date = getValue(z2, "date");
        const date1 = new Date(Date.parse(date));
        var date2 = date1.toLocaleString();

        var message = getValue(z, "message");

        var gatherAvatar = getValue(x[i], "author");
        var avatarGatherValue = getValue(gatherAvatar, "avatar_url");

        var div = document.getElementById("github-commits-"+repoName); 

        var newDiv = document.createElement("div");
        var image = document.createElement("img");
        image.src = avatarGatherValue;
        var user = document.createElement("p");
        user.textContent = name;
        var TextMessage = document.createElement("p");
        TextMessage.textContent = message;
        var TextDate = document.createElement("p");
        TextDate.textContent = date2;
        var link = document.createElement("a");
        link.href = "https://github.com/Altherneum/" + repoName + "/commit/" + y;
        var sha = document.createElement("p");
        sha.textContent = y2;
        var logo = document.createElement("img");
        logo.src = "/assets/svg/link.svg";
        logo.classList = "svg";

        newDiv.appendChild(image);
        newDiv.appendChild(user);
        newDiv.appendChild(TextMessage);
        newDiv.appendChild(TextDate);
        newDiv.appendChild(sha);
        link.appendChild(logo);
        newDiv.appendChild(link);

        div.appendChild(newDiv);
        }
    }
    var title = div.querySelector("#title");
    title.textContent = repoName;
}