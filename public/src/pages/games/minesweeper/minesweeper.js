
var minesAmount = 0;
var currentMinesAmount = 0;
var plateSizeRow = 0;
var plateSizeCol = 0;
var plateSize = plateSizeCol * plateSizeRow;
let plateMines = [];
var cheat = false;
var dateOfCreation;
var platePreGen = false;
var finish = false;
var sec = 0;

start(false);
function start(mustDeletePlate) {
    setInterval(
        function () {
            if(!finish){
                updateChrono();
            }
    }, 500);

    resetVar();

    if (mustDeletePlate) {
        deletePlate();
    }
    else {
        if (plateSizeCol < 1 || plateSizeRow < 1) {
            console.error("pas de taille de tableau logique")
            return;
        }

        if (minesAmount >= plateSize) {
            console.error("Trop de mine")
            return;
        }
    }
    createPlate();

    placeMines();

    createTable();
}

function updateChrono() {
    var dateHolder = document.getElementById("dateHolderMine");
    var nowDate = Date(Date.now);
    var now = Date.parse(nowDate);
    var prev = dateOfCreation;
    var diffTime = now - prev;
    sec = Math.round(diffTime / 1000);
    dateHolder.textContent = sec + " s";
}

function resetVar() {
    dateOfCreation = Date.now();
    minesAmount = Number(document.getElementById("Mines").value);
    currentMinesAmount = 0;
    plateSizeRow = Number(document.getElementById("Row").value);
    plateSizeCol = Number(document.getElementById("Col").value);
    plateSize = plateSizeCol * plateSizeRow;
    plateMines = [];
    cheat = Boolean(document.getElementById("cheatMode").checked);
    platePreGen = false;
    finish = false;
}

function deletePlate() {
    var plate = document.getElementById("plate");
    var table = plate.querySelector("table");
    if(table !== null){
        table.remove();
    }
}

function createPlate() {
    for (let x = 0; x < plateSizeRow; x++) {
        plateMines[x] = [];
        for (let y = 0; y < plateSizeCol; y++) {
            plateMines[x][y] = 0;
        }
    }
}

function getRandomInt(min, max) {
    const minCeiled = Math.ceil(min);
    const maxFloored = Math.floor(max);
    return Math.floor(Math.random() * (maxFloored - minCeiled) + minCeiled); 
}

function placeMines(){
    if(!platePreGen){
        while(currentMinesAmount < minesAmount){
            var cellX = getRandomInt(0, plateSizeRow);
            var cellY = getRandomInt(0, plateSizeCol);

            console.log("place mine");
            if (plateMines[cellX][cellY] == 0) {
                plateMines[cellX][cellY] = 1;
                currentMinesAmount++;
            }
            else if (plateMines[cellX][cellY] == 1) {
            }
            else { console.log("erreur placeMine"); }
        }
        platePreGen = true;
    }
}

function createTable() {
    var caption = document.getElementById("MineCaption");
    caption.textContent = "MineSweeper : " + minesAmount + " 💣";

    var table = document.createElement("table");
    table.addEventListener("contextmenu", e => { e.preventDefault(); });

    var holder = document.createElement("div");

    var thead = document.createElement("thead");
    table.appendChild(thead);

    var tr = document.createElement("tr");
    thead.appendChild(tr);

    for (x = 0; x < plateSizeCol + 1; x++){
        var th = document.createElement("th");
        th.scope = "col";
        th.textContent = String.fromCharCode(x+64);
        if (x == 0) {
            th.style.visibility = "hidden";
        }
        tr.appendChild(th);
    }

    var tbody = document.createElement("tbody");
    table.appendChild(tbody);

    for (x = 0; x < plateSizeRow + 1; x++){
        if(x!=0){
            var tr = document.createElement("tr");
            tbody.appendChild(tr);
        }
        var th = document.createElement("th");
        th.scope = "row";
        th.textContent = (x-1);
        tr.appendChild(th);
        if (x == 0) {
            th.style.visibility = "hidden";
        }

        for (y = 0; y < plateSizeCol; y++){
            if(x!=0){
                var td = document.createElement("td");

                setMineText(x-1, y, td);

                // add click event
                setMineClick(td);

                tr.appendChild(td);
            }
        }

        var th = document.createElement("th");
        th.scope = "row";
        th.textContent = x;
        tr.appendChild(th);
        if (x == 0) {
            th.style.visibility = "hidden";
        }
    }


    var thead = document.createElement("thead");
    table.appendChild(thead);

    var tr = document.createElement("tr");
    thead.appendChild(tr);

    for (x = 0; x < plateSizeCol + 1; x++) {
        var th = document.createElement("th");
        th.scope = "col";
        th.textContent = String.fromCharCode(x + 64);
        if (x == 0) {
            th.style.visibility = "hidden";
        }
        tr.appendChild(th);
    }

    var holder = document.getElementById("plate");
    holder.appendChild(table);
}

function setMineText(x, y, td) {
    var cell = plateMines[x][y];
    if (cell == 0) {
        td.textContent = " "; //No mine not discovered
        td.style.background = "var(--main-color)";
    }
    else if (cell == 1) {
        if (!cheat) {
            td.textContent = " "; //Mine no cheat
            td.style.background = "var(--main-color)";
        }
        else {
            td.textContent = "💣"; //Mine Cheater
            td.style.background = "red";
        }
    }
    else if (cell == 2) {
        td.textContent = "💣"; //Mine visible
        td.style.background = "red";
    }
    else if (cell == 3) {
        td.textContent = "🚩"; //Nothing under
    }
    else if (cell == 4) {
        td.textContent = "🚩"; //Mine under
    }
    else if (cell == 5) {
        var text = getNearbyBomb(x, y);
        if (text == 0) {
            td.textContent = " ";  // Unlocked // no mine
        }
        else { td.textContent = text; }
        td.style.background = "var(--background-color)";
    }
    else if (cell == 6) {
        td.textContent = "💀"; // Loosed
        td.style.background = "red";
    }
    else if (cell == 7) {
        td.textContent = "🏴‍☠️"; //Nothing under
        td.style.background = "red";
    }
    else if (cell == 8) {
        td.textContent = "🏴‍☠️"; //Mine under
        td.style.background = "red";
    }
    else {
        td.textContent = plateMines[x - 1][y];
    }
}

function checkIfHasWin() {
    var cell6 = 0;

    var cell1 = 0;
    var cell5 = 0;
    var cell8 = 0;

    for (x = 0; x < plateSizeRow + 1; x++) {
        if (x != 0) {
            for (y = 0; y < plateSizeCol; y++) {
                var cell = plateMines[x - 1][y];
                if (cell == 1) {
                    cell1++;
                }
                else if (cell == 5) {
                    cell5++;
                }
                else if (cell == 6) {
                    cell6++;
                }
                else if (cell == 8) {
                    cell8++;
                }
            }
        }
    }

    if (cell6 > 0 && !cheat) {
        finish = true;

        var caption = document.getElementById("MineCaption");
        caption.textContent = "MineSweeper : 💀 En " + sec + " secondes !";
    }
    else if (cheat) {
        //cant win with cheat
    }
    else{
        var total = cell1 + cell5 + cell8;
        if (total == plateSize) {
            finish = true;

            var caption = document.getElementById("MineCaption");
            caption.textContent = "MineSweeper : 🟢 En " + sec + " secondes !";
        }
    }
}

function setMineClick(td) {
    td.addEventListener('contextmenu', function (e) {
        e.preventDefault();
        var cellPose = getPoseOfCell(this);
        var x = (cellPose.x - 1);
        var y = (cellPose.y - 1);

        setFlag(x, y, td);

        checkIfHasWin();
    }, false);

    td.addEventListener("click", function (e) {
        var cellPose = getPoseOfCell(this);
        var x = (cellPose.x - 1);
        var y = (cellPose.y - 1);

        autoUnlockNearbyZone(x, y, td, true);
        clickCell(x, y, td, true);

        checkIfHasWin();
    });
}

function getPoseOfCell(td) {
    return {
        "x": (td.parentElement.rowIndex - 1),
        "y": td.cellIndex,
    };
}

function setFlag(x, y, td) {
    var cell = plateMines[x][y];
    if (cell == 0) {
        plateMines[x][y] = 7;
    }
    else if (cell == 1) {
        plateMines[x][y] = 8;
    }
    else if (cell == 3) {
        plateMines[x][y] = 0;
    }
    else if (cell == 4) {
        plateMines[x][y] = 1;
    }
    else if (cell == 7) {
        plateMines[x][y] = 3;
    }
    else if (cell == 8) {

        plateMines[x][y] = 4;
    }

    setMineText(x, y, td);
}

function clickCell(x, y, td) {
    var cell = plateMines[x][y];
    if (cell == 0 || cell == 3) {
        plateMines[x][y] = 5;
    }
    else if (cell == 1 || cell == 2 || cell == 4) {
        plateMines[x][y] = 6;
        //Game Over screen
    }    

    setMineText(x, y, td);
}

function getNearbyCell(x, y) {
    var plateMinesToCheck = new Array();
    for (i = -1; i < 2; i++) {
        for (i2 = -1; i2 < 2; i2++) {
            var cellToCheck = new Array();
            var x2 = x - i;
            var y2 = y - i2;

            if (x2 >= 0 && x2 < plateSizeRow && y2 >= 0 && y2 < plateSizeCol) {
                cellToCheck[0] = x2;
                cellToCheck[1] = y2;

                plateMinesToCheck.push(cellToCheck)
            }
        }
    }
    return plateMinesToCheck;
}

function autoUnlockNearbyZone(x, y, td, firstRun) {
    var cell = plateMines[x][y];
    if (cell == 0) {
        var mineAmout = getNearbyBomb(x, y);

        if (mineAmout < 1) {
            var cells = getNearbyCell(x, y);
            for (j = 0; j < cells.length; j++) {
                setTimeout(
                    function(cells, j) {
                        var newX = cells[j][0];
                        var newY = cells[j][1];

                        var tdx = getTD(newX, newY);

                        autoUnlockNearbyZone(newX, newY, tdx, false); 
                        clickCell(newX, newY, tdx, false);
                }, 50*j, cells, j);
            }
        }
    }
}

function getNearbyBomb(x, y) {
    var count = 0;

    var list = getNearbyCell(x, y);
    for (j = 0; j < (list.length); j++) {
        var x = list[j][0];
        var y = list[j][1];

        var cell = plateMines[x][y];
        if (cell == 1 || cell == 2 || cell == 4 || cell == 6 || cell == 8) {
            count++;
        }
    }
    return count;
}

function getTD(x, y) {
    var plate = document.getElementById("plate");
    var table = plate.querySelector("table");
    var tbodys = table.querySelector("tbody");
    var trs = tbodys.querySelectorAll("tr");
    var tds = trs[x].querySelectorAll("td");
    var td = tds[y];
    return td;
}