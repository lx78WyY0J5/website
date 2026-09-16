var JSONFile;
var FramesJSON;
var maxFrames = 0;
var frameNumber = 1;
var frameSpeed = 0;
var animation = "";
var intervalID;

getValue();

function getValue(){
    frameSpeed = document.getElementById("text-animation-speed").value;
    animation = document.getElementById("text-animation-name").value;

    start(animation);
}

var defaultStyle = document.getElementById("frames-menu").style.display;
function setMenuVisible(willBeVisible){
    let menu = document.getElementById("frames-menu");
    if(willBeVisible){
        menu.style.display = defaultStyle;
    }
    else{
        menu.style.display = "none";
    }
}

function injectAsBackground(){
    let menu = document.getElementById("frames-textarea");
    menu.style.borderStyle = "none";
    menu.style.outlineStyle = "none";
    menu.style.resize = "none";
}

async function start(animationName){
    console.log("starting");

    console.log("clearing data");
    clearInterval(intervalID);
    JSONFile = undefined;
    FramesJSON = undefined;
    maxFrames = 0;

    console.log("downloading data");
    await GetFile(animationName);
    intervalID = setInterval(StreamAllFrames, frameSpeed);
}

async function GetFile(fileName){
    console.log("running : " + fileName);

    if(JSONFile === undefined){
        JSONFile = await fetch("/assets/txt/frames/" + fileName + ".json");
        console.log("setting JSONFile");
    }

    if(FramesJSON === undefined){
        FramesJSON = await JSONFile.json();
        console.log("setting FramesJSON");
    }

    if(maxFrames === 0){
        maxFrames = Object.keys(FramesJSON.animation).length;
        console.log("setting MaxFrames");
    }
}

async function runFrames(frameNumber){    
    document.getElementById("frames-textarea").textContent = await getFrame(frameNumber);
}

async function getFrame(frameNumber){
    return FramesJSON.animation[frameNumber];
}

async function StreamAllFrames(){
    frameNumber++;
    if(frameNumber > maxFrames){
        frameNumber = 1;
    }

    await runFrames(frameNumber);
}