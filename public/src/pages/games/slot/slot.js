let CreditSlots = 5;
let RawSlots = 5;
let RawNumber = 3;

let currentCoins = 20;

let symbols = ["🍒", "🍋", "💲", "💎" ,"🔥"];
let value = [5, 10, 50, 100, 0];
let luck = [50, 25, 10, 5, 1];

setSlotMachine();
function setSlotMachine(){
    setSlotCredit(CreditSlots);
    setRawSlot(RawNumber);
    //setSlotOnRaw(RawSlots);
    //rotateSlot(RawSlots);
}

function setSlotCredit(SlotAmount){
    console.log("set credit slot");
    let creditFrame = document.getElementById("slot-machine-frame-credit");
    for (let slot = 0; slot < SlotAmount; slot++){
        let p = document.createElement("p");

        p.id = "slot-machine-frame-credit-"+slot;
        p.textContent = "0";
        p.style = "emptyCredit";

        console.log("set credit slot N°" + slot);
        creditFrame.appendChild(p);
    }
}

function setRawSlot(SlotAmount){
    console.log("set raws");
    let creditFrame = document.getElementById("slot-machine-frame-screen");
    for (let slot = 0; slot < SlotAmount; slot++){
        let p = document.createElement("div");

        p.id = "slot-machine-frame-screen-raw"+slot;
        p.style = "";

        creditFrame.appendChild(p);

        console.log("set raw N°" + slot);
    }
}

function setSlotOnRaw(SlotAmount){
    console.log("set raws slot");
    for (let slot = 0; slot < SlotAmount; slot++){
        let slotID = "slot-machine-frame-screen-raw"+slot;

        let element = document.getElementById(slotID);

        element.textContent = getRandomSlotObject();

        console.log("set raw item N°" + slot + " to " + element.textContent);
    }
}

function getRandomSlotObject(){
    let luck = Math.floor(Math.random() * 100) + 1;
    let next = true;
    let currentID = 0;
    while(next){
        if(luck >= luck[currentID]){
            next = false;
            console.log("Item : " + symbols[currentID]);
            return symbols[currentID];
        }
        else{
            currentID++;
        }
    }
}

function rotateSlot(SlotAmount){
    for (let slot = 0; slot < SlotAmount; slot++){
        let slotID = "slot-machine-frame-screen-raw"+slot;

        let element = document.getElementById(slotID);

        element.style.tran

	    document.querySelector("#box").style.animationPlayState = 'running';
    }
    //for slot, push px per px
    //when x (like 60) PX pushed, set to slotRawID+1
    //if in last slot, push to first again
    //to check wins, get raw 1,2,3
    //if can match a raw col, or gride, make win
}

function checkGride(){
    //check raw & col 1,2,3
    //make a gride of earning
}

function onPullLever(){
    alert("to do");
}