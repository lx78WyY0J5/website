(async function () {
    await getUpdate("Altherneum.github.io");
})();


async function getUpdate(repoName) {
    console.log("Getting Github-Update");

    var div = document.getElementById("github-update");
    var title = div.querySelector("#title");
    title.textContent += repoName;

    var x = await gather('https://api.github.com/repos/Altherneum/' + repoName + '/commits');
    var y = getValue(x[0], "sha");
    if (String(y).length >= 8) {
        var y2 = String(y.substring(0, 12) + "...");
    }

    document.getElementById("hash").href = 'https://github.com/Altherneum/' + repoName + '/commit/' +y;
    document.getElementById("hash").textContent = "📊 Hash : " + y2;

    var z = getValue(x[0], "commit")
    var z2 = getValue(z, "author");

    var name = getValue(z2, "name");
    document.getElementById("author").href = "https://github.com/" + name;
    document.getElementById("author").textContent = "👤 Par : " + name;

    var date = getValue(z2, "date");
    const date1 = new Date(Date.parse(date));
    var date2 = date1.toLocaleString();
    document.getElementById("date-github").textContent = "⏰ " + date2;

    var message = getValue(z, "message")
    document.getElementById("message").textContent = "💬 " + message;

    var gatherAvatar = getValue(x[0], "author");
    var avatarGatherValue = getValue(gatherAvatar, "avatar_url");

    document.getElementById("author-image").src = avatarGatherValue;

    var deployment = await gather('https://api.github.com/repos/Altherneum/' + repoName + '/deployments');
    var deploymentID = await getValue(deployment[0], "id");

    var deploymentStatus = await gather('https://api.github.com/repos/Altherneum/' + repoName + '/deployments/' + deploymentID + '/statuses');
    var deploymentStatusState = await getValue(deploymentStatus[0], "state");
    var deploymentStatusUrl = await getValue(deploymentStatus[0], "log_url");

    document.getElementById("status").href = deploymentStatusUrl;
    document.getElementById("status").textContent = "⭕ Build : " + deploymentStatusState;
}