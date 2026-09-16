var direction = "down";
var plate = [];
var nextStatePlate = [];
var movementList = [];
var plateSizeRow = 5;
var plateSizeCol = 5;
var plateSize = plateSizeCol * plateSizeRow;
var gametimer;

start();
function start(){
    console.log("start")

    createPlate(plate);
    createPlate(movementList);
    createPlate(nextStatePlate);

    plate[0][0] = 1;
    plate[1][0] = 1;
    plate[2][0] = 2;

    createTable();
    runGame();
}

function createPlate(variable) {
    for (let x = 0; x < plateSizeRow; x++) {
        variable[x] = [];
        for (let y = 0; y < plateSizeCol; y++) {
            variable[x][y] = 0;
        }
    }
}

document.onkeydown = function handleKeyDown(e){
    const key = e.keyCode;
    let newDirection;
    switch(key){
      case 37:
        newDirection = "left";
        break;
      case 38:
        newDirection = "up";
        break;
      case 39:
        newDirection = "right";
        break;
      case 40:
        newDirection = "down";
        break;
      case 32:
        start();
        return;
      default:
        return;
    }
    direction = newDirection;
    console.log(direction);
}

function gameLoop(){
    console.log("tick");
    console.log(movementList);
    nextStatePlate = createPlate(nextStatePlate);
    for (let x = 0; x < plateSizeRow; x++) {
        for (let y = 0; y < plateSizeCol; y++) {
            if(plate[x][y] == 2){
                PreMoveCell(x, y, direction, x, y);
            }
        }
    }
    //RunMouvement
}

function PreMoveCell(x, y, direction, prevX, prevY){
    console.log(x + " : " + y);
    let nextStateX; let nextStateY;

    switch(direction){
        case "left":
          movementList[x][y] = 1;
          nextStatePlate[x][y-1] = plate[x][y];
          nextStateX = x; nextStateY = y-1;
          break;
        case "up":
            movementList[x][y] = 2;
            nextStatePlate[x+1][y] = plate[x][y];
            nextStateX = x+1; nextStateY = y;
          break;
        case "right":
            movementList[x][y] = 3;
            nextStatePlate[x][y+1] = plate[x][y];
            nextStateX = x; nextStateY = y+1;
          break;
        case "down":
            movementList[x][y] = 4;
            nextStatePlate[x-1][y] = plate[x][y];
            nextStateX = x-1; nextStateY = y;
          break;
        default:
          return;
    }

    setMineText(nextStateX, nextStateY, getTD(nextStateX,nextStateY));

    var next = getNextBodyPart(x, y, prevX, prevY);
    if(next != null){
        PreMoveCell(next[0], next[1], getNextDirectionFromHead(next[0], next[1], x, y), x, y);

    }
    //setMineText(x, y, getTD(x,y));
}

function getNextDirectionFromHead(x, y, prevX, prevY){
    if(x - prevX >= 1){
        return "up";
    }
    else if (x - prevX <= -1){
        return "down";
    }
    else{
        if(y - prevY >= 1){
            return "right";
        }
        else if (y - prevY <= -1){
            return "left";
        }
    }
}

function getNextBodyPart(x, y, prevX, prevY){
    cell = plate[x][y];
    let cells = [[x+1,y],[x-1,y],[x,y+1],[x,y-1]];
    for(testCell in cells){
        CellToTest = cells[testCell];
        let x = CellToTest[0];
        let y = CellToTest[1];
        if(prevX != x && prevY != y){
            if(plate[x][y] != 0){
                return [x][y];
            }
        }        
    }
    return null;
}

function runGame(){
    gametimer = setInterval(gameLoop(), 500);
}

function createTable() {
    var table = document.createElement("table");

    var holder = document.createElement("div");

    var thead = document.createElement("thead");
    table.appendChild(thead);

    var tr = document.createElement("tr");
    thead.appendChild(tr);

    for (x = 0; x < plateSizeCol + 1; x++){
        var th = document.createElement("th");
        th.scope = "col";
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
        tr.appendChild(th);
        if (x == 0) {
            th.style.visibility = "hidden";
        }

        for (y = 0; y < plateSizeCol; y++){
            if(x!=0){
                var td = document.createElement("td");

                setMineText(x-1, y, td);

                tr.appendChild(td);
            }
        }

        var th = document.createElement("th");
        th.scope = "row";
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
        if (x == 0) {
            th.style.visibility = "hidden";
        }
        tr.appendChild(th);
    }

    var holder = document.getElementById("snake");
    holder.appendChild(table);
}

function setMineText(x, y, td) {
    var cell = plate[x][y];
    if (cell == 0) {
        td.textContent = " "; //No mine not discovered
        td.style.background = "var(--main-color)";
    }
    else if (cell == 1) {
        td.textContent = "🟩"; //Mine no cheat
        td.style.background = "var(--main-color)";
    }
    else if (cell == 2) {
        td.textContent = "🐍"; //Mine no cheat
        td.style.background = "var(--main-color)";
    }
}

function getTD(x, y) {
    //dont exist anymore //var plate = document.getElementById("plate");
    var table = document.querySelector("table");
    var tbodys = table.querySelector("tbody");
    var trs = tbodys.querySelectorAll("tr");
    var tds = trs[x].querySelectorAll("td");
    var td = tds[y];
    return td;
}