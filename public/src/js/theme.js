toggleTheme();
toggleBackgroundGride();

function setTheme(themeName) {
    localStorage.setItem("theme", themeName);
    document.documentElement.className = themeName;
    console.log("Loading theme : " + themeName);
    setTransparencyPower();
    setIconTheme();
}

function toggleBackgroundGride(){
    let body = document.body.classList;
    if(localStorage.getItem("BackgroundGride") !== "false"){
        body.add("gride");
    }
    else{
        body.remove("gride");
    }
}

function toggleTheme() {
    if (localStorage.getItem("theme") === "dark") {
        setTheme("dark");
    }
    else if (localStorage.getItem("theme") === "day") {
        setTheme("day");
    }
    else if (localStorage.getItem("theme") === "light") {
        setTheme("light");
    }
    else if (localStorage.getItem("theme") === "transparent") {
        setTheme("transparent");
    }

    else {
        const systemSettingDark = window.matchMedia("(prefers-color-scheme: dark)");
        if (systemSettingDark) {
            setTheme("dark");
            console.log("Default color scheme : Dark");
        }
        else {
            setTheme("light");
            console.log("Default color scheme : Light");
        }
    }
}

function changeTheme() {
    if (localStorage.getItem("theme") === "day") {
        setTheme("dark");
    }
    else if (localStorage.getItem("theme") === "dark") {
        setTheme("light");
    }
    else if(localStorage.getItem("theme") === "light" && localStorage.getItem("ThemeTransparent") === "true"){
        setTheme("transparent")
    }
    else if (localStorage.getItem("theme") === "transparent") {
        setTheme("day");
    }

    else { setTheme("dark"); }
}

async function setIconTheme() {
    var themeButton = document.getElementById("theme");
    if(themeButton === null){
        console.log("No content left : Anchor : present so returning")
        return;
    }

    var img = themeButton.children[0];

    if (localStorage.getItem("theme") === "dark") {
        img.src = "/src/assets/svg/sun.svg";
    }
    else if (localStorage.getItem("theme") === "day") {
        img.src = "/src/assets/svg/light.svg";
    }
    else if (localStorage.getItem("theme") === "light") {
        img.src = "/src/assets/svg/moon.svg";
    }
    else if (localStorage.getItem("theme") === "transparent") {
        img.src = "/src/assets/svg/linux.svg";
    }

    else { img.src = "/src/assets/svg/settings.svg"; }
}

function setTransparencyPower(){
    console.log("Loading transparency module");

    let tempThemeName = localStorage.getItem("theme");
    let finalThemeName;
    if(tempThemeName !== "" && tempThemeName !== null){
        finalThemeName = "." + tempThemeName;
    }

    let root = document.querySelector(":root" + finalThemeName);
    let transparencyPower = localStorage.getItem("TransparencyPower");
    let rootStyle = getComputedStyle(root);

    let R = rootStyle.getPropertyValue("--background-color-R");
    let G = rootStyle.getPropertyValue("--background-color-G");
    let B = rootStyle.getPropertyValue("--background-color-B");
        console.log("theme color ; " + R + ", "  + G + ", " + B);

    if(localStorage.getItem("ThemeTransparent") === "true" && localStorage.getItem("theme") === "transparent"){
        console.log("Transparent module ON");
        console.log(rootStyle.getPropertyValue("--background-color") + " to " + transparencyPower);
        document.documentElement.style.cssText = "--background-color: rgba("+ R + ", " + G + ", " + B + ", 0." + transparencyPower + ")";
    }
    else{
        console.log("No transparency settings");
        if (localStorage.getItem("theme") === "transparent") {
            changeTheme();
            console.log("Theme is transparent, changeTheme");
        }
    }
    console.log("CSS style ; " + document.documentElement.style.cssText);
}