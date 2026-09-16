// https://developers.google.com/youtube/player_parameters
var total = 0;
let GlobalVideoIDList;

async function loadYouTubeEmbed() {
    checkURL();
    await setVideoScreenLocking();
    createPlayListList();
    GlobalVideoIDList = GetVideoList();
    GetVideos(shuffle(GlobalVideoIDList), getVideoListType(), getType(), true);
}

function checkURL(){
    let hash = window.location.hash
    if(hash == "#footer" || hash == "#header"){
        window.history.replaceState({page: ""}, "", window.location.pathname);   
    }
}

var shown = ["all"];
function show(name) {
    setMenuActiveColor(name);

    var rootElement = document.getElementById("videoholder");
    var rootChilds = rootElement.children;
    for(children in rootChilds) {
        var child = rootChilds[children];
        showOrHideSong(shown, child);
    }
}

function setMenuActiveColor(MenuID) {
    var menu = document.getElementById(MenuID);
    menu.classList.toggle("active");

    if (MenuID === "all") {
        var allMenu = document.getElementById("menu-all").querySelectorAll("button");
        for(element in allMenu) {
            allMenu[element].classList = "";
        }

        var defaultMenu = document.getElementById("menu-default").querySelectorAll("button");
        for(element in defaultMenu) {
            defaultMenu[element].classList = "";
        }
        shown = ["all"];
    } else {
        if (menu.classList.contains("active")) {
            shown.push(MenuID);
        }
        else {
            var NewShown = shown.filter(e => e !== MenuID);
            shown = NewShown;
        }
    }
}

function hideMenu(menuName) {
    var menu = document.getElementById(menuName);
    menu.style.display = "none";
}

function showOrHideSong(name, element) {
    if (element != undefined && element.classList != undefined) {
        var found = true;
        for(tags in name) {
            if (!element.classList.contains(name[tags])) {
                found = false;
                element.style.display = "none";
            }
            if (name[tags] === "all") {
                found = true;
                element.style.display = "flex";
            }
        }
        if (found) {
            if (element.style.display === "none" || element.style.display === undefined || element.style.display === "") {
                element.style.display = "flex";
            }
        }
    }
}

function shuffle(array) {
    let currentIndex = array.length;
    console.log("array length is : " + array.length);

    let ShuffleSettings = localStorage.getItem('YoutubeShuffle');
    if(ShuffleSettings === null || ShuffleSettings === "true"){ ShuffleSettings = true;}
    else{ ShuffleSettings = false; }

    console.log("Shuffle : " + ShuffleSettings);

    if(ShuffleSettings === false){
        console.log("No shuffling made ");
        return array;
    }
    else{
        console.log("Shuffling songs ! ...");
        while (currentIndex != 0) {
            let randomIndex = Math.floor(Math.random() * currentIndex);
            currentIndex--;

            [array[currentIndex], array[randomIndex]] = [array[randomIndex], array[currentIndex]];
        }
        return array;
    }
}

const wait = (ms) => new Promise(resolve => setTimeout(resolve, ms));

async function GetVideos(videoList, VideoListType, videoType, includeLatestVideoOfChannel) {
    await include_script("/src/js/content/auto-scroll.js");
    addButtons(VideoListType);

    var hash = decodeURIComponent(window.location.hash).replace("#", "");
    var LoadSingleVideo = false;
    if (hash !== "") {
        LoadSingleVideo = true;
        hideMenu("menu-all");
        hideMenu("menu-default");
        
        await parseVideoParam(videoList, -1, hash, false, "videoholder", videoType); //pre load (even if video code dont exist)
    }
    else {
        hideMenu("menu-link");
    }

    let short;
    let count = 0;
    let delay = localStorage.getItem("YouTubeVideoDelay");
    if(delay == null){
        delay = 25;
    }

    for(video in videoList) {
        //if(count>=Infinity){break;} //limiter for testing
        if(count >= 1){
            await wait(delay);
            count = 0;
        }
        count++;

        var videoID = videoList[video].videoID;
        short = videoList[video].short;

        if (LoadSingleVideo) {
            if (videoID === hash) { //video code match requested page
                await parseVideoParam(videoList, video, videoID, false, "videoholder", videoType);
                total = 1;
                break;
            }
        }
        else {
            await parseVideoParam(videoList, video, videoID, false, "videoholder", videoType);
            total += 1;
            checkForDuplicate(videoID);
        }

        autoScroll(true, "center");
    }

    console.log(GlobalVideoIDList);

    if (LoadSingleVideo && total !== 1) { //if video code don't match requested page
        videoholder.replaceChildren();
        var div_card = addCard(false, false, hash, "", false, false, videoType, short);
        let savedTitle = "";
        if (videoList[videoID] !== undefined && videoList[videoID].text !== undefined) {
            savedTitle = " : " + videoList[videoID].text;
        }
        addCardData(div_card, "404" + savedTitle , "Code YouTube \" " + hash + " \" incorrect !", "/assets/svg/link-broken.svg", true);
        videoholder.appendChild(div_card);
        div_card.style.display = "flex";
    }
    else if (!LoadSingleVideo) {
        if(includeLatestVideoOfChannel === true){
            getVideoChannel();
        }
        setGlobalPlayList(videoType, short);
    }

    console.log("Total : " + total);
}

async function parseVideoParam(videoList, video, videoID, premadePlayList, divName, videoType) {
    var short = false;
    var top = false;
    var premadePlayList = false;
    var category = "";
    var text = "";
    var playlist = false;

    if (premadePlayList == false && videoList[video] != undefined) {
        short = videoList[video].short;
        if (short === undefined) {
            short = false;
        }

        top = videoList[video].top;
        if (top === undefined) {
            top = false;
        }

        premadePlayList = videoList[video].premadePlayList;
        if (premadePlayList === undefined) {
            premadePlayList = false;
        }

        category = videoList[video].category;

        if (videoList[video].text !== undefined) {
            text = videoList[video].text;
        }
        else {
            text = "";
        }

        playlist = videoList[video].playlist;
        if (playlist === undefined) {
            playlist = false;
        }
    }

    let fetchURL = getFetchURL(playlist, premadePlayList, videoID);

    await parseResponse(playlist, videoID, top, category, fetchURL, text, short, premadePlayList, false, divName, videoType);
}

function getFetchURL(playlist, premadePlayList, videoID) {
    if (playlist == true && premadePlayList == false) {
        return "https://youtube.com/oembed?url=https://www.youtube.com/playlist?list=" + videoID + "&format=json";
    } else if (playlist == true && premadePlayList == true) {
        return "https://www.youtube.com/oembed?url=https://youtube.com/watch?v=" + videoID + "&format=json";
    }
    else {
        return "https://www.youtube.com/oembed?url=https://youtube.com/watch?v=" + videoID + "&format=json";
    }
}

function addButtons(Types) {
    var menu = document.getElementById("menu-all");
    for(videoType in Types) {
        var type = Types[videoType];
        var emoji = getEmoji(type);

        var button = document.createElement("button");
        button.id = type;
        button.setAttribute("onClick", "show('" + type + "');");

        var text = document.createElement("p");
        text.textContent = emoji + " " + type;

        button.appendChild(text);
        menu.appendChild(button);
    }
}

async function addIFrame(playlist, videoID, top, category, text, short, premadePlayList, title, thumbnail, videoholder, div_card, videoType) {
    var length = 75;
    var title = title.length > length ? title.substring(0, length - 3) + "..." : title;


    var video_div = addCardData(div_card, title, text, thumbnail, false);
    addVideoCard(video_div, videoID, playlist, short, premadePlayList);

    videoholder.appendChild(div_card);

    await constructPlayList(videoID, playlist, top, category, videoType);

    showOrHideSong(shown, div_card);
}

async function setTitleInVar(videoIDParam, title){
    let SaveTitle = localStorage.getItem('YoutubeTitleSaving');
    if(SaveTitle === "true"){ SaveTitle = true;}
    else{ SaveTitle = false; }

    if(SaveTitle === true){
        let counter = 0;
        for(video in GlobalVideoIDList) {
            let videoID = GlobalVideoIDList[video].videoID;
            if(videoID === videoIDParam){
                if(GlobalVideoIDList[video].title !== title){
                    counter++;
                    console.log("title saved for video " + video + " and ID " + videoID);
                    GlobalVideoIDList[video].title = title;

                    let x = video % 50;
                    if(x === 0){
                        console.debug(counter);
                        console.log(GlobalVideoIDList);
                    }
                }
            }

            
        }
    }
}

async function parseResponse(playlist, videoID, top, category, fetchUrl, text, short, premadePlayList, latest, element, videoType) {
    try {
        //skip query with settings ?
        //Create a setting flag, retrieve it for here
        let doQuery = localStorage.getItem("IndividualsVids");
        if(doQuery == null){ doQuery = "true"; }
        
        if(doQuery === "true" || category === "Markdown" ){
            var response = await fetch(fetchUrl);
            var status = response.status;

            var videoholder = document.getElementById(element);
            let div_card = addCard(top, playlist, videoID, category, latest, premadePlayList, videoType, short);

            let SavedText = "";
            let videoList = GetVideoList();
            let index = videoList.findIndex(item => item.videoID === videoID);
            
            if (index >= 0 && videoList[index].title !== undefined) {
                SavedText = " : " + videoList[index].title;
            }

            if (status === 200) {
                var jsonResponse = await response.json();
                JSONdata = jsonResponse;


                var title = JSONdata.title;
                    setTitleInVar(videoID, title);
                var length = 75;
                var titleTrimed = title.length > length ? title.substring(0, length - 3) + "..." : title;
                var thumbnail = JSONdata.thumbnail_url;

                addIFrame(playlist, videoID, top, category, text, short, premadePlayList, titleTrimed, thumbnail, videoholder, div_card, videoType);

                return;
            }
            else if (status === 404) {
                addCardData(div_card, "404" + SavedText, "Vidéo supprimée !", "/assets/svg/link-broken.svg", true);
                videoholder.appendChild(div_card);
            }
            else if (status === 403) {
                addCardData(div_card, "403" + SavedText, "Vidéo privée !", "/assets/svg/link-broken.svg", true);
                videoholder.appendChild(div_card);
            }
            else if (status === 401) {
                addCardData(div_card, "401" + SavedText, "Vidéo sans embed !", "/assets/svg/link-broken.svg", true);
                videoholder.appendChild(div_card);
            }
            else {
                console.error(response);
                var jsonResponse = null;
            }
            showOrHideSong(shown, div_card);
        }
        else{
            constructPlayList(videoID, playlist, top, category, videoType);
        }
    } catch (error) {
        console.error(error + "\n" + videoID);
    }
}

function addCard(top, playlist, videoID, category, latest, premadePlayList, videoType, short) {
    var div_card = document.createElement("div");
    var classname = "";
    if (top) {
        classname += "top ";
    }
    if (playlist) {
        classname += "playlist ";
    }
    if (latest) {
        classname += "latest ";
    }

    if (premadePlayList) {
        classname += "premadePlayList ";
    }

    classname += "card " + category;
    div_card.className = classname;

    var divLogoHolder = document.createElement("div");
    divLogoHolder.id = "logoHolder";

    if (!premadePlayList) {
        var anchor = document.createElement("a");
        anchor.href = "#" + videoID;
        anchor.onclick = () => { };

        anchor.id = videoID;
        setScrollBehavior(anchor, "center");
        divLogoHolder.appendChild(anchor);

        var imageAnchor = document.createElement("img");
        imageAnchor.src = "/assets/svg/link.svg";
        imageAnchor.className = "topimg svg";
        anchor.appendChild(imageAnchor);
    }

    if (top) {
        var imageTop = document.createElement("img");
        imageTop.src = "/assets/svg/star.svg";
        imageTop.className = "topimg svg";
        divLogoHolder.appendChild(imageTop);
    }
    if (playlist) {
        var imagePlayList = document.createElement("img");
        imagePlayList.src = "/assets/svg/playlist.svg";
        imagePlayList.className = "playlistimg svg";
        divLogoHolder.appendChild(imagePlayList);
    }
    if (latest) {
        var imageNew = document.createElement("img");
        imageNew.src = "/assets/svg/new.svg";
        imageNew.className = "newimg svg";
        divLogoHolder.appendChild(imageNew);
    }
    if (premadePlayList) {
        var imagepremadePlayList = document.createElement("img");
        imagepremadePlayList.src = "/assets/svg/dj-turntable-vinyl.svg";
        imagepremadePlayList.className = "premadePlayList svg";
        divLogoHolder.appendChild(imagepremadePlayList);
    }


    let url = getURL(premadePlayList, short, playlist, videoID, false);

    if(videoType === "music"){ //add also a music.youtube if it's a song
        var urlOpenYoutubeMusic = document.createElement("a");
        urlOpenYoutubeMusic.href = url.replace("www.", "music.");
        var imageOpenOnYoutubeMusic = document.createElement("img");
        imageOpenOnYoutubeMusic.src = "/assets/svg/trademark/youtube-music.svg";
        imageOpenOnYoutubeMusic.className = "OpenOnYoutube svg";

        urlOpenYoutubeMusic.appendChild(imageOpenOnYoutubeMusic);
        divLogoHolder.appendChild(urlOpenYoutubeMusic);
    }

    var urlOpenYoutube = document.createElement("a");
    urlOpenYoutube.href = url;
    var imageOpenOnYoutube = document.createElement("img");
    imageOpenOnYoutube.src = "/assets/svg/trademark/youtube.svg";
    imageOpenOnYoutube.className = "OpenOnYoutube svg";

    urlOpenYoutube.appendChild(imageOpenOnYoutube);
    divLogoHolder.appendChild(urlOpenYoutube);

    var urlOpenGithub = document.createElement("a");
    if(videoType === "music"){
        urlOpenGithub.href = "https://github.com/search?q=" + videoID + "+repo:Altherneum/Altherneum.github.io+path:src/js/content/music.js&type=code";
    }
    else{
        urlOpenGithub.href = "https://github.com/search?q=" + videoID + "+repo:Altherneum/Altherneum.github.io+path:src/js/content/video.js&type=code";    
    }
    var imageOpenOnGithub = document.createElement("img");
    imageOpenOnGithub.src = "/assets/svg/trademark/github.svg";
    imageOpenOnGithub.className = "OpenOnGithub svg";

    urlOpenGithub.appendChild(imageOpenOnGithub);
    divLogoHolder.appendChild(urlOpenGithub);

    var urlCopy = document.createElement("button");
    urlCopy.onclick  = () => { navigator.clipboard.writeText(videoID);}

    var copyUrlImage = document.createElement("img");
    copyUrlImage.src = "/assets/svg/copy.svg";
    copyUrlImage.className = "copyurl svg";

    urlCopy.appendChild(copyUrlImage);
    divLogoHolder.appendChild(urlCopy);

    div_card.appendChild(divLogoHolder);

    var categorys = category;
    let categoryList = categorys.split(" ");
    var categoryHolder = document.createElement("p");
    categoryHolder.className = "categoryList";

    if (categorys !== "Markdown") {
        for(categoryIndex in categoryList) {
            categoryHolder.textContent += " " + getEmoji(categoryList[categoryIndex]);
        }
    }

    div_card.appendChild(categoryHolder);

    return div_card;
}

function addCardData(div_card, title, text, thumbnail, error) {
    var video_title = document.createElement("h1");
    video_title.textContent = title;
    div_card.appendChild(video_title);

    var video_subtext = document.createElement("p");
    video_subtext.innerHTML = text;
    video_subtext.className = "textSub";
    div_card.appendChild(video_subtext);

    var video_div = document.createElement("div");
    video_div.className += "videoDiv";
    div_card.appendChild(video_div);

    var video_image = document.createElement("img");
    video_image.src = thumbnail;
    if (error) {
        video_image.style.width = "100%";
        video_image.style.height = "100%";
        video_image.style.maxWidth = "none";
        video_image.className = "svg";
    }
    video_image.loading = "lazy";
    video_div.appendChild(video_image);

    return video_div;
}

function addVideoCard(video_div, videoID, playlist, short, premadePlayList) {
    var video_button = document.createElement("button");
    video_button.dataset.youtubeButton = videoID;
    video_button.dataset.youtubePlayList = playlist;
    video_button.dataset.youtubeShort = short;
    video_button.dataset.youtubePremadePlayList = premadePlayList;

    video_button.setAttribute("onclick", "createIframe(this)");

    video_div.appendChild(video_button);
}

async function createIframe(event) {
    var videoID = event.dataset.youtubeButton;
    var playlist = event.dataset.youtubePlayList;
    var short = event.dataset.youtubeShort;
    var premadePlayList = event.dataset.youtubePremadePlayList;
    var youtubePlaceholder = event.parentNode;

    var url = getURL(premadePlayList === "true", short === "true", playlist === "true", videoID, true);

    console.log("Loading embed : " + url);

    var iframe = '<iframe src="' + url + '" title="YouTube video player" frameborder="0" allow="screen-wake-lock; accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>'
    var htmlString = '<div id=' + videoID + ' class="videoDiv">' + iframe + '</div>';

    var card = youtubePlaceholder.parentNode;
    var classname = card.className;
    card.className = classname + " playing";

    youtubePlaceholder.style.display = 'none';
    youtubePlaceholder.insertAdjacentHTML('beforebegin', htmlString);
    youtubePlaceholder.parentNode.removeChild(youtubePlaceholder);

    if (premadePlayList == true) {
        //Décharge et recharge la vidéo car l'API YT est à chier :3 ...        //NB:Uniquement sur les PlayList temporaires "TL" "TempList"
        setTimeout(() => {
            var vid = document.getElementById(videoID);
            vid.firstChild.remove();
            vid.innerHTML += iframe;
        }, 1500);
        // reloading too fast dont help,
        //reloading after 1sec or more let youtube some spare time to really create the playList & embed
    }
}

function getURL(premadePlayList, short, playlist, videoID, emebed) {
    let loop;
    let autoplay = "&autoplay=1";
    let playlistarg;
    let rel = "&rel=0";
    let prot = "https://www.";
    let sitename = "youtube.com/"

    let preURL;
    if(emebed === true){
        preURL = prot + sitename + "embed/";
    }
    else
    {
        preURL = prot + sitename + "";
    }

    if (premadePlayList === true) {
        if(emebed === true){
            playlistarg = "?playlist=" + videoID;
        }
        else
        {
            playlistarg = "watch_videos?video_ids=" + videoID; 
        }

        loop = "";

        if(emebed === true){
            let firstVideoID = videoID.split(",")[0];
            return preURL + firstVideoID + playlistarg + autoplay + loop + rel;
        }
        else
        {
            return preURL + playlistarg + autoplay + loop + rel;
        }
    }
    else {
        if (short === false) {
            let YouTubeLoop = localStorage.getItem('YouTubeLoop');
            if(YouTubeLoop === null || YouTubeLoop === "false"){ YouTubeLoop = false;}
            else{ YouTubeLoop = true; }

            if (YouTubeLoop === true && playlist === false && emebed === true) {
                loop = "&loop=1";
                playlistarg = videoID + "?playlist=" + videoID;
            }
            else if (YouTubeLoop === true && playlist === false && emebed === false) {
                loop = "&loop=1";
                playlistarg = "watch?v=" + videoID + "&playlist=" + videoID;
            }
            else if (YouTubeLoop === true && playlist === true && emebed === true) {
                loop = "&loop=1";
                playlistarg = "?list=" + videoID + "&listType=playlist";
            }
            else if (YouTubeLoop === true && playlist === true && emebed === false) {
                loop = "&loop=1";
                playlistarg = "playlist?list=" + videoID + "&listType=playlist";
            }
            else if (YouTubeLoop === false && playlist === false && emebed === true) {
                loop = "&loop=0";
                playlistarg = videoID + "?si=Altherneum.fr";
            }
            else if (YouTubeLoop === false && playlist === false && emebed === false) {
                loop = "&loop=0";
                playlistarg = "watch?v=" + videoID + "?si=Altherneum.fr";
            }
            else if (YouTubeLoop === false && playlist === true && emebed === true) {
                loop = "&loop=0";
                playlistarg = "?list=" + videoID + "&listType=playlist";
            }
            else if (YouTubeLoop === false && playlist === true && emebed === false) {
                loop = "&loop=0";
                playlistarg = "playlist?list=" + videoID + "&listType=playlist";
            }
        }
        else {
            loop = "";
            autoplay = "?autoplay=1";
            playlistarg = videoID;
        }

        return preURL + playlistarg + autoplay + loop + rel;
    }
}

async function getLatestVideoOfChannel(ChannelID, maxVideoAmount, category, text, top, latest) {
    const channelURL = "https://www.youtube.com/feeds/videos.xml?channel_id=" + ChannelID;
    var data = await fetch("https://api.rss2json.com/v1/api.json?rss_url=" + channelURL)
        .then(resp => resp.json())
        .then(responseData => responseData.items)
        .then(items => {
            for(i in items) {
                if (i > maxVideoAmount) {
                    break;
                }

                let videoID = items[i].link.replace("https://www.youtube.com/watch?v=", "");

                parseResponse(false, videoID, top, category, "https://www.youtube.com/oembed?url=https://youtube.com/watch?v=" + videoID + "&format=json", text, false, false, latest, "videoholder")
            }
        });
}

async function setVideoScreenLocking() {
    //https://developer.mozilla.org/en-US/docs/Web/API/Screen_Wake_Lock_API
    try {
        if ("wakeLock" in navigator) {
            console.log("Screen Wake Lock API supported!");

            var wakeLock = null;

            if (document.visibilityState === 'visible') {
                wakeLock = await navigator.wakeLock.request("screen");
                console.log("Wake Lock is active!");

                wakeLock.addEventListener('release', () => {
                    console.log('Screen Wake State used : ' + !wakeLock.released);
                });
            }
            else {
                console.log("Screen is not visible : Document visibilty state ; " + document.visibilityState);
            }

            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    console.log("Requesting WakeLock ...");
                    navigator.wakeLock.request('screen').then(result => {
                        wakeLock = result;
                        console.log(wakeLock);
                    });
                }
                else {
                    console.log("Releasing WakeLock ...");
                    console.log(wakeLock);

                    wakeLock.release();

                    if (wakeLock === null) {
                        console.log("WakeLock is equal to \"null\", release OK !");
                    } else {
                        console.log("WakeLock not equal to \"null\" ...");
                        if (wakeLock.released) {
                            console.log("WakeLock is released (sucess)");
                        }
                        else {
                            console.log("WakeLock is not released (error)");
                        }
                        console.log(wakeLock);
                    }
                }
            });
        }
        else {
            console.log("Wake lock is not supported by this browser.");
        }
    } catch (err) {
        console.error(err);
    }
}

var categoryList;

var smallAutoMix;
var fullAutoMix;

function createPlayListList() {
    categoryList = getVideoListType();
    smallAutoMix = [];
    fullAutoMix = [];
    comboAlreadyDone = "";

    let categoryName = "";
    for(categoryType in categoryList) {
        for(categoryType2 in categoryList){

            if(!comboAlreadyDone.includes(categoryList[categoryType] + " " + categoryList[categoryType2]) && !comboAlreadyDone.includes(categoryList[categoryType2] + " " + categoryList[categoryType])){
                comboAlreadyDone += categoryList[categoryType] + " " + categoryList[categoryType2];

                if(categoryType != categoryType2){
                    categoryName = categoryList[categoryType] + " " + categoryList[categoryType2];
                } else{ categoryName = categoryList[categoryType]; }

                smallAutoMix.push({ tag: categoryName, videoIDList: "", amount: 0, top: "true" });
                fullAutoMix.push({ tag: categoryName, videoIDList: "", amount: 0, top: "true" });

                smallAutoMix.push({ tag: categoryName, videoIDList: "", amount: 0, top: "false" });
                fullAutoMix.push({ tag: categoryName, videoIDList: "", amount: 0, top: "false" });

                smallAutoMix.push({ tag: categoryName, videoIDList: "", amount: 0, top: "mixed" });
                fullAutoMix.push({ tag: categoryName, videoIDList: "", amount: 0, top: "mixed" });
            }
        }
    }
}

async function constructPlayList(videoID, playlist, top, category, videoType) {
    if (playlist == false) {
        var VideocategoryList = category.split(" ");
        for(categoryType in VideocategoryList) {
            var tag = VideocategoryList[categoryType];

            if(top === true){
                await setInPlayList("true", videoID, playlist, top, category, videoType, tag);//top small&full
            }
            else
            {
                await setInPlayList("false", videoID, playlist, top, category, videoType, tag);//notop small&full
            }
            await setInPlayList("mixed", videoID, playlist, top, category, videoType, tag); //mixed small&full
        }
    }
}

async function setInPlayList(topType, videoID, playlist, top, category, videoType, tag)
{
    for(categoryInList in smallAutoMix){
        if(smallAutoMix[categoryInList].tag.includes(tag) && smallAutoMix[categoryInList].top === topType){
            let include = true;
            let categoryToCheck = smallAutoMix[categoryInList].tag.split(" ");
            for(Currenttag in categoryToCheck){
                if(!category.includes(categoryToCheck[Currenttag])){
                    include = false;
                }
            }

            if(include == true){
                include = false;
                if(!smallAutoMix[categoryInList].videoIDList.includes(videoID)){
                    smallAutoMix[categoryInList].videoIDList += videoID + ",";
                    smallAutoMix[categoryInList].amount += 1;
                    await CheckIfPlayListAtLimit(smallAutoMix[categoryInList].tag, top, false, videoType, false, topType);
                }
                if(!fullAutoMix[categoryInList].videoIDList.includes(videoID)){
                    fullAutoMix[categoryInList].videoIDList += videoID + ",";
                    fullAutoMix[categoryInList].amount += 1;
                    //await CheckIfPlayListAtLimit(fullAutoMix[categoryInList].tag, top, false, videoType, false, topType);
                }
            }
        }
    }
}

async function CheckIfPlayListAtLimit(tag, top, mixed, videoType, short, topType) {
    let videoAmountForPlayList = localStorage.getItem("PlayListVideoAmount");
    if(videoAmountForPlayList == null){
        videoAmountForPlayList = 20;
    }
    
    if(topType === "true"){
        let videoIDList = smallAutoMix[smallAutoMix.findIndex(obj => obj.tag == tag && obj.top == topType)].videoIDList;
        let videoAmount = smallAutoMix[smallAutoMix.findIndex(obj => obj.tag == tag && obj.top == topType)].amount;
        if (videoAmount >= videoAmountForPlayList) {
            smallAutoMix[smallAutoMix.findIndex(obj => obj.tag == tag && obj.top == topType)].videoIDList = "";
            smallAutoMix[smallAutoMix.findIndex(obj => obj.tag == tag && obj.top == topType)].amount = 0;
            let div_card = addCard(true, true, videoIDList, tag, false, true, videoType, short);
            await addIFrame(true, videoIDList, true, tag, "Auto Mix Top : " + videoAmount, false, true, tag, "/assets/gif/logo.gif", document.getElementById("videoholder"), div_card, videoType);
        }
    }
    else if (topType === "false"){
        let videoIDList = smallAutoMix[smallAutoMix.findIndex(obj => obj.tag == tag && obj.top == topType)].videoIDList;
        let videoAmount = smallAutoMix[smallAutoMix.findIndex(obj => obj.tag == tag && obj.top == topType)].amount;
        if (videoAmount >= videoAmountForPlayList) {
            smallAutoMix[smallAutoMix.findIndex(obj => obj.tag == tag && obj.top == topType)].videoIDList = "";
            smallAutoMix[smallAutoMix.findIndex(obj => obj.tag == tag && obj.top == topType)].amount = 0;
            let div_card = addCard(false, true, videoIDList, tag, false, true, videoType, short);
            await addIFrame(true, videoIDList, false, tag, "Auto Mix No Top : " + videoAmount, false, true, tag, "/assets/gif/logo.gif", document.getElementById("videoholder"), div_card, videoType);
        }
    }
    else if(topType === "mixed") {
        let videoIDList = smallAutoMix[smallAutoMix.findIndex(obj => obj.tag == tag && obj.top == topType)].videoIDList;
        let videoAmount = smallAutoMix[smallAutoMix.findIndex(obj => obj.tag == tag && obj.top == topType)].amount;
        if (videoAmount >= videoAmountForPlayList) {
            smallAutoMix[smallAutoMix.findIndex(obj => obj.tag == tag && obj.top == topType)].videoIDList = "";
            smallAutoMix[smallAutoMix.findIndex(obj => obj.tag == tag && obj.top == topType)].amount = 0;
            let div_card = addCard(false, true, videoIDList, tag, false, true, videoType, short);
            await addIFrame(true, videoIDList, false, tag, "Auto Mix Mixed : " + videoAmount, false, true, tag, "/assets/gif/logo.gif", document.getElementById("videoholder"), div_card, videoType);
        }
    }
}

async function setGlobalPlayList(videoType, short) {
    let categoryList = getVideoListType();
    for(categoryType in categoryList) {
        var tag = categoryList[categoryType];

        let topType = "true";
        //top
        let videoIDListTop = fullAutoMix[fullAutoMix.findIndex(obj => obj.tag == tag && obj.top == topType)].videoIDList;
        let videoAmountTop = fullAutoMix[fullAutoMix.findIndex(obj => obj.tag == tag && obj.top == topType)].amount;

        let div_cardTop = addCard(true, true, videoIDListTop, tag, false, true, videoType, short);
        addIFrame(true, videoIDListTop, true, tag, "Auto Mix Top : " + videoAmountTop, false, true, tag, "/assets/gif/logo.gif", document.getElementById("videoholder"), div_cardTop, videoType);

        //Mixed
        topType = "mixed"
        let videoIDListMixed = fullAutoMix[fullAutoMix.findIndex(obj => obj.tag == tag && obj.top == topType)].videoIDList;
        let videoAmountMixed = fullAutoMix[fullAutoMix.findIndex(obj => obj.tag == tag && obj.top == topType)].amount;

        let div_cardMixed = addCard(false, true, videoIDListMixed, tag, false, true, videoType, short);
        addIFrame(true, videoIDListMixed, false, tag, "Auto Mix Mixed : " + videoAmountMixed, false, true, tag, "/assets/gif/logo.gif", document.getElementById("videoholder"), div_cardMixed, videoType);

        //NoTop
        topType = "false";
        let videoIDListNoTop = fullAutoMix[fullAutoMix.findIndex(obj => obj.tag == tag && obj.top == topType)].videoIDList;
        let videoAmountNoTop = fullAutoMix[fullAutoMix.findIndex(obj => obj.tag == tag && obj.top == topType)].amount;

        let div_cardNoTop = addCard(false, true, videoIDListNoTop, tag, false, true, videoType, short);
        addIFrame(false, videoIDListNoTop, true, tag, "Auto Mix No Top: " + videoAmountNoTop, false, true, tag, "/assets/gif/logo.gif", document.getElementById("videoholder"), div_cardNoTop, videoType);
    }
}

var stringVideoList = "";
var stringDuplicated = "";
function checkForDuplicate(videoID){
    if(stringVideoList.includes(videoID)){
        stringDuplicated += videoID + ", ";
        console.log("New duplicate found : " + videoID);
        console.log("Duplicate list : " + stringDuplicated);
    }
    else{
        stringVideoList += videoID;
    }
}
