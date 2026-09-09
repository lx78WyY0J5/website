const resultsBox = document.getElementById("result-box");
resultsBox.style.display = "none"
var showMore = false;

var lastResult = [];

function keyup(event, inputBoxParam) {
    var code = event.charCode || event.keyCode;

    if (code == 27) {
        inputBoxParam.value = '';
    }

    const inputBox = document.getElementById("input-box");
    let input = inputBox.value;
    getResult(input);
}

async function getResult(query) {
    if (query.length > 0) {
        var result = checkIfInputMatchLink(query).then(result => {

            console.log("SearchBar : " + query + " : " + result.length + " resultat !");

            var needToShowMore = false;
            if (result.length > 6 && showMore === false) {
                result = result.slice(0, 6);
                needToShowMore = true;
            }

            if (!compareArrays(result, lastResult) || showMore === true) {
                if (showMore === true) {
                    showMore = false;
                }

                showSearchbarResult(result);

                if (needToShowMore) {
                    AddShowMoreButton(query);
                }
            }

            hideTips();

            lastResult = result;
        });
    }
    else {
        console.log("SearchBar vide");
        clearAllSearchBar();
        showTips();
    }
}

function AddShowMoreButton(PrevQuery) {
    var resultBox = document.getElementById("result-box");
    var ShowMore = document.createElement("div");
    ShowMore.id = "showMore";
    ShowMore.className = "showMoreElem";

    var showMoreLink = document.createElement("a");
    showMoreLink.tabIndex = 0;
    showMoreLink.textContent = "Plus de résultat";
    showMoreLink.className = "showMoreElem";
    showMoreLink.href = ""; //permet de rendre le lien cliquable
    showMoreLink.addEventListener('click', function (e) {
        e.preventDefault();
        showMore = true;
        let query = document.getElementById("input-box").value;
        getResult(query);
    });

    var showMoreImage = document.createElement("img");
    showMoreImage.src = "/src/assets/svg/magnifier-plus.svg";
    showMoreImage.className = "svg showMoreElem";

    ShowMore.appendChild(showMoreLink);
    showMoreLink.appendChild(showMoreImage);
    resultBox.appendChild(ShowMore);
}

async function checkIfInputMatchLink(query) {
    let result = [];
    var queryListed = query.split(" ");
    var counter = 0;

    for (link in links) {
        let resultTemp = compareLinks(link, queryListed);
        if (resultTemp !== null) {
            result[counter] = resultTemp;
            counter++;
        }
    }

    return result;
}

function mergeYouTubeTitles(musicListMerge, song, video, film){
    for (link in musicListMerge) {
        let linked = musicListMerge[link];
        let htmlOffline = "";
        
        var linkText = "";
        if(linked.text !== undefined){
            linkText = linked.text;
        }

        let url;
        let icon;
        if(song === true){
            url = "music";
            icon = "youtube-music"
        }
        else if(video === true){
            url = "video";
            icon = "youtube";
        }else if(film === true){
            if(linked.category != undefined) {
                url = linked.category;
            }
            icon = "film"
        }

        if(linked.title !== undefined && (song === true || video === true)){
            object = {
                href: "/admin/" + url + htmlOffline + "#" + linked.videoID,
                tag: linked.category,
                title: linked.title,
                text: linkText,
                svg: "/assets/svg/trademark/" + icon + ".svg",
            };

            links.push(object);
        }
        else if (linked.title !== undefined && film === true){
            object = {
                href: "/admin/" + url + htmlOffline + "#" + linked.filmID,
                tag: linked.category,
                title: linked.title,
                svg: "/assets/svg/" + icon + ".svg",
            };

            links.push(object);
        }
    }
}

function compareLinks(linkID, queryListed) {
    var linkTitle = links[linkID].title;
    var linkText = "";
    if(links[linkID].text !== undefined){
        linkText = links[linkID].text;
    }
    var linkHref = links[linkID].href;
    var linkHrefTrim = links[linkID].href.replace(".html", "");
    var linkHrefTrimBis = links[linkID].href.replace("https://", "");
    var linkTag = links[linkID].tag;
    var linkSVG = links[linkID].svg;

    let matchAll = true;

    for (singleQuery in queryListed) {
        let match = compareStringToLink(queryListed[singleQuery], linkTitle, linkText, linkHrefTrimBis, linkTag, singleQuery);
        if (!match) {
            matchAll = false;
        }
    }

    if (matchAll) {
        if(linkTag.toLowerCase().includes("queryable")){
            return { "href": linkHrefTrim, "svg": linkSVG, "tag": linkTag, "title": linkTitle, "text": linkText, "query": queryListed.slice(1), "queryURL": links[linkID].queryURL};
        }

        return { "href": linkHrefTrim, "svg": linkSVG, "tag": linkTag, "title": linkTitle, "text": linkText };
    }
    return null;
}

function compareStringToLink(input, linkTitle, linkText, linkHrefTrim, linkTag, queryNumber) {
    let lowerCaseQuery = input.toLowerCase();

    if (linkTitle.toLowerCase().includes(lowerCaseQuery) 
        || linkHrefTrim.toLowerCase().includes(lowerCaseQuery) 
        || linkTag.toLowerCase().includes(lowerCaseQuery) 
        || linkText.toLowerCase().includes(lowerCaseQuery) 
        || (linkTag.toLowerCase().includes("queryable") && queryNumber >= 1)) {
        return true;
    }
    else {
        return false;
    }
}

function showTips() {
    if (!isTipsOpen()) {
        addSearchTips();
    }
    var tips = document.getElementById("search-tips");
    tips.style.display = "flex";
}

function hideTips() {
    var tips = document.getElementById("search-tips");
    tips.style.display = "none";
}

function isTipsOpen() {
    const tipsBox = document.getElementById("search-tips");

    if (tipsBox === null) { return false; }

    if ((tipsBox.style.display !== "none")) {
        return true;
    }
    return false;
}

const compareArrays = (a, b) => {
    return JSON.stringify(a) === JSON.stringify(b);
};

function showSearchbarResult(result) {
    const resultsBox = document.getElementById("result-box");
    clearSearchBarResultHTML();
    if (result.length) {
        for (i in result) {
            //check if result
            var query = "";
            if(result[i].query != undefined && result[i].query.length > 0 && result[i].query[0] != ""){
                // we have a query
                var queryText = "";
                for(textqueryIndex in result[i].query){
                    queryText += result[i].query[textqueryIndex] + " ";
                }
                query = '<p>Rechercher : </p><a target="_blank" href="' + result[i].queryURL + queryText + '">' + queryText + '</a>';
                console.info(queryText);
            }

            const href = result[i].href;
            const title = result[i].title;
            const text = result[i].text;
            let svg;
            if (result[i].svg === undefined) {
                svg = "/assets/svg/link.svg";
            }
            else if (result[i].svg === "/assets/svg/.svg") {
                svg = "/assets/svg/help-question.svg";
            }
            else { svg = result[i].svg; }

            resultsBox.innerHTML += '<div><a href="' + href + '"><img src="' + svg + '" class="svg">' + title + '<p>' + text + '</p></a>' + query + '</div>'
        }
        resultsBox.style.display = "flex";
    } else {
        resultsBox.style.display = "none";
    }
}

function clearAllSearchBar() {
    clearSearchBarResult();
    clearInputSearchBar();
    clearSearchBarResultHTML();
    clearLastResult();
}

function clearLastResult() {
    lastResult = [];
}

function clearSearchBarResult() {
    const resultsBox = document.getElementById("result-box");
    resultsBox.style.display = "none"
}

function clearSearchBarResultHTML() {
    const resultsBox = document.getElementById("result-box");
    resultsBox.innerHTML = '';
}

function clearInputSearchBar() {
    const inputBox = document.getElementById("input-box");
    inputBox.value = '';
}

document.body.addEventListener('keydown', function (e) {
    if (e.key == "Escape") {
        closeSearchBar();
    }
});

window.addEventListener('click', function (e) {
    if (e.target.classList.contains("showMoreElem")) {
        return;
    }

    if (!document.getElementById('searchbar').contains(e.target)) {
        closeSearchBar();
        return;
    }

    if (document.getElementById('input-box').contains(e.target)) {
        if(document.getElementById("result-box").style.display === "none"){
            showTips();
        }
        return;
    }
});

function isSearchBarOpend() {
    const inputBox = document.getElementById("input-box");
    const resultsBox = document.getElementById("result-box");

    if (inputBox === null) { return false; }
    if (resultsBox === null) { return false; }

    if ((resultsBox.style.display !== "none") || (inputBox.value !== '')) {
        return true;
    }
    return false;
}

function closeSearchBar() {
    if (isSearchBarOpend()) {
        clearAllSearchBar();
    }

    hideTips();

    var inputBox = document.getElementById("input-box");
    inputBox.blur();
}

//Target Search bar with shortcut Shift+F
let keysPressed = {};

document.addEventListener('keydown', (event) => {
    keysPressed[event.key] = true;
});

document.addEventListener('keyup', (event) => {
    keysPressed[event.key] = '';
    if (event.key == "Tab") {
        if (!document.getElementById("searchbar").contains(event.target)) {
            hideTips();
            clearAllSearchBar();
            console.log(event.target);
        }
    }
});

document.body.addEventListener('keydown', function (e) {
    keysPressed[e.key] = true;
    if ((keysPressed["Shift"] && e.key == "F") || (keysPressed["Shift"] && e.key == "f")) {
        var inputBox = document.getElementById("input-box");
        if (inputBox !== null) {
            if (!inputBox.contains(e.target)) {
                e.preventDefault();
                inputBox.focus();
            }
        }
    }
});

function addSearchTips() {
    var tips = document.getElementById("search-tips-holder");
    tips.innerHTML = '';
    var tipsList = getTagList(3);
    for (tipsText in tipsList) {
        var a = document.createElement("a");

        var tip = tipsList[tipsText]
        a.textContent = tip;
        a.tabIndex = 0;

        a.setAttribute("onClick", "setURLParameterClick(\"" + tip + "\", " + true + ", " + true + ", " + true + ")");
        a.setAttribute("onkeypress", "setURLParameterClick(\"" + tip + "\", " + true + ", " + true + ", " + true + ")");

        tips.appendChild(a);
    }
}

function setURLParameterClick(value, runGetAfter, setSearchInput, doHideTips) {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const paramName = "search";

    urlParams.set(paramName, value);
    console.warn("set ; " + paramName + " = " + value);

    if (runGetAfter) {
        getResult(value);
    }

    if (setSearchInput) {
        const inputBox = document.getElementById("input-box");
        inputBox.value = value;
    }

    if (doHideTips) {
        hideTips();
    }
}

function getURLParameter() {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const paramName = "search";
    if (urlParams.has(paramName)) {
        const searchQuery = urlParams.get(paramName);
        console.warn("found ; " + paramName + " = " + searchQuery);

        getResult(searchQuery);

        const inputBox = document.getElementById("input-box");
        inputBox.value = searchQuery;
        document.getElementById("input-box").setAttribute('value', searchQuery);
    }
}