var character = document.getElementById("character");
var container = document.getElementById("container");
var containerProps = document.getElementById("container-props");

var characterSizePx = window.getComputedStyle(character).width;
var characterSize = characterSizePx.replace("px", "");
var containerSizePx = window.getComputedStyle(container).width;
var containerSize = containerSizePx.replace("px", "");

var containerHeightPx = window.getComputedStyle(container).height;
var containerHeight = containerHeightPx.replace("px", "");
var containerPropsHeightPx = window.getComputedStyle(containerProps).height;
var containerPropsHeight = containerPropsHeightPx.replace("px", "");
var characterHeightPx = window.getComputedStyle(character).height;
var characterHeight = characterHeightPx.replace("px", "");

var interval;
var keyPressed = false;

var score = 0;

const fruits = [
    { "level": 1, "points": 25, "src": "/assets/fruits/coconut.png" },
    { "level": 2, "points": 100, "src": "/assets/fruits/lime.png" },
    { "level": 3, "points": 250, "src": "/assets/fruits/plum.png" },
    { "level": 4, "points": 500, "src": "/assets/fruits/peach.png" },
    { "level": 5, "points": 1000, "src": "/assets/fruits/orange.png" },
    { "level": 6, "points": 2500, "src": "/assets/fruits/green-apple.png" },
    { "level": 7, "points": 4000, "src": "/assets/fruits/raspberry.png" },
    { "level": 8, "points": 5000, "src": "/assets/fruits/star-fruit.png" },
    { "level": 9, "points": 10000, "src": "/assets/fruits/watermelon.png" },
];

function moveLeft() {
    var left = parseInt(window.getComputedStyle(character).getPropertyValue("left"));
    if (left > 0) {
        character.style.left = left - 1 + "px";
    }
}
function moveRight() {
    var left = parseInt(window.getComputedStyle(character).getPropertyValue("left"));
    if (left < (containerSize - characterSize)) {
        character.style.left = left + 1 + "px"
    }
}

function spawnProps(path) {
    var props = document.createElement("img");

    containerProps.appendChild(props);

    props.src = path || fruits[0].src;

    props.classList.add("props");

    props.dataset.level = 1;

    var left = parseInt(window.getComputedStyle(character).getPropertyValue("left"));
    props.style.left = left + "px";

    var propsHeight = parseInt(window.getComputedStyle(props).getPropertyValue("height"));
    props.style.top = -propsHeight + "px";

    score += fruits[0].points;
    updateScore();
}

document.addEventListener("keydown", event => {
    if (keyPressed == false) {
        /*
        console.log("code " + event.code);
        console.log("Keycode " + event.keyCode);
        console.log("code " + event.key);
        */

        if (event.code === "ArrowLeft" || event.code === "KeyA") {
            keyPressed = true;
            interval = setInterval(moveLeft, 1);
        }

        if (event.code === "ArrowRight" || event.code === "KeyD") {
            keyPressed = true;
            interval = setInterval(moveRight, 1);
        }

        if (event.code === "Space" || event.code === "Enter") {
            spawnProps();
        }
    }
});

document.addEventListener("keyup", event => {
    clearInterval(interval);
    keyPressed = false;
});


var gameLoop = setInterval(function () {
    for (props of document.getElementsByClassName("props")) {
        var top = parseFloat(window.getComputedStyle(props).getPropertyValue("top"));

        var propseHeightPx = window.getComputedStyle(props).getPropertyValue("height");
        var propseHeight = parseFloat(propseHeightPx.replace("px", ""));

        var size = propseHeight;

        if ((top + size) < parseFloat(containerPropsHeight)) {
            if (props.dataset.collide != "true") {
                props.style.top = top + 1 + "px";
            } else {
                //push to a side
            }
        }
    }
}, 1);

var collideLoop = setInterval(function () {
    checkCollide();
}, 250);

window.addEventListener("gamepadconnected", function (e) {
    setInterval(function () {
        var gp = navigator.getGamepads()[e.gamepad.index];

        /*
        if (gp && gp.connected) {
            var axes = gp.axes;
            for (var i in axes) {
                if (axes[i] >= 0.3 || axes[i] <= -0.3) { console.log('axes[%s] value is: %s', i, axes[i]); };
            };


            var buttons = gp.buttons;
            for (var i in buttons) {
                if (buttons[i].pressed == true) { console.log("buttons[%s] pressed", i); };
            };
        };
        */

        if (gp && gp.connected && keyPressed == false) {
            var buttons = gp.buttons;
            var axes = gp.axes;

            if (buttons[14].pressed || buttons[4].pressed || buttons[6].pressed) {
                keyPressed = true;
                moveLeft();
            }
            if (buttons[15].pressed || buttons[5].pressed || buttons[7].pressed) {
                keyPressed = true;
                moveRight()
            }
            if (axes[0] <= -0.5 || axes[2] <= -0.5) {
                keyPressed = true;
                moveLeft();
            }
            if (axes[0] >= 0.5 || axes[2] >= 0.5) {
                keyPressed = true;
                moveRight();
            }
        }

        clearInterval(interval);
        keyPressed = false;

    }, 1);
});



function checkCollide() {
    var props = document.getElementById("container-props");
    for (const prop of props.children) {
        for (const prop2 of props.children) {
            if (prop !== prop2) {
                isColliding(prop, prop2);
            }
        }
    }
}

function isColliding(prop, prop2) {
    const propHeight = parseInt(window.getComputedStyle(prop).getPropertyValue("height"));
    const propWidth = parseInt(window.getComputedStyle(prop).getPropertyValue("width"));
    const prop2Height = parseInt(window.getComputedStyle(prop2).getPropertyValue("height"));
    const prop2Width = parseInt(window.getComputedStyle(prop2).getPropertyValue("width"));
    const dx = parseInt(window.getComputedStyle(prop).getPropertyValue("left")) - parseInt(window.getComputedStyle(prop2).getPropertyValue("left"));
    const dy = parseInt(window.getComputedStyle(prop).getPropertyValue("top")) - parseInt(window.getComputedStyle(prop2).getPropertyValue("top"));
    const distance = Math.sqrt(dx * dx + dy * dy);

    const radius = Math.sqrt(propWidth * propWidth + propHeight * propHeight) / 2;
    const radius2 = Math.sqrt(prop2Width * prop2Width + prop2Height * prop2Height) / 2;

    if (distance < radius + radius2) {
        afterCollide(prop, prop2);
    }
}

function afterCollide(prop, prop2) {
    PropTop = parseInt(window.getComputedStyle(prop).getPropertyValue("top"));
    PropLeft = parseInt(window.getComputedStyle(prop).getPropertyValue("left"));

    Prop2Top = parseInt(window.getComputedStyle(prop2).getPropertyValue("top"));
    Prop2Left = parseInt(window.getComputedStyle(prop2).getPropertyValue("left"));

    medianneTop = ((PropTop + Prop2Top) / 2);
    medianneLeft = ((PropLeft + Prop2Left) / 2);

    var level = prop.dataset.level;
    var level2 = prop2.dataset.level;

    if (level === level2) {
        if (fruits[level] !== undefined) {
            mergeProps(level, medianneTop, medianneLeft);

            prop.remove();
            prop2.remove();
        }
    }
    else {
        setInterval(function () {
            //Patch this
            /*
            It push both prop à tour de rôle
            */
            var propToPush;
            if (PropTop < Prop2Top) {
                propToPush = prop;
            } else {
                propToPush = prop2;
            }
            prop.dataset.collide = true;
            prop2.dataset.collide = true;

            console.log("propToPush " + propToPush.src);
            //
            if (PropLeft < Prop2Left) {
                //Push propToPush 
                PropToPushLeft = parseInt(window.getComputedStyle(propToPush).getPropertyValue("left"));
                propToPush.style.left = PropToPushLeft - 1 + "px";
            } else {
                PropToPushLeft = parseInt(window.getComputedStyle(propToPush).getPropertyValue("left"));
                propToPush.style.left = PropToPushLeft + 1 + "px";
            }
        }, 100);
    }
}

function mergeProps(level, top, left) {
    var newProp = document.createElement("img");

    containerProps.appendChild(newProp);

    newProp.src = fruits[level].src;
    newProp.dataset.level = fruits[level].level;

    newProp.className = "props";

    newProp.style.left = left + "px";
    newProp.style.top = top + "px";

    newProp.style.scale = fruits[level].scale;

    score += fruits[level - 1].points;
    updateScore();
}

function updateScore() {
    var scoreTag = document.getElementById("score");
    scoreTag.textContent = score;
}