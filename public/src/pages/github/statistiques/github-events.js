(async function () {
    await getEvents(5, "Altherneum");
})();


async function getEvents(amount, orgsName) {
    console.log("Getting Github-PushEvent");

    var x = await gather('https://api.github.com/orgs/' + orgsName + '/events');
    var iResult = 0;
    for (var i in x) {
        var eventType = getValue(x[i], "type");
        if (eventType === "PushEvent") {
            var actor = getValue(x[i], "actor");
            var login = getValue(actor, "login");
            var avatar = getValue(actor, "avatar_url");

            var repo = getValue(x[i], "repo");
            var name = getValue(repo, "name");

            var payload = getValue(x[i], "payload");
            var commits = getValue(payload, "commits");

            var date = getValue(x[i], "created_at");

            var div = document.getElementById("github-events");
            var newDiv = document.createElement("div");

            var image = document.createElement("img");
            image.src = avatar;
            var user = document.createElement("p");
            user.textContent = login;
            var RepoName = document.createElement("p");
            var name2 = name.split("/")[1];
            RepoName.textContent = name2;

            var TextDate = document.createElement("p");
            const date1 = new Date(Date.parse(date));
            var date2 = date1.toLocaleString();
            TextDate.textContent = date2;

            newDiv.appendChild(image);
            newDiv.appendChild(user);
            newDiv.appendChild(RepoName);
            newDiv.appendChild(TextDate);
            for (var i2 in commits) {
                var message = getValue(commits[i2], "message");
                var TextMessage = document.createElement("p");
                TextMessage.textContent = message;
                newDiv.appendChild(TextMessage);

                var sha = getValue(commits[i2], "sha");
                var link = document.createElement("a");
                link.href = "https://github.com/" + name + "/commit/" + sha;
                var logo = document.createElement("img");
                logo.src = "/assets/svg/link.svg";
                logo.classList = "svg";
                link.appendChild(logo);
                newDiv.appendChild(link);
            }

            var link = document.createElement("a");
            link.href = "https://github.com/" + name;
            var logo = document.createElement("img");
            logo.src = "/assets/svg/trademark/repo.svg";
            logo.classList = "svg";

            link.appendChild(logo);
            newDiv.appendChild(link);
            div.appendChild(newDiv);

            iResult++;
            if (iResult >= amount) {
                break;
            }
        }
    }
} 