
async function startGame(){
    await loadFiles();
    updateGoldUI();
} startGame();

async function loadFiles(){
    await include_script("/src/js/content/idle-gold.js");
    await include_script("/src/js/content/idle-xp.js");
}

function getTimeStamp(){
    return new Date().getTime();
}

function GetDateAtTimeStamp(timeStamp){
    return new Date(timeStamp);
}

function getNumberOfActionBetweenTimeStamps(startTimeStamp, endTimeStamp, durationPerActionInMs){
    const timeDiff = endTimeStamp - startTimeStamp;
    return Math.floor(timeDiff / durationPerActionInMs);
}

function getDurationOfAction(action, actionlvl, playerLVLWithAction){
    let duration;

    switch(action){
        case "gather":
            duration = 1000;
            break;
        case "fight":
            duration = 2000;
            break;
        case "production":
            duration = 5000;
            break;
        case "moving":
            duration = 3000;
            break;
        default:
            duration = 1000;
    }

    duration = duration * (actionlvl + 1);
    duration = duration * (1 - playerLVLWithAction);

    return duration;
}

function getActionMultiplier(actionName) {
    switch (actionName) {
        case "gather":
            return 1.5;
        case "fight":
            return 3;
        case "production":
            return 2;
        default:
            return 1;
    }
}

function getListOfItemsInInventoryFromLocalStorage(){
    let storage = localStorage.getItem("playerInventory");
    if(storage === null){
        return [];
    }
    return JSON.parse(storage);
}

function saveListOfItemsInInventoryToLocalStorage(inventory){
    localStorage.setItem("playerInventory", JSON.stringify(inventory));
}

function addQuantityOfAnItem(itemName, quantity){
    let inventory = getListOfItemsInInventoryFromLocalStorage();
    let itemIndex = inventory.findIndex(item => item.name === itemName);
    if(itemIndex !== -1){
        inventory[itemIndex].quantity += quantity;
    } else {
        inventory.push({name: itemName, quantity: quantity});
    }
    saveListOfItemsInInventoryToLocalStorage(inventory);
}

function addPickAxeToInventory(inventory){
    addQuantityOfAnItem("PickAxe", 1);
}