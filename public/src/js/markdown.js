var isAnchorListSet = false;
var isAutoScrollLoaded = false;
var anchorList;
var isFinished = false;

async function addMarkdown(repo, file, gist) {
    console.log("Loading markdown CSS");
    await include_css("/src/css/markdown.css");
    console.log("Loading .md");

    if (isAutoScrollLoaded === false) {
        await include_script("/src/js/content/auto-scroll.js");
        isAutoScrollLoaded = true;
    }

    var markdownHolder = setMarkdownHolder();
    var content = setMarkdownFileDiv(repo, file, markdownHolder);

    await githubData(repo, file, content, gist);

    if (!isAnchorListSet) {
        anchorList = setAnchor();
        isAnchorListSet = true;
    }

    var text = await getMarkdownTextParsed(gist, content, repo, file);
    
    isFinished = true;
    
    //content.innerHTML += text;
    var textElem = document.createElement("p");
    textElem.innerHTML += text;
    content.appendChild(textElem);
    setAnchorTitles(anchorList, content);

    autoScroll(true, "center");
    console.log("Fin markdown");
}

function setMarkdownHolder() {
    var root = document.querySelector("#contentArticle");
    var markdownHolder = document.getElementById("markdown");
    if (markdownHolder === null || markdownHolder === undefined) {
        var newDiv = document.createElement("div");
        newDiv.id = "markdown";
        root.appendChild(newDiv);
        markdownHolder = document.getElementById("markdown")
    }
    return markdownHolder;
}

function setMarkdownFileDiv(repo, file, markdownHolder) {
    var content = document.createElement("div");
    content.id = repo + "/" + file;
    content.className = "markdowned";
    markdownHolder.appendChild(content);
    return content;
}

const parseMarkdown = async (text) => {
    console.log("Loading markdown parser");
    isFinished = false;
    var toHTML = text;

    const regex = /\(https:\/\/youtube\.com\/watch\?v=(.*)\)/g;
    var matched = toHTML.match(regex);
    if (matched) {
        await include_script("/src/js/content/youtubeEmbed.js");
        await include_css("/src/css/youtubeEmbed.css");

        toHTML = toHTML.replace(/([^!])\[([^\[]+)\]\((https:\/\/youtube\.com\/watch\?v=([^)]*)*)\)/g, '\n$2 - <a href=\"$3\">$4</a>\n<div class="youtubeEmbed">\n<div class="videoholder" id="video-id-$4">\n</div>\n</div>') //$3 = URL $4 = video ID, $2 = text
        for (index in matched) {
            const words = matched[index].split("v=");
            const videoID = words[1].split("&")[0].replace(")", "");

            let timer = setInterval(async function () {
                console.log("check ----------------------------" + timer);
                if (isFinished) {
                    clearInterval(timer);
                    await parseResponse(false, videoID, false, "Markdown", "https://www.youtube.com/oembed?url=https://youtube.com/watch?v=" + videoID + "&format=json", "", false, false, false, "video-id-" + videoID, "video");
                }
            }, 100);
        }
    }

    const regexShort = /\(https:\/\/youtube\.com\/shorts\/(.*)\)/g;
    var matchedShort = toHTML.match(regexShort);
    if (matchedShort) {
        await include_script("/src/js/content/youtubeEmbed.js");
        await include_css("/src/css/youtubeEmbed.css");

        toHTML = toHTML.replace(/([^!])\[([^\[]+)\]\((https:\/\/youtube\.com\/shorts\/([^)]*)*)\)/g, '\n$2 - <a href=\"$3\">$4</a>\n<div class="youtubeEmbed">\n<div class="videoholder" id="video-id-$4">\n</div>\n</div>') //$3 = URL $4 = video ID, $2 = text
        for (index in matchedShort) {
            const words = matchedShort[index].split("/");
            const videoID = words[4].replace(")", "");

            let timer = setInterval(async function () {
                console.log("check ----------------------------" + timer);
                if (isFinished) {
                    clearInterval(timer);
                    await parseResponse(false, videoID, false, "Markdown", "https://www.youtube.com/oembed?url=https://youtube.com/watch?v=" + videoID + "&format=json", "", true, false, false, "video-id-" + videoID, "video");
                }
            }, 100);
        }
    }

    toHTML = toHTML.replace(/([^!])\[([^\[]+)\]\(([^\)]+)\)/g, '$1<a href=\"$3\">$2</a>') // <a>

    toHTML = toHTML.replace(/!\[([^\[]+)\]\(([^\)]+)\)/g, '<img src=\"$2\" alt=\"$1\" />') // <img>

    toHTML = toHTML.replace(/\`{3}(.*?)\`{3}/gms, '<textarea>$1</textarea>') // <code>
    toHTML = toHTML.replace(/(?<!<(?:textarea|code)>)\`{2} (.*?) \`{2}/gm, '<code>$1</code>') // backtick inside <code>
    toHTML = toHTML.replace(/(?<!<(?:textarea|code)>)\`{1,2}(.*?)\`{1,2}/gm, '<code>$1</code>') // <code>

    let openAll=true;
    if (localStorage.getItem('OpenAllChapter') === "false") {
        openAll=false
    }

    let closeOnOpen=false;
    if (localStorage.getItem('CloseOnOpen') === "true") {
        openAll=true
    }

    if(openAll === true){
        if(closeOnOpen === true){
            toHTML = toHTML.replace(/(?<!<(?:textarea|code|!)[^>]*>[^<]*)^#{1} (.*)(([\s\S](?!^#{1} .*(?<!<(?:textarea|code|!)[^>]*>[^<]*)))*)/gim, '<details name="markdown" open>\n<summary onclick="openAndScrollToSummary(this)"><h1>$1</h1></summary>$2\n</details>\n')
            toHTML = toHTML.replace(/(?<!<(?:textarea|code|!)[^>]*>[^<]*)^#{2} (.*)(([\s\S](?!^#{2} .*(?<!<(?:textarea|code|!)[^>]*>[^<]*))(?!<\/(?:details)[^>]*>[^<]*))*)/gim, '<details name="markdown2" open>\n<summary onclick="openAndScrollToSummary(this)"><h2>$1</h2></summary>\n$2</details>\n')
        }
        else{
            toHTML = toHTML.replace(/(?<!<(?:textarea|code|!)[^>]*>[^<]*)^#{1} (.*)(([\s\S](?!^#{1} .*(?<!<(?:textarea|code|!)[^>]*>[^<]*)))*)/gim, '<details name="$1" open>\n<summary onclick="openAndScrollToSummary(this)"><h1>$1</h1></summary>$2\n</details>\n')
            toHTML = toHTML.replace(/(?<!<(?:textarea|code|!)[^>]*>[^<]*)^#{2} (.*)(([\s\S](?!^#{2} .*(?<!<(?:textarea|code|!)[^>]*>[^<]*))(?!<\/(?:details)[^>]*>[^<]*))*)/gim, '<details name="$1" open>\n<summary onclick="openAndScrollToSummary(this)"><h2>$1</h2></summary>\n$2</details>\n')
        }
    }
    else{
        if(closeOnOpen === true){
            toHTML = toHTML.replace(/(?<!<(?:textarea|code|!)[^>]*>[^<]*)^#{1} (.*)(([\s\S](?!^#{1} .*(?<!<(?:textarea|code|!)[^>]*>[^<]*)))*)/gim, '<details name="markdown">\n<summary onclick="openAndScrollToSummary(this)"><h1>$1</h1></summary>$2\n</details>\n')
            toHTML = toHTML.replace(/(?<!<(?:textarea|code|!)[^>]*>[^<]*)^#{2} (.*)(([\s\S](?!^#{2} .*(?<!<(?:textarea|code|!)[^>]*>[^<]*))(?!<\/(?:details)[^>]*>[^<]*))*)/gim, '<details name="markdown2">\n<summary onclick="openAndScrollToSummary(this)"><h2>$1</h2></summary>\n$2</details>\n')
        }
        else{
            toHTML = toHTML.replace(/(?<!<(?:textarea|code|!)[^>]*>[^<]*)^#{1} (.*)(([\s\S](?!^#{1} .*(?<!<(?:textarea|code|!)[^>]*>[^<]*)))*)/gim, '<details name="$1">\n<summary onclick="openAndScrollToSummary(this)"><h1>$1</h1></summary>$2\n</details>\n')
            toHTML = toHTML.replace(/(?<!<(?:textarea|code|!)[^>]*>[^<]*)^#{2} (.*)(([\s\S](?!^#{2} .*(?<!<(?:textarea|code|!)[^>]*>[^<]*))(?!<\/(?:details)[^>]*>[^<]*))*)/gim, '<details name="$1">\n<summary onclick="openAndScrollToSummary(this)"><h2>$1</h2></summary>\n$2</details>\n')

        }
    }

    toHTML = toHTML.replace(/(?<!.)(-{3,})(?!.)/g, '<hr/>') //hr (Decoration line)

    toHTML = toHTML.replace(/\~\~(.*?)\~\~/gim, '<del>$1</del>')// <del>
    toHTML = toHTML.replace(/\n(?:&gt;|\>)\W*(.*)/gim, '<blockquote><p>$1</p></blockquote>') // <blockquote>

    toHTML = toHTML.replace(/\_\_(.*?)\_\_/g, '<u>$1</u>') // underline
    toHTML = toHTML.replace(/(?<!<(?:textarea|code)[^>]*>[^<]*)(?<!\\)(\*{2}(.*?)\*{2})(?![^<]*<\/(?:textarea|code)>)/gim, '<b>$2</b>') //bold
    toHTML = toHTML.replace(/(?<!<(?:textarea|code)[^>]*>[^<]*)(?<!\\)(\*{1}(.*?)(?!\\)\*{1})(?![^<]*<\/(?:textarea|code)>)/gim, '<i>$2</i>') //italic

    toHTML = toHTML.replace(/(?<!<(?:textarea|code)[^>]*>[^<]*)^\*(.*$)(?![^<]*<\/(?:textarea|code)>)/gim, '<ul><li>$1</li></ul>') // <li>
    toHTML = toHTML.replace(/(?<!<(?:textarea|code)[^>]*>[^<]*)^- (.*$)(?![^<]*<\/(?:textarea|code)>)/gim, '<ul><li>$1</li></ul>') // <li>
    toHTML = toHTML.replace(/(?<!<(?:textarea|code)[^>]*>[^<]*)^ {2}- (.*$)(?![^<]*<\/(?:textarea|code)>)/gim, '<ul><li style="margin-left:12px;">$1</li></ul>') // <li>
    toHTML = toHTML.replace(/(?<!<(?:textarea|code)[^>]*>[^<]*)^ {4}- (.*$)(?![^<]*<\/(?:textarea|code)>)/gim, '<ul><li style="margin-left:24px;">$1</li></ul>') // <li>
    toHTML = toHTML.replace(/(?<!<(?:textarea|code)[^>]*>[^<]*)^ {6}- (.*$)(?![^<]*<\/(?:textarea|code)>)/gim, '<ul><li style="margin-left:36px;">$1</li></ul>') // <li>
    toHTML = toHTML.replace(/(?<!<(?:textarea|code)[^>]*>[^<]*)^ {8,}- (.*$)(?![^<]*<\/(?:textarea|code)>)/gim, '<ul><li style="margin-left:48px;">$1</li></ul>') // <li>
    toHTML = toHTML.replace(/(?<!<(?:textarea|code)[^>]*>[^<]*)(^[0-9])+\.\s*(.*$)(?![^<]*<\/(?:textarea|code)>)/gim, '<ol><li style="list-style-type: &quot;$1. &quot;;">$2</li></ol>') // <li>

    toHTML = toHTML.replace(/\\\*/g, '*') //replace /* & \* to *
    toHTML = toHTML.replace(/\\\~/g, '~') //replace /* & \* to *
    toHTML = toHTML.replace(/\\\_/g, '_') //replace /* & \* to *

    toHTML = toHTML.replace(/\[\x\]/gim, '<input type="checkbox" class="checkboxBox" checked/>')
    toHTML = toHTML.replace(/\[ \]/gim, '<input type="checkbox" class="checkboxBox"/>')

    toHTML = toHTML.replace(/(?<!<(?:textarea|code)[^>]*>[^<]*)(^#{7,} (.*))(?![^<]*<\/(?:textarea|code)>)/gim, '<h6>$2</h6>') // h7+ tag
    toHTML = toHTML.replace(/(?<!<(?:textarea|code)[^>]*>[^<]*)(^#{6} (.*))(?![^<]*<\/(?:textarea|code)>)/gim, '<h6>$2</h6>') // h6 tag
    toHTML = toHTML.replace(/(?<!<(?:textarea|code)[^>]*>[^<]*)(^#{5} (.*))(?![^<]*<\/(?:textarea|code)>)/gim, '<h5>$2</h5>') // h5 tag
    toHTML = toHTML.replace(/(?<!<(?:textarea|code)[^>]*>[^<]*)(^#{4} (.*))(?![^<]*<\/(?:textarea|code)>)/gim, '<h4>$2</h4>') // h4 tag
    toHTML = toHTML.replace(/(?<!<(?:textarea|code)[^>]*>[^<]*)(^#{3} (.*))(?![^<]*<\/(?:textarea|code)>)/gim, '<h3>$2</h3>') // h3 tag
    //toHTML = toHTML.replace(/(?<!<(?:textarea|code)[^>]*>[^<]*)(^#{2} (.*))(?![^<]*<\/(?:textarea|code)>)/gim, '<h2>$2</h2>') // h2 tag
    //toHTML = toHTML.replace(/(?<!<(?:textarea|code)[^>]*>[^<]*)(^# (.*))(?![^<]*<\/(?:textarea|code)>)/gim, '<hr style="margin-top:50px;margin-bottom:20px"><h1>$2</h1>') // h1 tag

    /*
    For the next update
        /*
        ^#{1} (.*)(?:\n|$)(([\s\S](?!^#{1} .*))*)
        */
    //this regex detect H1 and capture until next H1
    // need to update to include <code> <textarea> as ignored
    //https://regexr.com/8eo85




    //toHTML = toHTML.replace(/(?![^<]*>|[^>]*<\/)(.+)(?![^<]*>|[^>]*<\/.)/gim, '<p>$1</p>') // text p balise

    //text inside summary to do

    //toHTML = toHTML.replace(/[\n]{1,}/g, "<br>") //new line

    toHTML.trim();
    console.log("Loading return markdown trim");
    return toHTML;
}

async function getMarkdownTextParsed(gist, content, repo, file) {
    var x;
    var x2;
    if (gist === true) {
        x = await getMarkdown('https://gist.githubusercontent.com/' + repo + "/" + file + "/raw")
        x = "<h1>code</h1>\n<textarea>" + x + "</textarea>";
        x2 = x;
    } else if (gist === false) {
        x = await getMarkdown('https://raw.githubusercontent.com/' + repo + "/main/" + file);
        x2 = await parseMarkdown(x);
    }
    // console.log(".md : " + x);
    // console.log("Loading HTML wrapped .md :" + x2);

    //console.log("Loading clean code in .md");
    x2 = cleanMarkdownBR(x2);

    return x2;
}

function cleanMarkdownBR(text) {
    var regex = /<textarea>((?!<<textarea>>))*((?!<\/textarea>)[\s\S])*<\/textarea>/gim;
    var x3 = text.match(regex);
    //console.log(x3);

    for (i in x3) {
        var res = x3[i].replaceAll("<br>", "\n");
        text = text.replace(x3[i], res);
    }
    return text;
}

async function githubData(repo, file, content, gist) {
    var fileTrimed = file.replaceAll("/", "%2F");


    var holder = document.createElement("div");
    holder.id = "fileData";

    content.appendChild(holder);

    if (gist) {
        anchorButton(repo, file, gist, holder);
        anchorEdit(repo, file, gist, holder);
        anchorRaw(repo, file, holder, gist);
        anchorHolder(repo.split('/')[0], holder);
    }
    if (!gist) {
        var data = await gather("https://api.github.com/repos/" + repo + "/commits?path=" + fileTrimed + "&page=1&per_page=1");
        //console.log(data);

        var commit = getValue(data[0], "commit");
        var author = getValue(data[0], "author");
        var htmlUrl = getValue(data[0], "html_url");

        var commitAuthor = getValue(commit, "author");
        var commitMessage = getValue(commit, "message");

        var commitAuthorName = getValue(commitAuthor, "name");
        var commitAuthorDate = getValue(commitAuthor, "date");

        var authorLogin = getValue(author, "login");
        anchorRepo(repo, holder);
        anchorButton(repo, file, gist, holder);
        anchorEdit(repo, file, gist, holder);
        anchorRaw(repo, file, holder, gist);
        anchorHolder(repo.split('/')[0], holder);
        anchorUpdate(htmlUrl, holder);
        anchorAuthor(authorLogin, holder, commitAuthorName);
        textData(commitMessage, commitAuthorDate, holder);
    }
}

function textData(commitMessage, commitAuthorDate, holder) {
    var commitDiv = document.createElement("div");
    commitDiv.className = "commit";

    if (commitMessage !== undefined) {
        var updateMessage = document.createElement("p");
        updateMessage.textContent = commitMessage;
        commitDiv.appendChild(updateMessage);
    }

    if (commitAuthorDate !== undefined) {
        const date1 = new Date(commitAuthorDate);
        var date2 = date1.toLocaleString();
        var updateDate = document.createElement("p");
        updateDate.textContent = date2;
        commitDiv.appendChild(updateDate);
    }

    holder.appendChild(commitDiv);
}

function anchorUpdate(url, holder) {
    if (url === undefined) {
        return;
    }
    var link = document.createElement("a");
    var button = document.createElement("button");
    var image = document.createElement("img");
    var text = document.createElement("p");
    link.href = url;
    image.src = "/assets/svg/update.svg";
    button.classList = "edit-gist"
    text.textContent = "Last commit";
    link.target = "_blank";
    image.classList = "svg";
    button.appendChild(image);
    button.appendChild(text);
    link.appendChild(button);
    holder.appendChild(link);
}

function anchorAuthor(authorLogin, holder, commitAuthorName) {
    if (authorLogin === undefined) {
        return;
    }
    var link = document.createElement("a");
    var button = document.createElement("button");
    var image = document.createElement("img");
    var text = document.createElement("p");
    link.href = "https://github.com/" + authorLogin;
    image.src = "/assets/svg/writer-write-blogger-work-at-desk.svg";
    button.classList = "edit-gist"

    if (authorLogin !== commitAuthorName) {
        text.textContent = "committer : " + authorLogin + " (" + commitAuthorName + ")";
    } else {
        text.textContent = "committer : " + authorLogin;
    }

    link.target = "_blank";
    image.classList = "svg";
    button.appendChild(image);
    button.appendChild(text);
    link.appendChild(button);
    holder.appendChild(link);
}

function anchorRaw(repo, file, holder, gist) {
    var link = document.createElement("a");
    var button = document.createElement("button");
    var image = document.createElement("img");
    var text = document.createElement("p");
    if (gist === true) {
        link.href = "https://gist.github.com/" + repo + "/" + file + "/raw";
    }
    else if (gist === false) {
        link.href = "https://raw.githubusercontent.com/" + repo + "/main/" + file;
    }
    image.src = "/assets/svg/language.svg";
    button.classList = "edit-gist"
    text.textContent = "RAW";
    link.target = "_blank";
    image.classList = "svg";
    button.appendChild(image);
    button.appendChild(text);
    link.appendChild(button);
    holder.appendChild(link);
}

function anchorRepo(repo, holder) {
    var link = document.createElement("a");
    var button = document.createElement("button");
    var image = document.createElement("img");
    var text = document.createElement("p");
    link.href = "https://github.com/" + repo;
    image.src = "/assets/svg/trademark/repo.svg";
    button.classList = "edit-gist"
    text.textContent = repo.split("/")[1];
    link.target = "_blank";
    image.classList = "svg";
    button.appendChild(image);
    button.appendChild(text);
    link.appendChild(button);
    holder.appendChild(link);
}

function anchorHolder(user, holder) {
    var link = document.createElement("a");
    var button = document.createElement("button");
    var image = document.createElement("img");
    var text = document.createElement("p");

    link.href = "https://github.com/" + user;
    image.src = "/assets/svg/trademark/organisation.svg";
    button.classList = "edit-gist"
    text.textContent = "@ " + user;
    link.target = "_blank";
    image.classList = "svg";
    button.appendChild(image);
    button.appendChild(text);
    link.appendChild(button);
    holder.appendChild(link);
}

async function getMarkdown(url) {
    console.log("Loading download markdown");
    var data = (await fetch(url)).text();
    return data;
}

function anchorButton(repo, file, gist, holder) {
    var link = document.createElement("a");
    var button = document.createElement("button");
    var image = document.createElement("img");
    var text = document.createElement("p");
    if (gist === true) {
        link.href = "https://gist.github.com/" + repo + "/" + file;
        button.classList = "edit-gist"
    }
    else if (gist === false) {
        link.href = "https://github.com/" + repo + "/blob/main/" + file;
        button.classList = "edit-md"
    }
    image.src = "/assets/svg/trademark/github.svg";
    text.textContent = file.split('/').pop();
    link.target = "_blank";
    image.classList = "svg";
    button.appendChild(image);
    button.appendChild(text);
    link.appendChild(button);
    holder.appendChild(link);
}

function anchorEdit(repo, file, gist, holder) {
    var link = document.createElement("a");
    var button = document.createElement("button");
    var image = document.createElement("img");
    var text = document.createElement("p");
    if (gist === true) {
        link.href = "https://gist.github.com/" + repo + "/" + file + "/edit";
        button.classList = "edit-gist"
    }
    else if (gist === false) {
        link.href = "https://github.com/" + repo + "/edit/main/" + file;
        button.classList = "edit-md"
    }
    image.src = "/assets/svg/edit.svg";
    text.textContent = "Modifier";
    link.target = "_blank";
    image.classList = "svg";
    button.appendChild(image);
    button.appendChild(text);
    link.appendChild(button);
    holder.appendChild(link);
}

function setAnchorButton(id, asset) {
    var content = document.querySelector(id);

    var button = document.createElement("button");
    button.id = "SummaryAnchor";
    var image = document.createElement("img");

    button.onclick = function () {
        var anchorList = document.getElementById("anchorList");
        var contentLeft = document.getElementById("content-left");

        if (contentLeft.style.display === "block" || anchorList.style.display === "grid"){
            anchorList.style.display = "none";
            contentLeft.style.display = "none";
        }
        else if (contentLeft.style.display === "none" || anchorList.style.display === "none") {
            anchorList.style.display = "grid";
            contentLeft.style.display = "block";
        }
    }

    image.classList = "svg";
    image.src = asset;

    button.appendChild(image);
    content.appendChild(button);
}

function setAnchor() {
    setAnchorButton("#anchor-button", "/assets/svg/book.svg");
    var anchorList = document.createElement("div");
    anchorList.id = "anchorList";

    var contentLeft = document.getElementById("content-left");

    if (localStorage.getItem('ShowSummary') === "true") {
        anchorList.style.display = "none";
        contentLeft.style.display = "none";
    }
    else{
        anchorList.style.display = "grid";
        contentLeft.style.display = "block";
    }

    contentLeft.appendChild(anchorList);

    var divAnchorTitle = document.createElement("div");
    divAnchorTitle.id = "AnchorTitle";

    var anchorTitleLogo = document.createElement("img");
    anchorTitleLogo.src = "/assets/svg/book.svg";
    anchorTitleLogo.classList = "svg";
    anchorTitleLogo.style = "max-width: 25px; transform: translateY(25%); padding-right: 15px; padding-left: 10px;"
    divAnchorTitle.appendChild(anchorTitleLogo);

    var anchorTitle = document.createElement("b");
    anchorTitle.textContent = "Sommaire"
    divAnchorTitle.appendChild(anchorTitle);

    var AnchorSummary = document.createElement("div");
    AnchorSummary.id = "AnchorSummary";

    addChapterHidder(AnchorSummary, "summary-h2", "Titre #2");
    addChapterHidder(AnchorSummary, "summary-hidder", "Titre #3+");

    anchorList.appendChild(divAnchorTitle);
    anchorList.appendChild(AnchorSummary);
    setAnchorButton("#AnchorSummary", "/assets/svg/close.svg");

    return anchorList;
}

function addChapterHidder(AnchorSummaryElement, ElementClassName, Title) {
    var check = document.createElement("div");
    check.classList = "check";
    check.style = "padding:10px;";
    var checkbox = document.createElement("div");
    checkbox.classList = "checkboxToggle invert";
    var input = document.createElement("input");
    input.type = "checkbox";

    var hideSummary = false;
    if (localStorage.getItem('Titre2') === "true" && ElementClassName === "summary-h2") {
        hideSummary = true;
    }
    else if (localStorage.getItem('Titre3Plus') === "true" && ElementClassName === "summary-hidder") {
        hideSummary = true;
    }
    input.checked = hideSummary; //color is inverted    
    updateSummarySettings(ElementClassName, input);

    input.onclick = () => {
        updateSummarySettings(ElementClassName, input);
    };

    var textCheck = document.createElement("p");
    textCheck.textContent = Title;
    textCheck.style = "padding:10px;";
    var span = document.createElement("span");

    check.appendChild(checkbox);
    check.appendChild(textCheck);
    checkbox.appendChild(input);
    checkbox.appendChild(span);
    AnchorSummaryElement.appendChild(check);
}

function updateSummarySettings(ElementClassName, input){
    //get H2 or H3+
    var list = document.getElementById('anchorList').getElementsByClassName(ElementClassName);

    //update each link on the summary
    for (i = 0; i < list.length; i++) {
        var childDiv = list[i];
        if (!input.checked) {
            childDiv.style.display = "block";
        } else {
            childDiv.style.display = "none";
        }
    }

    //update the settings
    if(ElementClassName === "summary-h2"){
        localStorage.setItem("Titre2", input.checked);
    }
    else if(ElementClassName === "summary-hidder"){
        localStorage.setItem("Titre3Plus", input.checked);
    }
}

function setAnchorTitles(anchorListElement, text) {
    console.log(text);
    //var childDivs = document.getElementById('markdown').querySelectorAll("h1, h2, h3, h4, h5, h6");
    var childDivs = text.querySelectorAll("h1, h2, h3, h4, h5, h6");
    for (i = 0; i < childDivs.length; i++) {
        var childDiv = childDivs[i];
        var textPre = childDiv.textContent;
        var text = textPre.replaceAll(" ", "-");
        var text = text.replaceAll("'", "");
        var text = text.replaceAll(":", "");
        var text = text.replaceAll("@", "");
        var text = text.replaceAll("(", "");
        var text = text.replaceAll(")", "");
        var anchor = document.createElement("a");
        anchor.href = "#" + text;
        anchor.id = text;
        anchor.className = childDivs[i].tagName;
        childDiv.prepend(anchor);

        var anchorOnList = document.createElement("a");
        anchorOnList.href = "#" + text;
        /*

        under condition H1/H2... the localstorage to set if block or none display

        */
        if (childDivs[i].tagName.toLocaleLowerCase() === "h1") {
            anchorOnList.textContent = "#1 " + textPre;
            anchor.textContent = "# ";
            anchorOnList.style = "margin-left:5px;font-size: large;";
            anchorOnList.className = "summary-h1";
        }
        else if (childDivs[i].tagName.toLocaleLowerCase() === "h2") {
            anchorOnList.textContent = "#2 " + textPre;
            anchor.textContent = "## ";
            anchorOnList.style = "margin-left:10px;text-decoration:none;font-size: medium;";
            anchorOnList.className = "summary-h2";
            if(localStorage.getItem("Titre2") === "true"){
                anchorOnList.style.display = "none";
            }
        }
        else if (childDivs[i].tagName.toLocaleLowerCase() === "h3") {
            anchorOnList.textContent = "#3 " + textPre;
            anchor.textContent = "### ";
            anchorOnList.style = "margin-left:15px;text-decoration:none;font-size: small;";
            anchorOnList.className = "summary-hidder";
            if(localStorage.getItem("Titre3Plus") === "true"){
                anchorOnList.style.display = "none";
            }
        }
        else if (childDivs[i].tagName.toLocaleLowerCase() === "h4") {
            anchorOnList.textContent = "#4 " + textPre;
            anchor.textContent = "#### ";
            anchorOnList.style = "margin-left:20px;text-decoration:none;font-size: x-small;";
            anchorOnList.className = "summary-hidder";
            if(localStorage.getItem("Titre3Plus") === "true"){
                anchorOnList.style.display = "none";
            }
        }
        else if (childDivs[i].tagName.toLocaleLowerCase() === "h5") {
            anchorOnList.textContent = "#5 " + textPre;
            anchor.textContent = "##### ";
            anchorOnList.style = "margin-left:25px;text-decoration:none;font-size: x-small;";
            anchorOnList.className = "summary-hidder";
            if(localStorage.getItem("Titre3Plus") === "true"){
                anchorOnList.style.display = "none";
            }
        }
        else if (childDivs[i].tagName.toLocaleLowerCase() === "h6") {
            anchorOnList.textContent = "#6 " + textPre;
            anchor.textContent = "###### ";
            anchorOnList.style = "margin-left:30px;text-decoration:none;font-size: x-small;";
            anchorOnList.className = "summary-hidder";
            if(localStorage.getItem("Titre3Plus") === "true"){
                anchorOnList.style.display = "none";
            }
        }
        else {
            anchorOnList.textContent = "#7+ " + textPre;
            anchor.textContent = "#7+";
            anchorOnList.style = "margin-left:35px;text-decoration:none;font-size: xx-small;";
            anchorOnList.className = "summary-hidder";
            if(localStorage.getItem("Titre3Plus") === "true"){
                anchorOnList.style.display = "none";
            }
        }

        setScrollBehavior(anchorOnList, "center");
        anchorListElement.appendChild(anchorOnList);
    }
}

function openAndScrollToSummary(element) {
    var childs = element.children;
    setTimeout(() => {
        DoScrollIntoView(childs[0], false, "center");
    }, 100);

    var hrefChild = childs[0].children;
    var URL = hrefChild[0].href;

    //window.location.hash = URL;
    history.pushState(null, null, URL);
}
