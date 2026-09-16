var multiplyer = 2;

var sizeDefault = 175; //px
var size = sizeDefault * multiplyer;

var layerDefault = 10;
var layer = layerDefault * multiplyer;

spawnCube("cubeHolder");

function spawnCube(divID) {
    console.log("Loading 3D cube");
    var cubeHolder = document.getElementById(divID);
    for (i = 0; i < 20; i++){
        var div = document.createElement("div");
        div.id = "cube" + i;
        div.className = "cube";

        var translateFormula = i * size / layer - size / 2;
        div.style.transform = "translateZ(" + translateFormula + "px)";


        var childDiv = document.createElement("div");
        div.appendChild(childDiv);

        childDiv.id = "cubeSlice" + i;
        childDiv.className = "sphereSlice"; 
        childDiv.style.animationDelay = (i * 200 + "ms");

        cubeHolder.appendChild(div); 
    }
}