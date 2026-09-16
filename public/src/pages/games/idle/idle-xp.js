

function XPLootPerAction(action, actionlvl, playerLVLWithAction){
    let xpLooted;

    switch(action){
       case "gather":
            duration = 10;
            break;
        case "fight":
            duration = 10;
            break;
        case "production":
            duration = 10;
            break;
        case "moving":
            duration = 1;
            break;
        default:
            duration = 0;
    }

    xpLooted = xpLooted * (actionlvl + 1);
    xpLooted = xpLooted * (1 + playerLVLWithAction);

    return xpLooted;
}

function addXPOfAnAction(actionName, actionXP){
    let playerXP = getActionXPHandlerFromLocalStorage();

    if(playerXP[actionName]){
        playerXP[actionName] += actionXP;
    } else {
        playerXP[actionName] = actionXP;
    }

    saveActionXPHandlerToLocaStorage(playerXP);
}

function getActionXPHandlerFromLocalStorage(){
    let storage = localStorage.getItem("playerXP");
    let playerXP;

    if(storage === null){
        playerXP = {};
    }
    else{
        playerXP = JSON.parse(storage);
    }

    return playerXP;
}

function getActionXP(actionName){
    let playerXP = getActionXPHandlerFromLocalStorage();

    return playerXP[actionName] || 0;
}

function getLvlFromXPOfAction(actionName) {
    let xp = getActionXP(actionName);
    let level = 0;
    let xpForNextLevel = 100;

    while (xp >= xpForNextLevel) {
        level++;
        xp -= xpForNextLevel;
        xpForNextLevel = Math.floor(xpForNextLevel * getActionMultiplier(actionName));
    }

    return level;
}

function saveActionXPHandlerToLocaStorage(actionXP){
    localStorage.setItem("playerXP", JSON.stringify(playerXP));
}