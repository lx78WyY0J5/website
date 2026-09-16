function resetLocalStorage() {
    localStorage.clear();
    location.reload();
}

function resetWebSiteStorage() {
    localStorage.removeItem("theme");
    location.reload();
}

function SwitchViewCount() {
    Switcher("viewCount");
}

function SwitchYTIndividualsVids() {
    Switcher("IndividualsVids");
}

function SwitchDevMode() {
    Switcher("devMode");
}

function SwitchCursor() {
    Switcher("customCursor");
    getCursorSetting();
}

function SwitchGranted() {
    Switcher("Granted");
}

function SwitchOldSearchBar() {
    Switcher("OldSearchBar");
}

function SwitchAllSearchBar() {
    Switcher("AllSearchBar");
}

function SwitchSearchBarList() {
    Switcher("SearchBarList");
}

function SwitchYouTubeLoop() {
    Switcher("YouTubeLoop");
}

function SwitchErrorLogging() {
    Switcher("ErrorLogging");
}

function SwitchWarningLogging() {
    Switcher("WarningLogging");
}

function SwitchAllLogs() {
    Switcher("AllLogs");
}

function SwitchYoutubeShuffle() {
    Switcher("YoutubeShuffle");
}

function SwitchYoutubeTitleSaving() {
    Switcher("YoutubeTitleSaving");
}

function SwitchVisitedLogs() {
    Switcher("VisitedLogs");
}

function SwitchLogLogging() {
    Switcher("LogLogging");
}

function SwitchOpenAllChapter() {
    Switcher("OpenAllChapter");
}

function SwitchCloseOnOpen() {
    Switcher("CloseOnOpen");
}

function SwitchTitre2() {
    Switcher("Titre2");
}

function SwitchTitre3Plus() {
    Switcher("Titre3Plus");
}

function SwitchShowSummary(){
    Switcher("ShowSummary");
}

function SwitchThemeTransparent(){
    Switcher("ThemeTransparent");
    if (localStorage.getItem('theme') !== 'transparent' && localStorage.getItem('ThemeTransparent') === "true") {
        setTheme('transparent');
    }
}

function SwitchBackgroundGride(){
    Switcher("BackgroundGride");
}

function Switcher(name) {
    var checkbox = document.getElementById(name);
    var state = checkbox.checked;
    localStorage.setItem(name, state);
    console.log("set " + name + " to " + state)
    setSwitch(name, null);
}

function setSwitch(name, defaultTrue) {
    var checkbox = document.getElementById(name);
    console.log("setting switch " + name);
    if(checkbox == null){ return; }
    
    var state = localStorage.getItem(name);

    if(state === undefined || state === null){
        if (defaultTrue) {
            checkbox.indeterminate = true;
            checkbox.className += "defaultTrue";
        }
        if (!defaultTrue) {
            checkbox.indeterminate = true;
            checkbox.className += "defaultFalse";
        }
    }
    else {
        checkbox.className -= "defaultFalse";
        checkbox.className -= "defaultTrue";
        if (state === 'true') {
            checkbox.checked = true;
        }
        else if (state === false) {
            checkbox.checked = false;
        }
    }
}

function setInputValue(name, defaultValue) {
    var input = document.getElementById(name);
    if(input == null){ return; }
    
    var GotValue = localStorage.getItem(name);

    if(GotValue === undefined || GotValue === null){
        input.value = defaultValue;
        console.error("Setting input " + name + " to default value ; " + defaultValue);
    }
    else {
        input.value = GotValue;
        console.error("Setting input " + name + " to found value ; " + GotValue);
    }
}

function loadSettingsSwitch() {
    setSwitch("devMode", false);
    setSwitch("viewCount", false);
    setSwitch("customCursor", false);
    setSwitch("Granted", false);
    setSwitch("YouTubeLoop", false);
    setSwitch("YoutubeShuffle", true);
    setSwitch("YoutubeTitleSaving", false);
    setInputValue("PlayListVideoAmount", 20);
    setInputValue("YouTubeVideoDelay", 20);
    setSwitch("IndividualsVids", true);
    setSwitch("OldSearchBar", false);
    setSwitch("SearchBarList", true);
    setSwitch("AllSearchBar", false);
    setSwitch("LogLogging", false);
    setSwitch("WarningLogging", false);
    setSwitch("ErrorLogging", false);
    setSwitch("VisitedLogs", true);
    setSwitch("AllLogs", false);
    setSwitch("OpenAllChapter", true);
    setSwitch("CloseOnOpen", false);
    setSwitch("Titre2", true);
    setSwitch("Titre3Plus", true);
    setSwitch("ShowSummary", true);
    setSwitch("BackgroundGride", true);

    setSwitch("ThemeTransparent", false);
    setTransparencyPowerSetting();
}

function setTransparencyPowerSetting(){
    if(localStorage.getItem("ThemeTransparent") === "true"){
        if(Number.isInteger(parseInt(localStorage.getItem('TransparencyPower')))){
            document.getElementById("TransparencyPower").value = localStorage.getItem('TransparencyPower');
        }
        else{
            document.getElementById("TransparencyPower").value = 0.1;
        }
    }
    else{
        document.getElementById("TransparencyPower").value = 1;
    }
}